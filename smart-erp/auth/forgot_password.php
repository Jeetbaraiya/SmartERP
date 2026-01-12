<?php
// auth/forgot_password.php
session_start();
require_once '../config/db.php';

require_once '../utils/mailer.php';

$error = '';
$success = '';
$debug_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);

    // Check if email exists
    $sql = "SELECT id, name FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Generate Token
        $token = bin2hex(random_bytes(50));

        // Use Database Time for consistency
        $update = "UPDATE users SET reset_token='$token', reset_expiry=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email='$email'";

        if ($conn->query($update) === TRUE) {

            // Send Email using Mailer class
            $email_sent = Mailer::sendPasswordResetEmail($email, $user['name'], $token);

            if ($email_sent) {
                $success = "A password reset link has been sent to your email.";
            }

            if (!$email_sent) {
                // Dev Mode Fallback
                $base_url = "http://localhost:8000";
                $reset_link = $base_url . "/auth/reset_password.php?token=" . $token;
                $success = "Request received (Dev Mode)";
                $debug_msg = "<strong>Dev Mode Action:</strong><br> <a href='$reset_link' class='btn btn-sm btn-success mt-2 fw-bold'>Click here to reset password</a>";
            }

        } else {
            $error = "Database Error: " . $conn->error;
        }
    } else {
        $error = "No account found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Smart Residence ERP</title>
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

        @media (max-width: 991px) {
            .left-side {
                display: none;
            }

            .right-side {
                flex: 1;
            }
        }
    </style>
</head>

<body>

    <div class="split-screen">
        <div class="left-side">
            <div class="left-side-content">
                <h1>Account <br>Recovery.</h1>
                <p>Don't worry, it happens to the best of us.</p>
            </div>
        </div>

        <div class="right-side">
            <div class="card-contain">
                <a href="login.php" class="logo-box">
                    <img src="../assets/img/logo.png" alt="Smart Residence" style="height: 50px;">
                </a>

                <h2>Forgot Password?</h2>
                <p class="text-subtitle">Enter your email to receive recovery instructions.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4 small fw-600">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4 small fw-600">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 fs-5"></i>
                            <div><?php echo $success; ?></div>
                        </div>
                        <?php if ($debug_msg)
                            echo "<div class='mt-2 ps-4 opacity-75'>$debug_msg</div>"; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span
                                class="input-group-text bg-white border-2 border-end-0 text-muted rounded-start-4 ps-3">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control border-start-0 ps-2"
                                placeholder="name@residence.com" required style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-reset mb-4">Send Reset Link</button>

                    <div class="text-center">
                        <a href="login.php" class="text-secondary fw-700 text-decoration-none small">
                            <i class="fas fa-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>