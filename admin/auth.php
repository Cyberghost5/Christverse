<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin configuration credentials
define('ADMIN_USER', 'admin');
// Pre-hashed 'admin123' password using bcrypt
define('ADMIN_PASS_HASH', '$2y$10$UPKOHxSHGNFbAbBMxHNA7.UQSbruO3FKKg4sj4Jv.VB09uYz3U7zW'); 

/**
 * Checks if the administrator is currently logged in.
 *
 * @return bool
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Ensures the administrator is logged in. Redirects to login page if not.
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header("Location: login");
        exit;
    }
}

/**
 * Verifies credentials and logs in the administrator.
 *
 * @param string $username
 * @param string $password
 * @return bool
 */
function attempt_admin_login($username, $password) {
    if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = ADMIN_USER;
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}
?>
