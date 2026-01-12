<?php
// admin/service_requests.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$admin_level = $_SESSION['level'] ?? 5;

// Handle Status Update
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    $req = $conn->query("SELECT approval_stage, status FROM service_requests WHERE id=$id")->fetch_assoc();

    if ($req) {
        if ($action == 'approve') {
            // Immediate Final Approval (Single-Tier)
            $conn->query("UPDATE service_requests SET approval_stage=0, status='approved' WHERE id=$id");
            /* Deprecated Multi-Stage Logic 
            if ($req['approval_stage'] > 1) {
                // Move to next level (Decrease Stage Number)
                $new_stage = $req['approval_stage'] - 1;
                $conn->query("UPDATE service_requests SET approval_stage=$new_stage WHERE id=$id");
            } elseif ($req['approval_stage'] <= 1) {
                // Final Approval
                $conn->query("UPDATE service_requests SET approval_stage=0, status='approved' WHERE id=$id");
            }
            */
        } elseif ($action == 'reject') {
            $conn->query("UPDATE service_requests SET status='rejected', approval_stage=0 WHERE id=$id");
        } elseif ($action == 'complete') {
            $conn->query("UPDATE service_requests SET status='completed' WHERE id=$id");
        } elseif ($action == 'refund') {
            $conn->query("UPDATE service_requests SET refund_status='refunded' WHERE id=$id");
            header("Location: service_requests.php?msg=refunded");
            exit();
        }
    }

    // Redirect to avoid resubmission
    $redirect = "service_requests.php";
    if (isset($_GET['status']))
        $redirect .= "?status=" . $_GET['status'];
    header("Location: " . $redirect);
    exit();
}

// Filter Logic
$where_clauses = [];
if (isset($_GET['user_id'])) {
    $uid = intval($_GET['user_id']);
    $where_clauses[] = "r.user_id = $uid";
    // Fetch name for display
    $u_res = $conn->query("SELECT name FROM users WHERE id=$uid");
    if ($u_res->num_rows > 0)
        $filter_user_name = $u_res->fetch_assoc()['name'];
} else {
    $filter_user_name = "";
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// No filtering - show all requests
$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(' AND ', $where_clauses);
}

// Fetch Requests
$sql = "SELECT r.*, u.name as user_name, s.name as service_name, s.price 
        FROM service_requests r 
        JOIN users u ON r.user_id = u.id 
        JOIN services s ON r.service_id = s.id 
        $where_sql
        ORDER BY r.request_date DESC, r.created_at DESC";
$requests = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests - Smart Residence ERP</title>
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
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 25px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .table> :not(caption)>*>* {
            background-color: transparent;
            color: var(--text-main);
            border-bottom-color: var(--border-color);
        }

        .table thead th {
            background-color: var(--bg-hover);
            color: var(--text-muted);
            border-bottom: none;
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
    $active_page = 'service_requests.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Service Requests</h2>
                <p class="text-muted fw-600 mb-0">Manage service bookings and approvals.</p>
            </div>

            <?php if ($filter_user_name): ?>
                <div class="bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-3 fw-700">
                    Fishing details for: <?php echo $filter_user_name; ?>
                    <a href="service_requests.php" class="ms-2 text-danger"><i class="fas fa-times"></i></a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4 rounded-start-3">Request ID</th>
                            <th>Resident</th>
                            <th>Service</th>
                            <th>Status Details</th>
                            <th>Refund</th>
                            <th class="text-end pe-4 rounded-end-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($requests->num_rows > 0): ?>
                            <?php while ($row = $requests->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-800 text-muted">#REQ-<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-800"><?php echo htmlspecialchars($row['user_name']); ?></div>
                                        <div class="small text-muted">
                                            <?php echo date('M d, Y', strtotime($row['request_date'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-800 text-primary"><?php echo htmlspecialchars($row['service_name']); ?>
                                        </div>
                                        <div class="small fw-700">Rs. <?php echo number_format($row['price']); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <span class="status-badge bg-warning bg-opacity-10 text-warning">Pending</span>
                                            <div class="small fw-700 text-muted mt-1">Lvl <?php echo $row['approval_stage']; ?>
                                                Waiting</div>
                                        <?php elseif ($row['status'] == 'approved'): ?>
                                            <span class="status-badge bg-info bg-opacity-10 text-info">In Progress</span>
                                        <?php elseif ($row['status'] == 'completed'): ?>
                                            <span class="status-badge bg-success bg-opacity-10 text-success">Completed</span>
                                            <?php if ($row['rating'] > 0): ?>
                                                <div class="text-warning small mt-1">
                                                    <?php for ($i = 0; $i < $row['rating']; $i++)
                                                        echo '<i class="fas fa-star"></i>'; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="status-badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['refund_status'] == 'refunded'): ?>
                                            <span class="badge bg-warning text-dark">REFUNDED</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <?php
                                            // Show validation reasoning
                                            $can_approve = false;
                                            $can_approve = true; // All admins can approve now
                                            /* Deprecated Level Specific Logic
                                            if ($admin_level == 1)
                                                $can_approve = true;
                                            elseif ($admin_level == 2 && $row['approval_stage'] == 2)
                                                $can_approve = true;
                                            elseif ($admin_level == 3 && $row['approval_stage'] == 3)
                                                $can_approve = true; 
                                            elseif ($admin_level == 4 && $row['approval_stage'] == 4)
                                                $can_approve = true; 
                                            */
                                            ?>

                                            <?php if ($can_approve): ?>
                                                <a href="?id=<?php echo $row['id']; ?>&action=approve"
                                                    class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm">
                                                    <i class="fas fa-check me-1"></i> Approve
                                                </a>
                                                <a href="?id=<?php echo $row['id']; ?>&action=reject"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold ms-1">
                                                    Reject
                                                </a>
                                            <?php else: ?>
                                                <span class="small text-muted fst-italic">Waiting for Lvl
                                                    <?php echo $row['approval_stage']; ?></span>
                                            <?php endif; ?>

                                        <?php elseif ($row['status'] == 'approved'): ?>
                                            <a href="?id=<?php echo $row['id']; ?>&action=complete"
                                                class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm">
                                                Mark Complete
                                            </a>
                                        <?php elseif ($row['status'] == 'rejected' && $row['refund_status'] != 'refunded'): ?>
                                            <a href="?id=<?php echo $row['id']; ?>&action=refund"
                                                class="btn btn-sm btn-warning rounded-pill px-3 fw-bold">
                                                Issue Refund
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted fw-600">No service requests found.</div>
                                </td>
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