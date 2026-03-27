<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use app\chat\DataBase\DataBaseConnect;

class DataBaseConnectTest extends TestCase
{
    private DataBaseConnect $db;

    protected function setUp(): void
    {
        $this->db = new DataBaseConnect(':memory:');
    }

    public function testTablesAreCreatedOnConstruct(): void
    {
        $this->db->select('u', 'chat_users');
        $result = $this->db->result();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testInsertAndSelectUser(): void
    {
        $id = $this->db->insert('chat_users', [
            'user_name'   => 'joao',
            'hash'        => 'hash123',
            'user_status' => 'A',
            'last_update'  => '2024-01-01 00:00:00',
            'create_date'  => '2024-01-01 00:00:00',
        ]);

        $this->assertNotEmpty($id);

        $this->db->select('u', 'chat_users')->where("user_id = $id");
        $result = $this->db->result();

        $this->assertCount(1, $result);
        $this->assertEquals('joao', $result[0]['user_name']);
    }

    public function testUpdateUser(): void
    {
        $id = $this->db->insert('chat_users', [
            'user_name'   => 'maria',
            'hash'        => 'hash456',
            'user_status' => 'A',
            'last_update'  => '2024-01-01 00:00:00',
            'create_date'  => '2024-01-01 00:00:00',
        ]);

        $this->db->update('chat_users', [
            'user_status' => 'I',
        ])->where("user_id = $id")->fetch();

        $this->db->select('u', 'chat_users')->where("user_id = $id");
        $result = $this->db->result();

        $this->assertEquals('I', $result[0]['user_status']);
    }

    public function testInsertAndSelectMessage(): void
    {
        $userId = $this->db->insert('chat_users', [
            'user_name'   => 'teste',
            'hash'        => 'hash',
            'user_status' => 'A',
            'last_update'  => '2024-01-01 00:00:00',
            'create_date'  => '2024-01-01 00:00:00',
        ]);

        $this->db->insert('chat_messages', [
            'message_content' => 'Olá mundo!',
            'send_date'       => '2024-01-01 12:00:00',
            'user_id'         => $userId,
            'message_type'    => 'text',
            'img_url'         => null,
        ]);

        $this->db->select('m', 'chat_messages')->where("user_id = $userId");
        $messages = $this->db->result();

        $this->assertCount(1, $messages);
        $this->assertEquals('Olá mundo!', $messages[0]['message_content']);
    }

    public function testClearTableRemovesAllMessages(): void
    {
        $this->db->insert('chat_messages', [
            'message_content' => 'msg',
            'send_date'       => '2024-01-01 12:00:00',
            'user_id'         => 1,
            'message_type'    => 'text',
            'img_url'         => null,
        ]);

        $this->db->clearTable();

        $this->db->select('m', 'chat_messages');
        $result = $this->db->result();

        $this->assertCount(0, $result);
    }

    public function testInsertWithEmptyValuesReturnsFalse(): void
    {
        $result = $this->db->insert('chat_users', []);

        $this->assertFalse($result);
    }
}
