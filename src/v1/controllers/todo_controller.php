<?php

require_once ('db_controller.php');
require_once ('response_controller.php');
require_once ('../models/todo_item.php');
require_once ('../models/validation.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $response_data = array();

//    $response_data['results'] = parse_ini_file("../../../todo.ini");
//    ApiResponse::generateSuccessResponse(200, $response_data, 0);
//    exit();

    if(empty($_GET)) {
        $items = DB::getAllTodoItems();
        $response_data['count'] = count($items);
        $response_data['results'] = array();

        foreach ($items as $item){
            $response_data['results'][] = $item->toArray();
        }

        ApiResponse::generateSuccessResponse(200, $response_data, 0);
        exit();
    }

    elseif (array_key_exists('id',$_GET)) {

        if (!is_numeric($_GET['id']) || $_GET['id'] < 1) {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $result = DB::getTodoItem($_GET['id']);

        if($result === null || !$result->isValid()) {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $response_data['count'] = 1;
        $response_data['results'] = $result->toArray();
    }

    elseif (array_key_exists('completed',$_GET)) {

        if ($_GET['completed'] !== 'Y' && $_GET['completed'] !== 'N') {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $items = DB::getFilteredTodoItems($_GET['completed']);
        $response_data['count'] = count($items);
        $response_data['results'] = array();

        foreach ($items as $item){
            $response_data['results'][] = $item->toArray();
        }
    }

    elseif (array_key_exists('page',$_GET)) {

        if (!is_numeric($_GET['page']) || $_GET['page'] < 1) {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $page = $_GET['page'];
        $todo_item_count = DB::getTodoItemCount();
        $take = 20;
        $page_count = ceil($todo_item_count / $take) > 0 ? ceil($todo_item_count / $take) : 1;

        if ($page > $page_count) {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $skip = ($page == 1 ?  0 : (20*($page-1)));
        $items = DB::getPagedTodoItems($skip, $take);

        $response_data['total_item_count'] = $todo_item_count;
        $response_data['page_count'] = $page_count;
        $response_data['item_count'] = $page == $page_count ? $todo_item_count - (20 * ($page_count - 1))  : 20 ;
        $response_data['current_page'] = $page;
        $response_data['results'] = array();

        foreach ($items as $item){
            $response_data['results'][] = $item->toArray();
        }
    }

    else {

        $errors = array('Unknown resource.');
        ApiResponse::generateErrorResponse(404, $errors);
        exit();
    }

    ApiResponse::generateSuccessResponse(200, $response_data, 0);
    exit();
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!empty($_GET)){
        $errors = array('HTTP Request not supported.');
        ApiResponse::generateErrorResponse(400, $errors);
        exit();
    }

    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        $errors = array('Content-type header needs to be application/json');
        ApiResponse::generateErrorResponse(400, $errors);
        exit();
    }

    $postInput = json_decode(file_get_contents('php://input'));

    if(!$postInput){
        $errors = array('Request body is not valid JSON');
        ApiResponse::generateErrorResponse(400, $errors);
        exit();
    }
    $item = new TodoItem(null, $postInput->name, $postInput->description, $postInput->due_date, $postInput->completed);

    if(!$item->isValid()){
        ApiResponse::generateErrorResponse(400, $item->getErrorMessages());
        exit();
    }

    $id = DB::createTodoItem($item);

    if(!is_numeric($id)){
        $errors = array('Unable to save new Todo Item');
        ApiResponse::generateErrorResponse(500, $errors);
        exit();
    }

    $result = DB::getTodoItem($id);
    if($result === null || !$result->isValid()){
        $errors = array('Unable to retrieve new Todo Item');
        ApiResponse::generateErrorResponse(500, $errors);
        exit();
    }

    $response_data['item_count'] = 1;
    $response_data['results'] = $result->toArray();
    ApiResponse::generateSuccessResponse(200, $response_data, 0);
    exit();
}

elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {

    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        $errors = array('Content-type header needs to be application/json');
        ApiResponse::generateErrorResponse(400, $errors);
        exit();
    }

    if(!array_key_exists("id",$_GET)) {
        $errors = array('HTTP Request not supported.');
        ApiResponse::generateErrorResponse(404, $errors);
        exit();
    }

    $updates = json_decode(file_get_contents('php://input'));

    if(!$updates){
        $errors = array('Request body is not valid JSON');
        ApiResponse::generateErrorResponse(400, $errors);
        exit();
    }

    $id = $_GET['id'];
    $todo_item_updates = DB::getTodoItem($id);

    if($todo_item_updates === null || !$todo_item_updates->isValid()){
        $errors = array('HTTP Request not supported.');
        ApiResponse::generateErrorResponse(404, $errors);
        exit();
    }

    foreach ($updates as $key => $value){
        switch($key){
            case 'name':
                $name_validation = $todo_item_updates->setItemName($updates->name);
                if (!$name_validation->getValidationStatus()){
                    ApiResponse::generateErrorResponse(400, $name_validation->getErrorMessages());
                    exit();
                }
                break;
            case 'description':
                $description_validation = $todo_item_updates->setItemDescription($updates->description);
                if (!$description_validation->getValidationStatus()) {
                    ApiResponse::generateErrorResponse(400, $description_validation->getErrorMessages());
                    exit();
                }
                break;
            case 'due_date':
                $due_date_validation = $todo_item_updates->setItemDueDate($updates->due_date);
                if (!$due_date_validation->getValidationStatus()){
                    ApiResponse::generateErrorResponse(400, $due_date_validation->getErrorMessages());
                    exit();
                }
                break;
            case 'completed':
                $completion_status_validation = $todo_item_updates->setItemCompletionStatus($updates->completed);
                if (!$completion_status_validation->getValidationStatus()){
                    ApiResponse::generateErrorResponse(400, $completion_status_validation->getErrorMessages());
                    exit();
                }
                break;
        }
    }

    DB::updateTodoItem($todo_item_updates);

    $todo_item_updated = DB::getTodoItem($id);
    $dt = $todo_item_updates->getItemDueDate();

    if($todo_item_updated === null || !$todo_item_updated->isValid()){
        $errors = array('Unable to retrieve updated todo item.');
        ApiResponse::generateErrorResponse(500, $errors);
        exit();
    }

    if(!($todo_item_updates == $todo_item_updated)){
        $errors = array('Unable to update Todo Item');
        ApiResponse::generateErrorResponse(500, $errors);
        exit();
    }

    $response_data = array();
    $response_data['item_count'] = 1;
    $response_data['results'] = $todo_item_updated->toArray();
    ApiResponse::generateSuccessResponse(200, $response_data, 0);
    exit();

}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    if (!is_numeric($_GET['id']) || $_GET['id'] < 1) {
        $errors = array('Resource not found.');
        ApiResponse::generateErrorResponse(404, $errors);
        exit();
    }

    $result = DB::getTodoItem($_GET['id']);

    if($result === null || !$result->isValid()) {
        $errors = array('Resource not found.');
        ApiResponse::generateErrorResponse(404, $errors);
        exit();
    }

    $item = $result->toArray();

    if (!DB::deleteTodoItem($item['id'])) {
        $errors = array('Unable to delete item.');
        ApiResponse::generateErrorResponse(500, $errors);
        exit();
    }

    $response_data = array();
    $response_data['count'] = 1;
    $response_data['id'] = $item['id'];
    ApiResponse::generateSuccessResponse(200, $response_data, 0);
    exit();
}
else {
    $errors = array('HTTP method not supported.');
    ApiResponse::generateErrorResponse(500, $errors);
    exit();
}


