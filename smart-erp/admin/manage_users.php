<?php
// admin/manage_users.php
session_start();
require_once '../config/db.php';

// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Level 1, 2, or 3 can access (L4 cannot)
$level = $_SESSION['level'] ?? 5;
if ($level > 3) {
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Read-only mode for L2 and L3
$read_only = ($level > 1);

// Handle Delete User (L1 only)
if (isset($_GET['delete_user'])) {
    if ($level != 1) {
        header("Location: manage_users.php?error=unauthorized");
        exit();
    }
    $user_id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$user_id AND role='user'");
    header("Location: manage_users.php?msg=deleted");
    exit();
}

// Fetch Users
$result = $conn->query("SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Residents - Smart Residence ERP</title>
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
        /* Sidebar Styling defined in style.css */

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 3rem;
            min-height: 100vh;
            transition: all 0.3s ease;
            background-color: var(--bg-body);
        }

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }
        
        h2, h4, h5 { color: var(--text-main); }
        .text-dark { color: var(--text-main) !important; }
        .text-muted { color: var(--text-muted) !important; }

        /* Mobile Styles */
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

            .mobile-toggle {
                display: flex !important;
            }
        }

        .mobile-toggle {
            display: none;
            width: 45px;
            height: 45px;
            background: var(--bg-card);
            border-radius: 12px;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            cursor: pointer;
            color: var(--text-main);
        }
        
        .table { color: var(--text-main); }
        .table thead th { background-color: var(--bg-hover); color: var(--text-muted); border: none; }
        .table td { border-bottom: 1px solid var(--border-color); }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            background: var(--bg-hover);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary);
            font-size: 1.2rem;
        }
    </style>
</head>

<body>

    <?php
    $active_page = 'manage_users.php';
    include 'includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="mobile-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>

        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">User Directory</h2>
                <p class="text-muted fw-600 mb-0">Manage all registered residents.</p>
            </div>
        </header>

        <!-- User Table -->
        <div class="card-custom">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success">User updated successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success">User deleted successfully.</div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role / Status</th>
                            <th>Registered</th>
                            <?php if ($level == 1): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-800"><?php echo $row['name']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Level <?php echo $row['level']; ?></span>
                                    <span class="badge bg-success bg-opacity-10 text-success"><?php echo ucfirst($row['status']); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                
                                <?php if ($level == 1): ?>
                                    <td>
                                        <a href="manage_users.php?delete_user=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
</body>

</html>
