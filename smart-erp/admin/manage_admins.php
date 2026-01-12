<?php
// admin/manage_admins.php
session_start();
require_once '../config/db.php';

// Check Super Admin (Level 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['level'] != 1) {
    header("Location: dashboard.php");
    exit();
}

$msg = '';
$error = '';

// Handle Create Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $level = intval($_POST['level']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Check email
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (name, email, password, phone, role, level, status) 
                VALUES ('$name', '$email', '$password', '$phone', 'admin', $level, 'active')";
        if ($conn->query($sql)) {
            $msg = "Admin created successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Handle Update Level/Status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = intval($_POST['user_id']);
    $level = intval($_POST['level']);
    $status = $conn->real_escape_string($_POST['status']);

    // Prevent modifying self
    if ($id != $_SESSION['user_id']) {
        $sql = "UPDATE users SET level=$level, status='$status' WHERE id=$id";
        if ($conn->query($sql)) {
            $msg = "Admin updated successfully!";
        } else {
            $error = "Error updating: " . $conn->error;
        }
    }
}

// Handle Delete Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $delete_id = intval($_POST['user_id']);

    // Safety Checks
    if ($delete_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } elseif ($delete_id == 1) { // Assuming ID 1 is the root super admin
        $error = "Cannot delete the main Super Admin!";
    } else {
        $sql = "DELETE FROM users WHERE id=$delete_id AND role='admin'";
        if ($conn->query($sql)) {
            $msg = "Admin deleted successfully.";
        } else {
            $error = "Error deleting admin: " . $conn->error;
        }
    }
}

// Fetch Admins (excluding self if strictly needed, but showing all is fine, just disable edit for self)
$current_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE role='admin' AND id != $current_id ORDER BY level ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Smart Residence ERP</title>
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

        /* Table Styles */
        .table {
            color: var(--text-main);
            margin-bottom: 0;
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

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 30px;
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

        .form-control,
        .form-select {
            background-color: var(--bg-input);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--bg-input);
            color: var(--text-main);
            border-color: var(--primary);
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

    <?php
    $active_page = 'manage_admins.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Manage Admins</h2>
                <p class="text-muted fw-600 mb-0">Super Admin Control Panel</p>
            </div>
            <button class="btn btn-primary fw-700 px-4 rounded-3" data-bs-toggle="modal"
                data-bs-target="#createAdminModal">
                <i class="fas fa-plus me-2"></i> Create Admin
            </button>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success border-0 rounded-4 mb-4 fw-700 shadow-sm">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger border-0 rounded-4 mb-4 fw-700 shadow-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Admin Name</th>
                            <th>Role / Level</th>
                            <th>Status</th>
                            <th>Contact</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 px-3 fw-bold">
                                                <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-800">
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?php echo htmlspecialchars($row['email']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3">
                                            Level
                                            <?php echo $row['level']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'active'): ?>
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Active</span>
                                        <?php else: ?>
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['phone']); ?>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary rounded-3 btn-edit"
                                            data-bs-toggle="modal" data-bs-target="#editAdminModal"
                                            data-id="<?php echo $row['id']; ?>" data-level="<?php echo $row['level']; ?>"
                                            data-status="<?php echo $row['status']; ?>">
                                            <i class="fas fa-cog"></i> Edit
                                        </button>
                                        <form method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this admin?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 ms-1">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No other admins found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Admin Modal -->
    <div class="modal fade" id="createAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content card-custom border-0 p-0">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-800">New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-700 text-muted">Full Name</label>
                        <input type="text" name="name" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700 text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700 text-muted">Password</label>
                        <input type="password" name="password" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700 text-muted">Phone</label>
                        <input type="text" name="phone" class="form-control rounded-3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700 text-muted">Access Level (2-4)</label>
                        <select name="level" class="form-select rounded-3" required>
                            <!-- <option value="2">Level 2 (Central Admin)</option>
                            <option value="3">Level 3 (Branch Admin)</option>
                            <option value="4">Level 4 (Service Manager)</option> -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary fw-700 w-100 rounded-3">Create Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content card-custom border-0 p-0">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" id="edit_user_id">

                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-800">Edit Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-700 text-muted">Access Level</label>
                        <select name="level" id="edit_level" class="form-select rounded-3" required>
                            <option value="1">Level 1 (Super Admin)</option>
                            <!-- <option value="2">Level 2 (Central Admin)</option>
                            <option value="3">Level 3 (Branch Admin)</option>
                            <option value="4">Level 4 (Service Manager)</option> -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-700 text-muted">Account Status</label>
                        <select name="status" id="edit_status" class="form-select rounded-3" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary fw-700 w-100 rounded-3">Update Access</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Handle Edit Button Click
        const editButtons = document.querySelectorAll('.btn-edit');
        const editIdInput = document.getElementById('edit_user_id');
        const editLevelSelect = document.getElementById('edit_level');
        const editStatusSelect = document.getElementById('edit_status');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                editIdInput.value = this.dataset.id;
                editLevelSelect.value = this.dataset.level;
                editStatusSelect.value = this.dataset.status;
            });
        });
    </script>
</body>

</html>