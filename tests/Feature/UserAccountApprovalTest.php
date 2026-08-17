<?php

use App\Constants\UserRole;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a mobile registration creates an inactive user', function () {
    $response = $this->postJson('/api/register', [
        'phone' => '0901234567',
        'username' => 'pending-user',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('users', [
        'username' => 'pending-user',
        'role' => UserRole::USER->value,
        'is_active' => false,
    ]);
});

test('an inactive user cannot log in', function () {
    User::factory()->create([
        'username' => 'pending-user',
        'phone' => '0901234567',
        'role' => UserRole::USER->value,
        'is_active' => false,
        'password' => 'Password123',
    ]);

    $this->postJson('/api/login', [
        'username' => 'pending-user',
        'password' => 'Password123',
    ])
        ->assertForbidden()
        ->assertJson([
            'status' => false,
            'message' => 'Tài khoản đang chờ hệ thống kích hoạt.',
        ]);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('an admin can approve an account and the user can then log in', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'is_active' => true,
    ]);
    $user = User::factory()->create([
        'username' => 'approved-user',
        'phone' => '0901234567',
        'role' => UserRole::USER->value,
        'is_active' => false,
        'password' => 'Password123',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->callTableAction('approve', $user);

    expect($user->refresh()->is_active)->toBeTrue();

    $this->postJson('/api/login', [
        'username' => 'approved-user',
        'password' => 'Password123',
    ])
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonStructure(['data' => ['token']]);
});

test('an admin account cannot be deleted', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'is_active' => true,
    ]);

    expect(UserResource::canDelete($admin))->toBeFalse()
        ->and($admin->delete())->toBeFalse();

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('a regular user can still be deleted', function () {
    $user = User::factory()->create([
        'role' => UserRole::USER->value,
        'is_active' => true,
    ]);

    expect($user->delete())->toBeTrue();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
