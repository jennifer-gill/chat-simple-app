<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php'; 

$sender_number = $_GET['sender_number'] ?? '';
$receiver_number = $_GET['receiver_number'] ?? '';

if (empty($sender_number) || empty($receiver_number)) {
    echo json_encode(["error" => "Missing sender or receiver number."]);
    exit;
}

$sql = "SELECT sender_number, receiver_number, message, created_at 
        FROM messages 
        WHERE (sender_number = ? AND receiver_number = ?) 
        OR (sender_number = ? AND receiver_number = ?) 
        ORDER BY id ASC";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "SQL Error: " . $conn->error]); 
    exit;
}

$stmt->bind_param("ssss", $sender_number, $receiver_number, $receiver_number, $sender_number);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode($messages); 
} else {
    echo json_encode([]); 
}
?>
