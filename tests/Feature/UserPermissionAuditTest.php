<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\Library;
use App\Models\LibraryUserRequest;
use App\Models\LocalCopy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $curator;
    private User $userWithMixedRoles;
    private Library $libraryManaged;
    private Library $libraryReader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->curator = User::factory()->create(['role' => 'curator']);

        $this->libraryManaged = Library::create([
            'name' => 'Biblioteca Gestão',
            'code' => 'LIB_MNG',
            'is_active' => true,
        ]);

        $this->libraryReader = Library::create([
            'name' => 'Biblioteca Leitura',
            'code' => 'LIB_RDR',
            'is_active' => true,
        ]);

        // User is manager in libraryManaged, but reader in libraryReader
        // Notice role attribute is 'user' or 'manager'
        $this->userWithMixedRoles = User::factory()->create(['role' => 'user']);
        $this->userWithMixedRoles->libraries()->attach($this->libraryManaged->id, ['role' => 'manager']);
        $this->userWithMixedRoles->libraries()->attach($this->libraryReader->id, ['role' => 'user']);
    }

    public function test_profile_page_shows_edit_button_only_for_managed_libraries(): void
    {
        $response = $this->actingAs($this->userWithMixedRoles)->get(route('profile'));

        $response->assertStatus(200);
        $response->assertSee($this->libraryManaged->name);
        $response->assertSee($this->libraryReader->name);

        // Edit link for managed library should be present
        $response->assertSee(route('libraries.edit', $this->libraryManaged->id));

        // Edit link for reader library should NOT be present
        $response->assertDontSee(route('libraries.edit', $this->libraryReader->id));
    }

    public function test_library_edit_and_update_forbidden_for_reader_library(): void
    {
        // Allowed for managed library
        $response = $this->actingAs($this->userWithMixedRoles)->get(route('libraries.edit', $this->libraryManaged->id));
        $response->assertStatus(200);

        // Forbidden for reader-only library
        $response = $this->actingAs($this->userWithMixedRoles)->get(route('libraries.edit', $this->libraryReader->id));
        $response->assertStatus(403);

        $response = $this->actingAs($this->userWithMixedRoles)->put(route('libraries.update', $this->libraryReader->id), [
            'name' => 'Tentativa de alteração não autorizada',
        ]);
        $response->assertStatus(403);
    }

    public function test_global_manager_attribute_does_not_leak_manager_access_to_reader_library(): void
    {
        // Even if user's global role column is 'manager', their pivot role 'user' on libraryReader strictly governs
        $globalManager = User::factory()->create(['role' => 'manager']);
        $globalManager->libraries()->attach($this->libraryReader->id, ['role' => 'user']);

        $this->assertFalse($globalManager->isManagerOf($this->libraryReader->id));
        $this->assertFalse(in_array($this->libraryReader->id, $globalManager->getManagedLibraryIds()));

        $response = $this->actingAs($globalManager)->get(route('libraries.edit', $this->libraryReader->id));
        $response->assertStatus(403);
    }

    public function test_user_management_scoped_only_to_managed_libraries(): void
    {
        $otherReader = User::factory()->create(['role' => 'user', 'name' => 'Leitor Da Outra']);
        $otherReader->libraries()->attach($this->libraryReader->id, ['role' => 'user']);

        $managedReader = User::factory()->create(['role' => 'user', 'name' => 'Leitor Da Minha']);
        $managedReader->libraries()->attach($this->libraryManaged->id, ['role' => 'user']);

        $response = $this->actingAs($this->userWithMixedRoles)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertSee('Leitor Da Minha');
        $response->assertDontSee('Leitor Da Outra');

        // Cannot edit user from reader-only library
        $response = $this->actingAs($this->userWithMixedRoles)->get(route('users.edit', $otherReader->id));
        $response->assertStatus(403);
    }

    public function test_user_request_moderation_forbidden_for_reader_library(): void
    {
        $requestForReaderLib = LibraryUserRequest::create([
            'user_id' => User::factory()->create()->id,
            'library_id' => $this->libraryReader->id,
            'status' => 'pending',
            'requested_role' => 'user',
        ]);

        $response = $this->actingAs($this->userWithMixedRoles)->post(route('users.requests.approve', $requestForReaderLib->id));
        $response->assertStatus(403);

        $response = $this->actingAs($this->userWithMixedRoles)->post(route('users.requests.reject', $requestForReaderLib->id));
        $response->assertStatus(403);
    }

    public function test_catalog_create_and_store_scoped_to_staff_libraries(): void
    {
        // In catalog.create, user sees managed library but NOT reader library
        $response = $this->actingAs($this->userWithMixedRoles)->get(route('catalog.create'));
        $response->assertStatus(200);
        $response->assertSee($this->libraryManaged->name);
        $response->assertDontSee($this->libraryReader->name);

        // Attempting to store copy in reader-only library is forbidden/aborted
        $response = $this->actingAs($this->userWithMixedRoles)->post(route('catalog.store'), [
            'title' => 'Livro Teste Scope',
            'authors' => ['Autor X'],
            'library_id' => $this->libraryReader->id,
            'copies_count' => 1,
            'status' => 'available',
        ]);
        $response->assertStatus(403);
    }

    public function test_dashboard_loads_without_sql_errors_for_mixed_role_staff_user(): void
    {
        // Tests that dashboard queries execute cleanly without SQL errors (such as missing pivot_in column)
        $response = $this->actingAs($this->userWithMixedRoles)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee($this->libraryManaged->name);
    }
}

