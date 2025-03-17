<?php
include 'db.php';
$sender_number = $_POST['sender_number'] ?? null;
$receiver_number = $_POST['receiver_number'] ?? null;
$message = $_POST['message'] ?? null;
$name = "Unknown"; 

if ($sender_number && $receiver_number && $message) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_number, receiver_number, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $sender_number, $receiver_number, $message);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Message stored successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to store message"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input data"]);
}

$conn->close();
