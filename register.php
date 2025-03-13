<?php
require 'db.php';

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $number = trim($_POST['number']);

    if (empty($username) || empty($email) || empty($password) || empty($number) ) {
        $errors[] = "Please fill in all fields.";
    }

    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$checkStmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errors[] = 'User already exists.';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password,number) VALUES (?, ?, ?,?)");
            if (!$stmt) {
                die("Error preparing insert statement: " . $conn->error);
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $username, $email, $hashed_password,$number);

            if ($stmt->execute()) {
                $success = 'Registration successful!';
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "login.php";
                        }, 3000);
                      </script>';
            } else {
                $errors[] = 'Registration failed. Please try again later.';
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
  <title>Register</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .form-container {
      background-color: #fff;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
    h2 {
      text-align: center;
      color: #333;
    }
    label {
      font-size: 14px;
      color: #333;
      display: block;
      margin: 10px 0 5px;
    }
    input {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      border: none;
      outline: none;
      background-color: #f9f9f9;
      margin-bottom: 15px;
      transition: border-bottom 0.3s ease;
    }
    input:focus {
      border-bottom: 2px solid #5f9ea0;
    }
    .underline {
      border-bottom: 2px solid #5f9ea0;
      background: none;
      padding: 10px 5px;
      font-size: 16px;
      color: #333;
    }
    button {
      background-color: #5f9ea0;
      color: white;
      border: none;
      padding: 10px;
      width: 100%;
      font-size: 16px;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #4e8a7b;
    }
    .form-footer {
      text-align: center;
      margin-top: 20px;
    }
    .form-footer a {
      color: #5f9ea0;
      text-decoration: none;
    }
    .error, .success {
      font-size: 14px;
      margin-top: 10px;
      text-align: center;
    }
    .error {
      color: red;
    }
    .success {
      color: green;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2>Register</h2>

    <form method="POST" action="register.php">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" class="underline" required><br>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" class="underline" required><br>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" class="underline" required><br>

      
      <label for="number">Number:</label>
      <input type="number" id="number" name="number" class="underline" required><br>

      <button type="submit">Register</button>

      <?php if (!empty($errors)): ?>
        <div class="error">
          <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
      <?php endif; ?>
    </form>

    <div class="form-footer">
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
  </div>

</body>
</html>
