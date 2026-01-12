<?php
// admin/manage_complaints.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Level 3 (Branch Admin) or Level 1 (Super Admin) only
$level = $_SESSION['level'] ?? 5;
if ($level != 3 && $level != 1) {
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Handle Status Update
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status']; // in_progress, resolved
    $conn->query("UPDATE complaints SET status='$status' WHERE id=$id");
    header("Location: manage_complaints.php");
    exit();
}

// Fetch Complaints
$sql = "SELECT c.*, u.name as user_name FROM complaints c JOIN users u ON c.user_id = u.id ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Support - Smart Residence ERP</title>
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
        /* Sidebar Styling */
        /* Sidebar Styling defined in style.css */

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 3rem;
            min-height: 100vh;
            background-color: var(--bg-body);
        }

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        h2,
        h4,
        h5 {
            color: var(--text-main);
        }

        .text-dark {
            color: var(--text-main) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .table thead th {
            background: var(--bg-hover);
            border: none;
            padding: 15px 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .table tbody td {
            padding: 18px 20px;
            font-weight: 600;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
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
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <?php
    $active_page = 'manage_complaints.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Help Desk</h2>
                <p class="text-muted fw-600 mb-0">Manage and resolve resident complaints.</p>
            </div>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Resident</th>
                            <th>Issue Detail</th>
                            <th>Status</th>
                            <th class="text-end">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-800 text-primary small">#CMP-<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-800"><?php echo $row['user_name']; ?></div>
                                        <div class="small text-muted fw-600">
                                            <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-800"><?php echo $row['subject']; ?></div>
                                        <div class="small text-muted fw-600 text-truncate" style="max-width: 250px;">
                                            <?php echo $row['description']; ?>
                                        </div>
                                    </td>
                                    <td>
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
                                        <span
                                            class="status-badge bg-<?php echo $cls; ?> bg-opacity-10 text-<?php echo $cls; ?>">
                                            <?php echo str_replace('_', ' ', $s); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <?php if ($s == 'open'): ?>
                                                <a href="?id=<?php echo $row['id']; ?>&status=in_progress"
                                                    class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-800">Assign</a>
                                                <a href="?id=<?php echo $row['id']; ?>&status=resolved"
                                                    class="btn btn-outline-success btn-sm rounded-pill px-3 fw-800">Resolve</a>
                                            <?php elseif ($s == 'in_progress'): ?>
                                                <a href="?id=<?php echo $row['id']; ?>&status=resolved"
                                                    class="btn btn-success btn-sm rounded-pill px-3 fw-800 shadow-sm">Mark
                                                    Resolved</a>
                                            <?php else: ?>
                                                <span class="small text-success fw-800 opacity-75"><i
                                                        class="fas fa-check-circle me-1"></i> Closed</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted fw-600">No complaints reported.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>

</html>