<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;

class ActiveTeamScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $userId = Auth::id();
        $teamId = session('active_team_id');

        if ($userId === null || $teamId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder
            ->where($model->qualifyColumn('team_id'), $teamId)
            ->whereExists(function (QueryBuilder $query) use ($model, $teamId, $userId): void {
                $query->selectRaw('1')
                    ->from('team_user')
                    ->whereColumn('team_user.team_id', $model->qualifyColumn('team_id'))
                    ->where('team_user.team_id', $teamId)
                    ->where('team_user.user_id', $userId);
            });
    }
}
