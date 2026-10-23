<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $activeTeam = $user->teams()->whereKey(session('active_team_id'))->firstOrFail();
        $tasks = Task::with(['assignee', 'creator'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->get();
        $teamMembers = $activeTeam->users()->withCount([
            'assignedTasks as open_tasks_count' => fn ($query) => $query->where('status', '!=', 'done'),
        ])->orderBy('name')->get();
        $userTeams = $user->teams()->orderBy('teams.name')->get(['teams.id', 'teams.name']);

        return view('tasks.index', compact('activeTeam', 'tasks', 'teamMembers', 'userTeams'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->taskRules());
        $request->user()->createdTasks()->create($validated + [
            'team_id' => $request->session()->get('active_team_id'),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task added to the team board.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Task $task): View
    {
        $teamMembers = $request->user()
            ->teams()
            ->whereKey($request->session()->get('active_team_id'))
            ->firstOrFail()
            ->users()
            ->orderBy('name')
            ->get();

        return view('tasks.edit', compact('task', 'teamMembers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $task->update($request->validate($this->taskRules()));

        return redirect()->route('tasks.index')->with('success', 'Task details updated.');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
        ]);
        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task status updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function taskRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('team_user', 'user_id')->where('team_id', session('active_team_id')),
            ],
        ];
    }
}
