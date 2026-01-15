<?php
// user/includes/auto_expire.php
// Automatically expires pending requests that have passed their booking time

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Set timezone to India for accurate hour comparison
date_default_timezone_set('Asia/Kolkata');
$current_date = date('Y-m-d');
$current_hour = date('H');

// Logic:
// 1. Date is in past (< current_date)
// 2. Date is today (= current_date) AND End Hour <= Current Hour
//    (Expire only after the slot has fully passed)

$sql = "UPDATE service_requests 
        SET status = CASE WHEN payment_status='paid' THEN 'refunded' ELSE 'rejected' END, 
            refund_status = CASE WHEN payment_status='paid' THEN 'refunded' ELSE 'none' END
        WHERE status='pending' 
        AND (
            booking_date < '$current_date' 
            OR (
                booking_date = '$current_date' 
                AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(booking_slot, '-', -1), ':', 1) AS UNSIGNED) <= $current_hour
            )
        )";

$conn->query($sql);