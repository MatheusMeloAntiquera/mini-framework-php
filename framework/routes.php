<?php

namespace Framework;

class Routes
{
    private static $instance;
    private static $routes = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function get($path, $function)
    {
        self::setRoute($path, $function, 'GET');
    }

    public function post($path, $function)
    {
        self::setRoute($path, $function, 'POST');
    }

    private static function setRoute($path, $function, $method){
        self::$routes[] = [
            "path" => $path,
            "function" => $function,
            "method" => $method
        ];
    }

    public function getRoutes(){
        return self::$routes;
    }
}
