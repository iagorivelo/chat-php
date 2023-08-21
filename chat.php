<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat em Tempo Real</title>
    <style>
		body {
			font-family: Arial, sans-serif;
			margin: 0;
			padding: 0;
			display: flex;
			justify-content: center;
			align-items: center;
			min-height: 100vh;
			background-color: #f4f4f4;
		}

		.chat {
			width: 80%;
			max-width: 400px;
			background-color: #fff;
			border-radius: 10px;
			padding: 20px;
			box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
		}

		.chat-window {
			height: 300px;
			overflow-y: scroll;
			border: 1px solid #ccc;
			padding: 10px;
		}
    </style>
</head>
<body>
    <div class="chat">
        <div class="chat-window" id="chat-window">
        </div>
        <input type="text" id="message" placeholder="Digite sua mensagem">
        <button onclick="sendMessage()">Enviar</button>
    </div>
    <script>
    	function getMessages() {
			const chatWindow = document.getElementById('chat-window');
			const xhr = new XMLHttpRequest();
			xhr.open('GET', 'get_messages.php', true);
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4 && xhr.status === 200) {
				    chatWindow.innerHTML = xhr.responseText;
				    chatWindow.scrollTop = chatWindow.scrollHeight;
				}
			};
//			xhr.send();
		}

		function sendMessage() {
			const messageInput = document.getElementById('message');
			const message = messageInput.value.trim();
			if (message !== '') {
				const xhr = new XMLHttpRequest();
				xhr.open('POST', 'send_message.php', true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.onreadystatechange = function () {
				    if (xhr.readyState === 4 && xhr.status === 200) {
				        getMessages();
				        messageInput.value = '';
				    }
				};
				xhr.send('message=' + encodeURIComponent(message));
			}
		}

		setInterval(getMessages, 2000);
		getMessages(); // Carregar as mensagens iniciais
    </script>
</body>
</html>

