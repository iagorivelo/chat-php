<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use app\chat\Model\ChatModel;

class ChatModelTest extends TestCase
{
    private ChatModel $model;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $this->model = new ChatModel();
    }

    public function testRegisterWithShortPasswordFails(): void
    {
        $result = $this->model->register('usuario1', '123');

        $this->assertFalse($result['success']);
        $this->assertEquals('PasswordLength', $result['error']);
    }

    public function testRegisterWithInvalidUsernameFails(): void
    {
        $result = $this->model->register('', 'senha123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('User', $result['error']);
    }

    public function testRegisterWithSpecialCharsInUsernameFails(): void
    {
        $result = $this->model->register('user<script>', 'senha123456');

        $this->assertFalse($result['success']);
    }

    public function testLoginWithNonExistentUserFails(): void
    {
        $result = $this->model->login('naoexiste', 'senha123');

        $this->assertFalse($result['success']);
        $this->assertEquals('UserNotFound', $result['error']);
    }
}
