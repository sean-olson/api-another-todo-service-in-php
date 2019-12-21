<?php

//require_once('../db.php');
//require_once('../../model/Task.php');
//require_once('../../model/Response.php');

function getTask($taskid, $readDB){


    if ($taskid == '' || !is_numeric($taskid)){
        $errorResponse = ResponseGenerator::newErrorResponse(400, "Task ID must be numeric value");
        $errorResponse->send();
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        try {

            $query = $readDB->prepare('SELECT id, title, description, deadline, completed FROM tbltasks WHERE id = :taskid');
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
