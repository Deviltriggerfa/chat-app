<?php
require_once 'auth.php';
require_login();
require_specialist();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $specialist_id = $_SESSION['user_id'];
    // Por simplicidad, asignaremos el user_id a 1 (en una implementación real, esto debería basarse en el chat activo)
    $user_id = 1;

    $conn = db_connect();
    $stmt = $conn->prepare("INSERT INTO chats (user_id, specialist_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $specialist_id, $message);

    if ($stmt->execute()) {
        header("Location: specialist.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>