<?php

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the task board and teammate directory only show the active team', function () {
    $teamA = Team::factory()->create(['name' => 'Alpha']);
    $teamB = Team::factory()->create(['name' => 'Beta']);
    $memberA = User::factory()->create(['name' => 'Alpha Member', 'email' => 'alpha@example.test']);
    $memberB = User::factory()->create(['name' => 'Beta Member', 'email' => 'beta@example.test']);
    $teamA->users()->attach($memberA->id, ['role' => 'owner']);
    $teamB->users()->attach($memberB->id, ['role' => 'owner']);
    Task::factory()->create([
        'team_id' => $teamA->id,
        'created_by' => $memberA->id,
        'title' => 'Alpha private task',
    ]);
    Task::factory()->create([
        'team_id' => $teamB->id,
        'created_by' => $memberB->id,
        'title' => 'Beta private task',
    ]);
    $this->actingAs($memberA)->withSession(['active_team_id' => $teamA->id]);

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Alpha private task')
        ->assertSee('Alpha Member')
        ->assertDontSee('Beta private task')
        ->assertDontSee('Beta Member')
        ->assertDontSee('beta@example.test');
});

test('direct task URLs cannot read or mutate tasks from another team', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $memberA = User::factory()->create();
    $memberB = User::factory()->create();
    $teamA->users()->attach($memberA->id, ['role' => 'owner']);
    $teamB->users()->attach($memberB->id, ['role' => 'owner']);
    $task = Task::factory()->create([
        'team_id' => $teamB->id,
        'created_by' => $memberB->id,
        'title' => 'Keep this task private',
    ]);
    $this->actingAs($memberA)->withSession(['active_team_id' => $teamA->id]);

    $this->get(route('tasks.edit', $task))->assertNotFound();
    $this->put(route('tasks.update', $task), [
        'title' => 'Unauthorized change',
        'description' => null,
        'status' => 'done',
        'priority' => 'high',
        'due_date' => null,
        'assigned_to' => null,
    ])->assertNotFound();
    $this->patch(route('tasks.status', $task), ['status' => 'done'])->assertNotFound();
    $this->delete(route('tasks.destroy', $task))->assertNotFound();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Keep this task private',
        'team_id' => $teamB->id,
    ]);
});

test('task assignment rejects users outside the active team', function () {
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $memberA = User::factory()->create();
    $memberB = User::factory()->create();
    $teamA->users()->attach($memberA->id, ['role' => 'owner']);
    $teamB->users()->attach($memberB->id, ['role' => 'owner']);
    $this->actingAs($memberA)->withSession(['active_team_id' => $teamA->id]);

    $this->from(route('tasks.index'))->post(route('tasks.store'), [
        'title' => 'Cross-team assignment',
        'status' => 'todo',
        'priority' => 'medium',
        'assigned_to' => $memberB->id,
    ])->assertSessionHasErrors(['assigned_to']);

    $this->assertDatabaseMissing('tasks', ['title' => 'Cross-team assignment']);
});

test('a user can switch between teams they belong to and cannot activate another team', function () {
    $teamA = Team::factory()->create(['name' => 'Alpha']);
    $teamB = Team::factory()->create(['name' => 'Beta']);
    $teamC = Team::factory()->create(['name' => 'Gamma']);
    $user = User::factory()->create();
    $teamA->users()->attach($user->id, ['role' => 'owner']);
    $teamB->users()->attach($user->id, ['role' => 'member']);
    $memberC = User::factory()->create();
    $teamC->users()->attach($memberC->id, ['role' => 'owner']);
    Task::factory()->create([
        'team_id' => $teamA->id,
        'created_by' => $user->id,
        'title' => 'Alpha active work',
    ]);
    Task::factory()->create([
        'team_id' => $teamB->id,
        'created_by' => $user->id,
        'title' => 'Beta active work',
    ]);
    $this->actingAs($user)->withSession(['active_team_id' => $teamA->id]);

    $this->post(route('teams.activate'), ['team_id' => $teamB->id])
        ->assertRedirect(route('tasks.index'));
    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Beta active work')
        ->assertDontSee('Alpha active work');

    $this->from(route('tasks.index'))->post(route('teams.activate'), [
        'team_id' => $teamC->id,
    ])->assertSessionHasErrors(['team_id']);

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Beta active work')
        ->assertDontSee('Alpha active work');
});

test('a user can join an additional team without losing existing membership', function () {
    $existingTeam = Team::factory()->create();
    $joinableTeam = Team::factory()->create([
        'name' => 'Additional Workspace',
        'password' => 'team-password-123',
    ]);
    $user = User::factory()->create();
    $existingTeam->users()->attach($user->id, ['role' => 'owner']);
    $this->actingAs($user)->withSession(['active_team_id' => $existingTeam->id]);

    $this->post(route('teams.join'), [
        'team_id' => $joinableTeam->id,
        'team_password' => 'team-password-123',
    ])->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('team_user', [
        'team_id' => $existingTeam->id,
        'user_id' => $user->id,
    ]);
    $this->assertDatabaseHas('team_user', [
        'team_id' => $joinableTeam->id,
        'user_id' => $user->id,
        'role' => 'member',
    ]);
    expect(session('active_team_id'))->toBe($joinableTeam->id);
});

test('a user with multiple teams must choose an active team before accessing tasks', function () {
    $user = User::factory()->create();
    $teamA = Team::factory()->create();
    $teamB = Team::factory()->create();
    $teamA->users()->attach($user->id, ['role' => 'owner']);
    $teamB->users()->attach($user->id, ['role' => 'member']);
    $this->actingAs($user);

    $this->get(route('tasks.index'))->assertRedirect(route('welcome'));
});
