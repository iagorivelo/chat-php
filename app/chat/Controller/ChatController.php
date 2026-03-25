<?php

namespace app\chat\Controller;

use app\chat\Model\ChatModel;
use app\chat\Helper\AuthHelper;

class ChatController
{
  public static function home()
  {
    session_start();

    $error = False;

    if(isset($_SESSION['user_name']) && !empty($_SESSION['user_name']))
    {
      header('Location: /chat');
    }

    if(isset($_GET['Error']) && !empty($_GET['Error']))
    {
      $error = True;
    }

    if(!isset($_SESSION['user_name']) || empty($_SESSION['user_name']))
    {
      include_once "app/chat/View/home.phtml";
    }
  }

  /**
   * Processa login ou registro de usuário
   */
  public static function authenticate()
  {
    session_start();

    if(!isset($_POST['user']) || !isset($_POST['password']))
    {
      header('Location: /?Error=MissingData');
      exit;
    }

    $user = $_POST['user'];
    $password = $_POST['password'];
    $action = $_POST['action'] ?? 'login';

    if(strlen($user) > 20)
    {
      header('Location: /?Error=NameLength');
      exit;
    }

    $chatModel = new ChatModel();

    if($action === 'register')
    {
      $result = $chatModel->register($user, $password);
      
      if(!$result['success'])
      {
        header('Location: /?Error='.$result['error']);
        exit;
      }
    }
    else
    {
      $result = $chatModel->login($user, $password);
      
      if(!$result['success'])
      {
        header('Location: /?Error='.$result['error']);
        exit;
      }
    }

    header('Location: /chat');
    exit;
  }

  public static function chat()
  {
    session_start();

    if(!AuthHelper::checkAuth())
    {
      header('Location: /');
      exit;
    }

    if(isset($_SESSION['user_name']) && strlen($_SESSION['user_name']) > 20)
    {
      AuthHelper::destroyAuthSession();
      header('Location: /?Error=NameLength');
      exit;
    }

    $chatModel = new ChatModel();
    $emojis = $chatModel->getEmojis();

    include_once "app/chat/View/chat.phtml";
  }

  public static function getMessages()
  {
    $chatModel = new ChatModel();
    $chatModel->getMessages();
  }
  
  public static function sendMessage()
  {
    if(isset($_POST['message']) && !empty($_POST['message'])) {
      $chatModel = new ChatModel();
      $chatModel->sendMessage($_POST['message']);
    }
  }
  
  public static function getUsers()
  {
    $chatModel = new ChatModel();
    $chatModel->getUsersCount();
  }
  
  public static function clearMessages()
  {
    $chatModel = new ChatModel();
    $chatModel->clearMessages();
  }

  public static function logout()
  {
    session_start();

    if(isset($_SESSION['user_id']))
    {
      $chatModel = new ChatModel();
      $chatModel->disconnect($_SESSION['user_id']);
    }

    AuthHelper::destroyAuthSession();

    header('Location: /?Logout');
  }

  public static function error404() 
  {
    include_once "app/chat/View/404.phtml";
  }
}
