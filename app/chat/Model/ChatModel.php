<?php

namespace app\chat\Model;

use app\chat\DataBase\DataBaseConnect;
use app\chat\View\partials\PartialControl;
use app\chat\Helper\AuthHelper;

class ChatModel
{
  private $partial;
  private string $db_path = "app/db/db.sqlite";
  private array  $user;
  private DataBaseConnect $db;

  public function __construct(?DataBaseConnect $db = null)
  {
    $this->db = $db ?? new DataBaseConnect($this->db_path);
    $this->partial = new PartialControl();

    $this->user['data'] = [
      'user_id'   => '',
      'user_name' => ''
    ];
  }

  /**
   * Registra um novo usuário com autenticação segura
   */
  public function register(string $user, string $password): array
  {
    $user = AuthHelper::sanitizeInput($user);

    if ($this->verifyUsername($user) == True) {
      return ['success' => false, 'error' => 'User'];
    }

    if (strlen($password) < 6) {
      return ['success' => false, 'error' => 'PasswordLength'];
    }

    if ($this->verificaExisteUsuario($user)) {
      return ['success' => false, 'error' => 'UserExists'];
    }

    $password_hash = AuthHelper::hashPassword($password);

    $id_user = $this->db->insert('chat_users', [
      'user_name'    => $user,
      'hash'         => $password_hash,
      'user_status'  => "A",
      'last_update'  => date('Y-m-d H:i:s'),
      'create_date'  => date('Y-m-d H:i:s')
    ]);

    AuthHelper::createAuthSession($id_user, $user);

    $this->db->insert('chat_messages', [
      'message_content' => "",
      'send_date'       => date('Y-m-d H:i:s'),
      'user_id'         => $id_user,
      'message_type'    => "connect",
      'img_url'         => null
    ]);

    return ['success' => true, 'user_id' => $id_user];
  }

  /**
   * Realiza login do usuário com autenticação segura
   */
  public function login(string $user, string $password): array
  {
    $user = AuthHelper::sanitizeInput($user);

    if ($this->verifyUsername($user) == True) {
      return ['success' => false, 'error' => 'User'];
    }

    $this->db->select('u', 'chat_users')
      ->where("user_name = ?", [$user]);

    $users = $this->db->result();

    if (count($users) == 0) {
      return ['success' => false, 'error' => 'UserNotFound'];
    }

    $user_data = $users[0];

    if (!AuthHelper::verifyPassword($password, $user_data['hash'])) {
      return ['success' => false, 'error' => 'InvalidPassword'];
    }

    AuthHelper::createAuthSession($user_data['user_id'], $user_data['user_name']);

    $this->db->update('chat_users', [
      'user_status' => 'A',
      'last_update' => date('Y-m-d H:i:s')
    ])->where("user_id = ?", [$user_data['user_id']])->fetch();

    $this->db->insert('chat_messages', [
      'message_content' => "",
      'send_date'       => date('Y-m-d H:i:s'),
      'user_id'         => $user_data['user_id'],
      'message_type'    => "connect",
      'img_url'         => null
    ]);

    return ['success' => true, 'user_id' => $user_data['user_id']];
  }

  public function disconnect($user_id)
  {
    if (isset($user_id) && !empty($user_id)) {
      $this->db->update('chat_users', [
        'user_status' => "I",
        'last_update' => date('Y-m-d H:i:s')
      ])
        ->where("user_id = ? AND user_status = ?", [$user_id, 'A'])->fetch();

      $this->db->insert('chat_messages', [
        'message_content' => "",
        'send_date'       => date('Y-m-d H:i:s'),
        'user_id'         => $user_id,
        'message_type'    => "disconnect",
        'img_url'         => null
      ]);
    } else {
      unset($_SESSION['user_name']);
      unset($_SESSION['user_id']);

      header('Location: /');
    }
  }

  private function verificaExisteUsuario(string $user_name)
  {
    $this->db->select('m', 'chat_users')
      ->where("user_name = ?", [$user_name]);

    return count($this->db->result()) > 0 ? true : false;
  }

  public function getMessages()
  {
    session_start();

    $this->user['data'] = [

      'user_id'   => isset($_SESSION['user_id'])   ? $_SESSION['user_id']   : '',
      'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''
    ];

    if (isset($this->user['data']['user_name']) && !empty($this->user['data']['user_name'])) {
      $this->db->select('m', 'chat_messages');
      $messages = $this->db->result();

      if (count($messages) > 0) {
        foreach ($messages as $message) {
          $message['own'] = $this->user['data']['user_id'] == $message['user_id'] ? true : false;

          $this->db->select('m', 'chat_users')
            ->where("user_id = ?", [$message['user_id']]);

          $user = $this->db->result();

          $message['user_name'] = $user[0]['user_name'];
          $html = $this->partial->render('message', $message);

          echo $html;
        }
      } else {
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

    $text = AuthHelper::sanitizeInput($text);

    $this->db->insert('chat_messages', [
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

    $this->db->select('u', 'chat_users')
      ->where("user_status = 'A'")
      ->group('user_id');

    $result = $this->db->result();

    echo json_encode([
      'own_id' => $this->user['data']['user_id'],
      'count'  => count($result),
      'list'   => $result
    ]);
  }

  public function clearMessages()
  {
    $this->db->clearTable();
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

    if ($response) {
      $emojis = json_decode($response, true);
    }

    return $emojis;
  }
}
