<?php
// auth/verify_email_change.php
session_start();
require_once '../config/db.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);

    // Check if token exists and is not expired
    $sql = "SELECT user_id, new_email FROM email_change_tokens WHERE token='$token' AND expiry > NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $new_email = $row['new_email'];

        // Check if new email is already taken by another user
        $check_sql = "SELECT id FROM users WHERE email='$new_email' AND id != $user_id";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $error = "This email address is already in use by another account.";
        } else {
            // Update user's email
            $update_sql = "UPDATE users SET email='$new_email' WHERE id=$user_id";
            if ($conn->query($update_sql) === TRUE) {
                // Delete the token
                $delete_sql = "DELETE FROM email_change_tokens WHERE token='$token'";
                $conn->query($delete_sql);

                // Update session if this is the logged-in user
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
                    $_SESSION['email'] = $new_email;
                }

                $success = "Your email address has been successfully updated!";
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    } else {
        $error = "Invalid or expired verification link.";
    }
} else {
    $error = "No verification token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Smart Residence</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #1e3c72;
            --accent: #00d2ff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-card {
            background: white;
            border-radius: 28px;
            padding: 50px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
        }

        .icon-circle.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .icon-circle.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        h2 {
            font-weight: 800;
            margin-bottom: 15px;
            color: #1e293b;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary) 0%, #2a5298 100%);
            border: none;
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 800;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 60, 114, 0.2);
            color: white;
        }
    </style>
</head>

<body>
    <div class="verification-card">
        <?php if ($success): ?>
            <div class="icon-circle success">
                <i class="fas fa-check"></i>
            </div>
            <h2>Email Verified!</h2>
            <p class="text-muted mb-0">
                <?php echo $success; ?>
            </p>
            <a href="../user/profile.php" class="btn-primary-custom">
                <i class="fas fa-user-circle me-2"></i>Go to Profile
            </a>
        <?php else: ?>
            <div class="icon-circle error">
                <i class="fas fa-times"></i>
            </div>
            <h2>Verification Failed</h2>
            <p class="text-muted mb-0">
                <?php echo $error; ?>
            </p>
            <a href="../user/profile.php" class="btn-primary-custom">
                <i class="fas fa-arrow-left me-2"></i>Back to Profile
            </a>
        <?php endif; ?>
    </div>
</body>

</html>