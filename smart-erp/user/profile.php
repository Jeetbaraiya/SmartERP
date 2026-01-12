<?php
// user/profile.php
session_start();
require_once '../config/db.php';
require_once '../utils/mailer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";
$info = "";

// Handle Profile Update (Name, Phone, Address only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    $sql = "UPDATE users SET name='$name', phone='$phone', address='$address' WHERE id=$user_id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['name'] = $name; // Update session
        $success = "Your profile information has been securely updated.";
    } else {
        $error = "Update Failed: " . $conn->error;
    }
}

// Handle Email Change Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_email'])) {
    $new_email = $conn->real_escape_string($_POST['new_email']);
    $current_user = $conn->query("SELECT email FROM users WHERE id=$user_id")->fetch_assoc();

    if ($new_email === $current_user['email']) {
        $error = "This is already your current email address.";
    } else {
        // Check if email is already in use
        $check = $conn->query("SELECT id FROM users WHERE email='$new_email' AND id != $user_id");
        if ($check->num_rows > 0) {
            $error = "This email address is already in use.";
        } else {
            // Check for existing pending request
            $pending_check = $conn->query("SELECT id FROM email_change_requests WHERE user_id=$user_id AND status='pending'");
            if ($pending_check->num_rows > 0) {
                $error = "You already have a pending email change request. Please wait for admin approval.";
            } else {
                // Create new email change request
                $insert_sql = "INSERT INTO email_change_requests (user_id, old_email, new_email, status) 
                              VALUES ($user_id, '{$current_user['email']}', '$new_email', 'pending')";

                if ($conn->query($insert_sql) === TRUE) {
                    $info = "Email change request submitted successfully! Your request will be reviewed by the Super Admin.";
                } else {
                    $error = "Database error: " . $conn->error;
                }
            }
        }
    }
}

