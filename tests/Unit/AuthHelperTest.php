<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use app\chat\Helper\AuthHelper;

class AuthHelperTest extends TestCase
{
    public function testHashPasswordReturnsValidHash(): void
    {
        $hash = AuthHelper::hashPassword('senha123');

        $this->assertNotEmpty($hash);
        $this->assertNotEquals('senha123', $hash);
    }

    public function testVerifyPasswordWithCorrectPassword(): void
    {
        $hash = AuthHelper::hashPassword('senha123');

        $this->assertTrue(AuthHelper::verifyPassword('senha123', $hash));
    }

    public function testVerifyPasswordWithWrongPassword(): void
    {
        $hash = AuthHelper::hashPassword('senha123');

        $this->assertFalse(AuthHelper::verifyPassword('senhaErrada', $hash));
    }

    public function testSanitizeInputRemovesHtmlTags(): void
    {
        $result = AuthHelper::sanitizeInput('<script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testSanitizeInputTrimsWhitespace(): void
    {
        $result = AuthHelper::sanitizeInput('  usuario  ');

        $this->assertEquals('usuario', $result);
    }

    public function testSanitizeInputEncodesSpecialChars(): void
    {
        $result = AuthHelper::sanitizeInput('user"name\'test');

        $this->assertStringNotContainsString('"', $result);
    }
}
