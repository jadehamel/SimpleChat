<?php
require 'config.php';
session_start();

// Redirect to login page if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle message sending
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

// Fetch messages for the selected chat
$messages = [];
if (isset($_GET['chat_with'])) {
    $chat_with = filter_var(trim($_GET['chat_with']), FILTER_SANITIZE_STRING);

    // Retrieve chat_with user's ID from database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $chat_with]);
    $chat_with_id = $stmt->fetchColumn();

    // If chat_with user exists, fetch messages between the current user and chat_with user
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
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>

    <!-- Form for sending messages -->
    <form method="post" action="chat.php">
        <input type="text" name="receiver" placeholder="Send message to" required>
        <textarea name="message" placeholder="Type your message here" required></textarea>
        <button type="submit">Send</button>
    </form>

    <h2>Messages</h2>
    <?php foreach ($messages as $msg): ?>
        <p>
            <strong><?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'You' : htmlspecialchars($chat_with); ?>:</strong>
            <?php echo htmlspecialchars($msg['message']); ?>
            <br>
            <small><?php echo htmlspecialchars($msg['timestamp']); ?></small>
        </p>
    <?php endforeach; ?>

    <!-- Form for starting a new chat -->
    <h3>Chat with another user</h3>
    <form method="get" action="chat.php">
        <input type="text" name="chat_with" placeholder="Username" required>
        <button type="submit">Chat</button>
    </form>
</body>
</html>
