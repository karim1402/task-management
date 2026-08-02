<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Task>
     */
    public function listForProject(Project $project, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tasks->paginateForProject($project->id, $filters, $perPage);
    }

    /**
     * Create a task under a project, inheriting the project's owner.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Project $project, array $data): Task
    {
        $data['project_id'] = $project->id;
        $data['user_id'] = $project->user_id;

        return $this->tasks->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task
    {
        return $this->tasks->update($task, $data);
    }

    public function delete(Task $task): void
    {
        $this->tasks->delete($task);
    }
}
