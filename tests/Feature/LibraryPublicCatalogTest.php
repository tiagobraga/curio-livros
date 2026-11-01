<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Library;
use App\Models\CentralItem;
use App\Models\LocalCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryPublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_combined_registration_page(): void
    {
        $response = $this->get(route('register-library'));
        $response->assertStatus(200);
        $response->assertSee(__('Crie sua Biblioteca'));
    }

    public function test_guest_can_register_user_and_create_library(): void
    {
        $response = $this->post(route('register-library.post'), [
            'name' => 'John Manager',
            'email' => 'john@manager.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'library_name' => 'Biblioteca do John',
            'description' => 'Minha biblioteca experimental',
            'address' => 'Rua das Palmeiras, 100',
            'library_type' => 'Pessoal',
            'is_public_page' => '1',
            'share_code' => 'JOHNBIB',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'john@manager.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('manager', $user->role);

        $library = Library::where('name', 'Biblioteca do John')->first();
        $this->assertNotNull($library);
        $this->assertEquals('JOHNBIB', $library->share_code);
        $this->assertEquals('Minha biblioteca experimental', $library->description);
        $this->assertEquals('Pessoal', $library->library_type);
        $this->assertTrue((bool)$library->is_public_page);

        // Assert relationship
        $this->assertTrue($user->libraries()->where('libraries.id', $library->id)->exists());
        $this->assertEquals('manager', $user->libraries()->first()->pivot->role);
    }

    public function test_public_catalog_can_be_viewed_via_short_url(): void
    {
        $library = Library::create([
            'name' => 'Public Library Test',
            'is_public_page' => true,
            'share_code' => 'PUB001',
            'is_active' => true,
            'description' => 'A wonderful public test library',
            'address' => 'Public Ave, 10',
            'library_type' => 'Pública',
        ]);

        $book = CentralItem::create([
            'title' => 'Book of Wisdom',
            'isbn' => '9781234567890',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Book of Wisdom',
                'author' => 'Wise Sage',
                'subjects' => ['Philosophy', 'Wisdom'],
            ]
        ]);

        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'quantity' => 5,
            'status' => 'available',
        ]);

        $response = $this->get('/PUB001');
        $response->assertStatus(200);
        $response->assertSee('Public Library Test');
        $response->assertSee('A wonderful public test library');
        $response->assertSee('Book of Wisdom');
        $response->assertSee('Wise Sage');
    }

    public function test_private_catalog_returns_404_via_short_url(): void
    {
        $library = Library::create([
            'name' => 'Private Library Test',
            'is_public_page' => false,
            'share_code' => 'PRI002',
            'is_active' => true,
        ]);

        $response = $this->get('/PRI002');
        $response->assertStatus(404);
    }
}
