<?php
require_once 'auth.php';
require_login();
require_admin();
require_once 'db.php';

$id = $_GET['id'];
$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone_number = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $phone_number, $id);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$stmt = $conn->prepare("SELECT name, email, phone_number FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name, $email, $phone_number);
$stmt->fetch();
?>

<h1>Edit Specialist</h1>
<form method="post" action="edit_specialist.php?id=<?php echo $id; ?>">
    <label>Name:</label>
    <input type="text" name="name" value="<?php echo $name; ?>" required><br>
    <label>Email:</label>
    <input type="email" name="email" value="<?php echo $email; ?>" required><br>
    <label>Phone Number:</label>
    <input type="text" name="phone_number" value="<?php echo $phone_number; ?>" required><br>
    <button type="submit">Update</button>
</form>
<a href="admin.php">Back to Admin Panel</a>

<?php
$stmt->close();
$conn->close();
?>