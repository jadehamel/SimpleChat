<?php
require 'config.php';
session_start();

// Rate limiting: Allow only 5 attempts per 15 minutes from the same IP
$ip = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}
$_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function ($time) {
    return $time > (time() - 900); // 900 seconds = 15 minutes
});

if (count($_SESSION['login_attempts']) >= 5) {
    die("Too many login attempts. Please try again later.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize username (although not required in PDO prepared statements, good practice)
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Validate user and password
    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        unset($_SESSION['login_attempts']); // Reset login attempts on successful login
        header("Location: chat.php");
        exit;
    } else {
        // Invalid credentials
        echo "Invalid credentials";
        $_SESSION['login_attempts'][] = time(); // Record failed attempt time
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <form method="post" action="login.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
