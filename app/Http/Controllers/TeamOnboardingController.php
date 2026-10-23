<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TeamOnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $teams = Team::query()
            ->select(['id', 'name'])
            ->whereDoesntHave('users', fn ($query) => $query->whereKey($user->getKey()))
            ->orderBy('name')
            ->get();
        $userTeams = $user->teams()
            ->orderBy('teams.name')
            ->get(['teams.id', 'teams.name']);

        return view('teams.welcome', compact('teams', 'userTeams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:teams,name'],
            'password' => ['required', 'string', Password::min(12)->letters()->numbers()],
        ]);

        $team = DB::transaction(function () use ($user, $validated): Team {
            $team = Team::query()->create([
                'name' => $validated['name'],
                'password' => $validated['password'],
            ]);

            $team->users()->attach($user->getKey(), ['role' => 'owner']);

            return $team;
        });

        $request->session()->put('active_team_id', $team->getKey());

        return redirect()->route('tasks.index');
    }

    public function join(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'team_password' => ['required', 'string', 'max:255'],
        ]);

        $team = Team::query()->findOrFail($validated['team_id']);

        if ($user->teams()->whereKey($team->getKey())->exists()) {
            return back()->withErrors(['team_id' => 'You are already a member of this team.']);
        }

        if (! Hash::check($validated['team_password'], $team->password)) {
            return back()
                ->withErrors(['team_password' => 'The selected team password is incorrect.'])
                ->withInput(['team_id' => $validated['team_id']]);
        }

        DB::transaction(function () use ($team, $user): void {
            $team->users()->attach($user->getKey(), ['role' => 'member']);
        });

        $request->session()->put('active_team_id', $team->getKey());

        return redirect()->route('tasks.index');
    }

    public function activate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => [
                'required',
                'integer',
                Rule::exists('team_user', 'team_id')->where('user_id', $request->user()->getKey()),
            ],
        ]);

        $request->session()->put('active_team_id', $validated['team_id']);

        return redirect()->route('tasks.index');
    }
}
