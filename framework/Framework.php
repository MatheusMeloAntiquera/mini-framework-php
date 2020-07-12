<?php

namespace Framework;

use Framework\Routes\Handdle as RoutesHanddle;

class Framework {

    protected $routes;
    public static function setConfigApp(){
       $config = require_once('./config/app.php');
       foreach($config as $key => $value){
            $GLOBALS["config"][$key] = $value;
       }
    }

    
    public function __construct()
    {
        $this->setConfigApp();
    }
    
    public function handdleRoutes(RoutesHanddle $routes){
        $routes->handdle();
    }
    
    public static function start(){
        $framework = new self();
        $framework->handdleRoutes(new RoutesHanddle);
    }
}