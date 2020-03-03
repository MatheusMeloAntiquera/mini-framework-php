<?php

namespace Framework\Orm\Adapters;

use mysqli;
use Framework\Orm\Interfaces\DatabaseInterface;

class Mysql implements DatabaseInterface
{

    public $connection;
    private $host;
    private $username;
    private $password;
    private $db;

    public function __construct(array $config)
    {
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->db = $config['db'];
        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect()
    {
        $this->connection = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db
        );

        if ($this->connection->connect_errno) {
            throw new \Exception("Connection Mysql failed");
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function disconnect()
    {
        $this->connection->close();
    }

    public function insert(string $table, array $columns, array $values)
    {
        $columnsString = implode(",", $columns);
        $valuesBind = substr(str_repeat("? ,", count($columns)), 0, -1);

        $stmt = $this->connection->stmt_init();
        $stmt->prepare("INSERT INTO {$table} ({$columnsString}) VALUES ($valuesBind)");

        $this->bindValues($stmt, $values);

        if (!$stmt->execute()) {
            throw new \Exception($stmt->error);
        }

        return $stmt->insert_id;
    }
    public function update(string $table, array $columns, array $values, string $conditions)
    {
        $columnsString = "";
        for ($i = 0; $i < count($columns); $i++) {
            $columnsString .= "{$columns[$i]} = ? ";
            $columnsString .= ($i + 1) < count($columns) ? ", " : "";
        }

        $stmt = $this->connection->stmt_init();
        $stmt->prepare("UPDATE {$table} SET {$columnsString} WHERE {$conditions}");

        $this->bindValues($stmt, $values);

        if (!$stmt->execute()) {
            throw new \Exception($stmt->error);
        }

        return $stmt->affected_rows;
    }

    public function select($table, $columns = "*", $conditions, array $valuesOfConditions = [], $orderBy = "")
    {
        $stringCondition = !empty($conditions) ? "WHERE {$conditions}" : "";
        $stringOrderBy = !empty($orderBy) ? "ORDER BY {$orderBy}" : "";

        $stmt = $this->connection->prepare("SELECT {$columns} FROM {$table} {$stringCondition} {$stringOrderBy}");
        if (!empty($valuesOfConditions)) {
            $this->bindValues($stmt, $valuesOfConditions);
        }
        
        if (!$stmt->execute()) {
            throw new \Exception($stmt->error);
        }

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);

    }
    public function delete(string $table, string $conditions, array $valuesOfConditions = [])
    {
        $stmt = $this->connection->stmt_init();
        $stmt->prepare("DELETE FROM {$table} WHERE {$conditions}");

        if (!empty($valuesOfConditions)) {
            $this->bindValues($stmt, $valuesOfConditions);
        }

        if (!$stmt->execute()) {
            throw new \Exception($stmt->error);
        }

        return $stmt->affected_rows;
    }
    public function getTableFields($table)
    {
        if ($result = $this->connection->query("DESCRIBE {$table}")) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    private function bindValues(&$stmt, $values)
    {
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
    }
}
