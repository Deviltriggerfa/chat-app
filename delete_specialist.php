<?php
require_once 'auth.php';
require_login();
require_admin();
require_once 'db.php';

$id = $_GET['id'];
$conn = db_connect();

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: admin.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>