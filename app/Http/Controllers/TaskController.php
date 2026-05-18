<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Task::query()->whereHas('goal', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        });

        if ($request->filled('goal_id')) {
            $query->where('goal_id', $request->goal_id);
        }

        if ($request->has('is_completed')) {
            $query->where('is_completed', filter_var(
                $request->is_completed,
                FILTER_VALIDATE_BOOLEAN
            ));
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        $allowedSort = ['id', 'title', 'due_date', 'created_at', 'is_completed'];
        $sortBy = in_array($request->get('sort_by'), $allowedSort, true)
            ? $request->get('sort_by')
            : 'id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'goal_id' => 'required|integer|exists:goals,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_completed' => 'sometimes|boolean',
        ]);

        $this->ensureGoalOwnership($request, (int) $validated['goal_id']);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $task = Task::with('goal')->findOrFail($id);
        $this->ensureGoalOwnership($request, $task->goal_id);

        return response()->json($task);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $this->ensureGoalOwnership($request, $task->goal_id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_completed' => 'sometimes|boolean',
            'goal_id' => 'sometimes|integer|exists:goals,id',
        ]);

        if (isset($validated['goal_id']) && (int) $validated['goal_id'] !== $task->goal_id) {
            $this->ensureGoalOwnership($request, (int) $validated['goal_id']);
        }

        $task->update($validated);

        return response()->json($task);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $this->ensureGoalOwnership($request, $task->goal_id);

        $task->delete();

        return response()->json(['message' => 'Задача удалена.']);
    }

    private function ensureGoalOwnership(Request $request, int $goalId): void
    {
        $owns = Goal::where('id', $goalId)
            ->where('user_id', $request->user()->id)
            ->exists();

        abort_if(! $owns, 403, 'Цель не принадлежит пользователю.');
    }
}
