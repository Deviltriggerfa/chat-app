<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $phone_number = $_POST['phone_number'];

    $conn = db_connect();
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $role, $phone_number);

    if ($stmt->execute()) {
        echo "<script>alert('User registered successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='add_specialist.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>