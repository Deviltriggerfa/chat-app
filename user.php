<?php
require_once 'db.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_name = $_POST['user_name'];
    session_start();
    $_SESSION['user_name'] = $user_name;
    header("Location: chat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Chat</title>
</head>
<body>
    <h1>Start Chat</h1>
    <form method="post" action="user.php">
        <label>Name:</label>
        <input type="text" name="user_name" required><br>
        <button type="submit">Start Chat</button>
    </form>
</body>
</html>