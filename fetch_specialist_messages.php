<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: specialist.php");
    exit();
}

$specialist_id = $_SESSION['user_id'];

// Obtener los mensajes del chat asignado al especialista
$conn = db_connect();
$query = "SELECT chats.*, users.name AS user_name FROM chats JOIN users ON chats.user_id = users.id WHERE specialist_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $specialist_id);
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