<?php

require_once('db.php');
require_once('../model/Task.php');
require_once('../model/Response.php');

try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
} catch (PDOException $ex) {
    error_log("Connection error - ".$ex, 0);
    $errorResponse = ResponseGenerator::newErrorResponse(500, "Database connection error");
    $errorResponse->send();
    exit;
}

if(array_key_exists("taskid", $_GET)){

    $taskid = $_GET['taskid'];

    if ($taskid == '' || !is_numeric($taskid)){
        $errorResponse = ResponseGenerator::newErrorResponse(400, "Task ID must be numeric value");
        $errorResponse->send();
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {

            $query = $readDB->prepare('SELECT id, title, description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i") as deadline, completed FROM tbltasks WHERE id = :taskid');
            $query->bindParam(':taskid', $taskid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if ($rowCount === 0){
                $errorResponse = ResponseGenerator::newErrorResponse(404, "No task with that id.");
                $errorResponse->send();
                exit;
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
               $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
               $taskArray[] = $task->returnTaskAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['tasks'] = $taskArray;

            $successResponse = ResponseGenerator::newSuccessResponse(200, "", true);
            $successResponse->setData($returnData);
            $successResponse->send();

        } catch (PDOException $ex) {
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Error: ".$ex->getMessage());
            $errorResponse->send();
            exit;

        } catch (TaskException $ex) {
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Error: ".$ex->getMessage());
            $errorResponse->send();
            exit;
        }
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $query = $writeDB->prepare('DELETE FROM tbltasks WHERE id = :taskid');
            $query->bindParam(':taskid', $taskid, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Task not found.");
                $response->send();
                exit;
            }
            else {
                $response = new Response();
                $response->setHttpStatusCode(200);
                $response->setSuccess(true);
                $response->addMessage("Task deleted.");
                $response->send();
                exit;
            }
        }
        catch (PDOException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error: ".$ex->getMessage());
            $response->addMessage($ex);
            $response->send();
            exit;

        }
            catch (TaskException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error: ".$ex->getMessage());
            $response->addMessage($ex);
            $response->send();
            exit;
        }
    }
    elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {

    }
    else {
        $errorResponse = ResponseGenerator::newErrorResponse(405, "Unsupported HTTP method requested");
        $errorResponse->send();
        exit;
    }
}
elseif (array_key_exists("completed", $_GET)){

    $completed = $_GET['completed'];

    if($completed !== 'Y' && $completed !== 'N'){
        $errorResponse = ResponseGenerator::newErrorResponse(400, "Completed filter must be Y or N");
        $errorResponse->send();
        exit;
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {
            $query = $readDB->prepare('SELECT id, title, description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i") as deadline, completed FROM tbltasks WHERE completed = :completed');
            $query->bindParam(':completed', $completed, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            $taskArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
                $taskArray[] = $task->returnTaskAsArray();
            }

            $returnData = array();
            $returnData['row_count'] = $rowCount;
            $returnData['tasks'] = $taskArray;

            $successResponse = ResponseGenerator::newSuccessResponse(200, "", true);
            $successResponse->setData($returnData);
            $successResponse->send();

        }
        catch (PDOException $ex) {
            error_log("Database query error -".$ex->getMessage(), 0);
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Failed to get tasks");
            $errorResponse->send();
            exit;
        }
        catch (TaskException $ex) {
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Error: ".$ex->getMessage());
            $errorResponse->send();
            exit;
        }
    }
    else {
        $errorResponse = ResponseGenerator::newErrorResponse(405, "The HTTP method is not supported");
        $errorResponse->send();
        exit;
    }
}
elseif (array_key_exists("page", $_GET)) {

    if($_SERVER['REQUEST_METHOD'] === 'GET') {

        $page = $_GET['page'];
        $pageLimit = 20;

        if ($page === '' || !is_numeric($page)) {
            $errorResponse = ResponseGenerator::newErrorResponse(400, "Not a valid page number");
            $errorResponse->send();
            exit;
        }

        try {

            $query_count = $readDB->prepare('SELECT COUNT(id) AS totalTaskCount FROM tbltasks');
            $query_count->execute();
            $row = $query_count->fetch(PDO::FETCH_ASSOC);

            $taskCount = intval($row['totalTaskCount']);
            $numOfPages = ceil($taskCount/$pageLimit);

            if($numOfPages == 0){
                $numOfPages = 1;
            }

            if($page == 0 || $page > $numOfPages){
                $errorResponse = ResponseGenerator::newErrorResponse(404, "Page not found.");
                $errorResponse->send();
                exit;
            }

            $offset = ($page == 1 ? 0 : ($pageLimit * ($page -1)));

            $query = $readDB->prepare('SELECT id, title, description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i") as deadline, completed FROM tbltasks LIMIT :pageLimit OFFSET :offset');
            $query->bindParam(':pageLimit', $pageLimit, PDO::PARAM_INT);
            $query->bindParam(':offset', $offset, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();
            $taskArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
                $taskArray[] = $task->returnTaskAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['total_rows'] = $taskCount;
            $returnData['current_page'] = $page;
            $returnData['total_pages'] = $numOfPages;
            $returnData['tasks'] = $taskArray;

            $successResponse = ResponseGenerator::newSuccessResponse(200, "", true);
            $successResponse->setData($returnData);
            $successResponse->send();

        }
        catch (PDOException $ex) {
            error_log("Database query error -".$ex->getMessage(), 0);
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Failed to get tasks".$ex->getMessage());
            $errorResponse->send();
            exit;
        }
        catch (TaskException $ex) {
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Error: ".$ex->getMessage());
            $errorResponse->send();
            exit;
        }
    }
    else {
        $errorResponse = ResponseGenerator::newErrorResponse(405, "The HTTP method is not supported");
        $errorResponse->send();
        exit;
    }
}
elseif (empty($_GET)) {

    if ($_SERVER['REQUEST_METHOD'] === 'GET'){

        try {

            $query = $readDB->prepare('SELECT id, title, description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i") as deadline, completed FROM tbltasks');
            $query->execute();

            $rowCount = $query->rowCount();
            $taskArray = array();

            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
                $taskArray[] = $task->returnTaskAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['tasks'] = $taskArray;

            $successResponse = ResponseGenerator::newSuccessResponse(200, "", true);
            $successResponse->setData($returnData);
            $successResponse->send();
            exit;

        } catch (PDOException $ex) {
            error_log("Database query error - ".$ex, 0);
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Failed to get tasks.");
            $errorResponse->send();
            exit;

        } catch (TaskException $ex) {
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Error: ".$ex->getMessage());
            $errorResponse->send();
            exit;
        }
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST'){
        try {

            if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
                $errorResponse = ResponseGenerator::newErrorResponse(400, "Incorrect Content-type header, must be application/json.");
                $errorResponse->send();
                exit;
            }

            $rawPOSTData = file_get_contents('php://input');
            $jsonData = json_decode($rawPOSTData);

            if (!$jsonData){
                $errorResponse = ResponseGenerator::newErrorResponse(400, "Request body is not valid JSON.");
                $errorResponse->send();
                exit;
            }

            if(!isset($jsonData->title) || !isset($jsonData->completed) ) {
                $errorResponse = ResponseGenerator::newErrorResponse(400, "The task title and completed fields are required.");
                $errorResponse->send();
                exit;
            }

            $newTask = new Task(null, $jsonData->title, (isset($jsonData->description) ? $jsonData->description : null), (isset($jsonData->deadline) ? $jsonData->deadline : null), $jsonData->completed);

            $title = $newTask->getTitle();
            $description = $newTask->getDescription();
            $deadline = $newTask->getDeadline();
            $completed = $newTask->getCompletedStatus();

            $query = $writeDB->prepare('INSERT INTO tblTasks (title, description, deadline, completed) VALUES (:title, :description, :deadline, :completed)');
            $query->bindParam(':title', $title, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':deadline', $deadline, PDO::PARAM_STR);
            $query->bindParam(':completed', $completed, PDO::PARAM_STR);

            $query->execute();
            $rowCount = $query->rowCount();

            if($rowCount === 0){
                $errorResponse = ResponseGenerator::newErrorResponse(500, "Failed to create task.");
                $errorResponse->send();
                exit;
            }

            $lastTaskId = $writeDB->lastInsertId();

            $query_s = $writeDB->prepare('SELECT id, title, description, deadline, completed FROM tbltasks WHERE id = :taskid');
            $query_s->bindParam(':taskid', $lastTaskId, PDO::PARAM_INT);
            $query_s->execute();

            $rowCount = $query_s->rowCount();

            if ($rowCount === 0){
                $errorResponse = ResponseGenerator::newErrorResponse(500, "Unable to retrieve the newly created task.");
                $errorResponse->send();
                exit;
            }

            while($row = $query_s->fetch(PDO::FETCH_ASSOC)) {
                $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
                $task->setShortDeadline();
                $taskArray[] = $task->returnTaskAsArray();
            }

            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['tasks'] = $taskArray;

            $successResponse = ResponseGenerator::newSuccessResponse(201, "", true);
            $successResponse->setData($returnData);
            $successResponse->send();
        }
        catch (TaskException $ex){
            $errorResponse = ResponseGenerator::newErrorResponse(400, $ex->getMessage());
            $errorResponse->send();
            exit;
        }
        catch (PDOException $ex){
            error_log("Database query error - ".$ex->getMessage());
            $errorResponse = ResponseGenerator::newErrorResponse(500, "Failed to insert task into database -- check submitted data for errors");
            $errorResponse->send();
            exit;
        }
    }
    else {
        $errorResponse = ResponseGenerator::newErrorResponse(405, "HTTP method not supported.");
        $errorResponse->send();
        exit;
    }

}
else {
    $errorResponse = ResponseGenerator::newErrorResponse(404, "Resource not found.");
    $errorResponse->send();
    exit;
}