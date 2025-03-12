<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die("Invalid request!");
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM users WHERE id = $id");
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User</h2>
    <form method="post">
        UserName: <input type="text" name="username" value="<?= $user['username'] ?>" required><br>
        Email: <input type="email" name="email" value="<?= $user['email'] ?>" required><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>
