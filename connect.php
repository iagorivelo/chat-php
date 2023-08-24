<?php

require_once './logic/chatControl.php';

if (isset($_POST['user']) && !empty($_POST['user'])) {
	$chatControl = new ChatControl($_POST['user']);
	$chatControl->connectChat();

	$message = "
        <span id='04b6e1a104ba0ed5e7985abde3e13140' class='connect-message'>
            <b>" . $_POST['user'] . " Conectou-se</b>
        </span><br>";

	file_put_contents('./logic/static/messages.txt', $message . PHP_EOL, FILE_APPEND);
	header('Location: ./chat.php');

} else {
	header('Location: ./index.php');
}
?>
