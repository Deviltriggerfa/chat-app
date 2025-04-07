<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashea la contraseña
    $role = $_POST['role'];
    $phone_number = $_POST['phone_number'];

    $conn = db_connect();
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $role, $phone_number);

    if ($stmt->execute()) {
        echo "User registered successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<form method="post" action="register.php">
    <label>Name:</label>
    <input type="text" name="name" required><br>
    <label>Email:</label>
    <input type="email" name="email" required><br>
    <label>Password:</label>
    <input type="password" name="password" required><br>
    <label>Role:</label>
    <select name="role" required>
        <option value="admin">Admin</option>
        <option value="specialist">Specialist</option>
        <option value="user">User</option>
    </select><br>
    <label>Phone Number:</label>
    <input type="text" name="phone_number" required><br>
    <button type="submit">Register</button>
</form>