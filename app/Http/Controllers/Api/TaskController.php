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

    public function show(string $task): JsonResponse
    {
        if (!ctype_digit($task)) {
            return response()->json([
                'message' => 'Invalid task id',
                'errors' => ['id' => ['The id must be a numeric value.']]
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $id = (int)$task;
        $model = Task::find($id);
        if (!$model) {
            return response()->json(['message' => 'Task not found'], HttpResponse::HTTP_NOT_FOUND);
        }
        if ($model->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], HttpResponse::HTTP_FORBIDDEN);
        }
        return response()->json($model);
    }

    public function update(TaskUpdateRequest $request, string $task): JsonResponse
    {
        if (!ctype_digit($task)) {
            return response()->json([
                'message' => 'Invalid task id',
                'errors' => ['id' => ['The id must be a numeric value.']]
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $id = (int)$task;
        $model = Task::find($id);
        if (!$model) {
            return response()->json(['message' => 'Task not found'], HttpResponse::HTTP_NOT_FOUND);
        }
        if ($model->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], HttpResponse::HTTP_FORBIDDEN);
        }
        $data = $request->validated();
        unset($data['user_id']); // prevent ownership changes
        $model->update($data);
        return response()->json($model);
    }

    public function destroy(string $task): JsonResponse
    {
        if (!ctype_digit($task)) {
            return response()->json([
                'message' => 'Invalid task id',
                'errors' => ['id' => ['The id must be a numeric value.']]
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $id = (int)$task;
        $model = Task::find($id);
        if (!$model) {
            return response()->json(['message' => 'Task not found'], HttpResponse::HTTP_NOT_FOUND);
        }
        if ($model->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], HttpResponse::HTTP_FORBIDDEN);
        }
        $model->delete();
        return response()->json([], HttpResponse::HTTP_NO_CONTENT);
    }

    protected function authorizeTask(Task $task): void
    {
        abort_if($task->user_id !== Auth::id(), 403, 'Forbidden');
    }
}
