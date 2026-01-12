<?php
// utils/mailer.php

// Load PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static function getMailConfig()
    {
        return require __DIR__ . '/../config/mail_config.php';
    }

    private static function sendEmail($to, $toName, $subject, $htmlBody)
    {
        $mail_config = self::getMailConfig();

        // Check if real SMTP is configured
        if ($mail_config['username'] === 'EMAIL_HERE' || empty($mail_config['username'])) {
            return false; // Not configured
        }

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $mail_config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mail_config['username'];
            $mail->Password = $mail_config['password'];
            $mail->SMTPSecure = $mail_config['encryption'];
            $mail->Port = $mail_config['port'];

            // Recipients
            $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
            $mail->addAddress($to, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendReceipt($to, $name, $amount, $item)
    {
        $subject = "Payment Receipt - Smart Residence ERP";

        $message = "
        <html>
        <head>
            <title>Payment Successful</title>
        </head>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Hello $name,</h2>
            <p>Your payment of <strong>Rs. $amount</strong> for <strong>$item</strong> has been successfully received.</p>
            <p>Transaction Reference: <strong>SRP-" . time() . "</strong></p>
            <br>
            <p>Thank you for using Smart Residence ERP.</p>
        </body>
        </html>
        ";

        return self::sendEmail($to, $name, $subject, $message);
    }

    public static function sendEmailChangeVerification($to, $name, $token)
    {
        $subject = "Verify Your New Email Address - Smart Residence";
        $base_url = "http://localhost:8000";
        $verify_link = $base_url . "/auth/verify_email_change.php?token=" . $token;

        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Arial', sans-serif; background: #f8fafc; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #1e3c72; margin: 0; font-size: 28px; }
                .content { color: #475569; line-height: 1.6; }
                .button { display: inline-block; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 14px 32px; text-decoration: none; border-radius: 12px; font-weight: bold; margin: 20px 0; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 14px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Verify Your Email</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    <p>We received a request to change your email address. To complete this change, please verify your new email address by clicking the button below:</p>
                    <p style='text-align: center;'>
                        <a href='$verify_link' class='button'>Verify Email Address</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='background: #f1f5f9; padding: 12px; border-radius: 8px; word-break: break-all; font-size: 13px;'>$verify_link</p>
                    <p><strong>⏰ This link will expire in 1 hour.</strong></p>
                    <p>If you didn't request this change, please ignore this email and your email address will remain unchanged.</p>
                </div>
                <div class='footer'>
                    <p>Smart Residence ERP &copy; 2026<br>Secure Residential Management</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return self::sendEmail($to, $name, $subject, $htmlBody);
    }

    public static function sendPasswordResetEmail($to, $name, $token)
    {
        $subject = "Password Reset Request - Smart Residence";
        $base_url = "http://localhost:8000";
        $reset_link = $base_url . "/auth/reset_password.php?token=" . $token;

        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Arial', sans-serif; background: #f8fafc; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #1e3c72; margin: 0; font-size: 28px; }
                .content { color: #475569; line-height: 1.6; }
                .button { display: inline-block; background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%); color: white; padding: 14px 32px; text-decoration: none; border-radius: 12px; font-weight: bold; margin: 20px 0; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 14px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔑 Reset Your Password</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <p style='text-align: center;'>
                        <a href='$reset_link' class='button'>Reset Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p style='background: #f1f5f9; padding: 12px; border-radius: 8px; word-break: break-all; font-size: 13px;'>$reset_link</p>
                    <p><strong>⏰ This link will expire in 1 hour.</strong></p>
                    <p>If you didn't request a password reset, please ignore this email and your password will remain unchanged.</p>
                </div>
                <div class='footer'>
                    <p>Smart Residence ERP &copy; 2026<br>Secure Residential Management</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return self::sendEmail($to, $name, $subject, $htmlBody);
    }

    public static function sendPasswordChangedNotification($to, $name)
    {
        $subject = "Password Changed Successfully - Smart Residence";

        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Arial', sans-serif; background: #f8fafc; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #10b981; margin: 0; font-size: 28px; }
                .content { color: #475569; line-height: 1.6; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 14px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Password Changed</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>$name</strong>,</p>
                    <p>This is a confirmation that your password has been successfully changed.</p>
                    <p>If you did not make this change, please contact support immediately.</p>
                    <p><strong>Time:</strong> " . date('F j, Y, g:i a') . "</p>
                </div>
                <div class='footer'>
                    <p>Smart Residence ERP &copy; 2026<br>Secure Residential Management</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return self::sendEmail($to, $name, $subject, $htmlBody);
    }
}
?>