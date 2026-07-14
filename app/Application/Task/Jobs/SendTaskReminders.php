<?php

namespace App\Application\Task\Jobs;

use App\Domain\Task\Events\TaskDueSoon;
use App\Domain\Task\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find tasks with reminder_at within the next 5 minutes
        Task::with(['assignedTo'])
            ->whereNull('completed_at')
            ->whereNotNull('reminder_at')
            ->whereBetween('reminder_at', [now(), now()->addMinutes(5)])
            ->where('status', '!=', 'completed')
            ->each(function (Task $task) {
                event(new TaskDueSoon($task));
            });
    }
}
