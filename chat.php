<?php
require 'config.php';
session_start();

// Redirect to login page if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate receiver and message inputs
    $receiver = filter_var(trim($_POST['receiver']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);
    $sender_id = $_SESSION['user_id'];

    // Retrieve receiver's user ID from database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $receiver]);
    $receiver_id = $stmt->fetchColumn();

    // If receiver exists, insert message into database
    if ($receiver_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)");
        $stmt->execute(['sender_id' => $sender_id, 'receiver_id' => $receiver_id, 'message' => $message]);
    } else {
        echo "User not found";
    }
}

// Fetch messages received from another user
$received_messages = [];
if (isset($_GET['from_user'])) {
    $from_user = filter_var(trim($_GET['from_user']), FILTER_SANITIZE_STRING);

    // Retrieve sender's user ID from database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $from_user]);
    $from_user_id = $stmt->fetchColumn();

    // If sender exists, fetch messages from them to the current user
    if ($from_user_id) {
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE sender_id = :from_user_id AND receiver_id = :user_id ORDER BY timestamp");
        $stmt->execute(['from_user_id' => $from_user_id, 'user_id' => $_SESSION['user_id']]);
        $received_messages = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
</head>
<body>
    <h1>Received Messages</h1>

    <?php foreach ($received_messages as $msg): ?>
        <div style="border: 1px solid #ccc; margin-bottom: 10px; padding: 10px;">
            <p><strong>From: <?php echo htmlspecialchars($from_user); ?></strong></p>
            <p><?php echo htmlspecialchars($msg['message']); ?></p>
            <small>Sent on: <?php echo htmlspecialchars($msg['timestamp']); ?></small>
            <hr>
            <form method="post" action="chat.php">
                <input type="hidden" name="receiver" value="<?php echo htmlspecialchars($from_user); ?>">
                <textarea name="message" placeholder="Reply to this message" required></textarea><br>
                <button type="submit">Reply</button>
            </form>
        </div>
    <?php endforeach; ?>

    <a href="chat.php">Back to Chat</a>
</body>
</html>
