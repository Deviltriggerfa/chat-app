<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_name']) || !isset($_POST['available'])) {
    http_response_code(400);
    exit();
}

$specialist_id = $_SESSION['user_id'];
$available = $_POST['available'];

$conn = db_connect();
$query = "UPDATE users SET available = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $available, $specialist_id);
$stmt->execute();
$stmt->close();
$conn->close();