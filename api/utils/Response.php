<?php
/**
 * Response Utility Class
 * Handles API response formatting
 */
class Response {
    /**
     * Send success response
     */
    public static function success($data = null, $message = '', $statusCode = 200) {
        http_response_code($statusCode);
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Send error response
     */
    public static function error($message = 'An error occurred', $statusCode = 400) {
        http_response_code($statusCode);
        $response = [
            'success' => false,
            'message' => $message
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * Send paginated response
     */
    public static function paginated($data, $pagination, $message = '') {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination
        ];

        echo json_encode($response);
        exit;
    }
}
?>