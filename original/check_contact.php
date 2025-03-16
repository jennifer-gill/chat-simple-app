<?php
require 'db.php'; // Include your database connection

header('Content-Type: application/json');

// Get the receiver's number
$receiver_number = $_POST['receiver_number'] ?? '';

if (empty($receiver_number)) {
    echo json_encode(["status" => "error", "message" => "Receiver number is required"]);
    exit;
}

// Check if the number exists in the contacts table
$stmt = $pdo->prepare("SELECT id FROM contacts WHERE contact_number = ?");
$stmt->execute([$receiver_number]);
$contact = $stmt->fetch();

if ($contact) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Contact not found"]);
}
?>
