<?php
// admin/manage_email_requests.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Super Admin (Level 1) only
$level = $_SESSION['level'] ?? 5;
if ($level != 1) {
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Handle Approve Request
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'approve') {
    $id = intval($_GET['id']);
    $admin_id = $_SESSION['user_id'];

    // Get request details
    $result = $conn->query("SELECT user_id, new_email FROM email_change_requests WHERE id=$id AND status='pending'");
    if ($row = $result->fetch_assoc()) {
        // Update user email
        $new_email = $conn->real_escape_string($row['new_email']);
        $user_id = $row['user_id'];

        $conn->query("UPDATE users SET email='$new_email' WHERE id=$user_id");

        // Mark request as approved
        $conn->query("UPDATE email_change_requests SET status='approved', approved_by=$admin_id, approved_at=NOW() WHERE id=$id");
    }

    header("Location: manage_email_requests.php?msg=approved");
    exit();
}

// Handle Reject Request
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'reject') {
    $id = intval($_GET['id']);
    $admin_id = $_SESSION['user_id'];

    // Mark request as rejected
    $conn->query("UPDATE email_change_requests SET status='rejected', approved_by=$admin_id, approved_at=NOW() WHERE id=$id");

    header("Location: manage_email_requests.php?msg=rejected");
    exit();
}

// Fetch Email Change Requests
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';
$where_clause = $filter == 'all' ? '' : "WHERE r.status='$filter'";

$requests = $conn->query("SELECT r.*, u.name as user_name, u.email as current_email, a.name as admin_name 
                          FROM email_change_requests r 
                          JOIN users u ON r.user_id = u.id 
                          LEFT JOIN users a ON r.approved_by = a.id 
                          $where_clause
                          ORDER BY r.requested_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Change Requests - Smart Residence Admin</title>
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
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 40px;
            width: calc(100% - var(--sidebar-width));
            background-color: var(--bg-body);
            min-height: 100vh;
        }

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.03);
            padding: 30px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .filter-btn {
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 2px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        h2,
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

        /* Table Styles */
        .table {
            color: var(--text-main);
        }

        .table thead th {
            background-color: var(--bg-hover);
            color: var(--text-muted);
            border: none;
        }

        .table td {
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
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
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <?php
    $active_page = 'manage_email_requests.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Email Change Requests</h2>
                <p class="text-muted fw-600">Review and approve user email change requests.</p>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-4 shadow-sm fw-700">
                <i class="fas fa-envelope-open-text me-2"></i>
                <?php echo $requests->num_rows; ?> Requests
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 d-flex gap-2">
            <a href="?filter=pending" class="filter-btn <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending
            </a>
            <a href="?filter=approved" class="filter-btn <?php echo $filter == 'approved' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Approved
            </a>
            <a href="?filter=rejected" class="filter-btn <?php echo $filter == 'rejected' ? 'active' : ''; ?>">
                <i class="fas fa-times"></i> Rejected
            </a>
            <a href="?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                <?php
                if ($_GET['msg'] == 'approved')
                    echo '<i class="fas fa-check-circle me-2"></i> Email change request approved successfully!';
                if ($_GET['msg'] == 'rejected')
                    echo '<i class="fas fa-times-circle me-2"></i> Email change request rejected.';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 p-3 rounded-start-3">User</th>
                            <th class="border-0 p-3">Current Email</th>
                            <th class="border-0 p-3">Requested Email</th>
                            <th class="border-0 p-3">Requested On</th>
                            <th class="border-0 p-3 text-center">Status</th>
                            <th class="border-0 p-3 text-end rounded-end-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($requests->num_rows > 0): ?>
                            <?php while ($row = $requests->fetch_assoc()): ?>
                                <tr>
                                    <td class="p-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div
                                                style="width:40px; height:40px; background:var(--bg-hover); border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:800; color:var(--text-muted);">
                                                <?php echo strtoupper(substr($row['user_name'], 0, 1)); ?>
                                            </div>
                                            <div class="fw-700">
                                                <?php echo htmlspecialchars($row['user_name']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 fw-600 text-muted">
                                        <?php echo htmlspecialchars($row['old_email']); ?>
                                    </td>
                                    <td class="p-3 fw-700 text-primary">
                                        <?php echo htmlspecialchars($row['new_email']); ?>
                                    </td>
                                    <td class="p-3 text-muted fw-600">
                                        <?php echo date('M d, Y h:i A', strtotime($row['requested_at'])); ?>
                                    </td>
                                    <td class="p-3 text-center">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <span class="status-badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                        <?php elseif ($row['status'] == 'approved'): ?>
                                            <span class="status-badge bg-success bg-opacity-10 text-success">Approved</span>
                                        <?php else: ?>
                                            <span class="status-badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-end">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="manage_email_requests.php?id=<?php echo $row['id']; ?>&action=approve"
                                                class="btn btn-success btn-sm rounded-3 me-1"
                                                onclick="return confirm('Approve this email change request?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="manage_email_requests.php?id=<?php echo $row['id']; ?>&action=reject"
                                                class="btn btn-danger btn-sm rounded-3"
                                                onclick="return confirm('Reject this email change request?');">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php else: ?>
                                            <small class="text-muted">
                                                By:
                                                <?php echo htmlspecialchars($row['admin_name'] ?? 'Unknown'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted fw-600">No requests found.</td>
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