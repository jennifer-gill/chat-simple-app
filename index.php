<?php
include 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$sender_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM contacts WHERE user_id = $sender_id");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="styles.css">
    <style>
       * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(to right, #5f9ea0, rgba(3, 5, 8, 0.14));
        }

        .container {
            width: 90%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: white;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 15px;
        }

        .add-user {
            display: inline-block;
            padding: 12px 20px;
            background: #5f9ea0;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: 0.3s;
        }

        .add-user:hover {
            background: #4a8283;
        }

        /* User Cards */
        .users-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
            gap: 15px;
        }

        .user-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            width: 260px;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .user-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .username {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .email {
            font-size: 14px;
            color: #e0e0e0;
            margin-bottom: 10px;
        }

        /* Button Styles */
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .actions a {
            display: inline-block;
            padding: 10px;
            border-radius: 6px;
            font-size: 18px;
            text-decoration: none;
            transition: 0.3s;
        }

        .actions a i {
            pointer-events: none;
        }

        .chat {
            background: #5f9ea0;
            color: white;
        }

        .chat:hover {
            background: #4a8283;
        }

        .edit {
            background: #ffc107;
            color: black;
        }

        .edit:hover {
            background: #e0a800;
        }

        .delete {
            color: black;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Users List</h2>
        <a href="contact.php" class="add-user">Add New User 
        <i class="fas fa-plus"></i>
        </a>
        

        <div class="users-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-card">
                    <p class="username"><?= htmlspecialchars($row['username']) ?></p>
                    <p class="email"><?= htmlspecialchars($row['email']) ?></p>
                    <p class="number"><?= htmlspecialchars($row['number']) ?></p>
                    <div class="actions">
                        <a href="chat.php?receiver_id=<?= $row['id'] ?>&sender_id=<?= $sender_id ?>" class="chat">
                            <i class="fas fa-comments"></i>
                        </a>
                        <a href="update.php?id=<?= $row['id'] ?>" class="edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete.php?id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    </div>

</body>

</html>
