<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: user.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$message = $_POST['message'];
$user_id = $_SESSION['user_id'];

// Guardar el mensaje en la base de datos
$conn = db_connect();
$stmt = $conn->prepare("INSERT INTO chats (user_id, specialist_id, message, user_name) VALUES (?, NULL, ?, ?)");
$stmt->bind_param("iss", $user_id, $message, $user_name);
$stmt->execute();
$stmt->close();

// Enviar el mensaje a través de WebSocket
$data = json_encode([
    'user_name' => $user_name,
    'message' => $message,
    'created_at' => date('Y-m-d H:i:s')
]);
$ch = curl_init('http://localhost:8080');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);
$response = curl_exec($ch);
curl_close($ch);

header("Location: chat.php");