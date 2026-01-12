<?php
// middleware/check_level.php

/**
 * Enforce minimum access level.
 * Hierarchy: 1 (Super Admin) > ... > 5 (User)
 * @param int $required_level The highest level number allowed (e.g., 2 means 1 and 2 are allowed).
 */
function require_access_level($required_level)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['level'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    // User Level must be <= Required Level (numerically) to have access
    // Ex: User=1, Required=2 (1 <= 2) -> OK
    // Ex: User=3, Required=2 (3 > 2) -> Fail
    if ($_SESSION['level'] > $required_level) {
        header("Location: ../admin/dashboard.php?error=unauthorized");
        exit();
    }
}
?>