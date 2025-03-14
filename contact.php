<?php
require 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $contact_number = trim($_POST['contact_number']);
    $user_id = trim($_POST['user_id']);

    // Ensure fields are not empty
    if (empty($name) || empty($contact_number)||empty($user_id)) {
        $errors[] = "Please fill in all fields.";
    } else {
        // Check if contact already exists
        $checkStmt = $conn->prepare("SELECT id FROM contacts WHERE name = ? AND `contact_number` = ?");
        if (!$checkStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $checkStmt->bind_param("ss", $name, $contact_number);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $success = 'Contact already exists!';
        } else {
            $stmt = $conn->prepare("INSERT INTO contacts (`name`, `contact_number`,`user_id`) VALUES (?, ?,?)");
            if (!$stmt) {
                die("Error preparing statement: " . $conn->error);
            }
            $stmt->bind_param("sss", $name, $contact_number,$user_id);

            // Check if execution was successful
            if ($stmt->execute()) {
                $success = 'Contact saved successfully!';
                echo "<script>
                        alert('$success');
                        setTimeout(() => window.location.href = 'index.php', 2000);
                      </script>";
            } else {
                $errors[] = "Error saving contact: " . $stmt->error;
            }

            $stmt->close();
        }

        $checkStmt->close();
    }

   

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .form-container { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin: 10px 0 5px; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #5f9ea0; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error, .success { text-align: center; font-size: 14px; margin-top: 10px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>New Contact</h2>
        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>
            <label>Number:</label>
            <input type="number" name="contact_number" required>
            <input type="hidden" name="user_id" value="<?=$user_id?>">

            <button type="submit">Save</button>
            <?php if (!empty($errors)): ?>
                <div class="error"><?php foreach ($errors as $error) echo "<p>$error</p>"; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
