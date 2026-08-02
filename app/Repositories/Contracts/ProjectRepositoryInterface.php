<?php

namespace App\Repositories\Contracts;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProjectRepositoryInterface
{
    /**
     * Paginate projects owned by a user, optionally filtered by status.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Project>
     */
    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Project;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Project $project, array $data): Project;

    public function delete(Project $project): void;
}
