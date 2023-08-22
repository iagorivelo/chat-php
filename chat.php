<?php

	session_start();

	$users = [
		"<span class='badge bg-info text-dark'> 👑 Admin </span> Wagner Cunha",
		"Iago Rivelo",
		"Izadora Santana",
		"Ivalber Miguel",
		"Dayvisson Spacca",
		"Diego Filipe",
		"João Brito"
	];
	
	$pattern = '~^[[:alnum:]-]+$~u';
	
	if(!isset($_POST['userID']) || empty(trim(clean($_POST['userID']))) || ((boolean) preg_match($pattern, trim(clean($_POST['userID'])))) == false)
	{
		header('Location: ./index.php');
	}

	$_SESSION['username'] = clean($_POST['userID']); //$users[random_int(0,5)];
	
	function clean($string){
    
    	$text   = strip_tags($string);
    	$result = stripslashes($text);
    	
	  	return $result;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💬 Real time chat</title>
	<link rel="stylesheet" href="styles/chat.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>
<body>
    <div class="chat">
        <div class="chat-window" id="chat-window">
        </div>
		<form id="chat-form">
			<input class='text-bar form-control' maxlength="500" type="text" id="message" placeholder="Digite sua mensagem">
			<button class='btn btn-primary' type="submit" onclick="sendMessage()">Send</button>
		</form>
    </div>
    <script>

		document.getElementById("chat-form").addEventListener("submit",function(e){
			e.preventDefault();
		},true)

    	function getMessages() {
			const chatWindow = document.getElementById('chat-window');
			const xhr = new XMLHttpRequest();
			xhr.open('GET', 'logic/get_messages.php', true);
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
				xhr.open('POST', 'logic/send_message.php', true);
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
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</body>
</html>
