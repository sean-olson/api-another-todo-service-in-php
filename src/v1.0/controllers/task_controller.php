<?php

require_once ('db_controller.php');
require_once ('response_controller.php');
require_once ('../models/todo_item.php');
require_once ('../models/validation.php');

try {
    $dbConn = DB::connectDB();
}
catch(PDOException $ex) {
    error_log("DB CONNECTION ERROR: $ex");
    $error_messages = Array('Unable to connect to database.');
    ApiResponse::generateErrorResponse(500, $error_messages);
    exit;
}
catch (RuntimeException  $ex){
    error_log("DB CONNECTION ERROR: $ex");
    $error_messages = Array('Unable to connect to database.');
    ApiResponse::generateErrorResponse(500, $error_messages);
    exit;
}

$testTodo = new TodoItem('1', 'My big Todo', 'a description of the big todo', null, 'N');
$task_validation = $testTodo->getValidation();

if($task_validation['isValid'] === false){
    ApiResponse::generateErrorResponse(500, $task_validation['errorMessages']);
    exit;
}
else {
    ApiResponse::generateSuccessResponse(200, $testTodo->toArray(), 0);
    exit();
}

