<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // A known demo account for quick login during evaluation.
        $demo = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->seedProjectsAndTasksFor($demo);

        // A couple of extra users to prove ownership scoping works.
        User::factory()
            ->count(2)
            ->create()
            ->each(fn (User $user) => $this->seedProjectsAndTasksFor($user));
    }

    private function seedProjectsAndTasksFor(User $user): void
    {
        // Active projects, each with a spread of tasks.
        Project::factory()
            ->count(2)
            ->active()
            ->for($user)
            ->create()
            ->each(function (Project $project) use ($user) {
                Task::factory()->count(4)->create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                ]);

                // Guarantee at least one overdue and one completed task per project.
                Task::factory()->overdue()->create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'priority' => TaskPriority::High,
                    'title' => 'Overdue: '.fake()->sentence(3),
                ]);

                Task::factory()->done()->create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'status' => TaskStatus::Done,
                    'title' => 'Completed: '.fake()->sentence(3),
                ]);
            });

        // One completed and one archived project for dashboard variety.
        Project::factory()->completed()->for($user)->create([
            'status' => ProjectStatus::Completed,
        ]);
        Project::factory()->archived()->for($user)->create([
            'status' => ProjectStatus::Archived,
        ]);
    }
}
