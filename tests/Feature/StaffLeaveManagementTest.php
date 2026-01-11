<?php

use App\Models\Staff;
use App\Models\User;

test('admin can adjust staff leave balances', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
        'annual_leave_total' => 15,
        'annual_leave_used' => 0,
    ]);

    $response = $this->actingAs($admin)->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => 20,
        'annual_leave_used' => 5,
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 0,
        'leave_notes' => 'Increased annual leave allocation',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Leave balance updated successfully.');

    $staff->refresh();
    expect($staff->annual_leave_total)->toBe(20);
    expect($staff->annual_leave_used)->toBe(5);
    expect($staff->sick_leave_used)->toBe(2);
    expect($staff->leave_notes)->toBe('Increased annual leave allocation');
});

test('superadmin can adjust staff leave balances', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
    ]);

    $response = $this->actingAs($superadmin)->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => 18,
        'annual_leave_used' => 3,
        'sick_leave_total' => 12,
        'sick_leave_used' => 1,
        'emergency_leave_total' => 6,
        'emergency_leave_used' => 0,
        'leave_notes' => 'Special allocation',
    ]);

    $response->assertRedirect();
    $staff->refresh();
    expect($staff->annual_leave_total)->toBe(18);
    expect($staff->annual_leave_used)->toBe(3);
});

test('staff user cannot adjust leave balances', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);
    $anotherStaffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $anotherStaffUser->id,
    ]);

    $response = $this->actingAs($staffUser)->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => 20,
        'annual_leave_used' => 5,
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 0,
    ]);

    $response->assertStatus(403);
});

test('guest cannot adjust leave balances', function () {
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
    ]);

    $response = $this->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => 20,
        'annual_leave_used' => 5,
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 0,
    ]);

    $response->assertRedirect('/login');
});

test('validation prevents used leave from exceeding total', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
    ]);

    $response = $this->actingAs($admin)->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => 15,
        'annual_leave_used' => 20, // Used exceeds total
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 0,
    ]);

    $response->assertSessionHasErrors('annual_leave_used');
});

test('validation prevents negative leave values', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
    ]);

    $response = $this->actingAs($admin)->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => -5, // Negative value
        'annual_leave_used' => 0,
        'sick_leave_total' => 10,
        'sick_leave_used' => 2,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 0,
    ]);

    $response->assertSessionHasErrors('annual_leave_total');
});

test('validation requires all leave fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
    ]);

    $response = $this->actingAs($admin)->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => 15,
        // Missing other required fields
    ]);

    $response->assertSessionHasErrors([
        'annual_leave_used',
        'sick_leave_total',
        'sick_leave_used',
        'emergency_leave_total',
        'emergency_leave_used',
    ]);
});

test('admin can update leave notes separately', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
        'leave_notes' => 'Original notes',
    ]);

    $response = $this->actingAs($admin)->patch("/admin/staff/{$staff->id}/leave-notes", [
        'leave_notes' => 'Updated admin notes',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Leave notes updated successfully.');

    $staff->refresh();
    expect($staff->leave_notes)->toBe('Updated admin notes');
});

test('leave notes can be nullable', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
        'leave_notes' => 'Some notes',
    ]);

    $response = $this->actingAs($admin)->patch("/admin/staff/{$staff->id}/leave-notes", [
        'leave_notes' => null,
    ]);

    $response->assertRedirect();
    $staff->refresh();
    expect($staff->leave_notes)->toBeNull();
});

test('staff remaining leave is calculated correctly after adjustment', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staffUser = User::factory()->create(['role' => 'staff']);
    $staff = Staff::factory()->create([
        'user_id' => $staffUser->id,
        'annual_leave_total' => 15,
        'annual_leave_used' => 0,
    ]);

    $this->actingAs($admin)->post("/admin/staff/{$staff->id}/adjust-leave", [
        'annual_leave_total' => 20,
        'annual_leave_used' => 8,
        'sick_leave_total' => 10,
        'sick_leave_used' => 3,
        'emergency_leave_total' => 5,
        'emergency_leave_used' => 1,
    ]);

    $staff->refresh();
    expect($staff->annual_leave_remaining)->toBe(12); // 20 - 8
    expect($staff->sick_leave_remaining)->toBe(7);     // 10 - 3
    expect($staff->emergency_leave_remaining)->toBe(4); // 5 - 1
    expect($staff->total_leave_remaining)->toBe(23);   // 12 + 7 + 4
});
