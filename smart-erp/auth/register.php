<?php
// auth/register.php
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check = "SELECT id FROM users WHERE email='$email'";
        $result = $conn->query($check);

        if ($result->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Hash Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'resident'; // Default role

            $sql = "INSERT INTO users (name, email, phone, address, password, role) VALUES ('$name', '$email', '$phone', '$address', '$hashed_password', '$role')";

            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful! Redirecting to login...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Residence ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
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
            background: var(--bg-body);
            color: var(--text-main);
        }

        .split-screen {
            display: flex;
            height: 100vh;
        }

        /* Left Side */
        .left-side {
            flex: 1.2;
            background: url('https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
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
            color: #ffffff !important;
        }

        .left-side p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 500px;
        }

        /* Right Side */
        .right-side {
            flex: 0.8;
            background: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            z-index: 5;
            overflow-y: auto;
            /* Allow scrolling if form is long */
        }

        .login-card {
            width: 100%;
            max-width: 480px;
            /* Slightly wider for register form */
            padding: 20px 0;
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
            color: var(--text-main);
        }

        .text-subtitle {
            color: var(--text-muted);
            margin-bottom: 25px;
            font-size: 0.95rem;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-control {
            border: 2px solid var(--border-color);
            background: var(--input-bg);
            color: var(--text-main);
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 210, 255, 0.1);
        }

        /* Walking Animation Register Button */
        .btn-register {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 55px;
            background: var(--text-main);
            border: 2px solid var(--text-main);
            border-radius: 12px;
            padding: 0 25px;
            color: var(--bg-body);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: visible;
            /* Allowing falling figure to exit */
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }

        .btn-register:hover {
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
            border-radius: 3px;
            position: absolute;
            right: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 3px;
        }

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
            font-size: 18px;
            color: var(--text-main);
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --figure-duration: 200ms;
        }

        .btn-register:hover .walker {
            opacity: 1;
            transform: translateX(5px);
        }

        /* Falling Animation */
        .btn-register.falling .walker {
            animation: spin 1000ms infinite linear;
            bottom: -150px;
            /* Fall completely out */
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
                <h1>Join Our <br>Community.</h1>
                <p>Create your account to access all building services, payments, and community features.</p>
            </div>
        </div>

        <div class="right-side">
            <div class="login-card">
                <a href="login.php" class="logo-box">
                    <div class="logo-icon"><i class="fas fa-building"></i></div>
                    <span>SMART <span class="text-accent">RESIDENCE</span></span>
                </a>

                <h2>Create Account</h2>
                <p class="text-subtitle">Get started with your digital residence access</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-4 mb-4 small fw-600">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 rounded-4 mb-4 small fw-600">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1234567890" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address / Flat No</label>
                        <input type="text" name="address" class="form-control" placeholder="Flat 101, Block A" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <div class="position-relative">
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="••••••••" minlength="8" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                    onclick="togglePassword('password', 'icon1')" style="cursor: pointer;">
                                    <i class="fas fa-eye" id="icon1"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password" name="confirm_password" id="confirm_password"
                                    class="form-control" placeholder="••••••••" minlength="8" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer"
                                    onclick="togglePassword('confirm_password', 'icon2')" style="cursor: pointer;">
                                    <i class="fas fa-eye" id="icon2"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register w-100 mb-3">
                        <span class="btn-text">Create My Account</span>
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

                    document.querySelector('.btn-register').addEventListener('click', function (e) {
                        const btn = this;
                        const form = btn.closest('form');

                        if (form.checkValidity()) {
                            if (!btn.classList.contains('falling')) {
                                e.preventDefault();
                                btn.classList.add('falling');
                                setTimeout(() => {
                                    form.submit();
                                }, 1200);
                            }
                        }
                    });
                </script>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">Already have an account?</p>
                    <a href="login.php" class="text-primary fw-800 text-decoration-none small">LOGIN HERE</a>
                </div>
            </div>
        </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</html>