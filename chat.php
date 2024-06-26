<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver = $_POST['receiver'];
    $message = $_POST['message'];
    $sender_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $receiver]);
    $receiver_id = $stmt->fetchColumn();

    if ($receiver_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)");
        $stmt->execute(['sender_id' => $sender_id, 'receiver_id' => $receiver_id, 'message' => $message]);
    } else {
        echo "User not found";
    }
}

$messages = [];
if (isset($_GET['chat_with'])) {
    $chat_with = $_GET['chat_with'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $chat_with]);
    $chat_with_id = $stmt->fetchColumn();

    if ($chat_with_id) {
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = :user_id AND receiver_id = :chat_with_id) OR (sender_id = :chat_with_id AND receiver_id = :user_id) ORDER BY timestamp");
        $stmt->execute(['user_id' => $_SESSION['user_id'], 'chat_with_id' => $chat_with_id]);
        $messages = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>

    <form method="post" action="chat.php">
        <input type="text" name="receiver" placeholder="Send message to" required>
        <textarea name="message" placeholder="Type your message here" required></textarea>
        <button type="submit">Send</button>
    </form>

    <h2>Messages</h2>
    <?php foreach ($messages as $msg): ?>
        <p>
            <strong><?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'You' : $chat_with; ?>:</strong>
            <?php echo htmlspecialchars($msg['message']); ?>
            <br>
            <small><?php echo $msg['timestamp']; ?></small>
        </p>
    <?php endforeach; ?>

    <h3>Chat with another user</h3>
    <form method="get" action="chat.php">
        <input type="text" name="chat_with" placeholder="Username" required>
        <button type="submit">Chat</button>
    </form>
</body>
</html>
