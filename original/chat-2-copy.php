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
  width: 100%;
  max-height: 400px;
  overflow-y: auto;
  border-top: 1px solid #ddd;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.message {
  padding: 10px;
  border-radius: 20px;
  margin-bottom: 10px;
  word-wrap: break-word;
  max-width: 80%;
}

.sent {
  background-color: #4caf50; 
  align-self: flex-end; 
  text-align: right;
  color: white;
}

.received {
  background-color: #e1ffc7; 
  align-self: flex-start;
  text-align: left;
  color: black;
}

.message-time {
  font-size: 0.8em;
  color: #888;
  margin-top: 5px;
  display: block;
  text-align: right; 
  padding-right: 5px;
}

  </style>
</head>

<body>
  <form method="post">
    <div id="message-container">
      <h2>Send a Message</h2>

      <input type="text" id="message" placeholder="Enter your message">
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
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();  // Expecting JSON response
    })
    .then(messages => {
      // Handle the messages (if any)
      if (messages.length === 0) {
        console.log('No messages found');
        return;
      }

      messages.forEach(message => {
        const messageDisplay = document.createElement('p');
        messageDisplay.classList.add('message', message.sender_number === sender_number ? 'sent' : 'received');
        messageDisplay.textContent = message.message;

        const timestamp = document.createElement('span');
        timestamp.classList.add('message-time');
        timestamp.textContent = formatDate(new Date(message.timestamp * 1000)); // Adjust based on message timestamp

        messageDisplay.appendChild(timestamp);
        outputElement.appendChild(messageDisplay);
      });

      // Scroll to the bottom of the container
      outputElement.scrollTop = outputElement.scrollHeight;
    })
    .catch(error => {
      console.error('Error fetching messages:', error);
    });
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
  
  if (decodedMessage.type === 'clientIdAck') {
    messageContainer.style.display = 'block';
  } else if (decodedMessage.type === 'private') {
    const messageDisplay = document.createElement('p');

 
    if (decodedMessage.from === sender_number) {
      messageDisplay.classList.add('message', 'sent'); 
    } else {
      messageDisplay.classList.add('message', 'received'); 
    }

    
    messageDisplay.textContent = decodedMessage.message;


    const timestamp = document.createElement('span');
    timestamp.classList.add('message-time');
    timestamp.textContent = formatDate(message.created_at); 

    messageDisplay.appendChild(timestamp);


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
    const messageDisplay = document.createElement('p');
    messageDisplay.classList.add('message', 'sent'); 
    messageDisplay.textContent = message;

    const timestamp = document.createElement('span');
    timestamp.classList.add('message-time');
    timestamp.textContent = formatDate(message.created_at); 

    messageDisplay.appendChild(timestamp); 
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