<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Task Management API',
    description: 'RESTful API for managing users, projects and tasks. Authenticate with Laravel Sanctum bearer tokens.',
    contact: new OA\Contact(name: 'Karim', email: 'dev.karim12@gmail.com')
)]
#[OA\Server(url: 'http://localhost:8000', description: 'Local development server')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Pass the token returned by /login or /register as: Bearer {token}'
)]
abstract class Controller
{
    use AuthorizesRequests;

    /**
     * A standard success envelope.
     */
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            // Let the resource render itself; merge its top-level keys.
            $resolved = $data->response()->getData(true);
            $payload = array_merge($payload, $resolved);
        } elseif ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    /**
     * 201 Created helper.
     */
    protected function created(mixed $data = null, string $message = 'Resource created successfully.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * A standard error envelope.
     *
     * @param  array<string, mixed>  $errors
     */
    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * 204 No Content helper.
     */
    protected function noContent(string $message = 'Resource deleted successfully.'): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message], 200);
    }
}
