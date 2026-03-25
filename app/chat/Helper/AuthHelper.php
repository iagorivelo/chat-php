<?php

namespace app\chat\Helper;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthHelper
{
  private static string $secret_key = 'YOUR_SECRET_KEY_CHANGE_THIS_IN_PRODUCTION';
  private static string $algorithm = 'HS256';
  private static int $token_expiration = 86400; // 24 horas

  /**
   * Gera um hash seguro para a senha usando password_hash
   */
  public static function hashPassword(string $password): string
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  /**
   * Verifica se a senha corresponde ao hash usando password_verify
   */
  public static function verifyPassword(string $password, string $hash): bool
  {
    return password_verify($password, $hash);
  }

  /**
   * Gera um token JWT para o usuário
   */
  public static function generateToken(int $user_id, string $user_name): string
  {
    $issued_at = time();
    $expiration = $issued_at + self::$token_expiration;

    $payload = [
      'iat' => $issued_at,
      'exp' => $expiration,
      'user_id' => $user_id,
      'user_name' => $user_name
    ];

    return JWT::encode($payload, self::$secret_key, self::$algorithm);
  }

  /**
   * Valida e decodifica um token JWT
   */
  public static function validateToken(string $token): ?object
  {
    try {
      $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
      return $decoded;
    } catch (\Exception $e) {
      return null;
    }
  }

  /**
   * Sanitiza string de entrada com htmlspecialchars
   */
  public static function sanitizeInput(string $input): string
  {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
  }

  /**
   * Verifica se existe um token válido na sessão
   */
  public static function checkAuth(): bool
  {
    if (!isset($_SESSION['jwt_token']) || empty($_SESSION['jwt_token'])) {
      return false;
    }

    $decoded = self::validateToken($_SESSION['jwt_token']);
    
    if (!$decoded) {
      unset($_SESSION['jwt_token']);
      unset($_SESSION['user_id']);
      unset($_SESSION['user_name']);
      return false;
    }

    // Atualiza os dados da sessão se o token for válido
    $_SESSION['user_id'] = $decoded->user_id;
    $_SESSION['user_name'] = $decoded->user_name;

    return true;
  }

  /**
   * Cria sessão autenticada com JWT
   */
  public static function createAuthSession(int $user_id, string $user_name): void
  {
    $token = self::generateToken($user_id, $user_name);
    
    $_SESSION['jwt_token'] = $token;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $user_name;
  }

  /**
   * Destrói a sessão autenticada
   */
  public static function destroyAuthSession(): void
  {
    unset($_SESSION['jwt_token']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
  }
}
