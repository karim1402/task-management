<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_correct_aggregates_for_the_user(): void
    {
        $user = User::factory()->create();
        $active = Project::factory()->for($user)->active()->create();
        Project::factory()->for($user)->completed()->create();

        // 2 done, 1 pending (todo), 1 overdue (past due, not done)
        Task::factory()->forProject($active)->count(2)->done()->create();
        Task::factory()->forProject($active)->create(['status' => 'todo', 'due_date' => now()->addWeek()->toDateString()]);
        Task::factory()->forProject($active)->create(['status' => 'todo', 'due_date' => now()->subWeek()->toDateString()]);

        // Another user's data must not leak in.
        $other = User::factory()->create();
        Task::factory()->forProject(Project::factory()->for($other)->create())->count(5)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.total_projects', 2)
            ->assertJsonPath('data.active_projects', 1)
            ->assertJsonPath('data.total_tasks', 4)
            ->assertJsonPath('data.completed_tasks', 2)
            ->assertJsonPath('data.pending_tasks', 2)
            ->assertJsonPath('data.overdue_tasks', 1);
    }
}
