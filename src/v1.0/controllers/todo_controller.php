<?php

require_once ('db_controller.php');
require_once ('response_controller.php');
require_once ('../models/todo_item.php');
require_once ('../models/validation.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $response_data = array();

    if(empty($_GET)) {
        $items = DB::getAllTodoItems();
        $response_data['count'] = count($items);
        $response_data['results'] = $items;
    }

    elseif (array_key_exists('id',$_GET)) {

        if (!is_numeric($_GET['id']) || $_GET['id'] < 1) {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $item = DB::getTodoItem($_GET['id']);
        if($item === null || array_key_exists('errors', $item)) {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $response_data['count'] = 1;
        $response_data['results'] = $item;
    }

    elseif (array_key_exists('completed',$_GET)) {

        if ($_GET['completed'] !== 'Y' && $_GET['completed'] !== 'N') {
            $errors = array('Resource not found.');
            ApiResponse::generateErrorResponse(404, $errors);
            exit();
        }

        $items = DB::getFilteredTodoItems($_GET['completed']);
        $response_data['count'] = count($items);
        $response_data['results'] = $items;
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

        $response_data['item_count'] = $todo_item_count;
        $response_data['current_page'] = $page;
        $response_data['results'] = $items;
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
    $response_data = array("method"=>"POST");
    ApiResponse::generateSuccessResponse(200, $response_data, 0);
    exit();
}
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $response_data = array("method"=>"PUT");
    ApiResponse::generateSuccessResponse(200, $response_data, 0);
    exit();
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $response_data = array("method"=>"DELETE");
    ApiResponse::generateSuccessResponse(200, $response_data, 0);
    exit();
}
else {
    $errors = array('HTTP method not supported.');
    ApiResponse::generateErrorResponse(500, $errors);
    exit();
}


