<?php
require_once 'db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in — go straight to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in']  = true;
        $_SESSION['admin_username']   = 'admin';
        header("Location: " . BASE_URL . "/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; IVOR Paine Memorial Hospital</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css">
</head>
<body class="login-page">

    <div class="login-container">

        <div class="login-header">
            <div class="login-logo">&#x2695;</div>
            <h1>IVOR PAINE</h1>
            <h2>MEMORIAL HOSPITAL</h2>
            <p>Hospital Management System</p>
        </div>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validateLoginForm()">

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    value="<?php echo htmlspecialchars(isset($_POST['username']) ? $_POST['username'] : ''); ?>"
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-full">Login</button>

        </form>

        <div class="login-footer">
            <small>Group: Hamza Sultan &nbsp;&middot;&nbsp; Muhammad Hamza &nbsp;&middot;&nbsp; Ali Ammar</small>
        </div>

    </div>

    <script src="<?php echo BASE_URL; ?>/assets/script.js"></script>
</body>
</html>