// Handle Cancel Email Change Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_email_request'])) {
    $conn->query("DELETE FROM email_change_requests WHERE user_id=$user_id AND status='pending'");
    $success = "Email change request cancelled successfully.";
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get current password hash
    $user = $conn->query("SELECT password, name, email FROM users WHERE id=$user_id")->fetch_assoc();

    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password='$hashed_password' WHERE id=$user_id";

        if ($conn->query($update_sql) === TRUE) {
            // Send notification email
            Mailer::sendPasswordChangedNotification($user['email'], $user['name']);
            $success = "Your password has been changed successfully!";
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

// Check for Pending Email Change Request
$pending_email = null;
$pending_date = null;
$pending_check = $conn->query("SELECT new_email, requested_at FROM email_change_requests WHERE user_id=$user_id AND status='pending'");
if ($pending_check->num_rows > 0) {
    $pending_data = $pending_check->fetch_assoc();
    $pending_email = $pending_data['new_email'];
    $pending_date = $pending_data['requested_at'];
}

// Fetch Current Data
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Smart Residence</title>
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
        /* Profile-specific overrides relying on global variables from style.css */

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
            transition: all 0.2s;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: var(--bg-hover);
            color: var(--primary);
        }

        .sidebar-link.active {
            background: var(--primary);
            color: #ffffff;
            /* Always white on active primary bg */
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .section-header {
            border-bottom: 2px solid var(--border-color);
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

        .form-control-custom {
            background: var(--bg-input);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 15px 20px;
            font-weight: 600;
            color: var(--text-main);
            transition: all 0.2s;
        }

        .form-control-custom:focus {
            background: var(--bg-input);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
            color: var(--text-main);
        }

        .btn-update {
            background: var(--primary);
            border: none;
            color: white;
            padding: 18px;
            border-radius: 18px;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
            transition: all 0.3s;
        }

        .btn-update:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }

        .pending-badge {
            background: var(--warning);
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Helper for text colors in dark mode context */
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
        <?php $active_page = 'profile.php';
        include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h4 class="fw-800 mb-1">Account Settings</h4>
                    <p class="text-muted fw-600 mb-0 small text-uppercase" style="letter-spacing: 1px;">Securely manage
                        your resident identity</p>
                </div>
                <!-- User Initials Avatar -->
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-800"
                    style="width: 48px; height: 48px;">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success border-0 rounded-4 mb-4 p-3 fw-700 shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 rounded-4 mb-4 p-3 fw-700 shadow-sm">
                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($info): ?>
                <div class="alert alert-info border-0 rounded-4 mb-4 p-3 fw-700 shadow-sm">
                    <i class="fas fa-info-circle me-1"></i> <?php echo $info; ?>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <!-- Profile Header -->
                    <div class="card-custom text-center">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h4 class="fw-800 mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted fw-600 small">Resident Profile</p>
                    </div>

                    <!-- Personal Information -->
                    <div class="card-custom">
                        <div class="section-header">
                            <h5><i class="fas fa-user"></i> Personal Information</h5>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-800 small text-muted text-uppercase ms-1">Full
                                        Name</label>
                                    <input type="text" name="name" class="form-control form-control-custom"
                                        value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-800 small text-muted text-uppercase ms-1">Phone
                                        Number</label>
                                    <input type="tel" name="phone" class="form-control form-control-custom"
                                        value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-800 small text-muted text-uppercase ms-1">Residential
                                        Address</label>
                                    <textarea name="address" class="form-control form-control-custom" rows="3"
                                        required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-profile-action w-100">
                                        Save Changes <i class="fas fa-check-double ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>



                    <!-- Appearance Settings (NEW) -->
                    <div class="card-custom">
                        <div class="section-header">
                            <h5><i class="fas fa-palette"></i> Appearance</h5>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-800 mb-1">Interface Theme</h6>
                                <p class="text-muted small mb-0">Choose your preferred visual style.</p>
                            </div>
                            <button id="theme-toggle" class="btn btn-outline-primary btn-lg rounded-pill px-4"
                                aria-label="Switch to light mode">
                                <span class="theme-icon-info d-flex align-items-center gap-2">
                                    <i class="fas fa-moon"></i> Dark Mode
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="card-custom">
                        <div class="section-header">
                            <h5><i class="fas fa-envelope"></i> Email Settings</h5>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-800 small text-muted text-uppercase ms-1">Current Email</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="email" class="form-control form-control-custom"
                                    value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <?php if ($pending_email): ?>
                                    <span class="pending-badge">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($pending_email): ?>
                                <small class="text-warning ms-1 mt-2 d-block">
                                    <i class="fas fa-info-circle"></i> Awaiting Super Admin approval for:
                                    <strong><?php echo htmlspecialchars($pending_email); ?></strong>
                                    <br>
                                    <span class="text-muted">Requested on:
                                        <?php echo date('M d, Y h:i A', strtotime($pending_date)); ?></span>
                                </small>
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="cancel_email_request" value="1">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times"></i> Cancel Request
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php if (!$pending_email): ?>
                            <form method="POST">
                                <input type="hidden" name="change_email" value="1">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <label class="form-label fw-800 small text-muted text-uppercase ms-1">New Email
                                            Address</label>
                                        <input type="email" name="new_email" class="form-control form-control-custom"
                                            placeholder="Enter new email address" required>
                                        <small class="text-muted ms-1 mt-1 d-block">
                                            <i class="fas fa-shield-alt"></i> Your request will be sent to Super Admin for
                                            approval
                                        </small>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-profile-action w-100">
                                            Request Email Change <i class="fas fa-paper-plane ms-2"></i>
                                        </button>
                                    </div>

                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Security Settings -->
                    <div class="card-custom">
                        <div class="section-header">
                            <h5><i class="fas fa-lock"></i> Security Settings</h5>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="change_password" value="1">
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-800 small text-muted text-uppercase ms-1">Current
                                        Password</label>
                                    <div class="position-relative">
                                        <input type="password" name="current_password" id="current_password"
                                            class="form-control form-control-custom"
                                            placeholder="Enter current password" required>
                                        <span
                                            class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                            onclick="togglePassword('current_password', 'toggleIconCurrent')"
                                            style="cursor: pointer;">
                                            <i class="fas fa-eye" id="toggleIconCurrent"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-800 small text-muted text-uppercase ms-1">New
                                        Password</label>
                                    <div class="position-relative">
                                        <input type="password" name="new_password" id="new_password"
                                            class="form-control form-control-custom" placeholder="Enter new password"
                                            minlength="8" required>
                                        <span
                                            class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                            onclick="togglePassword('new_password', 'toggleIconNew')"
                                            style="cursor: pointer;">
                                            <i class="fas fa-eye" id="toggleIconNew"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-800 small text-muted text-uppercase ms-1">Confirm New
                                        Password</label>
                                    <div class="position-relative">
                                        <input type="password" name="confirm_password" id="confirm_password"
                                            class="form-control form-control-custom" placeholder="Confirm new password"
                                            minlength="8" required>
                                        <span
                                            class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                            onclick="togglePassword('confirm_password', 'toggleIconConfirm')"
                                            style="cursor: pointer;">
                                            <i class="fas fa-eye" id="toggleIconConfirm"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted ms-1">
                                        <i class="fas fa-info-circle"></i> Password must be at least 8 characters long
                                    </small>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-profile-action w-100">
                                        Change Password <i class="fas fa-key ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout_animation.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>

</html>