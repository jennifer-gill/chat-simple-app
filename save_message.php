<?php
include 'db.php';
// Get data from AJAX request
$sender_id = $_POST['sender_id'] ?? null;
$receiver_id = $_POST['receiver_id'] ?? null;
$message = $_POST['message'] ?? null;

if ($sender_id && $receiver_id && $message) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $sender_id, $receiver_id, $message);

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
?>
