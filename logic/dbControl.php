<?php

class DbControl
{
    protected $db,$query;

    public function __construct(string $dbName)
    {
        $path = '../db/'.$dbName;
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

        $this->query = $this->db->query("SELECT $columnsStr FROM $table AS $table_tag");
    }

    public function insert(string $table, array $values)
    {
        $query = $this->db->prepare("INSERT INTO $table (message, send_date, user, user_status) VALUES (?, ?, ?, ?)");
    
        $query->bindValue(1, $values[0], SQLITE3_TEXT);
        $query->bindValue(2, $values[1], SQLITE3_TEXT);
        $query->bindValue(3, $values[2], SQLITE3_TEXT);
        $query->bindValue(4, $values[3], SQLITE3_TEXT);
    
        $query->execute();
    }

    public function result()
    {
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
                user_status TEXT CHECK(user_status IN ('A', 'I'))
            );
        ");
    }

}

?>
