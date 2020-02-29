<?php 

use Framework\Routes;

$routeHandle = new Routes();

$apiRoutes = [
    "task" => [
        '/' => ["method" => 'GET', "controller-action" => "TaskController@index"]
    ]
];

$routeHandle->setApiRoutes($apiRoutes);
$routeHandle->handdle();
