<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Paginate tasks for a project with optional status/priority/search filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Task>
     */
    public function paginateForProject(int $projectId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Task;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Task $task, array $data): Task;

    public function delete(Task $task): void;
}
