<?php

class DB {
    private static $DBConnection;
    private static $SELECT_LIST = ' todo_item_id AS id, todo_item_name AS name, todo_item_description AS description, todo_item_due_date AS due_date, todo_item_is_completed AS completed ';

    private static function getDbConnection(){
        try {
            if(self::$DBConnection === null) {
                self::$DBConnection = new PDO('mysql:host=localhost;dbname=db_todo;charset=utf8', 'root', '');
                self::$DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$DBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            }
            return self::$DBConnection;
        }
        catch(PDOException $ex) {
                error_log("DB CONNECTION ERROR: $ex");
                $error_messages = Array('Unable to connect to database.');
                ApiResponse::generateErrorResponse(500, $error_messages);
                exit();
        }
    }

    private static function instanceTodoObject($row){
        return new TodoItem($row['id'], $row['name'], $row['description'], $row['due_date'], $row['completed']);
    }

    public static function getTodoItemCount() {
        try {
            $dbConn = self::getDbConnection();
            $is_deleted = 0;
            $query = $dbConn->prepare('SELECT COUNT(todo_item_id) AS todo_item_count FROM tbl_todo_items WHERE todo_is_deleted = :is_deleted');
            $query->bindParam(':is_deleted', $is_deleted, PDO::PARAM_INT);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            return $row['todo_item_count'];
        }
        catch(PDOException $ex) {
            error_log("DB CRUD ERROR: $ex");
            $error_messages = Array('Database error.');
            ApiResponse::generateErrorResponse(500, $error_messages);
            exit();
        }
    }

    public static function getTodoItem($id){
        try {
            $dbConn = self::getDbConnection();
            $is_deleted = 0;
            $query = $dbConn->prepare('SELECT ' . self::$SELECT_LIST . ' FROM tbl_todo_items WHERE todo_item_id = :todo_id AND todo_is_deleted = :is_deleted');
            $query->bindParam(':todo_id', $id, PDO::PARAM_INT);
            $query->bindParam(':is_deleted', $is_deleted, PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() === 0) {
                return null;
            }
            $todo_item = self::instanceTodoObject($query->fetch(PDO::FETCH_ASSOC));
            if (!$todo_item->isValid()) {
                return array("errors" => $todo_item->getErrorMessages());
            }
            return $todo_item->toArray();
        }
            catch(PDOException $ex) {
            error_log("DB CRUD ERROR: $ex");
            $error_messages = Array('Database error.');
            ApiResponse::generateErrorResponse(500, $error_messages);
            exit();
        }
    }

    public static function getAllTodoItems(){
        try {
            $dbConn = self::getDbConnection();
            $is_deleted = 0;
            $query = $dbConn->prepare('SELECT ' . self::$SELECT_LIST . ' FROM tbl_todo_items WHERE todo_is_deleted = :is_deleted');
            $query->bindParam(':is_deleted', $is_deleted, PDO::PARAM_INT);
            $query->execute();

            $todo_items = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $todo_item = self::instanceTodoObject($row);
                if ($todo_item->isValid()) {
                    $todo_items[] = $todo_item->toArray();
                }
            }
            return $todo_items;
        }
        catch(PDOException $ex) {
            error_log("DB CRUD ERROR: $ex");
            $error_messages = Array('Database error.');
            ApiResponse::generateErrorResponse(500, $error_messages);
            exit();
        }
    }

    public static function getFilteredTodoItems($status){
        try {
            $dbConn = self::getDbConnection();
            $is_deleted = 0;
            $query = $dbConn->prepare('SELECT ' . self::$SELECT_LIST . ' FROM tbl_todo_items WHERE todo_item_is_completed = :is_completed AND todo_is_deleted = :is_deleted');
            $query->bindParam(':is_completed', $status, PDO::PARAM_STR);
            $query->bindParam(':is_deleted', $is_deleted, PDO::PARAM_INT);
            $query->execute();

            $todo_items = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $todo_item = self::instanceTodoObject($row);
                if ($todo_item->isValid()) {
                    $todo_items[] = $todo_item->toArray();
                }
            }
            return $todo_items;
        }
        catch(PDOException $ex) {
            error_log("DB CRUD ERROR: $ex");
            $error_messages = Array('Database error.');
            ApiResponse::generateErrorResponse(500, $error_messages);
            exit();
        }
    }

    public static function getPagedTodoItems($skip, $take){
        try{
            $dbConn = self::getDbConnection();
            $is_deleted = 0;
            $query = $dbConn->prepare('SELECT ' . self::$SELECT_LIST . ' FROM tbl_todo_items WHERE todo_is_deleted = :is_deleted LIMIT :take OFFSET :skip');
            $query->bindParam(':is_deleted', $is_deleted, PDO::PARAM_INT);
            $query->bindParam(':skip', $skip, PDO::PARAM_INT);
            $query->bindParam(':take', $take, PDO::PARAM_INT);
            $query->execute();

            $todo_items = array();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $todo_item = self::instanceTodoObject($row);
                if ($todo_item->isValid()) {
                    $todo_items[] = $todo_item->toArray();
                }
            }
            return $todo_items;
        }
        catch(PDOException $ex) {
            error_log("DB CRUD ERROR: $ex");
            $error_messages = Array('Database error.');
            ApiResponse::generateErrorResponse(500, $error_messages);
            exit();
        }
    }
    public static function createTodoItem($item){
        try{

            $dbConn = self::getDbConnection();

            $name = $item->getItemName();
            $description = $item->getItemDescription();
            $due_date = $item->getItemDueDate();
            $completed = $item->getItemCompletionStatus();

            $query = $dbConn->prepare('INSERT INTO tbl_todo_items (todo_item_name, todo_item_description, todo_item_due_date, todo_item_is_completed) VALUES (:name, :description, :due_date, :completed)');
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':due_date', $due_date, PDO::PARAM_STR);
            $query->bindParam(':completed', $completed, PDO::PARAM_STR);
            $query->execute();

            return $dbConn->lastInsertId();
        }
        catch(PDOException $ex) {
            error_log("DB CRUD ERROR: $ex");
            $error_messages = Array('Database error.');
            ApiResponse::generateErrorResponse(500, $error_messages);
            exit();
        }


    }
    public static function updateTodoItem($item){}
    public static function deleteTodoItem($id){}
}
