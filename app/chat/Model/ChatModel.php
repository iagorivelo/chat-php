<?php

namespace app\chat\Model;

use app\chat\DataBase\DataBaseConnect;
use app\chat\View\partials\PartialControl;

class ChatModel
{
  private $partial;
  private string $db_path = "app/db/db.sqlite";
  private array  $user;

  public function __construct()
  {
    $this->partial = new PartialControl();

    $this->user['data'] = [
      'user_id'   => '',
      'user_name' => ''
    ];
  }

  public function connect($user)
  { 
    if(isset($user) && !empty($user))
    {
      if($this->verifyUsername($user) == True) 
      {
        unset($_SESSION['user_name']);
        header('Location: /?Error=User');
      }
      else
      {
        $_SESSION['user_name'] = $this->clearString($user);

        $this->user['data'] = [
          'user_name' => $_SESSION['user_name']
        ];
  
        if (!$this->verificaExisteUsuario($this->user['data']['user_name'])) 
        {
          $db = new DataBaseConnect($this->db_path);
  
          $id_user = $db->insert('chat_users', [
  
            'user_name'    => $this->user['data']['user_name'],
            'hash'         => "0", // [Todo] - Fazer sistema de login
            'user_status'  => "A",
            'last_update'  => date('Y-m-d H:i:s'),
            'create_date'  => date('Y-m-d H:i:s')
          ]);
  
          $_SESSION['user_id'] = $id_user;
  
          $this->user['data'] = [
            'user_id' => $_SESSION['user_id']
          ];
  
          $db = new DataBaseConnect($this->db_path);
  
          $db->insert('chat_messages', [
  
            'message_content' => "",
            'send_date'       => date('Y-m-d H:i:s'),
            'user_id'         => $this->user['data']['user_id'],
            'message_type'    => "connect",
            'img_url'         => null
          ]);
        }
        else
        {
          if(!isset($_SESSION['user_id']) || empty($_SESSION['user_id']))
          {
            $db = new DataBaseConnect($this->db_path);

            $db->select('u','chat_users')
            ->where("user_name = '".$this->user['data']['user_name']."'");
    
            $user = $db->result()[0];
    
            $this->user['data'] = [
              'user_id' => $user['user_id']
            ];
    
            $_SESSION['user_id'] = $user['user_id'];

            $db->insert('chat_messages', [
  
              'message_content' => "",
              'send_date'       => date('Y-m-d H:i:s'),
              'user_id'         => $this->user['data']['user_id'],
              'message_type'    => "connect",
              'img_url'         => null
            ]);
          }

          $db = new DataBaseConnect($this->db_path);
  
          $db->update('chat_users', [
            'user_status'  => "A",
            'last_update'  => date('Y-m-d H:i:s')
          ])->where("user_name = '".$this->user['data']['user_name']."'")->fetch();
        }
      }
    }
  }
  
  public function disconnect($user_id)
  {
    if(isset($user_id) && !empty($user_id))
    {
      $db = new DataBaseConnect($this->db_path);

      $db->update('chat_users', [
        'user_status' => "I",
        'last_update' => date('Y-m-d H:i:s')
      ])
      ->where(" user_id = '$user_id' AND user_status = 'A' ")
      ->fetch();

      $db->insert('chat_messages', [
        'message_content' => "",
        'send_date'       => date('Y-m-d H:i:s'),
        'user_id'         => $user_id,
        'message_type'    => "disconnect",
        'img_url'         => null
      ]);
    }
    else 
    {
      unset($_SESSION['user_name']);
      unset($_SESSION['user_id']);

      header('Location: /');
    }
  }

  private function verificaExisteUsuario(string $user_name)
  {
    $db = new DataBaseConnect($this->db_path);

    $db->select('m', 'chat_users')
    ->where("user_name = '$user_name'");

    return count($db->result()) > 0 ? true : false;
  }

  public function getMessages()
  {
    session_start();

    $this->user['data'] = [

      'user_id'   => isset($_SESSION['user_id'])   ? $_SESSION['user_id']   : '',
      'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''
    ];   

    if(isset($this->user['data']['user_name']) && !empty($this->user['data']['user_name']))
    {
      $db = new DataBaseConnect($this->db_path);

      $db->select('m', 'chat_messages');
      $messages = $db->result();

      if (count($messages) > 0) 
      {
        foreach ($messages as $message) 
        {
          $message['own'] = $this->user['data']['user_id'] == $message['user_id'] ? true : false;

          $db->select('m', 'chat_users')
          ->where("user_id = ".$message['user_id']);

          $user = $db->result();

          $message['user_name'] = $user[0]['user_name'];
          $html = $this->partial->render('message', $message);

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

    $this->user['data'] = [

      'user_id'   => $_SESSION['user_id'],
      'user_name' => $_SESSION['user_name']
    ]; 

    $text = $this->clearString($text);
      
    $db = new DataBaseConnect($this->db_path);

    $db->insert('chat_messages', [
      'message_content' => $text,
      'send_date'       => date('Y-m-d H:i:s'),
      'user_id'         => $this->user['data']['user_id'],
      'message_type'    => "text",
      'img_url'         => null
    ]);
  }
  
  public function getUsersCount()
  {
    session_start();

    $this->user['data'] = [

      'user_id'   => isset($_SESSION['user_id'])   ? $_SESSION['user_id']   : '',
      'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''
    ]; 

    $db = new DataBaseConnect($this->db_path);
    
    $db->select('u','chat_users')
    ->where("user_status = 'A'")
    ->group('user_id');

    $result = $db->result();

    echo json_encode([
      'own_id' => $this->user['data']['user_id'],
      'count'  => count($result),
      'list'   => $result
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
