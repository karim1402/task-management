<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $tasks) {}

    #[OA\Get(
        path: '/api/projects/{project}/tasks',
        summary: 'List tasks in a project (filter by status/priority, search by title)',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['todo', 'in_progress', 'done'])),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated list of tasks')]
    )]
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $filters = $request->only(['status', 'priority', 'search']);
        $perPage = (int) $request->integer('per_page', 15);

        $tasks = $this->tasks->listForProject($project, $filters, $perPage);

        return $this->success(TaskResource::collection($tasks), 'Tasks retrieved.');
    }

    #[OA\Post(
        path: '/api/projects/{project}/tasks',
        summary: 'Create a task within a project',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Design the landing page'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high']),
                    new OA\Property(property: 'status', type: 'string', enum: ['todo', 'in_progress', 'done']),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', example: '2026-09-01'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Task created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $task = $this->tasks->create($project, $request->validated());

        return $this->created(new TaskResource($task), 'Task created successfully.');
    }

    #[OA\Get(
        path: '/api/projects/{project}/tasks/{task}',
        summary: 'View a single task',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Task detail')]
    )]
    public function show(Project $project, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return $this->success(new TaskResource($task), 'Task retrieved.');
    }

    #[OA\Put(
        path: '/api/projects/{project}/tasks/{task}',
        summary: 'Update a task',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: '#/components/schemas/Task')),
        responses: [new OA\Response(response: 200, description: 'Task updated')]
    )]
    public function update(UpdateTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->tasks->update($task, $request->validated());

        return $this->success(new TaskResource($task), 'Task updated successfully.');
    }

    #[OA\Delete(
        path: '/api/projects/{project}/tasks/{task}',
        summary: 'Delete a task (soft delete)',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Task deleted')]
    )]
    public function destroy(Project $project, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->tasks->delete($task);

        return $this->noContent('Task deleted successfully.');
    }
}
