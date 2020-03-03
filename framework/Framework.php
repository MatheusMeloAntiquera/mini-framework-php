<?php

namespace Framework;

class Framework {

    public static function setConfigApp(){
       $config = require_once('./config/app.php');
       foreach($config as $key => $value){
            $GLOBALS["config"][$key] = $value;
       }
    }
}