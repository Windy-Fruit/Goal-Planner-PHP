<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // получить все цели
    public function index(Request $request)
    {
        $query = Goal::with(['tasks', 'category']);

        // фильтрация
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // пагинация
        $goals = $query->paginate(5);

        return response()->json($goals);
    }

    /**
     * Store a newly created resource in storage.
     */
    // создать цель
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'required|string|in:active,completed'
        ]);

        $goal = Goal::create($request->all());

        return response()->json($goal, 201);
    }

    /**
     * Display the specified resource.
     */
    // показать одну цель
    public function show(string $id)
    {
        return response()->json(
            Goal::with(['tasks', 'category'])->findOrFail($id)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    // обновить цель
    public function update(Request $request, string $id)
    {
        $goal = Goal::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|required|string|in:active,completed'
        ]);
        $goal->update($request->all());

        return response()->json($goal);
    }

    /**
     * Remove the specified resource from storage.
     */
    // удалить цель
    public function destroy(string $id)
    {
        Goal::destroy($id);

        return response()->json(['message' => 'Deleted']);
    }
}
