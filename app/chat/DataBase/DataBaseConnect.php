<?php

namespace app\chat\DataBase;

use SQLite3;

class DataBaseConnect
{
  protected $db, $query, $stringQuery;

  public function __construct(string $path = Null)
  {
    $this->db = new SQLite3($path);
    $this->createStartTables();
  }

  public function select(string $table_tag, string $table, array $columns = Null)
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

  public function join(string $table_tag, string $table, string $connect_table, string $join_type = "")
  {
    $this->stringQuery .=  " $join_type JOIN $table AS $table_tag ON $connect_table ";
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

    $this->stringQuery = " INSERT INTO $table (".implode(', ', $data_values).") VALUES (".implode(', ', $index_values).") ";

    $query = $this->db->prepare($this->stringQuery);

    $i = 1;

    foreach($values as $key => $value)
    {
      $query->bindValue($i, $value, SQLITE3_TEXT);
      $i++;
    }

    $query->execute();

    return $this->db->lastInsertRowID();  
  }

  public function fetch()
  {
    $this->db->exec($this->stringQuery.";");
  }

  public function result()
  {
    $this->query = $this->db->query($this->stringQuery);

    $rows = [];

    if ($this->query)
    {
      while ($row = $this->query->fetchArray(SQLITE3_ASSOC))
      {
        $rows[] = $row;
      }
    }

    return $rows;
  }

  public function stringQuery()
  {
    return $this->stringQuery;
  }

  private function create(string $table_name, array $columns, array $constraint_columns = Null)
  {
    $arr_columns = [];

    foreach ($columns as $column => $type) 
    {
      $arr_columns[] = "$column $type";
    }

    $str_columns = implode(", ", $arr_columns);

    $str_constraint = "";

    if(isset($constraint_columns) && !empty($constraint_columns))
    {
      $arr_constraint = [];

      foreach ($constraint_columns as $table => $column) 
      {
        $arr_constraint[] = " FOREIGN KEY ($column) REFERENCES $table($column) ";
      }

      $str_constraint = implode(", ", $arr_constraint); // [Todo] - Erro de sintax quando liberado as FK
    }

    $this->stringQuery = "
      CREATE TABLE IF NOT EXISTS $table_name (
        $str_columns
      );
    ";

    $this->db->exec($this->stringQuery);
  }

  public function createStartTables()
  {
    $this->create("chat_users",[

      "user_id"      => "INTEGER PRIMARY KEY AUTOINCREMENT",
      "user_name"    => "TEXT",
      "hash"         => "TEXT",
      "user_status"  => "TEXT",
      "last_update"  => "DATETIME",
      "create_date"  => "DATETIME"
    ]);

    $this->create("chat_messages",[

      "message_id"      => "INTEGER PRIMARY KEY AUTOINCREMENT",
      "message_content" => "TEXT",
      "send_date"       => "DATETIME",
      "user_id"         => "INTEGER",
      "message_type"    => "TEXT",
      "img_url"         => "TEXT NULL"
    ]);
  }

  public function clearTable() 
  {
    $this->db->exec("DELETE FROM chat_messages;");
  }
}