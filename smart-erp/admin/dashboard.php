<?php
// admin/dashboard.php
session_start();
require_once '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$user_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'")->fetch_assoc()['count'];
$service_count = $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM service_requests WHERE status='pending'")->fetch_assoc()['count'];
$completed_requests = $conn->query("SELECT COUNT(*) as count FROM service_requests WHERE status='completed'")->fetch_assoc()['count'];
$pending_docs = $conn->query("SELECT COUNT(*) as count FROM user_documents WHERE status='pending'")->fetch_assoc()['count'];

// Recent Requests (Limit 5)
$recent_sql = "SELECT r.*, u.name as user_name, s.name as service_name 
               FROM service_requests r 
               JOIN users u ON r.user_id = u.id 
               JOIN services s ON r.service_id = s.id 
               ORDER BY r.created_at DESC LIMIT 5";
$recent_requests = $conn->query($recent_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Residence ERP</title>
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

        .btn-primary-custom {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 800;
        }

        .badge-status {
            font-weight: 800;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
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
            <!-- Mobile Header -->
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
                        <h4 class="fw-800 mb-1">Administrative Control</h4>
                        <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">
                            System Overview & Management
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-md-block">
                            <div class="fw-800 text-main"><?php echo $_SESSION['username'] ?? 'Admin'; ?></div>
                            <div class="small text-muted fw-600">Level <?php echo $_SESSION['level']; ?> Admin</div>
                        </div>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-800"
                            style="width: 48px; height: 48px; font-size: 1.2rem;">
                            <i class="fas fa-user-shield"></i>
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
                            $greeting = 'Hello';
                        ?>
                        <h1 class="display-6 fw-800 mb-2"><?php echo $greeting; ?>, Admin!</h1>
                        <p class="lead opacity-75 fw-500 mb-0">Here is what is happening in your residence today.</p>
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

                <!-- Stats Grid -->
                <div class="row g-4 mb-5">
                    <div class="col-xl-3 col-md-6">
                        <div class="card-custom">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h6 class="text-muted fw-700 small text-uppercase mb-1">Total Residents</h6>
                            <h2 class="fw-900 mb-0 text-main"><?php echo number_format($user_count); ?></h2>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card-custom">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <h6 class="text-muted fw-700 small text-uppercase mb-1">Services Active</h6>
                            <h2 class="fw-900 mb-0 text-main"><?php echo number_format($service_count); ?></h2>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card-custom">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h6 class="text-muted fw-700 small text-uppercase mb-1">Pending Orders</h6>
                            <h2 class="fw-900 mb-0 text-main"><?php echo number_format($pending_requests); ?></h2>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card-custom">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <h6 class="text-muted fw-700 small text-uppercase mb-1">Pending Docs</h6>
                            <h2 class="fw-900 mb-0 text-main"><?php echo number_format($pending_docs); ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Recent Activity Table -->
                    <div class="col-lg-8">
                        <div class="card-custom">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-800 mb-0">Recent Service Requests</h5>
                                <a href="service_requests.php"
                                    class="text-primary text-decoration-none fw-800 small text-uppercase">View All</a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr class="text-muted small fw-700 text-uppercase">
                                            <th class="border-0 pb-3">Resident</th>
                                            <th class="border-0 pb-3">Service</th>
                                            <th class="border-0 pb-3">Date</th>
                                            <th class="border-0 pb-3 text-end">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_requests->num_rows > 0): ?>
                                            <?php while ($row = $recent_requests->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="border-0">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="bg-light rounded-circle p-2 text-muted fw-800 d-flex align-items-center justify-content-center"
                                                                style="width: 35px; height: 35px;">
                                                                <?php echo strtoupper(substr($row['user_name'], 0, 1)); ?>
                                                            </div>
                                                            <span
                                                                class="fw-700 text-main"><?php echo $row['user_name']; ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="border-0">
                                                        <span
                                                            class="fw-600 text-muted"><?php echo $row['service_name']; ?></span>
                                                    </td>
                                                    <td class="border-0 text-muted fw-600 small">
                                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                                    </td>
                                                    <td class="border-0 text-end">
                                                        <?php
                                                        $statusClass = 'bg-secondary text-white';
                                                        if ($row['status'] == 'pending')
                                                            $statusClass = 'bg-warning bg-opacity-10 text-warning-emphasis';
                                                        elseif ($row['status'] == 'completed')
                                                            $statusClass = 'bg-success bg-opacity-10 text-success';
                                                        elseif ($row['status'] == 'rejected')
                                                            $statusClass = 'bg-danger text-white';
                                                        ?>
                                                        <span class="badge-status <?php echo $statusClass; ?>">
                                                            <?php echo strtoupper($row['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">No recent requests
                                                    found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-lg-4">
                        <div class="card-custom">
                            <h5 class="fw-800 mb-4">Quick Management</h5>
                            <div class="d-grid gap-3">
                                <a href="manage_services.php"
                                    class="btn btn-primary-custom d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-concierge-bell"></i> Manage Services
                                </a>
                                <a href="manage_notices.php"
                                    class="btn btn-outline-primary fw-800 py-3 rounded-4 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-bullhorn"></i> Post Notice
                                </a>
                                <div class="p-3 rounded-4 bg-light mt-2">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <i class="fas fa-info-circle text-primary"></i>
                                        <span class="fw-700 text-main small">System Status</span>
                                    </div>
                                    <p class="mb-0 small text-muted lh-sm">All systems operational. Database connection
                                        stable.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/ui-settings.js"></script>
</body>

</html>