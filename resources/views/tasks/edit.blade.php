@extends('layouts.app')

@section('title', 'Edit task')

@section('content')
<main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-forest hover:underline">← Back to team board</a>
    <div class="mt-5 rounded-lg border border-stone-200 bg-white p-5 sm:p-8">
        <p class="text-[10px] font-bold tracking-[.18em] text-stone-400">TASK DETAILS</p>
        <h1 class="mt-2 text-2xl font-bold">Edit task</h1>
        @if ($errors->any())<div role="alert" class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-800">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('tasks.update', $task) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf @method('PUT')
            <label class="text-xs font-semibold text-stone-700 sm:col-span-2">Title<input name="title" value="{{ old('title', $task->title) }}" maxlength="160" required class="mt-1.5 block w-full rounded-md border border-stone-200 px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100"></label>
            <label class="text-xs font-semibold text-stone-700 sm:col-span-2">Description<textarea name="description" rows="4" maxlength="5000" class="mt-1.5 block w-full rounded-md border border-stone-200 px-3 py-2.5 text-sm font-normal outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">{{ old('description', $task->description) }}</textarea></label>
            <label class="text-xs font-semibold text-stone-700">Status<select name="status" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal"><option value="todo" @selected(old('status', $task->status) === 'todo')>To do</option><option value="in_progress" @selected(old('status', $task->status) === 'in_progress')>In progress</option><option value="done" @selected(old('status', $task->status) === 'done')>Done</option></select></label>
            <label class="text-xs font-semibold text-stone-700">Priority<select name="priority" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal"><option value="low" @selected(old('priority', $task->priority) === 'low')>Low</option><option value="medium" @selected(old('priority', $task->priority) === 'medium')>Medium</option><option value="high" @selected(old('priority', $task->priority) === 'high')>High</option></select></label>
            <label class="text-xs font-semibold text-stone-700">Due date<input name="due_date" type="date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal"></label>
            <label class="text-xs font-semibold text-stone-700">Assigned to<select name="assigned_to" class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm font-normal"><option value="">Unassigned</option>@foreach ($teamMembers as $member)<option value="{{ $member->id }}" @selected(old('assigned_to', $task->assigned_to) == $member->id)>{{ $member->name }}</option>@endforeach</select></label>
            <div class="mt-2 flex flex-wrap gap-2 sm:col-span-2"><button class="rounded-md bg-forest px-4 py-2.5 text-xs font-semibold text-white hover:bg-green-900">Save changes</button><a href="{{ route('tasks.index') }}" class="rounded-md border border-stone-200 px-4 py-2.5 text-xs font-semibold text-stone-600 hover:bg-stone-50">Cancel</a></div>
        </form>
    </div>
</main>
@endsection