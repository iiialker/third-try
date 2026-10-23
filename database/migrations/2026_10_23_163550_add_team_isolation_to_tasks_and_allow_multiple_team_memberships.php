<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tasksWithoutOneUnambiguousTeam = DB::table('tasks')
            ->leftJoin('team_user as creator_membership', 'creator_membership.user_id', '=', 'tasks.created_by')
            ->leftJoin('team_user as assignee_membership', 'assignee_membership.user_id', '=', 'tasks.assigned_to')
            ->select('tasks.id')
            ->groupBy('tasks.id', 'tasks.assigned_to')
            ->havingRaw(
                'COUNT(creator_membership.team_id) != 1 OR '
                .'(tasks.assigned_to IS NOT NULL AND ('
                .'COUNT(assignee_membership.team_id) != 1 OR '
                .'MIN(assignee_membership.team_id) != MIN(creator_membership.team_id)))'
            )
            ->limit(1)
            ->exists();

        if ($tasksWithoutOneUnambiguousTeam) {
            throw new RuntimeException(
                'Cannot enable team isolation until every existing task has a creator and assignee in exactly one shared team. '
                .'Correct the affected memberships or assignments and rerun the migration.'
            );
        }

        // Добавляем колонку team_id, если её еще нет
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'team_id')) {
                $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            }
        });

        $teamIdsByUser = DB::table('team_user')->pluck('team_id', 'user_id');

        DB::table('tasks')
            ->select(['id', 'created_by'])
            ->orderBy('id')
            ->chunkById(500, function ($tasks) use ($teamIdsByUser): void {
                foreach ($tasks as $task) {
                    if (isset($teamIdsByUser[$task->created_by])) {
                        DB::table('tasks')
                            ->where('id', $task->id)
                            ->update(['team_id' => $teamIdsByUser[$task->created_by]]);
                    }
                }
            });

        // Безопасно удаляем внешний ключ и индекс в таблице team_user
        Schema::table('team_user', function (Blueprint $table) {
            try {
                // Сначала пробуем сбросить возможный внешний ключ на user_id
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
                // Если внешнего ключа не было, пропускаем
            }

            try {
                $table->dropUnique('team_user_user_id_unique');
            } catch (\Exception $e) {
                // Если индекс уже удален, пропускаем
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $usersWithMultipleTeams = DB::table('team_user')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(team_id) > 1')
            ->limit(1)
            ->exists();

        if ($usersWithMultipleTeams) {
            throw new RuntimeException(
                'Cannot restore one-team-per-user membership while users belong to multiple teams.'
            );
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }
        });

        Schema::table('team_user', function (Blueprint $table) {
            try {
                $table->unique('user_id');
            } catch (\Exception $e) {
                // Пропускаем, если индекс уже существует
            }
        });
    }
};