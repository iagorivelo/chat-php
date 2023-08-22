<?php

    $today = date('d/m/Y H:i:s');

    session_start();

    if (isset($_POST['message'])) {

        $username = $_SESSION['username'];
        $text     = $_POST['message'];

        $message = "<span class='own-message'><b data-bs-toggle='tooltip' data-bs-placement='top' title='Send date: $today'>$username</b>: $text</span><br>";
        
        $filename = './static/messages.txt';
        file_put_contents($filename, $message . PHP_EOL, FILE_APPEND);
        echo "Mensagem enviada com sucesso!";
    }
    ?>