<?php
$filename = 'messages.txt';
if (file_exists($filename)) {
    $messages = file($filename);
    foreach ($messages as $message) {
        echo "<p>$message</p>";
    }
} else {
    echo "<p>Nenhuma mensagem ainda.</p>";
}
?>

