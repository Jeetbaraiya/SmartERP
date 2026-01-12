<?php
// auth/reset_password.php
require_once '../config/db.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    die("Invalid request.");
}

// Verify Token
$sql = "SELECT id FROM users WHERE reset_token='$token' AND reset_expiry > NOW()";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Invalid or expired token.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update Password and Clear Token
        $update = "UPDATE users SET password='$hashed_password', reset_token=NULL, reset_expiry=NULL WHERE reset_token='$token'";

        if ($conn->query($update) === TRUE) {
            $success = "Password successfully reset!";
            // Redirect after 2 seconds
            header("refresh:2;url=login.php");
        } else {
            $error = "Error updating password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Smart Residence ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #00d2ff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            background: #f8fafc;
        }

        .split-screen {
            display: flex;
            height: 100vh;
        }

        /* Left Side */
        .left-side {
            flex: 1.2;
            background: url('https://images.unsplash.com/photo-1574362848149-11496d93a7c7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
            background-size: cover;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            color: white;
        }

        .left-side::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.9) 0%, rgba(42, 82, 152, 0.7) 100%);
        }

        .left-side-content {
            position: relative;
            z-index: 2;
        }

        .left-side h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .left-side p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 500px;
        }

        /* Right Side */
        .right-side {
            flex: 0.8;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            z-index: 5;
        }

        .card-contain {
            width: 100%;
            max-width: 420px;
        }

        .logo-box {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: 30px;
            font-size: 1.25rem;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: var(--primary);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        h2 {
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            color: #1e293b;
        }

        .text-subtitle {
            color: #64748b;
            margin-bottom: 32px;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 210, 255, 0.1);
        }

        .btn-reset {
            background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 210, 255, 0.3);
            color: white;
        }
    </style>
</head>

<body>

    <div class="split-screen">
        <div class="left-side">
            <div class="left-side-content">
                <h1>Secure Your <br>Account.</h1>
                <p>Create a strong password to protect your personal data.</p>
            </div>
        </div>

        <div class="right-side">
            <div class="card-contain">
                <a href="login.php" class="logo-box">
                    <div class="logo-icon"><i class="fas fa-building"></i></div>
                    <span>SMART <span class="text-accent">RESIDENCE</span></span>
                </a>

                <h2>Set New Password</h2>
                <p class="text-subtitle">Enter your new credentials below</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4 small fw-600">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4 small fw-600">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        <div class="mt-2 small opacity-75">Redirecting to login...</div>
                    </div>
                <?php else: ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="position-relative">
                                <input type="password" name="password" id="new_password" class="form-control"
                                    placeholder="••••••••" minlength="8" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                    onclick="togglePassword('new_password', 'toggleIconNew')" style="cursor: pointer;">
                                    <i class="fas fa-eye" id="toggleIconNew"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                    placeholder="••••••••" minlength="8" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                    onclick="togglePassword('confirm_password', 'toggleIconConfirm')"
                                    style="cursor: pointer;">
                                    <i class="fas fa-eye" id="toggleIconConfirm"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-reset mb-3">Update Password</button>
                    </form>

                <?php endif; ?>
            </div>
        </div>
    </div>

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