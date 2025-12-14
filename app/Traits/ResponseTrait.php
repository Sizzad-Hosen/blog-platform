<?php

namespace App\Traits;

trait ResponseTrait
{
    public function sendResponse($data, $message = '', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function sendError($message = '', $errors = [], $code = 404)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
