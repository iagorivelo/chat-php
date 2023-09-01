<?php

namespace app\chat\Model;

use app\chat\DataBase\DataBaseConnect;
use app\chat\View\partials\PartialControl;

class ChatModel
{
  private $partial;

  private string $userName;
  private int    $userID;
  private string $db_path = "app/db/db.sqlite";

  public function connect($user)
  {
    if ($this->verifyUsername($user)) 
    {
      header('Location: /');
    }

    $_SESSION['username'] = $this->clearString($user);
    $this->userName = $_SESSION['username'];

    if ($this->verificaExiste($this->userName) == 0) 
    {
      $db = new DataBaseConnect($this->db_path);

      $id = $db->insert('chat_users', [

        'user_name'    => $this->userName,
        'hash'         => "", // [Todo] - Fazer sistema de login
        'user_status'  => "A",
        'last_update'  => date('Y-m-d H:i:s'),
        'create_date'  => date('Y-m-d H:i:s')
      ]);

      $_SESSION['user_id'] = $id;
      $this->userID = $_SESSION['user_id'];

      $db = new DataBaseConnect($this->db_path);

      $db->insert('chat_messages', [

        'message_content' => "",
        'send_date'       => date('Y-m-d H:i:s'),
        'user_id'         => $this->userID,
        'message_type'    => "connect",
        'img_url'         => null
      ]);
    }
  }
  
  public function disconnect($user_id)
  {
    $db = new DataBaseConnect($this->db_path);

    $db->update('chat_users',[
      'user_status' => "'I'",
      'last_update'  => "'".date('Y-m-d H:i:s')."'"
    ]);
    $db->where(" user_id = '" . $user_id . "' AND user_status = 'A' ");

    $db->fetch();

    $db = new DataBaseConnect($this->db_path);

    $db->insert('chat_messages', [

      'message_content' => "",
      'send_date'       => date('Y-m-d H:i:s'),
      'user_id'         => $user_id,
      'message_type'    => "disconnect",
      'img_url'         => null
    ]);
  }

  public function verificaExiste($user_name)
  {
    $db = new DataBaseConnect($this->db_path);

    $db->select('m', 'chat_users');
    $db->where("user_name = $user_name");

    return count($db->result());
  }

  public function getMessages()
  {
    session_start();

    if(isset($_SESSION['username']) && !empty($_SESSION['username']))
    {
      $this->partial  = new PartialControl();

      $this->userName = $_SESSION['username'];
      $this->userID   = $_SESSION['user_id'];

      $db = new DataBaseConnect($this->db_path);

      $db->select('m', 'chat_messages');
      $messages = $db->result();

      if (count($messages) > 0) 
      {
        foreach ($messages as $ln) 
        {
          $ln['own'] = $this->userID == $ln['user_id'] ? true : false;

          $db->select('m', 'chat_users');
          $db->where("user_id = ".$ln['user_id']);
          $user = $db->result()[0];

          $ln['user_name'] = $user['user_name'];
          $html = $this->partial->render('message', $ln);
          echo $html;
        }
      } 
      else 
      {
        echo $this->partial->render('no-messages');
      }
    }
  }
  
  public function sendMessage(string $text)
  {
    session_start();
    
    $this->userName = $_SESSION['username'];
    $this->userID   = $_SESSION['user_id'];
    
    $text = $this->clearString($text);
      
    $db = new DataBaseConnect($this->db_path);

    $db->insert('chat_messages', [
      'message_content' => $text,
      'send_date'       => date('Y-m-d H:i:s'),
      'user_id'         => $this->userID,
      'message_type'    => "text",
      'img_url'         => null
    ]);
  }
  
  public function getUsersCount()
  {
    $db = new DataBaseConnect($this->db_path);
    
    $db->select('u','chat_users');
    $db->where("user_status = 'A'");
    $db->group('user_id');

    $result = $db->result();

    echo json_encode([
      'count' => count($result),
      'list'  => $db->result()
    ]);
  }
  
  public function clearMessages()
  {
    $db = new DataBaseConnect($this->db_path);
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
  
  public function getEmojis()
  {
    $apiKey = '194953e82591ea5ae5a8b1ad1bdfdf9c95f944e6';
    $url = "https://emoji-api.com/emojis?access_key=$apiKey";

    $response = file_get_contents($url);
    $emojis = [];

    if ($response)
    {
        $emojis = json_decode($response,true);
    }
                
    return $emojis;
  }
}
