<?php

include_once './dbControl.php';

session_start();

if (isset($_POST['clear']) && !empty($_POST['clear'])) {
    if ($_POST['clear'] == 'true') {
        fclose(fopen('./static/messages.txt', "w"));
    }
}

if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $chatControl = new ChatControl($_SESSION['username']);

    if (isset($_GET['getMessages']) && !empty($_GET['getMessages'])) {
        $chatControl->getMessage();
    }

    if (isset($_GET['getUsers']) && !empty($_GET['getUsers'])) {
        $chatControl->getUsersCount();
    }

    if (isset($_POST['message']) && !empty($_POST['message'])) {
        if ($_POST['action'] == 'send-text') {
            $chatControl->sendMessage($_POST['message']);
        }
    }
} else {
    header('Location: ./index.php');
}

class ChatControl
{
    private string $userName;
    private string $filePath  = './static/messages.txt';

    public function __construct(string $userName)
    {
        $this->userName = $userName;
    }

    public function getMessage()
    {
        $db = new DbControl('messages.sqlite');
        $db->select('m','messages');
        
        if(count($db->result()) > 0) 
        {
            foreach ($db->result() as $ln) 
            {
                $html = "
                <span class='other-message bg-primary-subtle border border-success-subtle'>
                    <b data-bs-toggle='tooltip' data-bs-placement='top' title='Send date: {$ln['send_date']}'>{$ln['user']}</b>:{$ln['message']}
                </span><br>
                ";

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
        if (file_exists($this->filePath)) 
        {
            $online = 0;

            foreach (file($this->filePath) as $message) 
            {
                $desconnect = strpos($message, '6269d77081ed0d003f6f4fd002dae3a8');
                $connect    = strpos($message, '04b6e1a104ba0ed5e7985abde3e13140');

                if ($connect) {
                    $online++;
                }
                if ($desconnect) {
                    $online--;
                }
            }

            echo $online . ' Online';

        } 
        else 
        {
            echo "0 Online";
        }
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
