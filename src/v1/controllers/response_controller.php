<?php

/**
 * Class ApiResponse
 * <p>The The Response Controller class</p>
 * @author Sean Olson <so@seanolson.io>
 * @version 1.0.0*
 */
Class ApiResponse {
    /**
     * generates an HTTP error response with status code and error messages
     * @param $httpCode
     * @param $error_messages
     */
    public static function generateErrorResponse($httpCode, $error_messages) {

        header('Content-type:application/json;charset=utf-8');
        header('Cache-Control: no-cache, no-store');
        http_response_code($httpCode);

        $response = array();
        $response['statusCode'] = $httpCode;
        $response['errors'] = $error_messages;
        echo json_encode($response);
    }

    /**
     * generates an HTTP success response with status code, data, and cache response header
     * @param $httpCode
     * @param $data
     * @param $cache_value
     */
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
