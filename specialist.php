<?php
require_once 'auth.php';
require_login();
require_specialist();
require_once 'db.php';

// Obtener los chats asignados al especialista
$specialist_id = $_SESSION['user_id'];
$conn = db_connect();
$query = "SELECT DISTINCT user_id, users.name AS user_name FROM chats JOIN users ON chats.user_id = users.id WHERE specialist_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $specialist_id);
$stmt->execute();
$result = $stmt->get_result();
$active_chats = [];
while ($row = $result->fetch_assoc()) {
    $active_chats[] = $row;
}
$stmt->close();

// Obtener los mensajes del chat seleccionado
$user_id = $_GET['user_id'] ?? ($active_chats[0]['user_id'] ?? null);
$chats = [];
if ($user_id) {
    $query = "SELECT chats.*, users.name AS user_name FROM chats JOIN users ON chats.user_id = users.id WHERE user_id = ? AND specialist_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $specialist_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $chats[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialist Chat</title>
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
        .chat-form {
            display: flex;
        }
        .chat-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .chat-form button {
            padding: 10px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer.
        }
        .chat-form button:hover {
            background-color: #0056b3;
        }
        .chat-list {
            margin-bottom: 20px;
        }
        .chat-list ul {
            list-style-type: none;
            padding: 0;
        }
        .chat-list li {
            margin-bottom: 10px;
        }
        .chat-list a {
            text-decoration: none;
            color: #007bff;
        }
        .chat-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h1>Specialist Chat</h1>
        <div class="chat-list">
            <h2>Active Chats</h2>
            <ul>
                <?php if (!empty($active_chats)): ?>
                    <?php foreach ($active_chats as $chat): ?>
                        <li><a href="?user_id=<?php echo $chat['user_id']; ?>"><?php echo htmlspecialchars($chat['user_name']); ?></a></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No active chats assigned.</p>
                <?php endif; ?>
            </ul>
        </div>
        <div class="chat-box" id="chat-box">
            <?php if (!empty($chats)): ?>
                <?php foreach ($chats as $chat): ?>
                    <div class="message <?php echo $chat['user_name'] == $_SESSION['name'] ? 'user-message' : 'specialist-message'; ?>">
                        <div class="user"><?php echo htmlspecialchars($chat['user_name']); ?></div>
                        <div class="timestamp"><?php echo htmlspecialchars($chat['created_at']); ?></div>
                        <div class="text"><?php echo htmlspecialchars($chat['message']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages in this chat.</p>
            <?php endif; ?>
        </div>
        <form class="chat-form" id="chat-form" method="POST" action="send_specialist_message.php">
            <input type="text" name="message" id="message" placeholder="Type your message here..." required>
            <button type="submit">Send</button>
        </form>
        <a href="logout.php">Logout</a>
        <a href="chat_history_specialist.php">View Chat History</a>
    </div>
    <script>
        // Scroll to the bottom of the chat box
        var chatBox = document.getElementById('chat-box');
        chatBox.scrollTop = chatBox.scrollHeight;

        // WebSocket connection
        var conn = new WebSocket('ws://localhost:8080');
        conn.onopen = function(e) {
            console.log("Connection established!");
        };

        conn.onmessage = function(e) {
            var data = JSON.parse(e.data);
            var chatBox = document.getElementById('chat-box');
            var messageElement = document.createElement('div');
            messageElement.classList.add('message', data.user_name == "<?php echo $_SESSION['name']; ?>" ? 'user-message' : 'specialist-message');
            messageElement.innerHTML = `
                <div class="user">${data.user_name}</div>
                <div class="timestamp">${data.created_at}</div>
                <div class="text">${data.message}</div>
            `;
            chatBox.appendChild(messageElement);
            chatBox.scrollTop = chatBox.scrollHeight;
        };

        document.getElementById('chat-form').onsubmit = function(e) {
            e.preventDefault();
            var messageInput = document.getElementById('message');
            var message = messageInput.value;
            var user_name = "<?php echo $_SESSION['name']; ?>";
            var created_at = new Date().toLocaleString();
            var data = {
                user_name: user_name,
                message: message,
                created_at: created_at
            };
            conn.send(JSON.stringify(data));
            messageInput.value = '';

                    // Availability toggle
        document.getElementById('availability').addEventListener('change', function() {
            var available = this.checked ? 1 : 0;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_availability.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("available=" + available);
        });
        };
    </script>
</body>
</html>