<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Application\Task\DTOs\CreateTaskDTO;
use App\Application\Task\DTOs\TaskFilterDTO;
use App\Application\Task\DTOs\UpdateTaskDTO;
use App\Application\Task\Services\TaskService;
use App\Domain\Task\Models\Task;
use App\Domain\Task\Models\TaskComment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskCommentResource;
use App\Http\Resources\TaskResource;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('tasks.view');

        $paginated = $this->taskService->list(
            TaskFilterDTO::fromArray($request->query())
        );

        return ApiResponse::paginated($paginated->through(
            fn ($task) => new TaskResource($task)
        ));
    }

    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create(
            CreateTaskDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new TaskResource($task), 'Task created.', 201);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('tasks.view');

        return ApiResponse::success(
            new TaskResource($task->load(['assignedTo', 'createdBy', 'lead', 'contact', 'deal', 'comments.user']))
        );
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updated = $this->taskService->update(
            $task,
            UpdateTaskDTO::fromArray($request->validated())
        );

        return ApiResponse::success(new TaskResource($updated), 'Task updated.');
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('tasks.delete');
        $this->taskService->delete($task);

        return ApiResponse::success(null, 'Task deleted.');
    }

    public function complete(Task $task): JsonResponse
    {
        $this->authorize('tasks.update');
        $updated = $this->taskService->complete($task);

        return ApiResponse::success(new TaskResource($updated), 'Task completed.');
    }

    public function upcoming(Request $request): JsonResponse
    {
        $this->authorize('tasks.view');

        $days  = (int) $request->query('days', 7);
        $tasks = $this->taskService->upcoming($days);

        return ApiResponse::success(TaskResource::collection($tasks));
    }

    public function overdue(): JsonResponse
    {
        $this->authorize('tasks.view');

        return ApiResponse::success(TaskResource::collection($this->taskService->overdue()));
    }

    public function myStats(): JsonResponse
    {
        return ApiResponse::success($this->taskService->myStats());
    }

    public function tenantStats(): JsonResponse
    {
        $this->authorize('tasks.view');

        return ApiResponse::success($this->taskService->tenantStats());
    }

    public function storeComment(Request $request, Task $task): JsonResponse
    {
        $this->authorize('tasks.update');

        $request->validate(['content' => ['required', 'string']]);

        $comment = $this->taskService->addComment($task, $request->input('content'));

        return ApiResponse::success(
            new TaskCommentResource($comment->load('user')),
            'Comment added.',
            201
        );
    }

    public function destroyComment(Task $task, TaskComment $comment): JsonResponse
    {
        $this->authorize('tasks.update');
        $this->taskService->deleteComment($comment);

        return ApiResponse::success(null, 'Comment deleted.');
    }
}
