<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>

<head>
  <title>Chat User</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
      color: #333;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    #message-container {
      background-color: #5f9ea0;
      border-radius: 8px;
      padding: 20px;
      width: 300px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    input[type="text"] {
      width: 80%;
      padding: 10px;
      margin: 10px 0;
      border: none;
      border-radius: 4px;
      font-size: 14px;
    }

    button {
      padding: 10px 15px;
      background-color: #4caf50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    button:hover {
      background-color: #45a049;
    }

    #output {
      margin-top: 20px;
      width: 300px;
      max-height: 400px;
      overflow-y: auto;
      border-top: 1px solid #ddd;
    }

    .message {
      background-color: #e0f7fa;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 10px;
      max-width: 80%;
      word-wrap: break-word;
    }

    .sent {
      background-color: #c8e6c9;
      align-self: flex-end;
    }

    .received {
      background-color: #f1f8e9;
    }

    .message-time {
      font-size: 0.8em;
      color: #888;
      margin-top: 5px;
    }
  </style>
</head>

<body>
  <form method="post">
    <div id="message-container">
      <h2>Send a Message</h2>

      <input type="text" id="message" placeholder="Enter your message">
      <input type="hidden" name="receiver_number" value="receiver_number">
      <button id="send-message" type="button">Send Message</button>
    </div>

    <div id="output"></div>
  </form>

  <script>
    const ws = new WebSocket('ws://localhost:7799');
    const messageContainer = document.getElementById('message-container');
    const messageInput = document.getElementById('message');
    const sendMessageButton = document.getElementById('send-message');
    const outputElement = document.getElementById('output');

    const urlParams = new URLSearchParams(window.location.search);
    const sender_id = urlParams.get('sender_id');
    const receive_number = urlParams.get('receiver_number');
    const receiver_number = document.getElementById('receiver_number');

    ws.onopen = () => {
      console.log('Connected to the WebSocket server');
      ws.send(JSON.stringify({
        type: 'clientId',
        clientId: sender_id
      }));
    };

    ws.onmessage = (message) => {
      const decodedMessage = JSON.parse(message.data);
      if (decodedMessage.type === 'clientIdAck') {
        messageContainer.style.display = 'block';
      } else if (decodedMessage.type === 'private') {
        displayMessage(decodedMessage.from, decodedMessage.message, 'received');
      } else if (decodedMessage.type === 'error') {
        displayMessage('Error', decodedMessage.message, 'error');
      }
    };

    sendMessageButton.onclick = (e) => {
      e.preventDefault();
      const message = messageInput.value;
      console.log("Sending message to receiver_number: " + receiver_number);

      if (message) {
        const timestamp = new Date().toLocaleString();
        ws.send(JSON.stringify({
          type: 'private',
          from: sender_id,
          to: receiver_number,
          message: message
        }));

        const payload = {
          sender_id,
          receiver_number,
          message,
        };

        fetch('save_message.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams(payload).toString()
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            console.log('Message stored successfully');
          } else {
            console.error('Failed to store message:', data.message);
          }
        })
        .catch(error => console.error('Error:', error));

        displayMessage(receiver_number, message, 'sent', timestamp);

        messageInput.value = '';
      } else {
        alert('Please fill in both fields.');
      }
    };

    ws.onclose = () => {
      console.log('Disconnected from the WebSocket server');
    };

    function displayMessage(receiver_number, message, type, timestamp = '') {
      const messageDisplay = document.createElement('p');
      messageDisplay.classList.add('message', type);
      messageDisplay.textContent = ` ${message}`;

      if (timestamp) {
        const timeDisplay = document.createElement('span');
        timeDisplay.classList.add('message-time');
        timeDisplay.textContent = `(${timestamp})`;
        messageDisplay.appendChild(timeDisplay);
      }

      outputElement.appendChild(messageDisplay);
    }
  </script>
</body>

</html>
