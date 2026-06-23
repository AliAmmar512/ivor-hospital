<?php
// ============================================================
// Auth Check - include at the top of every protected page
// AFTER db_config.php has been included (BASE_URL needed)
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}
?>
