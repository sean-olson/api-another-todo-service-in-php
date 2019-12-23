<?php

/**
 * Class Validation
 * <p>The Validation class object used to provide a feedback mechanism for object setters.</p>
 * @author Sean Olson <so@seanolson.io>
 * @version 1.0.0
 */
class Validation {
    private $_isValid;
    private $_validation_errors;
    public function __construct() {
        $this->_isValid = true;
        $this->_validation_errors = array();
    }
    public function setValidationStatus($is_valid){
        $this->_isValid = $is_valid;
    }
    public function getValidationStatus(){
        return $this->_isValid;
    }
    public function setErrorMessage($message){
        $this->_validation_errors[] = $message;
    }
    public function getErrorMessages(){
        return $this->_validation_errors;
    }
    public function appendErrorMessages($messages){
        $this->_validation_errors = array_merge($this->getErrorMessages(), $messages);
    }

    public function concatenateValidation($validation){
        if(!$validation->getValidationStatus()){
            $this->setValidationStatus(false);
        };
        foreach ($validation->getErrorMessages() as $message){
            $this->setErrorMessage($message);
        }
    }

    public function __toString()
    {
        $valid_state = $this->_isValid ? 'is valid': 'is invalid';
        $message = "The property is $valid_state";
        return $message;
    }
}