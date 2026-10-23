@extends('layouts.app')

@section('title', 'Choose your team')

@section('content')
<main class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2.5 font-display text-lg font-extrabold tracking-tight text-stone-800"><span class="grid h-8 w-8 place-items-center rounded-lg bg-forest text-white">✓</span>taskflow</a>

        <section class="mt-10 text-center">
            <p class="text-[10px] font-bold tracking-[.18em] text-leaf">ONE LAST STEP</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Welcome, {{ auth()->user()->name }}.</h1>
            <p class="mt-3 text-sm text-stone-500">Create a team or join one to get started.</p>
        </section>

        <div class="mt-8 grid gap-5 lg:grid-cols-2">
            <section class="rounded-lg border border-stone-200 bg-white p-5 sm:p-7">
                <p class="text-[10px] font-bold tracking-[.18em] text-leaf">START SOMETHING NEW</p>
                <h2 class="mt-2 font-display text-xl font-bold">Create a new team</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Set up a shared workspace and invite others with its team password.</p>

                <form method="POST" action="{{ route('teams.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <label for="name" class="block text-xs font-semibold text-stone-700">Team name
                        <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                    </label>
                    @error('name')<p class="-mt-3 text-xs text-red-700">{{ $message }}</p>@enderror

                    <label for="password" class="block text-xs font-semibold text-stone-700">Team password
                        <input id="password" name="password" type="password" minlength="12" autocomplete="new-password" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                    </label>
                    @error('password')<p class="-mt-3 text-xs text-red-700">{{ $message }}</p>@enderror

                    <button class="flex w-full items-center justify-between rounded-md bg-forest px-4 py-3 text-sm font-semibold text-white transition hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-forest focus:ring-offset-2">Create team <span aria-hidden="true">↗</span></button>
                </form>
            </section>

            <section class="rounded-lg border border-stone-200 bg-white p-5 sm:p-7">
                <p class="text-[10px] font-bold tracking-[.18em] text-leaf">ALREADY HAVE A TEAM?</p>
                <h2 class="mt-2 font-display text-xl font-bold">Join an existing team</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Choose a workspace and enter the password shared by its owner.</p>

                @if ($teams->isEmpty())
                    <p class="mt-6 rounded-md bg-stone-50 px-4 py-3 text-sm text-stone-500">There are no teams to join yet. Create one to get started.</p>
                @else
                    <form method="POST" action="{{ route('teams.join') }}" class="mt-6 space-y-4">
                        @csrf
                        <label for="team_id" class="block text-xs font-semibold text-stone-700">Available teams
                            <select id="team_id" name="team_id" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                                <option value="">Choose a team</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        @error('team_id')<p class="-mt-3 text-xs text-red-700">{{ $message }}</p>@enderror

                        <label for="team_password" class="block text-xs font-semibold text-stone-700">Team password
                            <input id="team_password" name="team_password" type="password" autocomplete="current-password" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                        </label>
                        @error('team_password')<p class="-mt-3 text-xs text-red-700">{{ $message }}</p>@enderror

                        <button class="flex w-full items-center justify-between rounded-md border border-stone-200 px-4 py-3 text-sm font-semibold text-forest transition hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-leaf focus:ring-offset-2">Join team <span aria-hidden="true">↗</span></button>
                    </form>
                @endif
            </section>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
            @csrf
            <button class="text-xs font-semibold text-stone-500 hover:text-forest hover:underline">Sign out</button>
        </form>
    </div>
</main>
@endsection
