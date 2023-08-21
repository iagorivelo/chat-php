<?php
if (isset($_POST['message'])) {
    $message = $_POST['message'];
    $filename = 'messages.txt';
    file_put_contents($filename, $message . PHP_EOL, FILE_APPEND);
    echo "Mensagem enviada com sucesso!";
}
?>

