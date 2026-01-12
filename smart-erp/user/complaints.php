<?php
// user/complaints.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lodge_complaint'])) {
    $subject = $conn->real_escape_string($_POST['subject']);
    $description = $conn->real_escape_string($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if ($conn->query("INSERT INTO complaints (user_id, subject, description, status) VALUES ('$user_id', '$subject', '$description', 'open')")) {
        $success = "Complaint lodged successfully. We will address it soon.";
    }
}

// Fetch Complaints
$complaints = $conn->query("SELECT * FROM complaints WHERE user_id={$_SESSION['user_id']} ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - Smart Residence</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <script>
        window.currentUserId = '<?php echo $_SESSION['user_id'] ?? "default"; ?>';
    </script>
    <script src="../assets/js/theme-head.js?v=<?php echo time(); ?>"></script>
    <link rel="stylesheet" href="../assets/css/logout_animation.css">

    <style>
        /* Shared Dashboard Layout */
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
            transition: all 0.3s;
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

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            height: 100%;
        }

        .complaint-item {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }

        .complaint-item:hover {
            transform: scale(1.01);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border-color: var(--primary);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .btn-lodge {
            background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);
            border: none;
            color: white;
            padding: 15px;
            border-radius: 16px;
            font-weight: 800;
            transition: all 0.3s;
        }

        .btn-lodge:hover {
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
            transform: translateY(-2px);
            color: white;
        }

        h4,
        h5,
        h6 {
            color: var(--text-main);
        }

        .text-dark {
            color: var(--text-main) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .form-control {
            background-color: var(--bg-input);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .form-control:focus {
            background-color: var(--bg-input);
            color: var(--text-main);
            border-color: var(--primary);
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
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
        <?php $active_page = 'complaints.php';
        include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h4 class="fw-800 mb-1">Help & Support</h4>
                    <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">We're here to
                        solve your problems</p>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-800"
                    style="width: 48px; height: 48px;">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
            </header>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card-custom">
                        <h4 class="fw-800 mb-4 text-danger"><i class="fas fa-bug me-2"></i>Report An Issue</h4>

                        <?php if ($success): ?>
                            <div class="alert alert-success border-0 rounded-4 mb-4 p-3 fw-700 shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="lodge_complaint" value="1">
                            <div class="mb-3">
                                <label class="form-label fw-800 small text-muted text-uppercase">Subject</label>
                                <input type="text" name="subject" class="form-control rounded-4 py-3 fw-700 px-4"
                                    placeholder="e.g. Elevator Maintenance" required>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-800 small text-muted text-uppercase">Category</label>
                                    <select name="category" id="category" class="form-select rounded-4 py-3 fw-700">
                                        <option value="General">General</option>
                                        <option value="Plumbing">Plumbing</option>
                                        <option value="Electrical">Electrical</option>
                                        <option value="Cleaning">Cleaning</option>
                                        <option value="Security">Security</option>
                                        <option value="Lift">Lift/Elevator</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-800 small text-muted text-uppercase">Priority
                                        (Auto)</label>
                                    <select name="priority" id="priority" class="form-select rounded-4 py-3 fw-700">
                                        <option value="Low">Low</option>
                                        <option value="Medium">Medium</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-800 small text-muted text-uppercase">Full
                                    Description</label>
                                <textarea name="description" id="complaint_text"
                                    class="form-control rounded-4 py-3 fw-700 px-4" rows="6"
                                    placeholder="Describe the issue in detail..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-lodge w-100 shadow-sm">
                                Lodge Ticket <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-7">
                    <h5 class="fw-800 mb-4">Tracking Your Tickets</h5>
                    <?php if ($complaints->num_rows > 0): ?>
                        <?php while ($row = $complaints->fetch_assoc()): ?>
                            <div class="complaint-item">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-800 mb-0"><?php echo $row['subject']; ?></h6>
                                    <?php
                                    $s = $row['status'];
                                    $cls = 'secondary';
                                    if ($s == 'open')
                                        $cls = 'danger';
                                    elseif ($s == 'in_progress')
                                        $cls = 'warning';
                                    elseif ($s == 'resolved')
                                        $cls = 'success';
                                    ?>
                                    <span class="status-badge bg-<?php echo $cls; ?> bg-opacity-10 text-<?php echo $cls; ?>">
                                        <?php echo str_replace('_', ' ', $s); ?>
                                    </span>
                                </div>
                                <p class="text-muted fw-600 mb-3 small"><?php echo $row['description']; ?></p>
                                <div class="text-muted small fw-800 opacity-75">
                                    <i class="far fa-clock me-1"></i> Reported:
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card-custom text-center py-5">
                            <i class="fas fa-shield-heart fa-4x text-light mb-4"
                                style="color: var(--text-muted) !important; opacity: 0.3;"></i>
                            <h5 class="fw-800">Clear Record!</h5>
                            <p class="text-muted fw-600 mb-0">You haven't reported any issues yet. Everything seems great.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/smart-complaints.js"></script>
</body>

</html>