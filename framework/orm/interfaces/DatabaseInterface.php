<?php 

namespace Framework\Orm\Interfaces;

interface DatabaseInterface {

    function connect();
    function disconnect();
    function insert(string $table, array $columns, array $values);
    function update(string $table, array $columns, array $values, string $conditions);
    function select(string $table, array $columns, string $conditions);
    function delete(string $table, string $conditions);
    function getTableFields(string $table);
}