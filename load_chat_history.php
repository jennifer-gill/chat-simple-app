<?php
include 'db.php';
header('Content-Type: application/json');




$receiver_number = $_GET['receiver_number'] ?? null;
$sender_number = $_GET['sender_number'] ?? null;


if (!$receiver_number || !$sender_number) {
    echo json_encode(['error' => 'Receiver or sender number not provided']);
    exit;
}

$sql = "SELECT c.message, c.sender_number, u.username 
        FROM chat_history c
        JOIN users u ON c.sender_number = u.phone_number 
        WHERE c.sender_number = ? AND c.receiver_number = ?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("ss", $sender_number, $receiver_number);

$stmt->execute();
$result = $stmt->get_result();

$chat_history = [];
while ($row = $result->fetch_assoc()) {
    $chat_history[] = $row;  
}

if (empty($chat_history)) {
    echo json_encode(['status' => 'success', 'message' => 'No previous chat history found.']);
} else {
    echo json_encode(['status' => 'success', 'messages' => $chat_history]);
}


$conn->close();
?>
