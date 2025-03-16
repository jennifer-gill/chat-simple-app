<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use SplObjectStorage;
use PDO;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $pdo;

    public function __construct() {
        $this->clients = new SplObjectStorage();
        $this->users = [];

        // Database connection
        $dsn = "mysql:host=localhost;dbname=my_data_base;charset=utf8mb4";
        $username = "root";
        $password = "root";
        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection: ({$conn->resourceId})\n";
    }

    public function onClose(ConnectionInterface $conn) {
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }

        $contactNumber = array_search($conn, $this->users, true);
        if ($contactNumber !== false) {
            unset($this->users[$contactNumber]);
        }

        echo "Connection closed: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!isset($data['type'])) {
            $from->send(json_encode(["type" => "error", "message" => "Missing type in request."]));
            return;
        }

        if ($data['type'] === 'register') {
            $this->handleRegistration($from, $data);
            return;
        }

        if ($data['type'] === 'private') {
            $this->handlePrivateMessage($from, $data);
            return;
        }

        $from->send(json_encode(["type" => "error", "message" => "Invalid request type."]));
    }

    private function handleRegistration(ConnectionInterface $conn, $data) {
        if (!isset($data['contact_number']) || empty(trim($data['contact_number']))) {
            $conn->send(json_encode(["type" => "error", "message" => "Contact number cannot be empty."]));
            return;
        }

        $contactNumber = trim($data['contact_number']);

        // Check if user exists in the database
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE contact_number = ?");
        $stmt->execute([$contactNumber]);
        $user = $stmt->fetch();

        if (!$user) {
            $conn->send(json_encode(["type" => "error", "message" => "User not found."]));
            return;
        }

        // Register the user connection
        $this->users[$contactNumber] = $conn;

        // Send offline messages if any
        $stmt = $this->pdo->prepare("SELECT sender, message FROM messages WHERE receiver = ?");
        $stmt->execute([$contactNumber]);
        $messages = $stmt->fetchAll();

        foreach ($messages as $msg) {
            $conn->send(json_encode([ 
                "type" => "private",
                "from" => $msg['sender'],
                "message" => $msg['message']
            ]));
        }

        // Delete delivered messages
        $stmt = $this->pdo->prepare("DELETE FROM messages WHERE receiver = ?");
        $stmt->execute([$contactNumber]);

        $conn->send(json_encode(["type" => "registerAck", "message" => "Contact number registered."]));
    }

    private function handlePrivateMessage(ConnectionInterface $from, $data) {
        if (!isset($data['to'], $data['message'])) {
            $from->send(json_encode(["type" => "error", "message" => "Invalid private message format."]));
            return;
        }

        $toNumber = trim($data['to']);
        $message = trim($data['message']);
        $fromNumber = array_search($from, $this->users, true); // Sender's number (user_id)

        if (!$fromNumber) {
            $from->send(json_encode(["type" => "error", "message" => "Contact number not registered."]));
            return;
        }

        // Check if receiver exists in the user's contact list in the database
        $stmt = $this->pdo->prepare("SELECT * FROM contacts WHERE user_id = ? AND contact_number = ?");
        $stmt->execute([$fromNumber, $toNumber]);
        $contact = $stmt->fetch();

        if (!$contact) {
            $from->send(json_encode(["type" => "error", "message" => "Receiver is not in your contact list."]));
            return;
        }

        // Check if receiver exists in the users table
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE contact_number = ?");
        $stmt->execute([$toNumber]);
        $receiver = $stmt->fetch();

        if (!$receiver) {
            $from->send(json_encode(["type" => "error", "message" => "Receiver not found."]));
            return;
        }

        // Check if receiver is online
        if (isset($this->users[$toNumber])) {
            $toClient = $this->users[$toNumber];
            if ($this->clients->contains($toClient)) {
                $toClient->send(json_encode([
                    'type' => 'private',
                    'from' => $fromNumber,
                    'message' => $message
                ]));
                $from->send(json_encode([
                    'type' => 'private',
                    'to' => $toNumber,
                    'message' => $message
                ]));
                return;
            }
        }

        // If receiver is offline, save message in the database
        $stmt = $this->pdo->prepare("INSERT INTO messages (sender, receiver, message) VALUES (?, ?, ?)");
        $stmt->execute([$fromNumber, $toNumber, $message]);
        $from->send(json_encode(["type" => "info", "message" => "User is offline. Message saved."]));
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }
        $conn->close();
    }
}


// Start WebSocket Server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    7799
);

echo "WebSocket server running on ws://localhost:7799\n";
$server->run();
?>
