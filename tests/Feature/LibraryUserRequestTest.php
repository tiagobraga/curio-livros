<?php

namespace Tests\Feature;

use App\Models\Library;
use App\Models\User;
use App\Models\LibraryUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LibraryUserRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_reader_registration_creates_pending_request(): void
    {
        $library = Library::create([
            'name' => 'Biblioteca Central',
            'code' => 'BC01',
            'is_active' => true,
        ]);

        $response = $this->post(route('register'), [
            'name' => 'Leitor de Teste',
            'email' => 'leitortest@gmail.com',
            'password' => 'senha1234',
            'password_confirmation' => 'senha1234',
            'library_id' => $library->id,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'leitortest@gmail.com']);
        $user = User::where('email', 'leitortest@gmail.com')->first();
        
        // Assert no direct attachment yet
        $this->assertEquals(0, $user->libraries()->count());

        // Assert pending request created
        $this->assertDatabaseHas('library_user_requests', [
            'user_id' => $user->id,
            'library_id' => $library->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_submit_library_link_request_via_profile(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $library = Library::create([
            'name' => 'Biblioteca Nova',
            'code' => 'BN02',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'request_library_ids' => [$library->id],
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('library_user_requests', [
            'user_id' => $user->id,
            'library_id' => $library->id,
            'status' => 'pending',
        ]);
    }

    public function test_manager_can_approve_attendant_and_reader_requests_for_own_library(): void
    {
        Mail::fake();

        $library = Library::create([
            'name' => 'Biblioteca A',
            'code' => 'LA01',
            'is_active' => true,
        ]);

        $manager = User::factory()->create(['role' => 'manager']);
        $manager->libraries()->attach($library->id);

        $reader = User::factory()->create(['role' => 'user']);
        $request = LibraryUserRequest::create([
            'user_id' => $reader->id,
            'library_id' => $library->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($manager)->post(route('users.requests.approve', $request->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('library_user_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
        $this->assertTrue($reader->libraries()->where('libraries.id', $library->id)->exists());
    }

    public function test_manager_cannot_approve_request_for_other_library_or_manager_requests(): void
    {
        $libraryA = Library::create(['name' => 'Biblioteca A', 'code' => 'LA01', 'is_active' => true]);
        $libraryB = Library::create(['name' => 'Biblioteca B', 'code' => 'LB02', 'is_active' => true]);

        $manager = User::factory()->create(['role' => 'manager']);
        $manager->libraries()->attach($libraryA->id);

        $otherReader = User::factory()->create(['role' => 'user']);
        $requestB = LibraryUserRequest::create([
            'user_id' => $otherReader->id,
            'library_id' => $libraryB->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($manager)->post(route('users.requests.approve', $requestB->id));
        $response->assertStatus(403);
    }

    public function test_profile_page_loads_with_libraries_info(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $library = Library::create(['name' => 'Biblioteca Nova', 'code' => 'BN02', 'is_active' => true]);

        $response = $this->actingAs($user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertViewHas('allLibraries');
        $response->assertViewHas('linkedIds');
        $response->assertViewHas('pendingIds');
    }

    public function test_user_can_request_library_link_via_search_post_route(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $library = Library::create([
            'name' => 'Biblioteca Nova 2',
            'code' => 'BN03',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('profile.request-library'), [
            'library_id' => $library->id,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('library_user_requests', [
            'user_id' => $user->id,
            'library_id' => $library->id,
            'status' => 'pending',
            'role' => 'user',
        ]);
    }

    public function test_user_can_update_profile_password_via_dedicated_route(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->put(route('profile.update-password'), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(\Hash::check('newpassword123', $user->fresh()->password));
    }
}
