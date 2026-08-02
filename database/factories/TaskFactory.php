<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'due_date' => fake()->optional()->dateTimeBetween('-1 month', '+2 months')?->format('Y-m-d'),
        ];
    }

    /**
     * Configure the task to belong to a project (and inherit its owner).
     */
    public function forProject(Project $project): static
    {
        return $this->state(fn () => [
            'project_id' => $project->id,
            'user_id' => $project->user_id,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => now()->subDays(fake()->numberBetween(1, 20))->format('Y-m-d'),
            'status' => fake()->randomElement([TaskStatus::Todo, TaskStatus::InProgress]),
        ]);
    }

    public function done(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::Done]);
    }
}
