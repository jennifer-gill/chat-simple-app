<?php
include 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$_SESSION['user_online'] = true; 

$user_id = $_SESSION['user_id'];
$contact_number = $_SESSION['contact_number'];

$result = $conn->query("SELECT * FROM contacts WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(to right, #5f9ea0, rgba(3, 5, 8, 0.14));
        }

        .container {
            width: 90%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: white;
        }

        h2 {
            font-size: 26px;
            margin-bottom: 15px;
        }

        .add-user {
            display: inline-block;
            padding: 12px 20px;
            background: #5f9ea0;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: 0.3s;
        }

        .add-user:hover {
            background: #4a8283;
        }

        .users-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-top: 20px;
            gap: 15px;
        }

        .user-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .username {
            font-size: 18px;
            font-weight: bold;
            color: #fff;
        }

        .contact_number {
            font-size: 14px;
            color: #e0e0e0;
            margin-bottom: 10px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions a {
            display: inline-block;
            padding: 10px;
            border-radius: 6px;
            font-size: 18px;
            text-decoration: none;
            transition: 0.3s;
        }

        .actions a i {
            pointer-events: none;
        }

        .chat {
            background: #5f9ea0;
            color: white;
        }

        .chat:hover {
            background: #4a8283;
        }

        .edit {
            background: #ffc107;
            color: black;
        }

        .edit:hover {
            background: #e0a800;
        }

        .delete {
            background: #ff4040;
            color: white;
        }

        .delete:hover {
            background: #d81b1b;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #aaa;
            display: inline-block;
            flex-shrink: 0;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .chatbot-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #5f9ea0;
            color: white;
            padding: 15px 20px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
            font-size: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.3s;
            z-index: 999;
        }

        .chatbot-btn:hover {
            background-color: #4a8283;
        }

 
        .chat-window {
            position: fixed;
            bottom: 70px;
            right: 20px;
            background-color: #fff;
            width: 300px;
            height: 400px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .chat-header {
            background-color: #5f9ea0;
            color: white;
            padding: 10px;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 10px 10px 0 0;
        }

        .close-btn {
            background-color: transparent;
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }

        .chat-content {
            flex-grow: 1;
            padding: 10px;
            overflow-y: auto;
            background-color: #f9f9f9;
            border-bottom: 1px solid #ddd;
        }

        .chat-footer {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 0 0 10px 10px;
        }

        .chat-footer input {
            width: 80%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 14px;
            margin-right: 10px;
        }

        .chat-footer button {
            padding: 8px 15px;
            background-color: #5f9ea0;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .chat-footer button:hover {
            background-color: #4a8283;
        }

        .user-list {
            max-height: 300px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .user-list .user-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
        }

        .user-list .user-card:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 10px;
        }

        .status-indicator.online {
            background-color: green;
        }

        .status-indicator.offline {
            background-color: gray;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Contacts List</h2>
        <a href="contact.php" class="add-user">Add Contact <i class="fas fa-plus"></i></a>

        <div class="users-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-card">
                    <div class="avatar">
                        <?php if (!empty($row['profile_picture'])): ?>
                            <img src="<?= htmlspecialchars($row['profile_picture']) ?>" alt="User Avatar">
                        <?php else: ?>
                            <div style="background-color: #5f9ea0;"></div>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <p class="username"><?= htmlspecialchars($row['name']) ?></p>
                        <p class="contact_number"><?= htmlspecialchars($row['contact_number']) ?></p>                    </div>
                    <div class="actions">
                        <a href="chat.php?receiver_number=<?= $row['contact_number'] ?>&sender_number=<?= $contact_number ?>" class="chat">
                            <i class="fas fa-comments"></i>
                            <span class="status-indicator" id="status-<?= $row['contact_number'] ?>"></span>

                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="chatbot-btn" onclick="toggleChatWindow()">
        <i class="fas fa-comments"></i> Chat
    </div>

    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <span>Chat with Users</span>
            <button class="close-btn" onclick="toggleChatWindow()">X</button>
        </div>
        <div class="chat-content" id="chatContent">
            <div class="user-list" id="userList">
                <?php
                $result = $conn->query("SELECT * FROM contacts WHERE user_id = $user_id");
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="user-card" onclick="startChat(\'' . $row['contact_number'] . '\')">';
                    echo '<span>' . htmlspecialchars($row['name']) . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <div class="chat-footer">
            <input type="text" id="chatInput" placeholder="Type a message..." />
            <button id="sendBtn">Send</button>
        </div>
    </div>

    <script>
        function toggleChatWindow() {
            const chatWindow = document.getElementById('chatWindow');
            chatWindow.style.display = (chatWindow.style.display === 'flex') ? 'none' : 'flex';
            chatWindow.style.opacity = (chatWindow.style.display === 'flex') ? '1' : '0';
        }

        function startChat(contactNumber) {
           
            window.location.href = `chat.php?receiver_number=${contactNumber}&sender_number=<?= $contact_number ?>`;
        }

        document.getElementById('sendBtn').addEventListener('click', function () {
            const inputField = document.getElementById('chatInput');
            const message = inputField.value.trim();

            if (message) {
                const chatContent = document.getElementById('chatContent');

                const messageElem = document.createElement('div');
                messageElem.textContent = message;
                messageElem.style.padding = '10px';
                messageElem.style.margin = '5px 0';
                messageElem.style.backgroundColor = '#5f9ea0';
                messageElem.style.color = 'white';
                messageElem.style.borderRadius = '5px';
                chatContent.appendChild(messageElem);

                inputField.value = '';
                chatContent.scrollTop = chatContent.scrollHeight;
            }
        });


        let ws;
let isWebSocketOpen = false;

function sendMessage(data) {
    if (isWebSocketOpen) {
        ws.send(JSON.stringify(data));
    } else {
        console.log('WebSocket not open, retrying to send message...');
        setTimeout(() => sendMessage(data), 1000);
    }
}

function updateStatus(contactNumber) {
    const isOnline = sessionStorage.getItem(contactNumber) === 'online';
    const statusElement = document.getElementById('status-' + contactNumber);
    if (statusElement) {
        if (isOnline) {
            statusElement.classList.add('online');
            statusElement.classList.remove('offline');
        } else {
            statusElement.classList.add('offline');
            statusElement.classList.remove('online');
        }
    }
}

function onWebSocketOpen() {
    console.log('WebSocket connection established.');

    const contactNumbers = document.querySelectorAll('.contact_number');
    contactNumbers.forEach(function(contact) {
        const contactNumber = contact.textContent.trim();
        const status = sessionStorage.getItem(contactNumber) || 'online';
        sendMessage({ action: 'statusUpdate', contactNumber: contactNumber, status: status });
    });

    isWebSocketOpen = true;
}

ws = new WebSocket('ws://localhost:7799');

ws.addEventListener('open', onWebSocketOpen);
ws.addEventListener('message', function (event) {
    console.log('Message from server:', event.data);

    const data = JSON.parse(event.data);
    if (data.action === 'statusUpdate') {
        sessionStorage.setItem(data.contactNumber, data.status);
        updateStatus(data.contactNumber);
    }
});

ws.addEventListener('close', function (event) {
    console.log('WebSocket connection closed');
    isWebSocketOpen = false;
});

ws.addEventListener('error', function (event) {
    console.error('WebSocket error:', event);
});

window.addEventListener('load', function () {
    const contactNumbers = document.querySelectorAll('.contact_number');
    contactNumbers.forEach(function(contact) {
        const contactNumber = contact.textContent.trim();
        if (!sessionStorage.getItem(contactNumber)) {
            sessionStorage.setItem(contactNumber, 'online');
        }
        updateStatus(contactNumber);
    });

    contactNumbers.forEach(function(contact) {
        const contactNumber = contact.textContent.trim();
        sendMessage({ action: 'statusUpdate', contactNumber: contactNumber, status: 'online' });
    });
});
setInterval(() => {
    const contactNumbers = document.querySelectorAll('.contact_number');
    contactNumbers.forEach(function(contact) {
        const contactNumber = contact.textContent.trim();
        const currentStatus = sessionStorage.getItem(contactNumber) === 'online' ? 'offline' : 'online';
        sessionStorage.setItem(contactNumber, currentStatus);
        updateStatus(contactNumber);
        sendMessage({ action: 'statusUpdate', contactNumber: contactNumber, status: currentStatus });
    });
}, 6000);

window.addEventListener('beforeunload', function() {
    const contactNumbers = document.querySelectorAll('.contact_number');
    contactNumbers.forEach(function(contact) {
        const contactNumber = contact.textContent.trim();
        sendMessage({ action: 'statusUpdate', contactNumber: contactNumber, status: 'offline' });
        sessionStorage.removeItem(contactNumber);  // Clear status when the user logs out or closes the page
    });
});

function handleLogout() {
    const contactNumbers = document.querySelectorAll('.contact_number');
    contactNumbers.forEach(function(contact) {
        const contactNumber = contact.textContent.trim();
        sendMessage({ action: 'statusUpdate', contactNumber: contactNumber, status: 'offline' });

        sessionStorage.setItem(contactNumber, 'offline');
        updateStatus(contactNumber);  
    });
}

document.getElementById('logout-button').addEventListener('click', handleLogout);

    </script>

</body>

</html>
