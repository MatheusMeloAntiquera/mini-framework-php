<?php

namespace Framework\Orm;

use Exception;
use Framework\Orm\Factory;
use Framework\Orm\Interfaces\InterfaceModel;

class Model implements InterfaceModel
{

    protected $table;
    protected $primaryKey = 'id';
    protected $adapter;

    private $fields = [];
    private $attributes = [];

    public function __construct()
    {
        $factory = new Factory();
        $this->adapter = $factory->getAdapter();
        $this->setAttributes();
    }

    private function setAttributes()
    {
        $fields = $this->adapter->getTableFields($this->table);
        foreach ($fields as $field) {
            $this->fields[] = $field['Field'];
            $this->attributes[$field['Field']] = null;
        }
    }

    public function getAttributes(){
        return $this->attributes;
    }

    public function save()
    {

        try {
            $attributes = array_filter($this->attributes);
            $this->{$this->primaryKey} = $this->adapter->insert($this->table, array_keys($attributes), array_values($attributes));
            return true;
        } catch (Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
        }
    }

    public static function find($id)
    {
        $classModel = get_called_class();
        $newModel = new $classModel();

        $table = $newModel->table;
        $columns = implode(',', $newModel->fields);
        $conditions = "{$newModel->primaryKey} = ?";
        $valuesConditions = [$id];

        $result = $newModel->adapter->select($table, $columns, $conditions, $valuesConditions);
        if (!empty($result)) {
            $result = $result[0];

            foreach ($result as $field => $value) {
                $newModel->{$field} = $value;
            }

            return $newModel;
        }
        return null;
    }

    public static function all()
    {
        $classModel = get_called_class();
        $baseModel = new $classModel();

        $table = $baseModel->table;
        $columns = implode(',', $baseModel->fields);
        $conditions = null;
        $valuesConditions = [];

        $results = $baseModel->adapter->select($table, $columns, $conditions, $valuesConditions);
        if (!empty($results)) {
            $models = [];

            foreach ($results as $r) {
                $newModel = new $classModel();

                foreach ($r as $field => $value) {
                    $newModel->{$field} = $value;
                }
                $models[] = $newModel;
            }

            return $models;
        }
        return null;
    }

    public function destroy()
    {
        $id = $this->attributes[$this->primaryKey];
        if(!empty($id)){
            $affectedRows = $this->adapter->delete($this->table, "id = ?",  [$id]);
            return $affectedRows > 0;
        }
    }

    function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    function __get($name)
    {
        return $this->attributes[$name];
    }
}
