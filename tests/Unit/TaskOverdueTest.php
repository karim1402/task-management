<?php

namespace Tests\Unit;

use App\Enums\TaskStatus;
use App\Models\Task;
use Tests\TestCase;

class TaskOverdueTest extends TestCase
{
    public function test_task_with_past_due_date_and_not_done_is_overdue(): void
    {
        $task = new Task;
        $task->due_date = now()->subDay();
        $task->status = TaskStatus::Todo;

        $this->assertTrue($task->isOverdue());
    }

    public function test_task_with_future_due_date_is_not_overdue(): void
    {
        $task = new Task;
        $task->due_date = now()->addWeek();
        $task->status = TaskStatus::Todo;

        $this->assertFalse($task->isOverdue());
    }

    public function test_completed_task_is_never_overdue(): void
    {
        $task = new Task;
        $task->due_date = now()->subDay();
        $task->status = TaskStatus::Done;

        $this->assertFalse($task->isOverdue());
    }

    public function test_task_without_a_due_date_is_not_overdue(): void
    {
        $task = new Task;
        $task->status = TaskStatus::Todo;

        $this->assertFalse($task->isOverdue());
    }
}
