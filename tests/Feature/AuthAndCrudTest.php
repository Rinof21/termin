<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthAndCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (User::where('email', 'admin@admin.com')->doesntExist()) {
            $this->seed(RolePermissionSeeder::class);
        }
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Termin Portal');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_dashboard_is_accessible_when_authenticated(): void
    {
        $user = User::where('email', 'admin@admin.com')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Termin S1 Kedokteran');
        $response->assertSee('id="calendar"', false);
    }

    public function test_user_crud_works(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // Index
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        // Store
        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Budi Santoso',
            'email' => 'budi@termin.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['Administrator'],
        ]);
        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'budi@termin.test']);

        $budi = User::where('email', 'budi@termin.test')->first();
        $this->assertTrue($budi->hasRole('Administrator'));

        // Update
        $response = $this->actingAs($admin)->put("/admin/users/{$budi->id}", [
            'name' => 'Budi Updated',
            'email' => 'budi@termin.test',
            'roles' => ['Pengguna Biasa'],
        ]);
        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['name' => 'Budi Updated']);
        $this->assertTrue($budi->fresh()->hasRole('Pengguna Biasa'));

        // Destroy
        $response = $this->actingAs($admin)->delete("/admin/users/{$budi->id}");
        $response->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $budi->id]);
    }

    public function test_role_crud_works(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        // Index
        $response = $this->actingAs($admin)->get('/admin/roles');
        $response->assertStatus(200);

        // Store
        $response = $this->actingAs($admin)->post('/admin/roles', [
            'name' => 'Editor Konten',
            'permissions' => ['lihat-pengguna'],
        ]);
        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseHas('roles', ['name' => 'Editor Konten']);

        $role = Role::where('name', 'Editor Konten')->first();
        $this->assertTrue($role->hasPermissionTo('lihat-pengguna'));

        // Destroy
        $response = $this->actingAs($admin)->delete("/admin/roles/{$role->id}");
        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}

