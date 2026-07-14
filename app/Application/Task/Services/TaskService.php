<?php

namespace App\Application\Task\Services;

use App\Application\Task\DTOs\CreateTaskDTO;
use App\Application\Task\DTOs\TaskFilterDTO;
use App\Application\Task\DTOs\UpdateTaskDTO;
use App\Domain\Task\Contracts\TaskRepositoryInterface;
use App\Domain\Task\Events\TaskCompleted;
use App\Domain\Task\Events\TaskCreated;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    public function list(TaskFilterDTO $dto): LengthAwarePaginator
    {
        return $this->taskRepository->paginate([
            'search'       => $dto->search,
            'type'         => $dto->type,
            'status'       => $dto->status,
            'priority'     => $dto->priority,
            'assigned_to'  => $dto->assignedTo,
            'lead_id'      => $dto->leadId,
            'contact_id'   => $dto->contactId,
            'deal_id'      => $dto->dealId,
            'due_date_from'=> $dto->dueDateFrom,
            'due_date_to'  => $dto->dueDateTo,
            'overdue'      => $dto->overdue,
            'sort_by'      => $dto->sortBy,
            'sort_dir'     => $dto->sortDir,
        ], $dto->perPage);
    }

    public function create(CreateTaskDTO $dto): Task
    {
        $task = $this->taskRepository->create([
            'tenant_id'   => Auth::user()->tenant_id,
            'created_by'  => Auth::id(),
            'assigned_to' => $dto->assignedTo ?? Auth::id(),
            'lead_id'     => $dto->leadId,
            'contact_id'  => $dto->contactId,
            'deal_id'     => $dto->dealId,
            'title'       => $dto->title,
            'description' => $dto->description,
            'type'        => $dto->type,
            'priority'    => $dto->priority,
            'status'      => 'pending',
            'due_date'    => $dto->dueDate,
            'due_time'    => $dto->dueTime,
            'reminder_at' => $dto->reminderAt,
            'meta'        => $dto->meta,
        ]);

        event(new TaskCreated($task));

        return $task;
    }

    public function update(Task $task, UpdateTaskDTO $dto): Task
    {
        $wasCompleted = $task->isCompleted();

        $data = array_filter([
            'title'       => $dto->title,
            'description' => $dto->description,
            'type'        => $dto->type,
            'priority'    => $dto->priority,
            'status'      => $dto->status,
            'assigned_to' => $dto->assignedTo,
            'lead_id'     => $dto->leadId,
            'contact_id'  => $dto->contactId,
            'deal_id'     => $dto->dealId,
            'due_date'    => $dto->dueDate,
            'due_time'    => $dto->dueTime,
            'reminder_at' => $dto->reminderAt,
            'meta'        => $dto->meta,
        ], fn ($v) => $v !== null);

        if (($dto->status === 'completed') && ! $wasCompleted) {
            $data['completed_at'] = now();
        }

        $updated = $this->taskRepository->update($task, $data);

        if ($dto->status === 'completed' && ! $wasCompleted) {
            event(new TaskCompleted($updated));
        }

        return $updated;
    }

    public function complete(Task $task): Task
    {
        $updated = $this->taskRepository->update($task, [
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        event(new TaskCompleted($updated));

        return $updated;
    }

    public function delete(Task $task): bool
    {
        return $this->taskRepository->delete($task);
    }

    public function upcoming(int $days = 7): Collection
    {
        return $this->taskRepository->getUpcoming(Auth::id(), $days);
    }

    public function overdue(): Collection
    {
        return $this->taskRepository->getOverdue(Auth::user()->tenant_id);
    }

    public function myStats(): array
    {
        return $this->taskRepository->getStatsByUser(Auth::id());
    }

    public function tenantStats(): array
    {
        return $this->taskRepository->getStatsByTenant(Auth::user()->tenant_id);
    }

    public function addComment(Task $task, string $content): TaskComment
    {
        return $task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $content,
        ]);
    }

    public function deleteComment(TaskComment $comment): bool
    {
        return $comment->delete();
    }
}
