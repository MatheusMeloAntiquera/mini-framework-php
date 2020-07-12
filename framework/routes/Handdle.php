<?php

namespace Framework\Routes;

use Framework\Exceptions\RouteException;
use Framework\Routes;

class Handdle
{

    private $routes = [];

    private $segments;
    private $method;
    private $controllerClass;
    private $function;
    private $type;

    public function __construct()
    {
        $this->setPathRequest();
        $this->segments = explode('/', $this->pathRequest);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->setRoutes();
    }

    private function setPathRequest()
    {
        $this->pathRequest = $_REQUEST['path'] . (substr($_REQUEST['path'], -1) != "/" ? "/" : "");
    }

    private function setRoutes()
    {

        if ($this->segments[1] == 'api') {
            $file = 'api.php';
            $this->type = "api";
        } else {
            $file = 'web.php';
            $this->type = "";
        }

        $file = $this->segments[1] == 'api' ? 'api.php' : 'web.php';
        self::getRoutesFromFile($file);

        $routes = Routes::getRoutes();
        foreach ($routes as $route) {
            $this->setRoute($route['path'], $route['function'], $route['method']);
        }
    }

    private static function getRoutesFromFile($file)
    {
        Routes::getInstance();
        require_once(DIRECTORY_ROOT . "/routes/{$file}");
    }


    public function get($route, $function)
    {
        $this->setRoute($route, $function, 'GET');
    }

    public function post($route, $function)
    {
        $this->setRoute($route, $function, 'POST');
    }

    private function setRoute($route, $function, $method)
    {
        $route = substr($route, 1) != "/" ? "/" . $route : $route;
        $route .= substr($route, -1) != "/" ? "/" : "";

        if ($this->type == 'api') {
            $route = "/{$this->type}" . $route;
        }


        $this->routes[] = [
            "method" => $method,
            "route" => $route,
            "function" => $function,
            "regex" => $this->getRegex($route)
        ];
    }

    private function getRegex($route)
    {
        $regex = "[.\S]\/";
        $segments = array_filter(explode('/', $route));
        foreach ($segments as $segment) {

            preg_match("/{[\w]+}/", $segment, $result);
            if (!empty($result)) {
                $regex .= "[.\S]\/";
            } else {
                $regex .= "{$segment}\/";
            }
        }

        return $regex;
    }

    public function handdle()
    {
        try {

            //Filter by method
            $routesFiltered = array_filter($this->routes, function ($route) {
                return $route['method'] == $this->method;
            });

            if (empty($routesFiltered)) {
                throw new RouteException("Method not allow", 1);
            }

            $this->checkIsRouteValid($routesFiltered);
            $this->execute();
        } catch (RouteException $e) {
            http_response_code(404);
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
        }
    }

    private function checkIsRouteValid($routesFiltered)
    {

        $routeFound = null;
        foreach ($routesFiltered as $route) {
            preg_match("/" . $route['regex'] . "/", $this->pathRequest, $result);

            if (!empty($result)) {
                $routeFound = $route;
            }
        }
        if (!empty($routeFound)) {
            $array = array_combine(explode('/', $routeFound['route']), $this->segments);
            /* $parameters = array_filter($array, [$this, 'isParameter'], ARRAY_FILTER_USE_KEY); */
            $parameters = [];
            foreach ($array as $key => $value) {
                if ($this->isParameter($key)) {
                    $parameters[] = $value;
                }
            }

            if (gettype($routeFound['function']) != 'string') {
                call_user_func($routeFound['function'], ...$parameters);
            } else {
                $controllerAndFunction = explode("@", $routesInfo['controller-action']);

                if (empty($controllerAndFunction) || count($controllerAndFunction) != 2) {
                    throw new RouteException("The correct sintax route is: Controller@function", 1);
                }

                $this->setControllerClass($controllerAndFunction[0]);
                $this->setFunction($controllerAndFunction[1]);
            }
        } else {
            throw new RouteException("Route not found");
        }
    }

    public function isParameter($key)
    {
        preg_match("/{[\w]+}/", $key, $result);
        return !empty($result);
    }

    private function setControllerClass($class)
    {
        $this->controllerClass = $class;
    }


    private function setFunction($function)
    {
        $this->function = $function;
    }

    private function execute()
    {

        if (class_exists('App\\Controllers\\' . $this->controllerClass)) {
            $reflectionClass  = new \ReflectionClass('App\\Controllers\\' . $this->controllerClass);
            $controller = $reflectionClass->newInstanceArgs([]);

            if (!method_exists($controller, $this->function)) {
                throw new RouteException("Function \"$this->function\" not is exists in \"{$this->controllerClass}\"");
            }
            $controller->{$this->function}();
        } else {
            throw new RouteException("Class controller \"{$this->controllerClass}\" not found");
        }
    }
}
