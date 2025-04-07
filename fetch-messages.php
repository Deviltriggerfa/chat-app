<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: user.php");
    exit();
}

$user_name = $_SESSION['user_name'];

// Obtener el `user_id` del usuario
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
    echo json_encode([]);
    exit();
}

// Obtener los mensajes del chat
$query = "SELECT chats.*, users.name AS user_name FROM chats JOIN users ON chats.user_id = users.id WHERE user_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode($messages);
?>