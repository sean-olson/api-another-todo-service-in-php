<?php

Class ApiResponse {
    public static function generateErrorResponse($httpCode, $error_messages) {

        header('Content-type:application/json;charset=utf-8');
        header('Cache-Control: no-cache, no-store');
        http_response_code($httpCode);

        $response = array();
        $response['statusCode'] = $httpCode;
        $response['errors'] = $error_messages;
        echo json_encode($response);
    }

    public static function generateSuccessResponse($httpCode, $data, $cache_value) {

        header('Content-type:application/json;charset=utf-8');
        if($cache_value === 0){
            header('Cache-Control: no-cache, no-store');
        }
        else {
            header('Cache-Control: max-age='.$cache_value);
        }

        http_response_code($httpCode);

        $response = array();
        $response['statusCode'] = $httpCode;
        $response['data'] = $data;
        echo json_encode($response);
    }
}
