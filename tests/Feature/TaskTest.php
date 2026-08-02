<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user)->create();
        Sanctum::actingAs($this->user);
    }

    public function test_a_user_can_create_a_task_in_their_project(): void
    {
        $this->postJson("/api/projects/{$this->project->id}/tasks", [
            'title' => 'Write tests',
            'priority' => 'high',
            'status' => 'todo',
            'due_date' => '2026-09-01',
        ])->assertStatus(201)->assertJsonPath('data.title', 'Write tests');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Write tests',
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_tasks_can_be_filtered_by_status(): void
    {
        Task::factory()->forProject($this->project)->create(['status' => 'todo']);
        Task::factory()->forProject($this->project)->create(['status' => 'done']);

        $this->getJson("/api/projects/{$this->project->id}/tasks?status=done")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'done');
    }

    public function test_tasks_can_be_filtered_by_priority(): void
    {
        Task::factory()->forProject($this->project)->create(['priority' => 'high']);
        Task::factory()->forProject($this->project)->create(['priority' => 'low']);

        $this->getJson("/api/projects/{$this->project->id}/tasks?priority=high")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.priority', 'high');
    }

    public function test_tasks_can_be_searched_by_title(): void
    {
        Task::factory()->forProject($this->project)->create(['title' => 'Deploy to production']);
        Task::factory()->forProject($this->project)->create(['title' => 'Refactor module']);

        $this->getJson("/api/projects/{$this->project->id}/tasks?search=Deploy")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Deploy to production');
    }

    public function test_a_user_can_update_a_task(): void
    {
        $task = Task::factory()->forProject($this->project)->create(['status' => 'todo']);

        $this->putJson("/api/projects/{$this->project->id}/tasks/{$task->id}", [
            'status' => 'done',
        ])->assertOk()->assertJsonPath('data.status', 'done');
    }

    public function test_a_user_cannot_touch_tasks_in_another_users_project(): void
    {
        $otherProject = Project::factory()->create();
        $task = Task::factory()->forProject($otherProject)->create();

        $this->getJson("/api/projects/{$otherProject->id}/tasks/{$task->id}")
            ->assertStatus(403);
    }

    public function test_deleting_a_task_soft_deletes_it(): void
    {
        $task = Task::factory()->forProject($this->project)->create();

        $this->deleteJson("/api/projects/{$this->project->id}/tasks/{$task->id}")
            ->assertOk();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
