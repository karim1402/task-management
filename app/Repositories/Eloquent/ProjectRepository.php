<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Project::query()
            ->ownedBy($userId)
            ->withCount('tasks')
            ->when(
                ! empty($filters['status']),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['search']),
                fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%')
            )
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
