<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->goals()->with(['tasks', 'categories']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        $allowedSort = ['id', 'title', 'deadline', 'created_at', 'status'];
        $sortBy = in_array($request->get('sort_by'), $allowedSort, true)
            ? $request->get('sort_by')
            : 'id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 5);
        $goals = $query->paginate($perPage);

        return response()->json($goals);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|string|in:active,completed',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $goal = $request->user()->goals()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        if (! empty($validated['category_ids'])) {
            $goal->categories()->sync($validated['category_ids']);
        }

        return response()->json(
            $goal->load(['tasks', 'categories']),
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $goal = $request->user()->goals()
            ->with(['tasks', 'categories'])
            ->findOrFail($id);

        return response()->json($goal);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $goal = $request->user()->goals()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|required|string|in:active,completed',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $goal->update(collect($validated)->except('category_ids')->toArray());

        if (array_key_exists('category_ids', $validated)) {
            $goal->categories()->sync($validated['category_ids']);
        }

        return response()->json($goal->load(['tasks', 'categories']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $goal = $request->user()->goals()->findOrFail($id);
        $goal->delete();

        return response()->json(['message' => 'Цель удалена.']);
    }

    public function progress(Request $request, int $id): JsonResponse
    {
        $goal = $request->user()->goals()->findOrFail($id);

        $total = $goal->tasks()->count();
        $completed = $goal->tasks()->where('is_completed', true)->count();
        $percent = $total === 0 ? 0 : (int) round($completed / $total * 100);

        return response()->json([
            'goal_id' => $goal->id,
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'progress_percent' => $percent,
        ]);
    }
}
