<?php

namespace logic;

use SQLite3;

class DbControl
{
    protected $db,$query,$stringQuery;

    public function __construct(string $dbName, string $path = NULL)
    {
        if(!isset($path) || empty($path))
        {
            $path = './db/'.$dbName;
        }

        $this->db = new SQLite3($path);

        $this->createTable();
    }

    public function select(string $table_tag, string $table, array $columns = NULL)
    {
        $columnsStr = '*';

        if (isset($columns) && !empty($columns)) 
        {
            $columnsStr = implode(', ', $columns);
        }

        $this->stringQuery = " SELECT $columnsStr FROM $table AS $table_tag ";
    }

    public function update(string $table, array $setValues)
    {
        $arraySet = [];

        foreach($setValues as $key => $value)
        {
            $arraySet[] = " $key = $value ";
        }

        $this->stringQuery .=  " UPDATE $table SET ".implode(', ', $arraySet)." ";
    }

    public function where(string $whereQuery)
    {
        $this->stringQuery .=  " WHERE $whereQuery ";
    }

    public function group(string $column)
    {
        $this->stringQuery .= " GROUP BY $column ";
    }

    public function insert(string $table, array $values)
    {
        $index_values = [];
        $data_values  = [];

        foreach($values as $key => $value)
        {
            $index_values[] = "?";
            $data_values[]  = $key;
        }

        $query = $this->db->prepare("
            INSERT INTO $table (".implode(', ', $data_values).") VALUES (".implode(', ', $index_values).")
        ");

        $i = 1;

        foreach($values as $key => $value)
        {
            $query->bindValue($i, $value, SQLITE3_TEXT);
            $i++;
        }

        $query->execute();
    }

    public function fetch()
    {
       $this->db->exec($this->stringQuery);
    }

    public function result()
    {
        $this->query = $this->db->query($this->stringQuery);

        $rows = [];
    
        if ($this->query) {
            while ($row = $this->query->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
        }
    
        return $rows;
    }

    public function createTable()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS messages (
                ID INTEGER PRIMARY KEY AUTOINCREMENT,
                message TEXT,
                send_date DATETIME,
                user TEXT,
                user_status TEXT CHECK(user_status IN ('A', 'I')),
                message_type TEXT,
                img_url TEXT NULL
            );
        ");
    }

    public function clearTable() 
    {
        $this->db->exec("DELETE FROM messages;");
    }
}