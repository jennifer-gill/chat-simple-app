<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;   
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class ChatServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        echo "New connection: ({$conn->resourceId})\n";
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Connection closed: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

    
        if ($data['type'] === 'username') {
            $username = $data['username'];
            $this->clients[$username] = $from; 

          
            $from->send(json_encode(['type' => 'usernameAck']));
            return;
        }

        if ($data['type'] === 'private') {
            $toUsername = $data['to'];
            $message = $data['message'];
            $fromUsername = $data['from'];

            if (isset($this->clients[$toUsername])) {
                $toClient = $this->clients[$toUsername];
                $toClient->send(json_encode([
                    'type' => 'private',
                    'from' => $fromUsername,
                    'message' => $message
                ]));

                $from->send(json_encode([
                    'type' => 'private',
                    'from' => $fromUsername,
                    'message' => $message
                ]));
            } else {

                $from->send(json_encode([
                    'type' => 'error',
                    'message' => "User $toUsername not found."
                ]));
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $conn->close();
    }
}

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
