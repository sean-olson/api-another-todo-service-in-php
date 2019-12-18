<?php

class TaskException extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class Task {
    private $_id;
    private $_title;
    private $_description;
    private $_deadline;
    private $_completedStatus;

    public function __construct($id, $title, $description, $deadline, $completedStatus) {

       $this->setId($id);
       $this->setTitle($title);
       $this->setDescription($description);
       $this->setDeadline($deadline);
       $this->setCompletedStatus($completedStatus);
    }

    public function getId() {
        return $this->_id;
    }
    public function setId($id) {
        if (($id !== null  && !is_numeric($id)) || $id < 0 || $id > (pow(2,63) -1) || $this->_id !== null) {
            throw new TaskException("Task Id error.");
        } else {
            $this->_id = $id;
        }
    }

    public function getTitle() {
        return $this->_title;
    }
    public function setTitle($title) {
        if(strlen($title) < 3 || strlen($title) > 255){
            throw new TaskException("Task title error.");
        }
        $this->_title = $title;
    }

    public function getDescription() {
        return $this->_description;
    }
    public function setDescription($description) {
        if(strlen($description) < 0 || strlen($description) >  (pow(2,24) -1)){
            throw new TaskException("Task description error.");
        }
        $this->_description = $description;
    }

    public function getDeadline() {
        return $this->_deadline;
    }
    public function setDeadline($deadline) {

        if($deadline === null) {
            return;
        }

        $deadlineDate = date_create_from_format('d/m/Y H:i', $deadline);
        $parsedDate = date_format($deadlineDate, 'd/m/Y H:i');

        if($parsedDate != $deadline) {
            return;
        }

        $this->_deadline = $deadline;
    }

    public function getCompletedStatus() {
        return $this->_completedStatus;
    }
    public function setCompletedStatus($completed) {
        if(strtoupper($completed) !== 'N' && strtoupper($completed) !== 'Y') {
            throw new TaskException("Task completed must be Y or N");
        }
        $this->_completedStatus = $completed;
    }

    public function returnTaskAsArray() {
        $task = array();
        $task['id'] = $this->getId();
        $task['title'] = $this->getTitle();
        $task['description'] = $this->getDescription();
        $task['deadline'] = $this->getDeadline();
        $task['completed'] = $this->getCompletedStatus();
        return $task;
    }

}