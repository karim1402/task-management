<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Task $task) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task overdue: '.$this->task->title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('The following task is now overdue:')
            ->line('"'.$this->task->title.'"')
            ->line('Due date: '.optional($this->task->due_date)->toDateString())
            ->line('Please update its status or due date when you get a chance.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'due_date' => optional($this->task->due_date)->toDateString(),
            'message' => 'Task "'.$this->task->title.'" is overdue.',
        ];
    }
}
