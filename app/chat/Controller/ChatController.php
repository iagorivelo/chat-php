<?php

namespace app\chat\Controller;

use app\chat\Model\ChatModel;

class ChatController
{
  public static function home()
  {
    session_start();

    if(!isset($_SESSION['user_name']) || empty($_SESSION['user_name']))
    {
      include_once "app/chat/View/home.phtml";
    }
    else
    {
      header('Location: /chat');
    }
  }

  public static function chat()
  {
    session_start();

    if(!isset($_SESSION['user_name']) || empty($_SESSION['user_name']))
    {
      header('Location: /');
    }

    if (isset($_POST["user"]) && !empty($_POST["user"])) {
      $chatModel = new ChatModel();
      $chatModel->connect($_POST["user"]);
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

    $chatModel = new ChatModel();
    $chatModel->disconnect($_SESSION['user_id']);

    unset($_SESSION['user_name']);
    unset($_SESSION['user_id']);

    header('Location: /');
  }
}
