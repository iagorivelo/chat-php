<?php

session_start();

require_once __DIR__ . '/partialControl.php';
require_once __DIR__ . '/dbControl.php';

if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $chatControl = new ChatControl($_SESSION['username']);

    if (isset($_GET['getMessages']) && !empty($_GET['getMessages'])) {
        $chatControl->getMessage();
    }

    if (isset($_GET['getUsers']) && !empty($_GET['getUsers'])) {
        // $chatControl->getUsersCount();
    }

    if (isset($_POST['message']) && !empty($_POST['message'])) {
        if ($_POST['action'] == 'send-text') {
            $chatControl->sendMessage($_POST['message']);
        }
    }
} else {
    header('Location: ../index.php');
}

class ChatControl
{
    private string $userName;
    private $partial;

    public function __construct(string $userName)
    {
        $this->userName = $userName;
        $this->partial = new PartialControl();
    }

    public function getMessage()
    {
        $db = new DbControl('messages.sqlite');
        $db->select('m','messages');
        
        if(count($db->result()) > 0) 
        {
            foreach ($db->result() as $ln) 
            {
                $ln['own'] = $this->userName == $ln['user'] ? true : false;
                $html      = $this->partial->render('message', $ln);

                echo $html;
            }
        } 
        else 
        {
            echo "<p>Nenhuma mensagem ainda</p>";
        }
    }

    public function getUsersCount()
    {

    }

    public function sendMessage(string $text)
    {
        $text = $this->clearString($text);

        $db = new DbControl('messages.sqlite');

        $db->insert('messages', [
            $text,
            date('Y-m-d H:i:s'),
            $this->userName,
            "A"
        ]);
    }

    public function connectChat()
    {
        $pattern = '~^[[:alnum:]-]+$~u';

        if (
            !isset($this->userName) ||
            empty(trim($this->clearString($this->userName))) ||
            ((bool) preg_match($pattern, trim($this->clearString($this->userName)))) == false
        ) {
            header('Location: ./index.php');
        }

        $_SESSION['username'] = $this->clearString($_POST['user']);
    }

    private function clearString(string $string)
    {
        return stripslashes(strip_tags($string));
    }
}
