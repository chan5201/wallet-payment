<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function responseSuccess(string $message = '', array $data = [], int $statusCode = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    public function responseError(string $message = '', array $data = [], int $statusCode = 422, )
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
