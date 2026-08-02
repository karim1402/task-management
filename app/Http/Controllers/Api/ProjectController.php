<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projects) {}

    #[OA\Get(
        path: '/api/projects',
        summary: 'List the authenticated user\'s projects',
        security: [['sanctum' => []]],
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['active', 'completed', 'archived'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated list of projects')]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);
        $perPage = (int) $request->integer('per_page', 15);

        $projects = $this->projects->listForUser($request->user(), $filters, $perPage);

        return $this->success(ProjectResource::collection($projects), 'Projects retrieved.');
    }

    #[OA\Post(
        path: '/api/projects',
        summary: 'Create a project',
        security: [['sanctum' => []]],
        tags: ['Projects'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Website Redesign'),
                    new OA\Property(property: 'description', type: 'string', example: 'Q3 marketing site rebuild'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'completed', 'archived']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Project created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projects->create($request->user(), $request->validated());

        return $this->created(new ProjectResource($project), 'Project created successfully.');
    }

    #[OA\Get(
        path: '/api/projects/{project}',
        summary: 'View a single project',
        security: [['sanctum' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Project detail'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->success(new ProjectResource($project->loadCount('tasks')), 'Project retrieved.');
    }

    #[OA\Put(
        path: '/api/projects/{project}',
        summary: 'Update a project',
        security: [['sanctum' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: '#/components/schemas/Project')),
        responses: [
            new OA\Response(response: 200, description: 'Project updated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = $this->projects->update($project, $request->validated());

        return $this->success(new ProjectResource($project), 'Project updated successfully.');
    }

    #[OA\Delete(
        path: '/api/projects/{project}',
        summary: 'Delete a project (soft delete)',
        security: [['sanctum' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Project deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projects->delete($project);

        return $this->noContent('Project deleted successfully.');
    }
}
