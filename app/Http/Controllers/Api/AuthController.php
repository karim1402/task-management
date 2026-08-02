<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Karim'),
                    new OA\Property(property: 'email', type: 'string', example: 'karim@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Password123!'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'Password123!'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User registered'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->register($request->validated());

        return $this->created([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Registration successful.');
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Log in and receive an API token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'demo@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 401, description: 'Invalid credentials'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->validated());

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Login successful.');
    }

    #[OA\Get(
        path: '/api/me',
        summary: 'Get the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [new OA\Response(response: 200, description: 'Current user')]
    )]
    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()), 'Authenticated user.');
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Revoke the current API token',
        security: [['sanctum' => []]],
        tags: ['Authentication'],
        responses: [new OA\Response(response: 200, description: 'Logged out')]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return $this->success(null, 'Logged out successfully.');
    }
}
