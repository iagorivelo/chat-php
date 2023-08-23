<?php

    session_start();

    if(isset($_POST['clear']) && !empty($_POST['clear']))
    {
        if($_POST['clear'] == 'true')
        {
            fclose(fopen('./static/messages.txt', "w"));
        }
    }

    if(isset($_SESSION['username']) && !empty($_SESSION['username']))
    {
        $chatControl = new ChatControl($_SESSION['username']);

        if(isset($_GET['getMessages']) && !empty($_GET['getMessages']))
        {
            $chatControl->getMessage();
        }

        if(isset($_POST['message']) && !empty($_POST['message']))
        {
            if($_POST['action'] == 'send-text')
            {
                $chatControl->sendMessage($_POST['message']);
            }
        }
    }
    else
    {
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
        if (file_exists($this->filePath)) {

            foreach (file($this->filePath) as $message) 
            {
                echo "$message";
            }
        } 
        else 
        {
            echo "<p>Nenhuma mensagem ainda :(</p>";
        }
    }

    public function sendMessage(string $text = "TESTE")
    {
        $text = $this->clearString($text);

        $message = "
        <span class='own-message'>
            <b data-bs-toggle='tooltip' data-bs-placement='top' title='Send date: ".date('d/m/Y H:i:s')."'>$this->userName</b>: $text
        </span><br>";
        
        file_put_contents($this->filePath, $message . PHP_EOL, FILE_APPEND);
    }

    public function connectChat()
    {
        $pattern = '~^[[:alnum:]-]+$~u';
	
        if (
         !isset($this->userName) ||
         empty(trim($this->clearString($this->userName))) || 
         ((boolean) preg_match($pattern, trim($this->clearString($this->userName)))) == false
        )
        {
            header('Location: ./index.php');
        }

	    $_SESSION['username'] = $this->clearString($_POST['user']);
    }

	private function clearString(string $string) 
    {
        return stripslashes(strip_tags($string));
    }
}