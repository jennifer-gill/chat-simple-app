<?php
include 'db.php';

session_start(); 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$sender_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users where id != $sender_id");


?>

<!DOCTYPE html>
<html>

<head>
    <title>Users List</title>
</head>

<body>
    <h2>Contacts</h2>
    <a href="create.php">Add New User</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>UserName</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['email'] ?></td>
                <td>
                    <a target="_blank" href="chat.php?receiver_id=<?= $row['id'] ?>&sender_id=<?= $sender_id?>">Chat</a>
                    <a target="_blank" href="update.php?id=<?= $row['id'] ?>">Edit</a>
                    <a target="_blank" href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>