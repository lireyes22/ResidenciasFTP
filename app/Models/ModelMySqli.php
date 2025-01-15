<?php
namespace App\Models;
use mysqli;

class ModelMySqli {
    protected $db_host = DB_HOST;
    protected $db_user = DB_USER;
    protected $db_pass = DB_PASS;
    protected $db_name = DB_NAME;
    protected $table;
    protected $connection;
    protected $query;
    protected $conditions = []; // Para almacenar las condiciones WHERE
    protected $result; // Para almacenar el resultado de la consulta

    public function __construct() {
        $this->connection();
    }

    public function connection() {
        $this->connection = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
        if ($this->connection->connect_error) {
            die('Connection failed: ' . $this->connection->connect_error);
        }
    }

    public function query($sql) {
        $this->result = $this->connection->query($sql);
        return $this;
    }

    public function first() {
        return $this->result->fetch_assoc();
    }

    public function get() {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->conditions);
        }
        $this->query($sql);
        return $this->result->fetch_all(MYSQLI_ASSOC);
    }

    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->query($sql)->get();
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE _id = $id";
        return $this->query($sql)->first();
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->conditions[] = "{$column} {$operator} '{$value}'";
        return $this;
    }

    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $this->query($sql);
        $insert_id = $this->connection->insert_id;
        return $this->find($insert_id);
    }

    public function update($id, $data) {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = '{$value}'";
        }
        $fields = implode(', ', $fields);
        $sql = "UPDATE {$this->table} SET {$fields} WHERE _id = {$id}";
        $this->query($sql);
        return $this->find($id);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE _id = {$id}";
        $this->query($sql);
        return $this->connection->affected_rows;
    }
}
