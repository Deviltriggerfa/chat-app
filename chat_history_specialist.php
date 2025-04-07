<?php
require_once 'auth.php';
require_login();
require_specialist();
require_once 'db.php';

$specialist_id = $_SESSION['user_id'];
$conn = db_connect();

// Obtener el historial de chats del especialista
$query = "SELECT chat_history.*, users.name AS user_name FROM chat_history JOIN users ON chat_history.user_id = users.id WHERE chat_history.specialist_id = ? ORDER BY chat_history.created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $specialist_id);
$stmt->execute();
$result = $stmt->get_result();
$chat_history = [];
while ($row = $result->fetch_assoc()) {
    $chat_history[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialist Chat History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .chat-container {
            width: 80%;
            margin: 0 auto;
            margin-top: 20px;
        }
        .chat-box {
            width: 100%;
            height: 400px;
            border: 1px solid #ccc;
            overflow-y: scroll;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .user-message {
            background-color: #d1e7dd;
            text-align: left;
        }
        .specialist-message {
            background-color: #f8d7da;
            text-align: right;
        }
        .message .user {
            font-weight: bold;
        }
        .message .timestamp {
            color: #888;
            font-size: 0.85em;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h1>Specialist Chat History</h1>
        <div class="chat-box" id="chat-box">
            <?php if (!empty($chat_history)): ?>
                <?php foreach ($chat_history as $chat): ?>
                    <div class="message <?php echo $chat['user_name'] == $_SESSION['user_name'] ? 'user-message' : 'specialist-message'; ?>">
                        <div class="user"><?php echo htmlspecialchars($chat['user_name']); ?></div>
                        <div class="timestamp"><?php echo htmlspecialchars($chat['created_at']); ?></div>
                        <div class="text"><?php echo htmlspecialchars($chat['message']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No chat history available.</p>
            <?php endif; ?>
        </div>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>