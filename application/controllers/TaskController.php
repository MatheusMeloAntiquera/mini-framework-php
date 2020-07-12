<?php

namespace App\Controllers;

use App\Models\TaskModel;

class TaskController extends Controller {

    public function index(){

        $tasks = TaskModel::all();
        
        $array = [];
        if(!empty($tasks)){
            foreach($tasks as $task){
                $array[] = $task->getAttributes();
            }
        }

        echo json_encode(["success" => true, "tasks" => $array], JSON_PRETTY_PRINT);

    }
}