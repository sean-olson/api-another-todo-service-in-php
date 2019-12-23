<?php

class DB {
    private static $DBConnection;
    public static function connectDB() {
        if(self::$DBConnection === null) {
            self::$DBConnection = new PDO('mysql:host=localhost;dbname=db_todo;charset=utf8', 'root', '');
            self::$DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$DBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$DBConnection;
    }
}
