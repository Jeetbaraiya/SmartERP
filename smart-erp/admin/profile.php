<?php
// admin/profile.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// Handle Profile Update (Name & Email)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = $conn->real_escape_string($_POST['email']);

    $sql = "UPDATE users SET email='$email' WHERE id=$user_id";
    if ($conn->query($sql)) {
        $msg = "Profile updated successfully.";
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $res = $conn->query("SELECT password FROM users WHERE id=$user_id");
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (password_verify($current_pass, $row['password'])) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $conn->query("UPDATE users SET password='$hashed' WHERE id=$user_id");
                $msg = "Password changed successfully.";
            } else {
                $error = "Incorrect current password.";
            }
        }
    }
}

// Fetch User Data
$result = $conn->query("SELECT * FROM users WHERE id=$user_id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Smart Residence ERP</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        window.currentUserId = '<?php echo $_SESSION['user_id'] ?? "default"; ?>';
    </script>
    <script src="../assets/js/theme-head.js"></script>
    <link rel="stylesheet" href="../assets/css/logout_animation.css">

    <style>
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
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 30px;
        }

        .section-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .section-header h5 {
            font-weight: 800;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: var(--primary);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
            margin: 0 auto 30px;
        }

        .form-control {
            background-color: var(--bg-input);
            border-color: var(--border-color);
            color: var(--text-main);
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
        }

        .form-control:focus {
            background-color: var(--bg-input);
            color: var(--text-main);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
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
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <?php
    $active_page = 'profile.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 mb-1">Profile Settings</h2>
                <p class="text-muted fw-600 mb-0">Manage your admin account preferences.</p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-8">

                <?php if ($msg): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4 fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4 fw-bold shadow-sm">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Card -->
                <div class="card-custom">
                    <div class="profile-avatar mb-4">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-muted small text-uppercase">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 text-muted"><i
                                            class="fas fa-user"></i></span>
                                    <input type="text" name="name" class="form-control"
                                        value="<?php echo $user['name']; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-muted small text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 text-muted"><i
                                            class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo $user['email']; ?>" required>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-profile-action rounded-pill px-4 py-2 fw-800">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>



                <!-- Appearance Settings -->
                <div class="card-custom">
                    <div class="section-header">
                        <h5><i class="fas fa-palette"></i> Appearance</h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-800 mb-1">Interface Theme</h6>
                            <p class="text-muted small mb-0">Switch between dark and light modes.</p>
                        </div>
                        <button id="theme-toggle" class="btn btn-outline-primary btn-lg rounded-pill px-4"
                            aria-label="Switch to light mode">
                            <span class="theme-icon-info d-flex align-items-center gap-2">
                                <i class="fas fa-moon"></i> Dark Mode
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="card-custom">
                    <div class="section-header">
                        <h5 class="text-danger"><i class="fas fa-lock"></i> Security</h5>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-800 text-muted small text-uppercase">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-muted small text-uppercase">New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-800 text-muted small text-uppercase">Confirm
                                    Password</label>
                                <input type="password" name="confirm_password" class="form-control" required
                                    minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-profile-danger w-100 rounded-pill py-3 fw-800">
                            Update Password
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>

</html>