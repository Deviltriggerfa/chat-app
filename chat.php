<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: user.php");
    exit();
}

$user_name = $_SESSION['user_name'];

// Verificar si el usuario ya tiene una entrada en la tabla `users`
$conn = db_connect();
$query = "SELECT id FROM users WHERE name = ? AND role = 'user' LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_name);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    $user_id = $user['id'];
} else {
    // Generar un correo electrónico temporal único
    $temp_email = uniqid('user_', true) . '@temporary.email';
    
    // Insertar un usuario temporal en la tabla `users`
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone_number) VALUES (?, ?, '', 'user', '')");
    $stmt->bind_param("ss", $user_name, $temp_email);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();
}

// Verificar si el usuario ya tiene un chat asignado
$query = "SELECT * FROM chats WHERE user_id = ? AND specialist_id IS NOT NULL LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$assigned_chat = $result->fetch_assoc();
$stmt->close();

if ($assigned_chat) {
    $specialist_id = $assigned_chat['specialist_id'];
} else {
    // Insertar el código proporcionado aquí para asignar un especialista disponible
    $query = "SELECT id FROM users WHERE role = 'specialist' AND available = 1 AND id NOT IN (SELECT specialist_id FROM chats WHERE specialist_id IS NOT NULL) LIMIT 1";
    $result = $conn->query($query);
    $specialist = $result->fetch_assoc();

    if (!$specialist) {
        // Si no hay especialistas disponibles, asignar el chat a un especialista que ya esté gestionando otros chats
        $query = "SELECT id FROM users WHERE role = 'specialist' AND available = 1 LIMIT 1";
        $result = $conn->query($query);
        $specialist = $result->fetch_assoc();
    }

    $specialist_id = $specialist['id'];
    $stmt = $conn->prepare("INSERT INTO chats (user_id, specialist_id, message, user_name) VALUES (?, ?, '', ?)");
    $stmt->bind_param("iis", $user_id, $specialist_id, $user_name);
    $stmt->execute();
    $stmt->close();
}

// Obtener los mensajes del chat
$query = "SELECT chats.*, users.name AS user_name FROM chats JOIN users ON chats.user_id = users.id WHERE user_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$chats = [];
while ($row = $result->fetch_assoc()) {
    $chats[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Chat</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="chat-container">
        <nav class="navbar">
            <h1>User Chat</h1>
            <a href="logout.php">Logout</a>
        </nav>
        <div class="chat-box" id="chat-box">
            <?php if (!empty($chats)): ?>
                <?php foreach ($chats as $chat): ?>
                    <div class="message <?php echo $chat['user_name'] == $user_name ? 'user-message' : 'specialist-message'; ?>">
                        <div class="user"><?php echo htmlspecialchars($chat['user_name']); ?></div>
                        <div class="timestamp"><?php echo htmlspecialchars($chat['created_at']); ?></div>
                        <div class="text"><?php echo htmlspecialchars($chat['message']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages in this chat.</p>
            <?php endif; ?>
        </div>
        <form class="chat-form" id="chat-form" method="POST" action="send_user_message.php">
            <input type="text" name="message" id="message" placeholder="Type your message here..." required>
            <button type="submit">Send</button>
        </form>
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
            messageElement.classList.add('message', data.user_name == "<?php echo $user_name; ?>" ? 'user-message' : 'specialist-message');
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
            var user_name = "<?php echo $user_name; ?>";
            var created_at = new Date().toLocaleString();
            var data = {
                user_name: user_name,
                message: message,
                created_at: created_at
            };
            conn.send(JSON.stringify(data));
            messageInput.value = '';
        };
    </script>
</body>
</html>