<?php

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login before viewing the team board', function () {
    $this->get('/tasks')->assertRedirect(route('login'));
});

test('a visitor registers and must choose a team before reaching the protected board', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Alex Morgan',
        'email' => 'alex@example.test',
        'password' => 'securepassword123',
        'password_confirmation' => 'securepassword123',
    ]);

    $response->assertRedirect(route('welcome'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'alex@example.test']);
    $this->get(route('tasks.index'))->assertRedirect(route('welcome'));

    $this->post(route('teams.store'), [
        'name' => 'Alex Team',
        'password' => 'team-password-123',
    ])->assertRedirect(route('tasks.index'));

    $this->get(route('tasks.index'))->assertOk()->assertSee('Work in motion');
});

test('a registered user can sign in and sign out', function () {
    $user = User::factory()->create(['password' => 'securepassword123']);
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['role' => 'member']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'securepassword123',
    ])->assertRedirect(route('tasks.index'));

    $this->assertAuthenticatedAs($user);
    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a team member can create update and delete a task', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach([$creator->id, $assignee->id], ['role' => 'member']);
    $this->actingAs($creator);

    $this->post(route('tasks.store'), [
        'title' => 'Prepare launch notes',
        'description' => 'Collect the release highlights.',
        'status' => 'todo',
        'priority' => 'high',
        'due_date' => '2026-11-01',
        'assigned_to' => $assignee->id,
    ])->assertRedirect(route('tasks.index'));

    $task = Task::query()->firstOrFail();
    expect($task->created_by)->toBe($creator->id)
        ->and($task->team_id)->toBe($team->id)
        ->and($task->assigned_to)->toBe($assignee->id);

    $this->patch(route('tasks.status', $task), ['status' => 'in_progress'])
        ->assertRedirect(route('tasks.index'));
    expect($task->fresh()->status)->toBe('in_progress');

    $this->put(route('tasks.update', $task), [
        'title' => 'Finalize launch notes',
        'description' => 'Approved release highlights.',
        'status' => 'done',
        'priority' => 'medium',
        'due_date' => null,
        'assigned_to' => null,
    ])->assertRedirect(route('tasks.index'));
    expect($task->fresh()->title)->toBe('Finalize launch notes');

    $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('task input rejects invalid statuses and unknown assignees', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['role' => 'member']);
    $this->actingAs($user);

    $this->from(route('tasks.index'))->post(route('tasks.store'), [
        'title' => 'Invalid task',
        'status' => 'blocked',
        'priority' => 'urgent',
        'assigned_to' => 99999,
    ])->assertSessionHasErrors(['status', 'priority', 'assigned_to']);

    expect(Task::query()->count())->toBe(0);
});

test('registration requires matching strong passwords and a unique email', function () {
    $existingUser = User::factory()->create();

    $this->post(route('register.store'), [
        'name' => 'Alex Morgan',
        'email' => $existingUser->email,
        'password' => 'weak',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['email', 'password']);
});
