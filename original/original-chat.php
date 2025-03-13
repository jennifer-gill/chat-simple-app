<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>

<head>
  <title>Chat User</title>
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
    const clientInfo = document.getElementById('client-info');
    const messageContainer = document.getElementById('message-container');
    const recipientInput = document.getElementById('recipient');
    const senderInput = document.getElementById('sender');
    const messageInput = document.getElementById('message');
    const sendMessageButton = document.getElementById('send-message');
    const outputElement = document.getElementById('output');


    const urlParams = new URLSearchParams(window.location.search);

  
    const sender_id = urlParams.get('sender_id');
    const receiver_id = urlParams.get('receiver_id');


    ws.onopen = () => {
      console.log('Connected to the WebSocket server');

      ws.send(JSON.stringify({
        type: 'clientId',
        clientId: sender_id
      }));
    };
  
    ws.onmessage = (message) => {
      const decodedMessage = JSON.parse(message.data);

      console.log(decodedMessage);
      

      if (decodedMessage.type === 'clientIdAck') {

        messageContainer.style.display = 'block'; 
      } else if (decodedMessage.type === 'private') {
        const messageDisplay = document.createElement('p');
        messageDisplay.classList.add('message', 'received');
        messageDisplay.textContent = `Private message from ${decodedMessage.from}: ${decodedMessage.message}`;
        outputElement.appendChild(messageDisplay);
      } else if (decodedMessage.type === 'error') {
        const errorDisplay = document.createElement('p');
        errorDisplay.classList.add('message');
        errorDisplay.textContent = `Error: ${decodedMessage.message}`;
        outputElement.appendChild(errorDisplay);
      }
    };

  
    sendMessageButton.onclick = (e) => {
      e.preventDefault();
      const message = messageInput.value;

      if (message) {
        ws.send(JSON.stringify({
          type: 'private',
          from: sender_id, // Sender's ID
          to: receiver_id, // Recipient's ID
          message: message
        }));


        const payload = {
          sender_id,
          receiver_id,
          message
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




        const messageDisplay = document.createElement('p');
        messageDisplay.classList.add('message', 'sent');
        messageDisplay.textContent = `Sent to ${receiver_id}: ${message}`;
        outputElement.appendChild(messageDisplay);

    
        messageInput.value = '';
      } else {
        alert('Please fill in both fields.');
      }
    };


    ws.onclose = () => {
      console.log('Disconnected from the WebSocket server');
    };
  </script>
</body>

</html>