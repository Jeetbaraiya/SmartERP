<?php
// user/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// Check User
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

include 'includes/auto_expire.php';

$user_id = $_SESSION['user_id'];

// Get Stats
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM service_requests WHERE user_id=$user_id AND status='pending'")->fetch_assoc()['count'];
$completed_requests = $conn->query("SELECT COUNT(*) as count FROM service_requests WHERE user_id=$user_id AND status='completed'")->fetch_assoc()['count'];

// Recent History
$history = $conn->query("SELECT r.*, s.name as service_name FROM service_requests r JOIN services s ON r.service_id = s.id WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 5");

// Fetch Notices
$notices = $conn->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Smart Residence</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <script>
        window.currentUserId = '<?php echo $_SESSION['user_id'] ?? "default"; ?>';
    </script>
    <script src="../assets/js/theme-head.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="../assets/css/logout_animation.css">

    <style>
        /* Sidebar Layout - leveraging global vars */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            padding: 30px 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
            background-color: var(--bg-body);
        }

        .nav-logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding: 0 10px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
        }

        .notices-scroll {
            max-height: 140px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .notices-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .notices-scroll::-webkit-scrollbar-track {
            background: var(--bg-hover);
            border-radius: 10px;
        }

        .notices-scroll::-webkit-scrollbar-thumb {
            background: var(--text-muted);
            border-radius: 10px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 700;
            border-radius: 16px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: var(--bg-hover);
            color: var(--primary);
        }

        .sidebar-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .sidebar-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        /* Dashboard Cards */
        .welcome-card {
            background: var(--primary);
            border-radius: 32px;
            padding: 45px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
            margin-bottom: 40px;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            right: -50px;
            top: -50px;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            height: 100%;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        /* Activity Table */
        .table-custom tr {
            border-bottom: 1px solid var(--border-color);
        }

        .table-custom tr:last-child {
            border-bottom: none;
        }

        .table-custom td {
            padding: 20px 0;
            vertical-align: middle;
            color: var(--text-main);
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-primary-custom {
            background: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary-custom:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .badge-status {
            font-weight: 800;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .bg-light {
            background-color: var(--bg-hover) !important;
        }

        .text-dark {
            color: var(--text-main) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <?php $active_page = 'dashboard.php';
        include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Mobile Header (Visible only on mobile) -->
            <div
                class="d-flex justify-content-between align-items-center d-lg-none mb-4 p-3 bg-white rounded shadow-sm">
                <button id="mobileSidebarToggle" class="btn btn-light text-primary">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                <span class="h5 mb-0 font-weight-bold text-primary">SMART ERP</span>
                <div style="width: 40px;"></div> <!-- Spacer -->
            </div>

            <div class="welcome-banner mb-4">
                <!-- Header -->
                <header class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h4 class="fw-800 mb-1">Experience Luxury</h4>
                        <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">Smart
                            Residence
                            Resident Panel</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-md-block">
                            <div class="fw-800 text-main"><?php echo $_SESSION['name']; ?></div>
                            <div class="small text-muted fw-600">Resident User</div>
                        </div>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-800"
                            style="width: 48px; height: 48px; font-size: 1.2rem;">
                            <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                        </div>
                    </div>
                </header>

                <!-- Welcome Hero -->
                <div class="welcome-card d-flex flex-wrap justify-content-between align-items-center gap-4">
                    <div>
                        <?php
                        $hour = date('H');
                        if ($hour >= 5 && $hour < 12)
                            $greeting = 'Good Morning';
                        elseif ($hour >= 12 && $hour < 17)
                            $greeting = 'Good Afternoon';
                        elseif ($hour >= 17 && $hour < 21)
                            $greeting = 'Good Evening';
                        else
                            $greeting = 'Good Night';

                        $firstName = explode(' ', $_SESSION['name'])[0];
                        ?>
                        <h1 class="display-6 fw-800 mb-2 text-white"><span
                                id="greeting-text"><?php echo $greeting; ?></span>,
                            <?php echo $firstName; ?>!
                        </h1>
                        <script>
                            function updateGreeting() {
                                const hour = new Date().getHours();
                                let greeting = 'Good Night'; // Default covers 21-23 and 0-4
                                if (hour >= 5 && hour < 12) greeting = 'Good Morning';
                                else if (hour >= 12 && hour < 17) greeting = 'Good Afternoon';
                                else if (hour >= 17 && hour < 21) greeting = 'Good Evening';

                                document.getElementById('greeting-text').innerText = greeting;
                            }
                            updateGreeting(); // Run immediately
                            setInterval(updateGreeting, 60000); // Run every minute
                        </script>
                        <p class="lead opacity-75 fw-500 mb-0 text-white">Manage your home, book services, and stay
                            secure.</p>
                    </div>
                    <!-- Glassy date box -->
                    <div class="p-4 rounded-4 shadow-sm"
                        style="background: rgba(255,255,255,0.1); backdrop-filter: blur(5px);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle p-2 text-primary d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <div class="small opacity-75 fw-600 text-uppercase"
                                    style="font-size: 0.65rem; color: white;">Today's Date
                                </div>
                                <div class="fw-800 fs-5 text-white"><?php echo date('D, M d'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <!-- Stat Cards -->
                    <div class="col-md-4">
                        <div class="card-custom">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <h6 class="text-muted fw-700 small text-uppercase mb-1">Active Requests</h6>
                            <h2 class="fw-900 mb-0 text-main"><?php echo $pending_requests; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-custom">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <h6 class="text-muted fw-700 small text-uppercase mb-1">Total Completed</h6>
                            <h2 class="fw-900 mb-0 text-main"><?php echo $completed_requests; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-custom">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <h6 class="text-muted fw-700 small text-uppercase mb-1">Total Spending</h6>
                            <h2 class="fw-900 mb-0 text-main">₹0</h2>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Recent Activities -->
                    <div class="col-lg-8">
                        <div class="card-custom">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-800 mb-0">Recent Activity History</h5>
                                <a href="my_requests.php"
                                    class="text-primary text-decoration-none fw-800 small text-uppercase"
                                    style="letter-spacing: 1px;">View All History</a>
                            </div>

                            <?php if ($history && $history->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-custom mb-0">
                                        <thead>
                                            <tr class="text-muted small fw-700 text-uppercase" style="letter-spacing: 1px;">
                                                <th class="border-0 pb-3">Service</th>
                                                <th class="border-0 pb-3">Date</th>
                                                <th class="border-0 pb-3 text-end">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $history->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="border-0">
                                                        <div class="fw-800 text-main"><?php echo $row['service_name']; ?></div>
                                                        <div class="small text-muted fw-600">ID:
                                                            #SR-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></div>
                                                    </td>
                                                    <td class="border-0 text-muted fw-700">
                                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                                    </td>
                                                    <td class="border-0 text-end">
                                                        <span class="badge-status <?php
                                                        if ($row['refund_status'] == 'refunded')
                                                            echo 'bg-info bg-opacity-10 text-info-emphasis';
                                                        elseif ($row['status'] == 'completed')
                                                            echo 'bg-success bg-opacity-10 text-success';
                                                        elseif ($row['status'] == 'rejected')
                                                            echo 'bg-danger text-white';
                                                        else
                                                            echo 'bg-warning bg-opacity-10 text-warning-emphasis';
                                                        ?>">
                                                            <?php
                                                            if ($row['refund_status'] == 'refunded')
                                                                echo 'REFUNDED';
                                                            else
                                                                echo strtoupper($row['status']);
                                                            ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4"
                                        style="width: 80px; height: 80px;">
                                        <i class="fas fa-box-open fa-2x text-muted"></i>
                                    </div>
                                    <h6 class="fw-800 mb-2">No Activities Yet</h6>
                                    <p class="text-muted fw-600 small mb-4">Book your first professional home service today.
                                    </p>
                                    <a href="services.php" class="btn btn-primary-custom btn-action">Start New Booking</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notices & Community -->
                    <div class="col-lg-4">
                        <div class="card-custom mb-4">
                            <h5 class="fw-800 mb-4">Community Updates</h5>
                            <div class="notices-scroll">
                                <?php if ($notices && $notices->num_rows > 0): ?>
                                    <?php while ($notice = $notices->fetch_assoc()): ?>
                                        <div class="notice-item p-3 rounded-4 mb-3 border-start border-4 border-primary"
                                            style="background: var(--bg-hover);">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span
                                                    class="small text-muted fw-700"><?php echo date('M d', strtotime($notice['created_at'])); ?></span>
                                                <span class="badge bg-primary rounded-pill px-2 py-1 fw-800"
                                                    style="font-size: 0.55rem;">NEW</span>
                                            </div>
                                            <h6 class="fw-800 text-main mb-1"><?php echo $notice['title']; ?></h6>
                                            <p class="small text-muted fw-600 mb-0">
                                                <?php echo substr($notice['message'], 0, 70); ?>...
                                            </p>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 rounded-4" style="background: var(--bg-hover);">
                                        <p class="text-muted fw-700 small mb-0">No active notices.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card-custom"
                            style="background: linear-gradient(rgba(59, 130, 246, 0.05), transparent);">
                            <h5 class="fw-800 mb-4">Professional Help</h5>
                            <div class="d-grid gap-3">
                                <a href="services.php"
                                    class="btn btn-primary-custom btn-action d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-plus-circle"></i> New Service Request
                                </a>
                                <a href="complaints.php"
                                    class="btn btn-outline-primary btn-action d-flex align-items-center justify-content-center gap-2 border-2">
                                    <i class="fas fa-comment-dots"></i> Report an Issue
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                </main>
            </div>

            <!-- Chatbot -->
            <?php include 'includes/chatbot_widget.php'; ?>

            <!-- Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="../assets/js/logout_animation.js"></script>
            <script src="../assets/js/theme.js"></script>
</body>

</html>