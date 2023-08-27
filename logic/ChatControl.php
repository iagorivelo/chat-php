<?php

namespace logic;

require __DIR__ . '/../vendor/autoload.php';

use logic\DbControl;
use logic\PartialControl;

session_start();

if (isset($_SESSION['username']) && !empty($_SESSION['username'])) 
{
    $chatControl = new ChatControl($_SESSION['username']);

    if (isset($_GET['getMessages']))
    {
        $chatControl->getMessage();
    }
    elseif (isset($_GET['getUsers'])) 
    {
        $chatControl->getUsersCount();
    }

    if (isset($_POST['message']) && !empty($_POST['message'])) 
    {
        if ($_POST['action'] == 'send-text') 
        {
            $chatControl->sendMessage($_POST['message']);
        }
        elseif ($_POST['action'] == 'send-image')
        {
            // $chatControl->sendMessage($_POST['message']);
        }
    }
} 
else 
{
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
        $db = new DbControl('messages.sqlite');

        $db->select('m','messages');
        $db->where("user = '$this->userName' AND user_status = 'A'");
        $db->group('user');

        echo count($db->result());
    }

    public function sendMessage(string $text)
    {
        $text = $this->clearString($text);

        $db = new DbControl('messages.sqlite');

        $db->insert('messages', [
            'message'      => $text,
            'send_date'    => date('Y-m-d H:i:s'),
            'user'         => $this->userName,
            'user_status'  => "A",
            'message_type' => "text",
            'img_url'      => null
        ]);
    }

    public function connectChat()
    {
        if ($this->verifyUsername()) 
        {
            header('Location: ./index.php');
        }

        $_SESSION['username'] = $this->clearString($_POST['user']);

        $db = new DbControl('messages.sqlite','./logic/db/messages.sqlite');

        $db->insert('messages', [
            'message'      => "CONNECT",
            'send_date'    => date('Y-m-d H:i:s'),
            'user'         => $this->userName,
            'user_status'  => "A",
            'message_type' => "connect",
            'img_url'      => null
        ]);
    }

    public function disconnectChat()
    {
		header('Location: ./index.php');

        $db = new DbControl('messages.sqlite','./logic/db/messages.sqlite');

        $db->insert('messages', [
            'message'      => "DISCONNECT",
            'send_date'    => date('Y-m-d H:i:s'),
            'user'         => $this->userName,
            'user_status'  => "A",
            'message_type' => "disconnect",
            'img_url'      => null
        ]);

        $db = new DbControl('messages.sqlite','./logic/db/messages.sqlite');

        $db->update('messages',[
            'user_status' => "'I'"
        ]);
        $db->where("user = '$this->userName'");
        $db->fetch();
    }
    
    private function clearString(string $string)
    {
        return stripslashes(strip_tags($string));
    }

    private function verifyUsername()
    {
        $pattern = '~^[[:alnum:]-]+$~u';
        return !isset($this->userName) || empty(trim($this->clearString($this->userName))) || ((bool) preg_match($pattern, trim($this->clearString($this->userName)))) == false;
    }
}
