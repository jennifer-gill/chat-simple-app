<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat User</title>
  <style>
 <style>
  body {
    font-family: Arial, sans-serif;
    background-color: #e5e5e5;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    height: 100vh;
    justify-content: flex-end;
  }

  #message-container {
    background-color: #5f9ea0;
    border-radius: 8px;
    padding: 20px;
    width: 100%;
    max-width: 600px; /* Limit the max width */
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    height: 100%;
    position: relative;
    margin: auto;
  }

  #output {
    margin-top: 20px;
    width: 100%;
    height: calc(100% - 150px); 
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-right: 15px;
  }

  .message {
    padding: 12px;
    border-radius: 20px;
    margin-bottom: 10px;
    word-wrap: break-word;
    max-width: 80%;
    display: inline-block;
    position: relative;
  }

  .sent {
    background-color: #4caf50;
    align-self: flex-end;
    color: white;
    text-align: right;
    border-radius: 20px;
  }

  .received {
    background-color: #ffffff;
    border: 1px solid #ddd;
    align-self: flex-start;
    color: black;
    text-align: left;
    border-radius: 20px;
  }

  .message-time {
    font-size: 0.8em;
    color: black;
    margin-top: 5px;
    text-align: right;
    padding-right: 5px;
    margin-left:5px;
  }
  .input-box {
    width: 100%;
    bottom: 0;

  }

  input[type="text"] {
    width: 80%;
    padding: 10px;
    margin-right: 10px;
    border-radius: 20px;
    border: 1px solid #ddd;
    outline: none;
    font-size: 16px;
  }

  button {
    padding: 10px 15px;
    background-color: #4caf50;
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
  }

  button:hover {
    background-color: #45a049;
  }

  .chat-header {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 15px;
    font-size: 18px;
    color: #fff;
  }

  .chat-header .name {
    font-weight: bold;
  }

  @media (max-width: 768px) {
    #message-container {
      width: 100%; 
      padding: 15px;
    }

    .input-box {
      flex-direction: column;
      align-items: stretch;
    }

    input[type="text"] {
      width: 100%; 
      margin-bottom: 10px;
    }

    button {
      width: 100%;
      padding: 12px;
    }

    .chat-header {
      font-size: 16px;
    }
  }

  @media (max-width: 480px) {
    #message-container {
      width: 100%; 
      padding: 10px;
    }

   

    input[type="text"] {
      padding: 8px;
      font-size: 14px;
    }

    button {
      padding: 10px;
      font-size: 14px;
    }

    .chat-header {
      font-size: 14px;
    }

   
  }

</style>


  </style>
</head>

<body>
  <form method="post">
    <div id="message-container">
      <div class="chat-header">
        <span class="name">Chat</span>
      </div>

      <!-- Message List -->
      <div id="output"></div>

      <!-- Input Box -->
      <div class="input-box">
        <input type="text" id="message" placeholder="Type a message..." />
        <button id="send-message" type="button">Send</button>
      </div>
    </div>
  </form>

  <script>
    const ws = new WebSocket('ws://localhost:7799');
    const messageContainer = document.getElementById('message-container');
    const messageInput = document.getElementById('message');
    const sendMessageButton = document.getElementById('send-message');
    const outputElement = document.getElementById('output');

    const urlParams = new URLSearchParams(window.location.search);
    const sender_number = urlParams.get('sender_number');
    const receiver_number = urlParams.get('receiver_number');

    ws.onopen = () => {
      console.log('Connected to the WebSocket server');
      ws.send(JSON.stringify({
        type: 'clientId',
        clientId: sender_number
      }));
    };

    function fetchMessages() {
      fetch(`get_messages.php?sender_number=${sender_number}&receiver_number=${receiver_number}`)
        .then(response => response.json())
        .then(messages => {
          if (messages.length === 0) {
            console.log('No messages found');
            return;
          }

          messages.forEach(message => {
            const messageDisplay = document.createElement('div');
            messageDisplay.classList.add('message', message.sender_number === sender_number ? 'sent' : 'received');
            messageDisplay.textContent = message.message;

            const timestamp = document.createElement('span');
            timestamp.classList.add('message-time');
            timestamp.textContent = formatDate(new Date(message.created_at));

            messageDisplay.appendChild(timestamp);
            outputElement.appendChild(messageDisplay);
          });
          outputElement.scrollTop = outputElement.scrollHeight;
        })
        .catch(error => console.error('Error fetching messages:', error));
    }

    function formatDate(timestamp) {
      const date = new Date(timestamp);
      const hours = date.getHours();
      const minutes = date.getMinutes();
      const ampm = hours >= 12 ? 'PM' : 'AM';
      const hourFormatted = hours % 12 || 12;
      const minuteFormatted = minutes < 10 ? '0' + minutes : minutes;
      return `${hourFormatted}:${minuteFormatted} ${ampm}`;
    }

    ws.onmessage = (message) => {
      const decodedMessage = JSON.parse(message.data);

      if (decodedMessage.type === 'private') {
        const messageDisplay = document.createElement('div');
        messageDisplay.classList.add('message', decodedMessage.from === sender_number ? 'sent' : 'received');
        messageDisplay.textContent = decodedMessage.message;

        const timestamp = document.createElement('span');
        timestamp.classList.add('message-time');
        timestamp.textContent = formatDate(new Date().getTime());

        messageDisplay.appendChild(message.created_at);
        outputElement.appendChild(messageDisplay);
        outputElement.scrollTop = outputElement.scrollHeight;
      }
    };

    sendMessageButton.onclick = (e) => {
      e.preventDefault();
      const message = messageInput.value;

      const payload = {
        sender_number,
        receiver_number,
        message,
      };

      if (message) {
        const messageDisplay = document.createElement('div');
        messageDisplay.classList.add('message', 'sent');
        messageDisplay.textContent = message;

        const timestamp = document.createElement('span');
        timestamp.classList.add('message-time');
        timestamp.textContent = formatDate(new Date().getTime());

        messageDisplay.appendChild(message.created_at);
        outputElement.appendChild(messageDisplay);
        outputElement.scrollTop = outputElement.scrollHeight;

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
              ws.send(JSON.stringify({
                type: 'private',
                from: sender_number,
                to: receiver_number,
                message: message,
                me: true,
              }));
              console.log('Message stored successfully');
            } else {
              console.error('Failed to store message:', data.message);
            }
          })
          .catch(error => console.error('Error:', error));
      } else {
        alert('Please fill in both fields.');
      }
    };

    ws.onclose = () => {
      console.log('Disconnected from the WebSocket server');
    };

    fetchMessages();
  </script>
</body>

</html>
