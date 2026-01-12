<?php
// auth/login.php
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {

            // Check Account Status
            if (isset($user['status']) && $user['status'] === 'inactive') {
                $error = "Your account is deactivated. Contact Admin.";
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['level'] = $user['level'] ?? 5; // Default to 5 (User)

                // Role based redirection
                if ($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../user/dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No user found with this email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Residence ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

    <!-- Clear Theme Preference on Login -->
    <!-- Script removed to allow theme persistence across sessions as per user request -->
    <!-- <script>
        localStorage.removeItem('smart_erp_theme');
    </script> -->

    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #00d2ff;
            --bg-glass: rgba(255, 255, 255, 0.85);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            background: var(--bg-body);
            color: var(--text-main);
        }

        .split-screen {
            display: flex;
            height: 100vh;
        }

        /* Left Side - Image & Branding */
        .left-side {
            flex: 1.2;
            background:
                url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
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
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.85) 0%, rgba(42, 82, 152, 0.6) 100%);
        }

        .left-side-content {
            position: relative;
            z-index: 2;
        }

        .left-side h1 {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 20px;
            letter-spacing: -2px;
            color: #ffffff !important;
        }

        .left-side p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 500px;
        }

        /* Right Side - Form */
        .right-side {
            flex: 0.8;
            background: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            z-index: 5;
        }

        .login-card {
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
            margin-bottom: 40px;
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
            letter-spacing: -1px;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .text-subtitle {
            color: var(--text-muted);
            margin-bottom: 32px;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid var(--border-color);
            background: var(--input-bg);
            color: var(--text-main);
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 210, 255, 0.1);
        }

        /* Walking Animation Button */
        .btn-login {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 55px;
            background: var(--text-main);
            /* Adaptive contrast (Dark in Light mode, Light in Dark mode) */
            border: 2px solid var(--text-main);
            border-radius: 12px;
            padding: 0 25px;
            color: var(--bg-body);
            /* Adaptive text color */
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: visible;
            /* Changed from hidden to allow falling out */
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: transparent;
            color: var(--text-main);
            border-color: var(--text-main);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-text {
            z-index: 2;
        }

        .animation-container {
            display: flex;
            align-items: center;
            position: relative;
            width: 40px;
            height: 40px;
        }

        /* The Door */
        .door {
            width: 20px;
            height: 32px;
            background: var(--primary);
            /* Blue door */
            border-radius: 3px;
            position: absolute;
            right: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 3px;
        }

        /* Door Handle */
        .door::after {
            content: '';
            width: 3px;
            height: 3px;
            background: white;
            border-radius: 50%;
        }

        /* The Walking Figure */
        .walker {
            position: absolute;
            right: 25px;
            /* Start position outside/left of door */
            font-size: 18px;
            color: var(--text-main);
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --figure-duration: 200ms;
        }

        .btn-login:hover .walker {
            opacity: 1;
            transform: translateX(5px);
            /* Move towards door */
        }

        /* Falling Animation from Request */
        .btn-login.falling .walker {
            animation: spin 1000ms infinite linear;
            bottom: -50px;
            /* Adjusted to fall below button */
            opacity: 0;
            right: 1px;
            transition:
                transform calc(var(--figure-duration) * 1ms) linear,
                bottom calc(var(--figure-duration) * 1ms) cubic-bezier(0.7, 0.1, 1, 1) 100ms,
                opacity calc(var(--figure-duration) * 0.25ms) linear calc(var(--figure-duration) * 0.75ms);
            z-index: 11;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .divider::after {
            content: 'OR';
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 15px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
        }

        .social-login {
            display: flex;
            gap: 15px;
        }

        .btn-social {
            flex: 1;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-social:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        @media (max-width: 991px) {
            .left-side {
                display: none;
            }

            .right-side {
                flex: 1;
            }

            body {
                overflow: auto;
            }
        }
    </style>
</head>

<body>
    <div class="split-screen">
        <div class="left-side">
            <div class="left-side-content">
                <h1>Smart <br>Living.</h1>
                <p>Welcome to the premium residential management ecosystem. Experience seamless living across every
                    service.</p>
            </div>
        </div>
        <div class="right-side">
            <div class="login-card">
                <a href="../index.php" class="logo-box">
                    <div class="logo-icon"><i class="fas fa-building"></i></div>
                    <span>SMART <span class="text-accent">RESIDENCE</span></span>
                </a>

                <h2>Welcome back</h2>
                <p class="text-subtitle">Enter your credentials to access your portal</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4 small fw-600">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@residence.com" required>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label class="form-label">Password</label>
                            <a href="forgot_password.php"
                                class="text-accent text-decoration-none small fw-700">Forgot?</a>
                        </div>
                        <div class="position-relative">
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="••••••••" required>
                            <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                onclick="togglePassword('password', 'toggleIcon')" style="cursor: pointer;">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login w-100 mb-3">
                        <span class="btn-text">Login to Dashboard</span>
                        <div class="animation-container">
                            <i class="fas fa-person-walking walker"></i>
                            <div class="door"></div>
                        </div>
                    </button>
                </form>

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

                    // Button Falling Animation
                    document.querySelector('.btn-login').addEventListener('click', function (e) {
                        const btn = this;
                        const form = btn.closest('form');

                        // Basic validation check (html5)
                        if (form.checkValidity()) {
                            if (!btn.classList.contains('falling')) {
                                e.preventDefault();
                                btn.classList.add('falling');

                                // Wait for animation to finish (1000ms+)
                                setTimeout(() => {
                                    form.submit();
                                }, 1200);
                            }
                        }
                    });

                </script>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">Don't have an account yet?</p>
                    <a href="register.php" class="text-primary fw-800 text-decoration-none small">CREATE AN ACCOUNT</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>