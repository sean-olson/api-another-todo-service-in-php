<?php

require_once ('../model/Response.php');

$response = new Response();
$response->setSuccess(false);
$response->setHttpStatusCode(200);
$response->addMessage('Test message 1');
$response->addMessage('Test message 2');
$response->send();

