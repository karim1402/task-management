<?php

namespace App\Console\Commands;

use App\Jobs\NotifyOverdueTaskJob;
use App\Models\Task;
use Illuminate\Console\Command;

class CheckOverdueTasks extends Command
{
    protected $signature = 'tasks:check-overdue';

    protected $description = 'Find overdue tasks and queue overdue notifications for their owners';

    public function handle(): int
    {
        $count = 0;

        Task::query()
            ->overdue()
            ->with('user')
            ->chunkById(200, function ($tasks) use (&$count) {
                foreach ($tasks as $task) {
                    NotifyOverdueTaskJob::dispatch($task);
                    $count++;
                }
            });

        $this->info("Queued {$count} overdue task notification(s).");

        return self::SUCCESS;
    }
}
