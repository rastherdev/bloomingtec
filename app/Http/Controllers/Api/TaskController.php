<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TaskStoreRequest;
use App\Http\Requests\Api\TaskUpdateRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::where('user_id', Auth::id())->latest()->get();
        return response()->json($tasks);
    }

    public function store(TaskStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $task = Task::create($data);
    return response()->json($task, HttpResponse::HTTP_CREATED);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorizeTask($task);
        return response()->json($task);
    }

    public function update(TaskUpdateRequest $request, Task $task): JsonResponse
    {
        $this->authorizeTask($task);
        $task->update($request->validated());
        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorizeTask($task);
        $task->delete();
    return response()->json([], HttpResponse::HTTP_NO_CONTENT);
    }

    protected function authorizeTask(Task $task): void
    {
        abort_if($task->user_id !== Auth::id(), 403, 'Forbidden');
    }
}
