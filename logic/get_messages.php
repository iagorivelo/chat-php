<?php

    $filename = './static/messages.txt';

    if (file_exists($filename)) {
        $messages = file($filename);
        foreach ($messages as $message) {
            echo "$message";
        }
    } else {
        echo "<p>Nenhuma mensagem ainda :(</p>";
    }
    ?>

