<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    public function paginateForProject(int $projectId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Task::query()
            ->where('project_id', $projectId)
            ->when(
                ! empty($filters['status']),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['priority']),
                fn ($query) => $query->where('priority', $filters['priority'])
            )
            ->when(
                ! empty($filters['search']),
                fn ($query) => $query->where('title', 'like', '%'.$filters['search'].'%')
            )
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
