<?php 

namespace Framework\Orm;

class Factory {
    
    protected $config;
    protected $database;
    protected $adapter;
    public function __construct()
    {
        $this->database = $GLOBALS['config']['database'];
        $this->getDatabaseConfig();
        $this->setAdapter();
    }

    private function getDatabaseConfig(){

        $databaseConfigs = require( DIRECTORY_ROOT . "/config/database.php");
        $this->config = $databaseConfigs[$this->database];
        
    }
    
    private function setAdapter(){

        if (class_exists('Framework\\Orm\\Adapters\\'. ucfirst($this->database))) {
            /* $reflectionClass  = new \ReflectionClass('Framework\\Orm\\Adapters\\' . ucfirst($this->database));
            $this->adapter = $reflectionClass->newInstanceArgs([$this->config]); */
            $nameClass = 'Framework\\Orm\\Adapters\\' . ucfirst($this->database);
            $this->adapter = new $nameClass($this->config);
        }
    }

    public function getAdapter(){
        return $this->adapter;
    }
}
