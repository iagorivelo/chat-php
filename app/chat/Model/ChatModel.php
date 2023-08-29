<?php

namespace app\chat\Model;

use app\chat\DataBase\DataBaseConnect;
use app\chat\View\partials\PartialControl;

class ChatModel
{
  private string $userName;
  private $partial;

  public function connect($user)
  {
    if ($this->verifyUsername($user)) {
      header('Location: /');
    }

    session_start();

    $_SESSION['username'] = $this->clearString($user);

    $this->userName = $_SESSION['username'];

    if ($this->verificaExiste($this->userName) == 0) {
      $db = new DataBaseConnect('messages.sqlite', 'app/db/messages.sqlite');

      $db->insert('messages', [
        'message'      => "CONNECT",
        'send_date'    => date('Y-m-d H:i:s'),
        'user'         => $this->userName,
        'user_status'  => "A",
        'message_type' => "connect",
        'img_url'      => null
      ]);
    }
  }
  
  public function disconnect()
  {
      
  }

  public function verificaExiste($user)
  {
    $db = new DataBaseConnect('messages.sqlite', 'app/db/messages.sqlite');

    $db->select('m', 'messages');
    $db->where("user = '" . $user . "' AND user_status = 'A'");

    return count($db->result());
  }

  public function getMessages()
  {
    session_start();

    $this->partial  = new PartialControl();
    $this->userName = $_SESSION['username'];

    $db = new DataBaseConnect('messages.sqlite', 'app/db/messages.sqlite');
    $db->select('m', 'messages');

    if (count($db->result()) > 0) {
      foreach ($db->result() as $ln) {

        $ln['own'] = $this->userName == $ln['user'] ? true : false;
        $html = $this->partial->render('message', $ln);
        
        echo $html;
      }
    } else {
      echo $this->partial->render('no-messages');
    }
  }
  
  public function sendMessage(string $text)
  {
    session_start();
    
    $this->userName = $_SESSION['username'];
    
    $text = $this->clearString($text);
      
    $db = new DataBaseConnect('messages.sqlite', 'app/db/messages.sqlite');

    $db->insert('messages', [
      'message'      => $text,
      'send_date'    => date('Y-m-d H:i:s'),
      'user'         => $this->userName,
      'user_status'  => "A",
      'message_type' => "text",
      'img_url'      => null
    ]);
  }
  
  public function getUsersCount()
  {
    $db = new DataBaseConnect('messages.sqlite', 'app/db/messages.sqlite');
    
    $db->select('m','messages');
    $db->where("user_status = 'A'");
    $db->group('user');

    $result = $db->result();

    echo json_encode([
      'count' => count($result),
      'list'  => $db->result()
    ]);
  }
  
  public function clearMessages()
  {
    $db = new DataBaseConnect('messages.sqlite', 'app/db/messages.sqlite');
    $db->clearTable();
  }

  private function clearString(string $string)
  {
    return stripslashes(strip_tags($string));
  }

  private function verifyUsername(string $user)
  {
    $pattern = '~^[[:alnum:]-]+$~u';
    return !isset($user) || empty(trim($this->clearString($user))) || ((bool) preg_match($pattern, trim($this->clearString($user)))) == false;
  }
}
