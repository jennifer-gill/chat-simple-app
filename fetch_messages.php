<?php
include 'db.php';

$sender_number = $_GET['sender_number'] ?? '';
$receiver_number = $_GET['receiver_number'] ?? '';

if ($sender_number && $receiver_number) {
    $stmt = $conn->prepare("SELECT sender_number, receiver_number, message FROM messages WHERE 
        (sender_number = ? AND receiver_number = ?) 
        OR (sender_number = ? AND receiver_number= ?) 
        ORDER BY id ASC");
    $stmt->bind_param("ssss", $sender_number, $receiver_number, $receiver_number, $sender_number);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode($messages);
} else {
    echo json_encode([]);
}
?>
