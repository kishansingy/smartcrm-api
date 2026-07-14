<?php

namespace App\Application\Task\Listeners;

use App\Domain\Task\Events\TaskCreated;
use App\Domain\Task\Events\TaskCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyTaskAssignee implements ShouldQueue
{
    public function handleTaskCreated(TaskCreated $event): void
    {
        $task = $event->task->load('assignedTo', 'createdBy');

        // Skip notification if assigned to self
        if ($task->assigned_to === $task->created_by) {
            return;
        }

        // Notification dispatched to assignee
        // $task->assignedTo->notify(new TaskAssignedNotification($task));
    }

    public function handleTaskCompleted(TaskCompleted $event): void
    {
        $task = $event->task->load('createdBy');

        // Notify task creator on completion
        // $task->createdBy->notify(new TaskCompletedNotification($task));
    }
}
