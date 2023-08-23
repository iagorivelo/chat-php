<?php

session_start();

include './logic/chatControl.php';

if (isset($_POST['user']) && !empty($_POST['user'])) {
	$chatControl = new ChatControl($_POST['user']);
	$chatControl->connectChat();

	$_SESSION['numberOfUsers'] += 1;

	$message = "
        <span id='04b6e1a104ba0ed5e7985abde3e13140' class='connect-message'>
            <b>" . $_POST['user'] . " Conectou-se</b>
        </span><br>";

	file_put_contents('./logic/static/messages.txt', $message . PHP_EOL, FILE_APPEND);
} else {
	header('Location: ./index.php');
	$_SESSION['numberOfUsers'] -= 1;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>💬 Bate Papo</title>
	<link rel="stylesheet" href="public/styles/chat.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
	<main class="chat">
		<header class="d-flex justify-content-around align-items-center">
			<svg fill='#484C89' xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" height="70">
				<path d="M7.01 10.207h-.944l-.515 2.648h.838c.556 0 .97-.105 1.242-.314.272-.21.455-.559.55-1.049.092-.47.05-.802-.124-.995-.175-.193-.523-.29-1.047-.29zM12 5.688C5.373 5.688 0 8.514 0 12s5.373 6.313 12 6.313S24 15.486 24 12c0-3.486-5.373-6.312-12-6.312zm-3.26 7.451c-.261.25-.575.438-.917.551-.336.108-.765.164-1.285.164H5.357l-.327 1.681H3.652l1.23-6.326h2.65c.797 0 1.378.209 1.744.628.366.418.476 1.002.33 1.752a2.836 2.836 0 0 1-.305.847c-.143.255-.33.49-.561.703zm4.024.715l.543-2.799c.063-.318.039-.536-.068-.651-.107-.116-.336-.174-.687-.174H11.46l-.704 3.625H9.388l1.23-6.327h1.367l-.327 1.682h1.218c.767 0 1.295.134 1.586.401s.378.7.263 1.299l-.572 2.944h-1.389zm7.597-2.265a2.782 2.782 0 0 1-.305.847c-.143.255-.33.49-.561.703a2.44 2.44 0 0 1-.917.551c-.336.108-.765.164-1.286.164h-1.18l-.327 1.682h-1.378l1.23-6.326h2.649c.797 0 1.378.209 1.744.628.366.417.477 1.001.331 1.751zM17.766 10.207h-.943l-.516 2.648h.838c.557 0 .971-.105 1.242-.314.272-.21.455-.559.551-1.049.092-.47.049-.802-.125-.995s-.524-.29-1.047-.29z" />
			</svg>
			<h3><b>CHAT</b></h3>
			<div class="bg-success-subtle border border-success-subtle rounded-2 p-1 d-flex align-items-center">
				<div class="online-circle mx-2"></div> <span id='numberOfUsers'><i class="fa fa-refresh fa-spin"></i> Online</span>
			</div>
		</header>
		<div class="chat-window" id="chat-window"></div>

		<form id="chat-form">
			<input class='text-bar form-control' maxlength="500" type="text" id="message" placeholder="Digite sua mensagem">
			<button class='btn btn-primary' type="submit" onclick="sendMessage()">Enviar</button>
		</form>

	</main>
</body>
<script src="./public/js/chat.js"></script>

</html>
