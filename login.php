<?php
session_start();  

include 'db.php'; 


if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_number = $_POST['contact_number'];
    $password = $_POST['password'];

   
    $contact_number = htmlspecialchars($contact_number);
    $password = htmlspecialchars($password);

  
    $query = "SELECT * FROM users WHERE contact_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $contact_number);  
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['contact_number'] = $user['contact_number'];

            echo 'Login successful! Redirecting...';
            header('Location: index.php');
            exit(); 
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'number does not exist.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 300px;
            text-align: center;
        }

        h2 {
            color: #5f9ea0;
            margin-bottom: 20px;
        }

        label {
      font-size: 14px;
      color: #333;
      display: block;
      margin: 10px 0 5px;
    }
    input {
      width: 90%;
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

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="login.php">
        <label for="contact_number">Contact_Number:</label>
      <input type="contact_number" id="contact_number" name="contact_number" class="underline" required><br>


            <label for="password">Password:</label>
            <input type="password" name="password"  class="underline" required><br>

            <button type="submit">Login</button>
        </form>
        
    
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>

