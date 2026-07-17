<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    /**
     * Return a success JSON response.
     */
    protected function sendResponse($data, string $message = '', int $code = 200, array $meta = [])
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        } elseif ($message) {
            $response['meta'] = ['message' => $message];
        }

        return response()->json($response, $code);
    }

    /**
     * Return an error JSON response.
     */
    protected function sendError(string $errorCode, string $errorMessage = '', int $code = 404, array $details = [])
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $errorMessage ?: 'An error occurred.',
            ]
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $code);
    }
}
