@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<main class="grid min-h-screen lg:grid-cols-[0.9fr_1.1fr]">
    <section class="relative flex min-h-[270px] flex-col justify-between overflow-hidden bg-forest px-7 py-7 text-white sm:px-12 lg:min-h-screen lg:px-16 lg:py-10">
        <a href="{{ route('login') }}" class="relative z-10 flex items-center gap-2.5 font-display text-xl font-extrabold tracking-tight"><span class="grid h-8 w-8 place-items-center rounded-lg bg-white text-forest">✓</span>taskflow</a>
        <div class="relative z-10 max-w-lg py-8 lg:py-0">
            <p class="text-[10px] font-bold tracking-[.2em] text-green-100">YOUR TEAM, IN SYNC</p>
            <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">Make room<br>for <span class="text-lime-200">good work.</span></h1>
            <p class="mt-4 max-w-sm text-sm leading-6 text-green-100">One clear view of what matters, who’s on it, and what’s next.</p>
        </div>
        <p class="relative z-10 text-xs text-green-100">Small steps. Great teamwork.</p>
        <div class="pointer-events-none absolute -right-40 top-1/4 h-[28rem] w-[28rem] rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -right-24 top-1/3 h-80 w-80 rounded-full border border-white/10"></div>
    </section>
    <section class="flex items-center justify-center px-6 py-12 sm:px-12">
        <div class="w-full max-w-sm rise-in">
            <p class="text-[10px] font-bold tracking-[.18em] text-leaf">WELCOME BACK</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight">Pick up where you left off.</h2>
            <p class="mt-2 text-sm text-stone-500">Sign in to your team’s shared workspace.</p>
            @if (session('status'))
                <div class="mt-5 rounded-md border border-green-200 bg-green-50 px-3 py-2.5 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('login.store') }}" class="mt-7 space-y-4">
                @csrf
                <label class="block text-xs font-semibold text-stone-700">Email address
                    <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-3 text-sm outline-none transition focus:border-leaf focus:ring-2 focus:ring-green-100">
                </label>
                @error('email')<p class="-mt-3 text-xs text-red-700">{{ $message }}</p>@enderror
                <label class="block text-xs font-semibold text-stone-700">Password
                    <input name="password" type="password" autocomplete="current-password" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-3 text-sm outline-none transition focus:border-leaf focus:ring-2 focus:ring-green-100">
                </label>
                @error('password')<p class="-mt-3 text-xs text-red-700">{{ $message }}</p>@enderror
                <label class="flex items-center gap-2 text-xs text-stone-600"><input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-forest focus:ring-leaf">Keep me signed in</label>
                <button class="flex w-full items-center justify-between rounded-md bg-forest px-4 py-3 text-sm font-semibold text-white transition hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-forest focus:ring-offset-2">Sign in <span aria-hidden="true">↗</span></button>
            </form>
            <p class="mt-6 text-center text-xs text-stone-500">New to TaskFlow? <a href="{{ route('register') }}" class="font-bold text-forest hover:underline">Create an account</a></p>
        </div>
    </section>
</main>
@endsection