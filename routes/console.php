<?php

use App\Console\Commands\CheckOverdueTasks;
use Illuminate\Support\Facades\Schedule;

// Every hour, scan for tasks that have become overdue and queue notifications.
Schedule::command(CheckOverdueTasks::class)->hourly();
