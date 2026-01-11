<?php

use App\Models\Staff;
use App\Models\User;

test('annual_leave_remaining accessor calculates correctly', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
        'annual_leave_total' => 15,
        'annual_leave_used' => 5,
    ]);

    expect($staff->annual_leave_remaining)->toBe(10);
});

test('annual_leave_remaining accessor returns zero when used exceeds total', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
        'annual_leave_total' => 15,
        'annual_leave_used' => 20,
    ]);

    expect($staff->annual_leave_remaining)->toBe(0);
});

test('sick_leave_remaining accessor calculates correctly', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
        'sick_leave_total' => 10,
        'sick_leave_used' => 3,
    ]);

    expect($staff->sick_leave_remaining)->toBe(7);
});

test('sick_leave_remaining accessor returns zero when used exceeds total', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
        'sick_leave_total' => 10,
        'sick_leave_used' => 15,
    ]);

    expect($staff->sick_leave_remaining)->toBe(0);
});

test('emergency_leave_remaining accessor calculates correctly', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 2,
    ]);

    expect($staff->emergency_leave_remaining)->toBe(3);
});

test('emergency_leave_remaining accessor returns zero when used exceeds total', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 10,
    ]);

    expect($staff->emergency_leave_remaining)->toBe(0);
});

test('total_leave_remaining accessor calculates sum of all remaining leaves', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
        'annual_leave_total' => 15,
        'annual_leave_used' => 5,
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 1,
    ]);

    // (15-5) + (10-2) + (5-1) = 10 + 8 + 4 = 22
    expect($staff->total_leave_remaining)->toBe(22);
});

test('staff can be created with default leave values', function () {
    $user = User::factory()->create();
    $staff = Staff::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($staff->annual_leave_total)->toBe(15);
    expect($staff->annual_leave_used)->toBe(0);
    expect($staff->sick_leave_total)->toBe(10);
    expect($staff->sick_leave_used)->toBe(0);
    expect($staff->emergency_leave_total)->toBe(5);
    expect($staff->emergency_leave_used)->toBe(0);
});
