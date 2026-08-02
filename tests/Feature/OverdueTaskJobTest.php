<?php

namespace Tests\Feature;

use App\Jobs\NotifyOverdueTaskJob;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OverdueTaskJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_command_queues_a_job_only_for_overdue_tasks(): void
    {
        Queue::fake();

        $project = Project::factory()->create();
        Task::factory()->forProject($project)->create([
            'status' => 'todo',
            'due_date' => now()->subDays(3)->toDateString(),
        ]);
        Task::factory()->forProject($project)->create([
            'status' => 'todo',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);
        Task::factory()->forProject($project)->done()->create([
            'due_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->artisan('tasks:check-overdue')->assertSuccessful();

        Queue::assertPushed(NotifyOverdueTaskJob::class, 1);
    }

    public function test_the_job_notifies_the_task_owner(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->forProject($project)->create([
            'status' => 'todo',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        (new NotifyOverdueTaskJob($task))->handle();

        Notification::assertSentTo($user, TaskOverdueNotification::class);
    }
}
