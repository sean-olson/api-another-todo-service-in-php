<?php

/**
 * Class TodoItem
 * <p>The Todo class object</p>
 * @author Sean Olson <so@seanolson.io>
 * @version 1.0.0
 */
class TodoItem {
    private $_todo_item_id;
    private $_todo_item_name;
    private $_todo_item_description;
    private $_todo_item_due_date;
    private $_todo_item_is_completed;
    private $_is_valid;
    private $_error_messages;

    /**
     * TodoItem constructor.
     * @param $todo_item_id -- integer
     * @param $todo_item_name -- string
     * @param $todo_item_description -- string
     * @param $todo_item_due_date -- string
     * @param $todo_item_is_completed -- enum: 'Y', 'N'
     */
    public function __construct($todo_item_id,
                                $todo_item_name,
                                $todo_item_description,
                                $todo_item_due_date,
                                $todo_item_is_completed) {

        $validation = new Validation();

        $validation->concatenateValidation($this->setItemId($todo_item_id));
        $validation->concatenateValidation($this->setItemName($todo_item_name));
        $validation->concatenateValidation($this->setItemDescription($todo_item_description));
        $validation->concatenateValidation($this->setItemDueDate($todo_item_due_date));
        $validation->concatenateValidation($this->setItemCompletionStatus($todo_item_is_completed));

        $this->_is_valid = $validation->getValidationStatus();
        $this->_error_messages = $validation->getErrorMessages();
    }

    /**
     * Getter method for TodoItem id
     * @return integer
     */
    public function getItemId() {
        return $this->_todo_item_id;
    }

    /**
     * Setter method for TodoItem id
     * @param $id -- integer value
     * @return Validation
     */
    public function setItemId($id) {
        $validation = new Validation();

        if (($id !== null && !is_numeric($id)) || (is_numeric($id) && $id <= 0)) {
            $validation->setValidationStatus(false);
            $validation->setErrorMessage('Task ID Error: The ID must be a positive integer.');
        } else {
            $this->_todo_item_id = $id;
            $validation->setValidationStatus(true);
        }

        return $validation;
    }

    /**
     * Getter method for TodoItem name
     * @return string
     */
    public function getItemName() {
        return $this->_todo_item_name;
    }

    /**
     * Setter method for TodoItem name
     * @param $item_name -- string value, max-length 255
     * @return Validation
     */
    public function setItemName($item_name) {
        $validation = new Validation();

        if ($item_name == null || strlen($item_name) == 0  || strlen($item_name) > 255) {
            $validation->setValidationStatus(false);
            $validation->setErrorMessage('Task name is required.');
        } else {
            $this->_todo_item_name = $item_name;
            $validation->setValidationStatus(true);
        }

        return $validation;
    }

    /**
     * Getter method for TodoItem description
     * @return string
     */
    public function getItemDescription() {
        return $this->_todo_item_description;
    }

    /**
     * Setter method for TodoItem description
     * @param $item_description -- string value, 2^16 max-length
     * @return Validation
     */
    public function setItemDescription($item_description) {

        $validation = new Validation();

        if ($item_description !== null && strlen($item_description) > pow(2, 16)) {
            $validation->setValidationStatus(false);
            $validation->setErrorMessage("The description field exceeds the  maximum length: 2^16");
        } else {
            $this->_todo_item_description = $item_description;
            $validation->setValidationStatus(true);
        }

        return $validation;
    }

    /**
     * Getter method for TodoItem due date
     * @return string
     */
    public function getItemDueDate() {
        return $this->_todo_item_due_date;
    }

    /**
     * Setter method for TodoItem due date
     * @param $due_date -- date-time string
     * @return Validation
     */
    public function setItemDueDate($due_date) {

        $validation = new Validation();
        try {
            if ($due_date == null) {
                $this->_todo_item_due_date = null;
            } elseif (DateTime::createFromFormat('m/d/Y H:s', $due_date)) {
                $this->_todo_item_due_date = date_format(DateTime::createFromFormat('m/d/Y H:s', $due_date), 'Y-m-d H:i:s');
                $validation->setValidationStatus(true);
            } else {
                $validation->setValidationStatus(true);
                $this->_todo_item_due_date = $due_date;
            }
        }
        catch (Exception $ex){
            $validation->setValidationStatus(false);
            $validation->setErrorMessage("SET DATE ERROR".$ex);
        }
        return $validation;
    }

    /**
     * Getter returns a formatted due date in the form m/d/y if set, otherwise returns null
     * @return string|null
     */
    public function getFormattedItemDueDate(){
        if($this->_todo_item_due_date === null){
            return null;
        }
        else {
            return date_format(DateTime::createFromFormat('Y-m-d H:i:s', $this->getItemDueDate()) , 'm/d/Y');
        }
    }

    /**
     * Getter method for TodoItem completion status enum
     * @return string -- enum value: 'Y', 'N'
     */
    public function getItemCompletionStatus() {
        return $this->_todo_item_is_completed;
    }

    /**
     * Setter method for TodoItem completion status enum
     * @param $is_complete -- enum value: 'Y', 'N'
     * @return Validation
     */
    public function setItemCompletionStatus($is_complete) {

        $validation = new Validation();

        if ($is_complete == null || ($is_complete !== 'N'  && $is_complete !== 'Y')) {
            $validation->setValidationStatus(false);
            $validation->setErrorMessage("Completion status is required.  Must be either 'Y' or 'N'");
        } else {
            $this->_todo_item_is_completed = $is_complete;
            $validation->setValidationStatus(true);
        }

        return $validation;
    }

    /**
     * returns a validation status boolean calculated by the TodoItem constructor method.
     * @return associative array
     */
    public function isValid(){
        return $this->_is_valid;
    }

    /**
     * returns an error-message array generated by the TodoItem constructor method.
     * @return array of strings
     */
    public function getErrorMessages(){
        return $this->_error_messages;
    }

    /**
     * clears the validation status object produced by the TokenItem constructor method.
     */
    public function clearValidationStatus(){
        $this->_is_valid = true;
        $this->_error_messages = array();
    }

    /**
     * returns the TodoItem properties as an array
     * @return array
     */
    public function toArray() {
        $todoItem = array();
        $todoItem['id'] = $this->getItemId();
        $todoItem['name'] = $this->getItemName();
        $todoItem['description'] = $this->getItemDescription();
        $todoItem['dueDate'] = $this->getFormattedItemDueDate();
        $todoItem['isCompleted'] = $this->getItemCompletionStatus();
        return $todoItem;
    }
}