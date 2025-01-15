<?php
namespace App\Models;

use PDO;
use PDOException;

class Model
{
    protected $db_host = DB_HOST;
    protected $db_user = DB_USER;
    protected $db_pass = DB_PASS;
    protected $db_name = DB_NAME;
    protected $table;
    protected $connection;
    protected $query;
    protected $conditions = []; // Para almacenar las condiciones WHERE
    protected $bindings = []; // Para almacenar los valores de los parámetros de las condiciones
    protected $result; // Para almacenar el resultado de la consulta

    public function __construct()
    {
        $this->connection();
    }

    public function connection()
    {
        try {
            $this->connection = new PDO("mysql:host={$this->db_host};dbname={$this->db_name}", $this->db_user, $this->db_pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function query($sql, $params = [])
    {
        $this->query = $this->connection->prepare($sql);
        foreach ($params as $key => &$val) {
            $this->query->bindParam($key, $val);
        }
        $this->query->execute();
        $this->result = $this->query; // Guardar el resultado de la consulta
        return $this;
    }

    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE _id = :id";
        return $this->query($sql, [':id' => $id])->first();
    }

    public function first()
    {
        return $this->result->fetch(PDO::FETCH_ASSOC);
    }

    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->query($sql)->get();
    }

    public function get()
    {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->conditions);
        }
        return $this->query($sql, $this->bindings)->result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFirst()
    {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($this->conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->conditions);
        }
        return $this->query($sql, $this->bindings)->first();
    }

    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $placeholder = ':' . $column . count($this->bindings);
        $this->conditions[] = "{$column} {$operator} {$placeholder}";
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        $insert_id = $this->connection->lastInsertId();
        return $this->find($insert_id);
    }

    public function update($id, $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $fields = implode(', ', $fields);
        $sql = "UPDATE {$this->table} SET $fields WHERE _id = :id";
        $data['id'] = $id;

        $this->query($sql, $data);
        return $this->find($id);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE _id = :id";
        $this->query($sql, [':id' => $id]);
        return $this->query->rowCount();
    }
}
