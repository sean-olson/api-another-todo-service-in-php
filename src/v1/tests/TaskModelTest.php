<?php

require_once('../model/Task.php');
require_once('../model/Response.php');

try{
    $task = new Task(1, "Title here", "Description here", "01/01/2019 12:00", "N");

    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->addMessage('The task was created successfully.');
    $response->setData($task->returnTaskAsArray());
    $response->send();

} catch (TaskException $ex){
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Error: ".$ex->getMessage());
    $response->addMessage($ex);
    $response->send();
    exit;
}
