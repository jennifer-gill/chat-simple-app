<?php
include 'db.php';
// Get data from AJAX request
$sender_id = $_POST['sender_id'] ?? null;
$receiver_number = $_POST['receiver_number'] ?? null;
$message = $_POST['message'] ?? null;

if ($sender_id && $receiver_number && $message) {
    // Check if receiver_number exists in the contact list for the sender
    $stmt_check = $conn->prepare("SELECT * FROM contacts WHERE user_id = ? AND contact_number = ?");
    $stmt_check->bind_param("is", $sender_id, $receiver_number);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Receiver exists in contact list, proceed to insert message
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_number, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $sender_id, $receiver_number, $message);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Message stored successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to store message"]);
        }
        $stmt->close();
    } else {
        // Receiver not in contact list, add them as a new contact
        // First, check if the receiver is in the database by phone number (without user_id)
        $stmt_add_contact = $conn->prepare("INSERT INTO contacts (user_id, contact_number) VALUES (?, ?)");
        $stmt_add_contact->bind_param("is", $sender_id, $receiver_number);

        if ($stmt_add_contact->execute()) {
            // Now, proceed to insert the message
            $stmt_message = $conn->prepare("INSERT INTO messages (sender_id, receiver_number, message) VALUES (?, ?, ?)");
            $stmt_message->bind_param("sss", $sender_id, $receiver_number, $message);

            if ($stmt_message->execute()) {
                echo json_encode(["status" => "success", "message" => "Message stored and contact added successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to store message after adding contact"]);
            }
            $stmt_message->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add new contact"]);
        }

        $stmt_add_contact->close();
    }
    $stmt_check->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input data"]);
}

$conn->close();
?>
