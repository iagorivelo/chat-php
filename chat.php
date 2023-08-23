<?php

	session_start();

	include './logic/chatControl.php';

	if(isset($_POST['user']) && !empty($_POST['user']))
	{
		$chatControl = new ChatControl($_POST['user']);
		$chatControl->connectChat();

		$message = "
        <span class='connect-message'>
            <b>".$_POST['user']." Conectou-se</b>
        </span><br>";
        
        file_put_contents('./logic/static/messages.txt', $message . PHP_EOL, FILE_APPEND);
	}
	else
	{
		header('Location: ./index.php');
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 Bate Papo</title>
	<link rel="stylesheet" href="styles/chat.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
	<script src="./public/js/chat.js"></script>
</head>
<body>
    <main class="chat">

        <div class="chat-window" id="chat-window"></div>

		<form id="chat-form">
			<input class='text-bar form-control' maxlength="500" type="text" id="message" placeholder="Digite sua mensagem">
			<button class='btn btn-primary' type="submit" onclick="sendMessage()">Enviar</button>
		</form>

    </main>
</body>

<script>

	document.getElementById("chat-form").addEventListener("submit",function(event){
		event.preventDefault();
	},true)

	setInterval(getMessages, 2000);
	getMessages();

	setInterval(clearMessages, (1000 * 60) * 30); // 30 Min

	function clearMessages() {

		const xhr = new XMLHttpRequest();
		xhr.open('POST', 'logic/chatControl.php', true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send('&clear=true');
	}

	function getMessages() {

		const chatWindow = document.getElementById('chat-window');
		const xhr = new XMLHttpRequest();
		xhr.open('GET', 'logic/chatControl.php?getMessages=true', true);
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4 && xhr.status === 200) {
				chatWindow.innerHTML = xhr.responseText;
				chatWindow.scrollTop = chatWindow.scrollHeight;
			}
		};
		xhr.send();
	}

	function sendMessage(event) {

		const messageInput = document.getElementById('message');
		const message = messageInput.value.trim();
		if (message !== '') {
			const xhr = new XMLHttpRequest();
			xhr.open('POST', 'logic/chatControl.php', true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4 && xhr.status === 200) {
					getMessages();
					messageInput.value = '';
				}
			};
			xhr.send('message=' + encodeURIComponent(message) + '&action=send-text');
		}
	}

</script>

</html>
