<?php
require 'config.php';
session_start();

// Rate limiting: Allow only 5 attempts per 15 minutes from the same IP
$ip = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = [];
}
$_SESSION['attempts'] = array_filter($_SESSION['attempts'], function ($time) {
    return $time > (time() - 900); // 900 seconds = 15 minutes
});

if (count($_SESSION['attempts']) >= 5) {
    die("Too many registration attempts. Please try again later.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate username
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
    if (!preg_match('/^[a-zA-Z0-9]{5,}$/', $username)) {
        die("Invalid username. Username must be at least 5 characters long and contain only letters and numbers.");
    }

    // Validate password
    $password = $_POST['password'];
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[\W]/', $password)) {
        die("Invalid password. Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.");
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetchColumn() > 0) {
        die("Username already taken.");
    }

    // Insert new user into the database
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    if ($stmt->execute(['username' => $username, 'password' => $passwordHash])) {
        echo "Registration successful! Please check your email to verify your account.";
        $_SESSION['attempts'][] = time();

        // Here, send an email verification link to the user
        // Example: sendVerificationEmail($username, $userEmail);
    } else {
        echo "Registration failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'; style-src 'self';">
    <style>
        /* Add basic styling and password strength meter */
        .strength {
            width: 100%;
            height: 10px;
            background-color: grey;
        }
        .strength-bar {
            height: 100%;
            width: 0;
            background-color: red;
        }
    </style>
    <script>
        function checkPasswordStrength(password) {
            var strengthBar = document.getElementById('strength-bar');
            var strength = 0;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[\W]/.test(password)) strength += 1;
            
            switch (strength) {
                case 1: strengthBar.style.width = '20%'; strengthBar.style.backgroundColor = 'red'; break;
                case 2: strengthBar.style.width = '40%'; strengthBar.style.backgroundColor = 'orange'; break;
                case 3: strengthBar.style.width = '60%'; strengthBar.style.backgroundColor = 'yellow'; break;
                case 4: strengthBar.style.width = '80%'; strengthBar.style.backgroundColor = 'blue'; break;
                case 5: strengthBar.style.width = '100%'; strengthBar.style.backgroundColor = 'green'; break;
            }
        }
    </script>
</head>
<body>
    <form method="post" action="register.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" oninput="checkPasswordStrength(this.value)" required>
        <div class="strength">
            <div id="strength-bar" class="strength-bar"></div>
        </div>
        <!-- Add Google reCAPTCHA -->
        <div class="g-recaptcha" data-sitekey="your-site-key"></div>
        <button type="submit">Register</button>
    </form>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>
