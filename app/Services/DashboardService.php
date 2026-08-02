<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class DashboardService
{
    /**
     * Build the dashboard summary for a given user.
     *
     * @return array<string, int>
     */
    public function summaryFor(User $user): array
    {
        $projects = Project::query()->ownedBy($user->id);
        $tasks = Task::query()->where('user_id', $user->id);

        return [
            'total_projects' => (clone $projects)->count(),
            'active_projects' => (clone $projects)->where('status', ProjectStatus::Active->value)->count(),
            'total_tasks' => (clone $tasks)->count(),
            'completed_tasks' => (clone $tasks)->where('status', TaskStatus::Done->value)->count(),
            'pending_tasks' => (clone $tasks)->where('status', '!=', TaskStatus::Done->value)->count(),
            'overdue_tasks' => (clone $tasks)->overdue()->count(),
        ];
    }
}
