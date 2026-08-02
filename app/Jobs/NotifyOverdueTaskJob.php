<?php

namespace App\Jobs;

use App\Models\Task;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyOverdueTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly Task $task) {}

    public function handle(): void
    {
        // Re-check inside the job in case the task changed before processing.
        if (! $this->task->fresh()?->isOverdue()) {
            return;
        }

        $this->task->user->notify(new TaskOverdueNotification($this->task));
    }
}
