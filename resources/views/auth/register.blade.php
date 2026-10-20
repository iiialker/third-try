@extends('layouts.app')

@section('title', 'Create an account')

@section('content')
<main class="grid min-h-screen lg:grid-cols-[0.9fr_1.1fr]">
    <section class="relative flex min-h-[250px] flex-col justify-between overflow-hidden bg-forest px-7 py-7 text-white sm:px-12 lg:min-h-screen lg:px-16 lg:py-10">
        <a href="{{ route('login') }}" class="relative z-10 flex items-center gap-2.5 font-display text-xl font-extrabold tracking-tight"><span class="grid h-8 w-8 place-items-center rounded-lg bg-white text-forest">✓</span>taskflow</a>
        <div class="relative z-10 max-w-lg py-7 lg:py-0">
            <p class="text-[10px] font-bold tracking-[.2em] text-green-100">A BETTER WAY TO WORK TOGETHER</p>
            <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">Good work<br>starts <span class="text-lime-200">together.</span></h1>
            <p class="mt-4 max-w-sm text-sm leading-6 text-green-100">Bring the people and priorities behind your next great thing into one clear view.</p>
        </div>
        <p class="relative z-10 text-xs text-green-100">One shared space for your whole team.</p>
        <div class="pointer-events-none absolute -right-40 top-1/4 h-[28rem] w-[28rem] rounded-full border border-white/10"></div>
    </section>
    <section class="flex items-center justify-center px-6 py-10 sm:px-12">
        <div class="w-full max-w-sm rise-in">
            <p class="text-[10px] font-bold tracking-[.18em] text-leaf">GET STARTED</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight">Create your account.</h2>
            <p class="mt-2 text-sm text-stone-500">Start organizing work with your team.</p>
            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-3.5">
                @csrf
                <label class="block text-xs font-semibold text-stone-700">Name
                    <input name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="100" required autofocus class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                </label>
                @error('name')<p class="-mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
                <label class="block text-xs font-semibold text-stone-700">Email address
                    <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                </label>
                @error('email')<p class="-mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
                <label class="block text-xs font-semibold text-stone-700">Password
                    <input name="password" type="password" autocomplete="new-password" minlength="12" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                </label>
                @error('password')<p class="-mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
                <label class="block text-xs font-semibold text-stone-700">Confirm password
                    <input name="password_confirmation" type="password" autocomplete="new-password" minlength="12" required class="mt-1.5 block w-full rounded-md border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-leaf focus:ring-2 focus:ring-green-100">
                </label>
                <button class="flex w-full items-center justify-between rounded-md bg-forest px-4 py-3 text-sm font-semibold text-white transition hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-forest focus:ring-offset-2">Create account <span aria-hidden="true">↗</span></button>
            </form>
            <p class="mt-5 text-center text-xs text-stone-500">Already have an account? <a href="{{ route('login') }}" class="font-bold text-forest hover:underline">Sign in</a></p>
        </div>
    </section>
</main>
@endsection