@extends('layouts.app')

@section('title', 'Team board')

@section('content')
<div class="min-h-screen">
    <header class="border-b border-stone-200 bg-white/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('tasks.index') }}" class="flex items-center gap-2.5 font-display text-lg font-extrabold tracking-tight"><span class="grid h-8 w-8 place-items-center rounded-lg bg-forest text-white">✓</span>taskflow</a>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('teams.activate') }}" class="flex items-center gap-1">
                    @csrf
                    <label for="active-team" class="sr-only">Active team</label>
                    <select id="active-team" name="team_id" class="max-w-28 rounded-md border border-stone-200 bg-white px-1.5 py-2 text-xs font-semibold text-stone-700 sm:max-w-40 sm:px-2">
                        @foreach ($userTeams as $userTeam)
                            <option value="{{ $userTeam->id }}" @selected($userTeam->id === $activeTeam->id)>{{ $userTeam->name }}</option>
                        @endforeach
                    </select>
                    <button aria-label="Switch active team" class="rounded-md border border-stone-200 px-2 py-2 text-xs font-semibold text-forest hover:bg-green-50">↗</button>
                </form>
                <a href="{{ route('welcome') }}" class="rounded-md border border-stone-200 px-2 py-2 text-xs font-semibold text-forest hover:bg-green-50 sm:border-0 sm:px-0 sm:py-0 sm:hover:bg-transparent sm:hover:underline"><span class="sm:hidden">+</span><span class="hidden sm:inline">Manage teams</span></a>
                <span class="hidden text-xs text-stone-500 sm:inline">{{ auth()->user()->name }}</span>
                <span class="grid h-8 w-8 place-items-center rounded-full bg-green-100 text-[10px] font-bold text-forest">{{ str(auth()->user()->name)->initials() }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-md border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 hover:bg-stone-50">Sign out</button></form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 pb-12 pt-8 sm:px-6 lg:px-8 lg:pt-11">
        <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-[10px] font-bold tracking-[.18em] text-stone-400">{{ now()->format('l, F j') }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">A good day to <span class="text-leaf">make progress.</span></h1>
                <p class="mt-2 text-sm text-stone-500">{{ $activeTeam->name }} · Here’s where your team’s work is moving.</p>
            </div>
            <a href="#new-task" class="inline-flex items-center justify-center gap-2 rounded-md bg-forest px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-green-900"><span class="text-lg leading-none">+</span> New task</a>
        </section>

        @if (session('success'))
            <div role="status" class="mt-6 flex items-center justify-between rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900"><span>{{ session('success') }}</span><button type="button" onclick="this.parentElement.remove()" aria-label="Dismiss message" class="text-green-800">×</button></div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Please check the task details.</p>
                <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section aria-label="Team summary" class="mt-7 grid grid-cols-2 overflow-hidden rounded-lg border border-stone-200 bg-white sm:grid-cols-4">
            <article class="border-b border-r border-stone-100 p-4 sm:border-b-0 sm:p-5"><p class="text-[10px] font-bold tracking-wider text-stone-400">OPEN TASKS</p><p class="mt-3 font-display text-2xl font-bold">{{ $tasks->where('status', '!=', 'done')->count() }}</p><p class="mt-1 text-[10px] text-stone-500">ready to move forward</p></article>
            <article class="border-b border-stone-100 bg-green-50/70 p-4 sm:border-b-0 sm:border-r sm:p-5"><p class="text-[10px] font-bold tracking-wider text-stone-400">IN PROGRESS</p><p class="mt-3 font-display text-2xl font-bold">{{ $tasks->where('status', 'in_progress')->count() }}</p><p class="mt-1 text-[10px] text-stone-500">on the workbench</p></article>
            <article class="border-r border-stone-100 p-4 sm:p-5"><p class="text-[10px] font-bold tracking-wider text-stone-400">COMPLETED</p><p class="mt-3 font-display text-2xl font-bold">{{ $tasks->where('status', 'done')->count() }}</p><p class="mt-1 text-[10px] text-stone-500">work well done</p></article>
            <article class="p-4 sm:p-5"><p class="text-[10px] font-bold tracking-wider text-stone-400">TEAM MEMBERS</p><p class="mt-3 font-display text-2xl font-bold">{{ $teamMembers->count() }}</p><p class="mt-1 text-[10px] text-stone-500">sharing this workspace</p></article>
        </section>

        <details id="new-task" class="group mt-8 rounded-lg border border-stone-200 bg-white open:pb-5">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-4 sm:px-5">
                <span><span class="block font-display text-sm font-bold">Create a task</span><span class="mt-1 block text-xs text-stone-500">Give the next piece of work a clear owner.</span></span>
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-md bg-green-50 text-lg text-forest transition group-open:rotate-45">+</span>
            </summary>
            <form method="POST" action="{{ route('tasks.store') }}" class="grid gap-4 border-t border-stone-100 px-4 pt-5 sm:grid-cols-2 sm:px-5 lg:grid-cols-4">
                @csrf
                <label class="text-xs font-semibold text-stone-700 lg:col-span-2">Title <span class="text-coral">*</span><input name="title" maxlength="160" value="{{ old('title') }}" required placeholder="What needs to get done?" class="mt-1.5 block w-full rounded-md border border-stone-200 px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100"></label>
                <label class="text-xs font-semibold text-stone-700">Assigned to<select name="assigned_to" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100"><option value="">Unassigned</option>@foreach ($teamMembers as $member)<option value="{{ $member->id }}" @selected(old('assigned_to') == $member->id)>{{ $member->name }}</option>@endforeach</select></label>
                <label class="text-xs font-semibold text-stone-700">Due date<input name="due_date" type="date" value="{{ old('due_date') }}" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100"></label>
                <label class="text-xs font-semibold text-stone-700">Status<select name="status" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100"><option value="todo">To do</option><option value="in_progress">In progress</option><option value="done">Done</option></select></label>
                <label class="text-xs font-semibold text-stone-700">Priority<select name="priority" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></label>
                <label class="text-xs font-semibold text-stone-700 sm:col-span-2 lg:col-span-2">Description<textarea name="description" rows="2" maxlength="5000" placeholder="Add a little context…" class="mt-1.5 block w-full resize-y rounded-md border border-stone-200 px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">{{ old('description') }}</textarea></label>
                <div class="flex items-end sm:col-span-2 lg:col-span-4"><button class="rounded-md bg-forest px-4 py-2.5 text-xs font-semibold text-white hover:bg-green-900">Add to team board</button></div>
            </form>
        </details>

        <section class="mt-9" aria-labelledby="board-title">
            <div class="mb-4 flex items-end justify-between gap-3"><div><p class="text-[10px] font-bold tracking-[.18em] text-stone-400">TEAM BOARD</p><h2 id="board-title" class="mt-1.5 text-lg font-bold">Work in motion <span class="ml-1 rounded-full bg-stone-200 px-2 py-1 align-middle font-sans text-[10px] font-semibold text-stone-600">{{ $tasks->count() }}</span></h2></div><a href="#team" class="text-xs font-semibold text-forest hover:underline">See the team ↓</a></div>
            <div class="grid items-start gap-3 lg:grid-cols-3">
                @php($statuses = ['todo' => 'To do', 'in_progress' => 'In progress', 'done' => 'Done'])
                @foreach ($statuses as $statusKey => $statusLabel)
                    @php($columnTasks = $tasks->where('status', $statusKey))
                    <section class="min-w-0 rounded-lg border border-stone-200 bg-stone-100/80 p-3">
                        <header class="mb-3 flex items-center justify-between px-1">
                            <h3 class="flex items-center gap-2 text-xs font-bold"><span class="h-2 w-2 rounded-full {{ $statusKey === 'todo' ? 'bg-emerald-600' : ($statusKey === 'in_progress' ? 'bg-sky-600' : 'bg-lime-700') }}"></span>{{ $statusLabel }}<span class="rounded-full bg-stone-200 px-1.5 py-0.5 text-[10px] text-stone-600">{{ $columnTasks->count() }}</span></h3>
                            <a href="#new-task" aria-label="Add task to {{ $statusLabel }}" class="grid h-7 w-7 place-items-center rounded text-stone-500 hover:bg-white hover:text-forest">+</a>
                        </header>
                        <div class="space-y-2">
                            @forelse ($columnTasks as $task)
                                <article class="rise-in rounded-md border border-stone-200 bg-white p-3 shadow-sm">
                                    <div class="flex items-center justify-between gap-2"><span class="inline-flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide {{ $task->priority === 'high' ? 'text-red-700' : ($task->priority === 'medium' ? 'text-amber-700' : 'text-stone-500') }}"><span class="h-1.5 w-1.5 rounded-full {{ $task->priority === 'high' ? 'bg-red-500' : ($task->priority === 'medium' ? 'bg-amber-500' : 'bg-stone-400') }}"></span>{{ $task->priority }}</span><a href="{{ route('tasks.edit', $task) }}" class="rounded px-2 py-1 text-[10px] font-semibold text-stone-500 hover:bg-green-50 hover:text-forest">Edit</a></div>
                                    <h4 class="mt-2 text-sm font-bold leading-5">{{ $task->title }}</h4>
                                    @if ($task->description)<p class="mt-1 line-clamp-2 text-xs leading-5 text-stone-500">{{ $task->description }}</p>@endif
                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-stone-100 pt-2.5 text-[10px] text-stone-500">
                                        <span class="{{ $task->due_date && $task->due_date->isPast() && $task->status !== 'done' ? 'font-semibold text-red-700' : '' }}">{{ $task->due_date ? $task->due_date->format('M j') : 'No due date' }}</span>
                                        <span class="inline-flex items-center gap-1.5"><span class="grid h-5 w-5 place-items-center rounded-full bg-green-100 text-[8px] font-bold text-forest">{{ $task->assignee ? str($task->assignee->name)->initials() : '?' }}</span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                    </div>
                                    <div class="mt-2.5 flex items-center gap-2 border-t border-stone-100 pt-2">
                                        <form method="POST" action="{{ route('tasks.status', $task) }}" class="flex min-w-0 flex-1 items-center gap-1.5">@csrf @method('PATCH')<label class="sr-only" for="status-{{ $task->id }}">Change status of {{ $task->title }}</label><select id="status-{{ $task->id }}" name="status" class="min-w-0 flex-1 rounded border border-stone-200 bg-white px-1.5 py-1 text-[10px] text-stone-600"><option value="todo" @selected($task->status === 'todo')>To do</option><option value="in_progress" @selected($task->status === 'in_progress')>In progress</option><option value="done" @selected($task->status === 'done')>Done</option></select><button class="rounded px-2 py-1 text-[10px] font-semibold text-forest hover:bg-green-50">Save</button></form>
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">@csrf @method('DELETE')<button class="rounded px-2 py-1 text-[10px] font-semibold text-stone-400 hover:bg-red-50 hover:text-red-700">Delete</button></form>
                                    </div>
                                    <p class="mt-1 text-[9px] text-stone-400">Added by {{ $task->creator->name }}</p>
                                </article>
                            @empty
                                <div class="grid min-h-24 place-items-center rounded-md border border-dashed border-stone-300 px-4 text-center text-xs text-stone-400">A little room to breathe.</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </section>

        <section id="team" class="mt-10 border-t border-stone-200 pt-7">
            <div class="flex items-end justify-between"><div><p class="text-[10px] font-bold tracking-[.18em] text-stone-400">THE PEOPLE BEHIND THE PROGRESS</p><h2 class="mt-1.5 text-lg font-bold">Your team, at a glance</h2></div><span class="rounded-full bg-stone-200 px-2 py-1 text-[10px] font-semibold text-stone-600">{{ $teamMembers->count() }}</span></div>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($teamMembers as $member)
                    <article class="flex min-w-0 items-center gap-3 rounded-md border border-stone-200 bg-white px-3 py-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-green-100 text-[10px] font-bold text-forest">{{ str($member->name)->initials() }}</span>
                        <span class="min-w-0 flex-1"><strong class="block truncate text-xs">{{ $member->name }} @if ($member->id === auth()->id())<span class="ml-1 text-[9px] font-semibold uppercase text-leaf">You</span>@endif</strong><span class="block truncate text-[10px] text-stone-500">{{ $member->email }}</span></span>
                        <span class="shrink-0 rounded bg-stone-100 px-2 py-1 text-[9px] text-stone-600">{{ $member->open_tasks_count }} open</span>
                    </article>
                @endforeach
            </div>
        </section>
        <footer class="mt-9 border-t border-stone-200 pt-4 text-[10px] text-stone-400">TaskFlow <span class="px-1">·</span> make work feel lighter.</footer>
    </main>
</div>
@endsection