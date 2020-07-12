<?php 

use Framework\Routes;

$routeHandle = new Routes();

echo '<pre>'; print_r($routeHandle::teste()); echo '</pre>'; exit;
$apiRoutes = [
    "task" => [
        '/' => ["method" => 'GET', "controller-action" => "TaskController@index"],
        '/:id' => ["method" => 'GET', "controller-action" => "TaskController@index"]
    ]
];

$routeHandle->setApiRoutes($apiRoutes);
$routeHandle->handdle();
