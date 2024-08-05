<?php

require_once "vendor/autoload.php";

use app\chat\Controller\ChatController;

$route = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

switch ($route) {
  case "/":
    ChatController::home();

    break;

  case "/chat":
    ChatController::chat();

    break;

  case "/getMessages":
      
    ChatController::getMessages();

    break;
  
  case "/sendMessage":
      
    ChatController::sendMessage();

    break;
  
  case "/getUsers":
      
    ChatController::getUsers();

    break;
  
  case "/clearMessages":
    ChatController::clearMessages();

    break;

  case "/logout":
    ChatController::logout();

    break;

  default:
    ChatController::error404();

    break;
}
