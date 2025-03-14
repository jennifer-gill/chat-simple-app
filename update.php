<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die("Invalid request!");
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM contacts WHERE id = $id");
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact_number = $_POST['contact_number'];

    $stmt = $conn->prepare("UPDATE contacts SET name = ?, contact_number = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $contact_number, $id);

    if ($stmt->execute()) {
        echo "success update";
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #5f9ea0;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="contact_number"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
        }
        input[type="text"]:focus, input[type="contact_number"]:focus {
            border-color: #5f9ea0;
            outline: none;
        }
        button {
            padding: 10px 20px;
            background-color: #5f9ea0;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #4e8b8c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>
        <form method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= $user['name'] ?>" required><br>
            
            <label for="contact_number">Number:</label>
            <input type="text" id="contact_number" name="contact_number" value="<?= $user['contact_number'] ?>" required><br>

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
