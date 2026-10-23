<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guests are redirected to sign in before viewing onboarding', function () {
    $this->get(route('welcome'))->assertRedirect(route('login'));
});

test('an authenticated user can create a team with a hashed password and become its owner', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('teams.store'), [
        'name' => 'Design Studio',
        'password' => 'team-password-123',
    ])->assertRedirect(route('tasks.index'));

    $team = Team::query()->where('name', 'Design Studio')->firstOrFail();

    expect(Hash::check('team-password-123', $team->password))->toBeTrue()
        ->and($team->users()->whereKey($user->id)->firstOrFail()->pivot->role)->toBe('owner');

    $this->get(route('tasks.index'))->assertOk()->assertSee('Work in motion');
});

test('an authenticated user can browse and join a team with its password', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'name' => 'Product Studio',
        'password' => 'team-password-123',
    ]);
    $this->actingAs($user);

    $this->get(route('welcome'))
        ->assertOk()
        ->assertSee('Product Studio')
        ->assertDontSee($team->password);

    $this->post(route('teams.join'), [
        'team_id' => $team->id,
        'team_password' => 'team-password-123',
    ])->assertRedirect(route('tasks.index'));

    expect($team->users()->whereKey($user->id)->firstOrFail()->pivot->role)->toBe('member');
});

test('an incorrect team password is rejected without creating membership', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $this->actingAs($user);

    $this->from(route('welcome'))->post(route('teams.join'), [
        'team_id' => $team->id,
        'team_password' => 'incorrect-password',
    ])->assertSessionHasErrors([
        'team_password' => 'The selected team password is incorrect.',
    ]);

    $this->assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);
});

test('team creation rejects duplicate names', function () {
    $user = User::factory()->create();
    Team::factory()->create(['name' => 'Existing Team']);
    $this->actingAs($user);

    $this->from(route('welcome'))->post(route('teams.store'), [
        'name' => 'Existing Team',
        'password' => 'team-password-123',
    ])->assertSessionHasErrors([
        'name' => 'The name has already been taken.',
    ]);
});

test('team creation rejects passwords that do not meet the strength requirement', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->from(route('welcome'))->post(route('teams.store'), [
        'name' => 'Weak Password Team',
        'password' => 'short',
    ])->assertSessionHasErrors(['password']);

    $this->assertDatabaseMissing('teams', ['name' => 'Weak Password Team']);
});

test('users without a team are redirected to onboarding and existing members cannot reopen it', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('tasks.index'))->assertRedirect(route('welcome'));
    $this->get(route('welcome'))->assertOk();

    $team = Team::factory()->create();
    $team->users()->attach($user->id, ['role' => 'member']);

    $this->get(route('welcome'))->assertRedirect(route('tasks.index'));

    $this->post(route('teams.store'), [
        'name' => 'Another Team',
        'password' => 'team-password-123',
    ])->assertRedirect(route('tasks.index'));

    $this->assertDatabaseMissing('teams', ['name' => 'Another Team']);
});
