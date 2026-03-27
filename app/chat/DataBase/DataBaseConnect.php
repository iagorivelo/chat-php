<?php

namespace app\chat\DataBase;

use SQLite3;

class DataBaseConnect
{
  protected $db, $query, $stringQuery;
  protected array $bindings = [];

  public function __construct(string $path = null)
  {
    $this->db = new SQLite3($path);
    $this->createStartTables();
  }

  public function select(string $table_tag, string $table, array $columns = null)
  {
    $columnsStr = '*';
    if (isset($columns) && !empty($columns)) {
      $columnsStr = implode(', ', $columns);
    }
    $this->stringQuery = " SELECT $columnsStr FROM $table AS $table_tag ";
    $this->bindings = [];
    return $this;
  }

  public function update(string $table, array $setValues)
  {
    $arraySet = [];
    $this->bindings = [];
    foreach ($setValues as $key => $value) {
      $arraySet[] = " $key = ? ";
      $this->bindings[] = $value;
    }
    $this->stringQuery = " UPDATE $table SET " . implode(', ', $arraySet) . " ";
    return $this;
  }

  /**
   * WHERE com placeholders seguros
   */
  public function where(string $whereQuery, array $params = [])
  {
    $this->stringQuery .= " WHERE $whereQuery ";
    $this->bindings = array_merge($this->bindings, $params);
    return $this;
  }

  public function join(string $table_tag, string $table, string $connect_table, string $join_type = "")
  {
    $this->stringQuery .= " $join_type JOIN $table AS $table_tag ON $connect_table ";
    return $this;
  }

  public function group(string $column)
  {
    $this->stringQuery .= " GROUP BY $column ";
    return $this;
  }

  public function insert(string $table, array $values)
  {
    if (empty($values)) {
      return false;
    }

    $columns = implode(', ', array_keys($values));
    $placeholders = implode(', ', array_fill(0, count($values), '?'));
    $this->stringQuery = "INSERT INTO $table ($columns) VALUES ($placeholders);";

    try {
      $query = $this->db->prepare($this->stringQuery);
      $i = 1;
      foreach ($values as $value) {
        $query->bindValue($i, $value, SQLITE3_TEXT);
        $i++;
      }
      $query->execute();
      return $this->db->lastInsertRowID();
    } catch (\Exception $e) {
      echo "Erro na consulta: " . $e->getMessage();
    }
    return "";
  }

  /**
   * Executa UPDATE/DELETE com prepared statements
   */
  public function fetch()
  {
    $stmt = $this->db->prepare($this->stringQuery . ";");
    foreach ($this->bindings as $i => $value) {
      $stmt->bindValue($i + 1, $value, SQLITE3_TEXT);
    }
    $stmt->execute();
    $this->stringQuery = "";
    $this->bindings = [];
  }

  /**
   * Executa SELECT com prepared statements
   */
  public function result()
  {
    $stmt = $this->db->prepare($this->stringQuery);
    foreach ($this->bindings as $i => $value) {
      $stmt->bindValue($i + 1, $value, SQLITE3_TEXT);
    }
    $queryResult = $stmt->execute();

    $rows = [];
    if ($queryResult) {
      while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
      }
    }
    $this->stringQuery = "";
    $this->bindings = [];
    return $rows;
  }

  public function stringQuery()
  {
    return $this->stringQuery;
  }

  private function create(string $table_name, array $columns, array $constraint_columns = null)
  {
    $arr_columns = [];
    foreach ($columns as $column => $type) {
      $arr_columns[] = "$column $type";
    }
    $str_columns = implode(", ", $arr_columns);

    $this->stringQuery = "
            CREATE TABLE IF NOT EXISTS $table_name (
                $str_columns
            );
        ";
    $this->db->exec($this->stringQuery);
  }

  public function createStartTables()
  {
    $this->create("chat_users", [
      "user_id"     => "INTEGER PRIMARY KEY AUTOINCREMENT",
      "user_name"   => "TEXT",
      "hash"        => "TEXT",
      "user_status" => "TEXT",
      "user_theme"  => "TEXT",
      "last_update"  => "DATETIME",
      "create_date"  => "DATETIME"
    ]);

    $this->create("chat_messages", [
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
