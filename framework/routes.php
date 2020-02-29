<?php

namespace Framework;

use Framework\Exceptions\RouteException;

class Routes
{

    private $routes = [
        "api",
        "web"
    ];

    private $segments;
    private $method;
    private $controllerClass;
    private $function;

    public function __construct()
    {
        $this->segments = explode('/', $_REQUEST['path']);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function setApiRoutes($routes)
    {
        $this->routes['api'] = $routes;
    }

    public function handdle()
    {
        try {
            $this->checkIsRouteValid();
            $this->execute();
        } catch (RouteException $e) {
            http_response_code(404);
            echo '<pre>'; print_r($e); echo '</pre>'; exit;
        }
    }

    private function checkIsRouteValid(){
  
        if($this->segments[1] == "api"){
            $firstSegment = "api";
            $routeController = $this->segments[2];
            $action = $this->segments[3];
        } else {
            $firstSegment = "web";
            $routeController = $this->segments[1];
            $action = $this->segments[2];
        }

        if($action == ''){
            $action = '/';
        }

        if(!empty($this->routes[$firstSegment][$routeController][$action])){

            $routesInfo = $this->routes[$firstSegment][$routeController][$action];
            if(isset($routesInfo['method']) && $routesInfo['method'] != $this->method){
                throw new RouteException("Method not allow", 1);
            }

            $controllerAndFunction = explode("@", $routesInfo['controller-action']);
            
            if(empty($controllerAndFunction) || count($controllerAndFunction) != 2 ){
                throw new RouteException("The correct sintax route is: Controller@function", 1);
            }

            $this->setControllerClass($controllerAndFunction[0]);
            $this->setFunction($controllerAndFunction[1]);

        } else {
            throw new RouteException("Route not found");
        }
    }

    private function setControllerClass($class){
        $this->controllerClass = $class;
    }


    private function setFunction($function){
        $this->function = $function;
    }

    private function execute(){

        if(class_exists('App\\Controllers\\' . $this->controllerClass)){
            $reflectionClass  = new \ReflectionClass('App\\Controllers\\' . $this->controllerClass);
            $controller = $reflectionClass->newInstanceArgs([]);
            
            if(!method_exists($controller, $this->function)){
                throw new RouteException("Function \"$this->function\" not is exists in \"{$this->controllerClass}\"");
            }
            echo '<pre>'; print_r($controller->{$this->function}()); echo '</pre>'; exit;
        } else {
            throw new RouteException("Class controller \"{$this->controllerClass}\" not found");
        }
    }

}
