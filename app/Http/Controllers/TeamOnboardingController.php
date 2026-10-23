<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TeamOnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->teams()->exists()) {
            return redirect()->route('tasks.index');
        }

        $teams = Team::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return view('teams.welcome', compact('teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->teams()->exists()) {
            return redirect()->route('tasks.index');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:teams,name'],
            'password' => ['required', 'string', Password::min(12)->letters()->numbers()],
        ]);

        DB::transaction(function () use ($user, $validated): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if ($lockedUser->teams()->exists()) {
                return;
            }

            $team = Team::query()->create([
                'name' => $validated['name'],
                'password' => $validated['password'],
            ]);

            $team->users()->attach($lockedUser->getKey(), ['role' => 'owner']);
        });

        return redirect()->route('tasks.index');
    }

    public function join(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->teams()->exists()) {
            return redirect()->route('tasks.index');
        }

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'team_password' => ['required', 'string', 'max:255'],
        ]);

        $team = Team::query()->findOrFail($validated['team_id']);

        if (! Hash::check($validated['team_password'], $team->password)) {
            return back()
                ->withErrors(['team_password' => 'The selected team password is incorrect.'])
                ->withInput(['team_id' => $validated['team_id']]);
        }

        DB::transaction(function () use ($team, $user): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if ($lockedUser->teams()->exists()) {
                return;
            }

            $team->users()->attach($lockedUser->getKey(), ['role' => 'member']);
        });

        return redirect()->route('tasks.index');
    }
}
