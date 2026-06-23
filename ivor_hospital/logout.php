<?php
require_once 'db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_destroy();

header("Location: " . BASE_URL . "/login.php");
exit();
?>
