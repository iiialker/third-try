<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamMembership
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userTeams = $request->user()->teams()->pluck('teams.id');

        if ($userTeams->isEmpty()) {
            return redirect()->route('welcome');
        }

        $activeTeamId = $request->session()->get('active_team_id');

        if ($activeTeamId === null || ! $userTeams->contains((int) $activeTeamId)) {
            if ($userTeams->count() > 1) {
                $request->session()->forget('active_team_id');

                return redirect()->route('welcome');
            }

            $activeTeamId = $userTeams->first();
            $request->session()->put('active_team_id', $activeTeamId);
        }

        return $next($request);
    }
}
