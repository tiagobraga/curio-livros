<?php

namespace Tests\Feature;

use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $curator;
    private User $manager;
    private User $attendant;
    private User $reader;
    private Library $libraryA;
    private Library $libraryB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->libraryA = Library::create(['name' => 'Biblioteca A', 'code' => 'L01', 'is_active' => true]);
        $this->libraryB = Library::create(['name' => 'Biblioteca B', 'code' => 'L02', 'is_active' => true]);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->curator = User::factory()->create(['role' => 'curator']);
        
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->manager->libraries()->attach($this->libraryA->id, ['role' => 'manager']);

        $this->attendant = User::factory()->create(['role' => 'attendant']);
        $this->attendant->libraries()->attach($this->libraryA->id, ['role' => 'attendant']);

        $this->reader = User::factory()->create(['role' => 'user']);
        $this->reader->libraries()->attach($this->libraryA->id, ['role' => 'user']);
    }

    public function test_manager_scope_limits(): void
    {
        // Manager can see reader and attendant from same library
        $response = $this->actingAs($this->manager)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertSee($this->reader->name);
        $response->assertSee($this->attendant->name);
        $response->assertDontSee($this->curator->name);

        // Manager cannot edit curator
        $response = $this->actingAs($this->manager)->get(route('users.edit', $this->curator->id));
        $response->assertStatus(403);

        $response = $this->actingAs($this->manager)->put(route('users.update', $this->curator->id), [
            'name' => 'New Name',
            'email' => $this->curator->email,
            'role' => 'curator',
            'library_ids' => [$this->libraryA->id],
        ]);
        $response->assertStatus(403);

        // Manager cannot edit users of another library
        $otherReader = User::factory()->create(['role' => 'user']);
        $otherReader->libraries()->attach($this->libraryB->id);

        $response = $this->actingAs($this->manager)->get(route('users.edit', $otherReader->id));
        $response->assertStatus(403);

        $response = $this->actingAs($this->manager)->put(route('users.update', $otherReader->id), [
            'name' => 'New Name',
            'email' => $otherReader->email,
            'role' => 'user',
            'library_ids' => [$this->libraryB->id],
        ]);
        $response->assertStatus(403);
    }

    public function test_attendant_scope_limits(): void
    {
        // Attendant can see reader from same library, but not manager
        $response = $this->actingAs($this->attendant)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertSee($this->reader->name);
        $response->assertDontSee($this->manager->name);

        // Attendant cannot edit manager
        $response = $this->actingAs($this->attendant)->get(route('users.edit', $this->manager->id));
        $response->assertStatus(403);

        // Attendant cannot edit users of another library
        $otherReader = User::factory()->create(['role' => 'user']);
        $otherReader->libraries()->attach($this->libraryB->id);

        $response = $this->actingAs($this->attendant)->get(route('users.edit', $otherReader->id));
        $response->assertStatus(403);
    }

    public function test_curator_permissions(): void
    {
        // Curator can see all users
        $response = $this->actingAs($this->curator)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertSee($this->reader->name);
        $response->assertSee($this->manager->name);
        $response->assertSee($this->admin->name);

        // Curator can access edit view of user
        $response = $this->actingAs($this->curator)->get(route('users.edit', $this->reader->id));
        $response->assertStatus(200);

        // Curator CANNOT update users
        $response = $this->actingAs($this->curator)->put(route('users.update', $this->reader->id), [
            'name' => 'New Name',
            'email' => $this->reader->email,
            'role' => 'user',
            'library_ids' => [$this->libraryA->id],
        ]);
        $response->assertStatus(403);

        // Curator CANNOT create users
        $response = $this->actingAs($this->curator)->get(route('users.create'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->curator)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'newuser@teste.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'library_ids' => [$this->libraryA->id],
        ]);
        $response->assertStatus(403);
    }

    public function test_user_can_edit_own_profile(): void
    {
        // User can access own profile settings
        $response = $this->actingAs($this->reader)->get(route('profile.edit'));
        $response->assertStatus(200);

        // User can update own name and email
        $response = $this->actingAs($this->reader)->put(route('profile.update'), [
            'name' => 'My Brand New Name',
            'email' => 'mybrandnewemail@teste.com',
        ]);
        $response->assertRedirect();
        
        $this->reader->refresh();
        $this->assertEquals('My Brand New Name', $this->reader->name);
        $this->assertEquals('mybrandnewemail@teste.com', $this->reader->email);
    }

    public function test_user_creation_roles_matrix(): void
    {
        // 1. Reader cannot access user creation
        $response = $this->actingAs($this->reader)->get(route('users.create'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->reader)->post(route('users.store'), [
            'name' => 'Test Reader Creator',
            'email' => 'rc@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'library_roles' => [$this->libraryA->id => 'user']
        ]);
        $response->assertStatus(403);

        // 2. Attendant can access user creation and create reader in their linked library
        $response = $this->actingAs($this->attendant)->get(route('users.create'));
        $response->assertStatus(200);

        // Attendant tries to create a manager (should fail)
        $response = $this->actingAs($this->attendant)->post(route('users.store'), [
            'name' => 'Fail Manager',
            'email' => 'failmanager@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'library_roles' => [$this->libraryA->id => 'manager']
        ]);
        $response->assertStatus(403);

        // Attendant tries to create a user in a library they are not linked to (should fail)
        $response = $this->actingAs($this->attendant)->post(route('users.store'), [
            'name' => 'Fail Library',
            'email' => 'faillibrary@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'library_roles' => [$this->libraryB->id => 'user']
        ]);
        $response->assertStatus(403);

        // Attendant creates reader in linked library (should succeed)
        $response = $this->actingAs($this->attendant)->post(route('users.store'), [
            'name' => 'Success Reader',
            'email' => 'successreader@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'library_roles' => [$this->libraryA->id => 'user']
        ]);
        $response->assertRedirect(route('users.index'));
        $this->assertTrue(User::where('email', 'successreader@test.com')->exists());

        // 3. Manager can create readers and attendants in their linked library
        // Manager tries to create a curator (should fail)
        $response = $this->actingAs($this->manager)->post(route('users.store'), [
            'name' => 'Fail Curator',
            'email' => 'failcurator@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'global_role' => 'curator',
            'library_roles' => [$this->libraryA->id => 'user']
        ]);
        $response->assertStatus(403);

        // Manager creates attendant in linked library (should succeed)
        $response = $this->actingAs($this->manager)->post(route('users.store'), [
            'name' => 'Success Attendant',
            'email' => 'successattendant@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'library_roles' => [$this->libraryA->id => 'attendant']
        ]);
        $response->assertRedirect(route('users.index'));
        $this->assertTrue(User::where('email', 'successattendant@test.com')->exists());
    }

    public function test_can_edit_user_hierarchy_and_ui_link_visibility(): void
    {
        $anotherManager = User::factory()->create(['role' => 'manager', 'name' => 'Manager Two']);
        $anotherManager->libraries()->attach($this->libraryA->id, ['role' => 'manager']);

        // 1. Manager Abadia cannot edit another Manager or Admin
        $this->assertFalse($this->manager->canEditUser($anotherManager));
        $this->assertFalse($this->manager->canEditUser($this->admin));
        $this->assertFalse($this->manager->canEditUser($this->curator));

        // 2. Manager CAN edit reader and attendant sharing the library
        $this->assertTrue($this->manager->canEditUser($this->reader));
        $this->assertTrue($this->manager->canEditUser($this->attendant));

        // 3. UI Edit link visibility check in users.index
        $response = $this->actingAs($this->manager)->get(route('users.index'));
        $response->assertStatus(200);

        // Edit link for reader should be visible
        $response->assertSee(route('users.edit', $this->reader->id));

        // Edit link for another manager should NOT be visible
        $response->assertDontSee(route('users.edit', $anotherManager->id));
    }
}

