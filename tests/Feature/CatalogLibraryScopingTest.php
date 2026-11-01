<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\Library;
use App\Models\LocalCopy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogLibraryScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_sees_only_available_copies_from_linked_libraries_in_authenticated_catalog(): void
    {
        // 1. Create two libraries
        $libraryA = Library::create(['name' => 'Biblioteca Central', 'code' => 'BC01', 'is_active' => true]);
        $libraryB = Library::create(['name' => 'Biblioteca Externa', 'code' => 'BE02', 'is_active' => true]);

        // 2. Create manager linked ONLY to Library A
        $manager = User::factory()->create(['role' => 'manager']);
        $manager->libraries()->attach($libraryA->id, ['role' => 'manager']);

        // 3. Create a book
        $book = CentralItem::create([
            'title' => 'Livro de Teste de Escopo',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Livro de Teste de Escopo',
                'author' => 'Autor Teste',
                'subjects' => []
            ]
        ]);

        // 4. Copies:
        // Copy 1 in Library A (available) -> SHOULD BE VISIBLE
        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $libraryA->id,
            'call_number' => 'CALL-A1',
            'shelf_location' => 'Estante A1',
            'quantity' => 2,
            'status' => 'available'
        ]);

        // Copy 2 in Library A (unavailable) -> SHOULD NOT BE VISIBLE
        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $libraryA->id,
            'call_number' => 'CALL-A2-UNAVAIL',
            'shelf_location' => 'Estante A2',
            'quantity' => 1,
            'status' => 'unavailable'
        ]);

        // Copy 3 in Library B (available) -> SHOULD NOT BE VISIBLE (No link)
        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $libraryB->id,
            'call_number' => 'CALL-B-EXT',
            'shelf_location' => 'Estante B',
            'quantity' => 5,
            'status' => 'available'
        ]);

        $response = $this->actingAs($manager)->get(route('catalog.show', $book->id));

        $response->assertStatus(200);
        $response->assertSee('Biblioteca Central');
        $response->assertSee('CALL-A1');
        $response->assertDontSee('CALL-A2-UNAVAIL');
        $response->assertDontSee('Biblioteca Externa');
        $response->assertDontSee('CALL-B-EXT');
    }

    public function test_public_library_catalog_scopes_only_to_target_library(): void
    {
        $library = Library::create([
            'name' => 'Biblioteca Pública do Bairro',
            'share_code' => 'PUBBAR',
            'is_public_page' => true,
            'is_active' => true,
        ]);

        $otherLibrary = Library::create([
            'name' => 'Outra Biblioteca',
            'share_code' => 'OUTRA1',
            'is_public_page' => true,
            'is_active' => true,
        ]);

        $book = CentralItem::create([
            'title' => 'História Local',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'História Local',
                'author' => 'Historiador',
                'subjects' => []
            ]
        ]);

        // Copy in target library
        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $library->id,
            'call_number' => 'CALL-PUB-100',
            'quantity' => 3,
            'status' => 'available'
        ]);

        // Copy in other library
        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $otherLibrary->id,
            'call_number' => 'CALL-OUTRA-999',
            'quantity' => 10,
            'status' => 'available'
        ]);

        // 1. Visit public catalog of the library
        $responseLib = $this->get('/PUBBAR');
        $responseLib->assertStatus(200);
        $responseLib->assertSee('Biblioteca Pública do Bairro');
        $responseLib->assertSee('História Local');
        $responseLib->assertSee('Disponível (3)');
        $responseLib->assertDontSee('Disponível (10)');

        // 2. Visit shared book with library parameter (?lib=PUBBAR)
        $responseBook = $this->get('/' . $book->share_code . '?lib=PUBBAR');
        $responseBook->assertStatus(200);
        $responseBook->assertSee('Exemplares Disponíveis — Biblioteca Pública do Bairro');
        $responseBook->assertSee('CALL-PUB-100');
        $responseBook->assertDontSee('CALL-OUTRA-999');
        $responseBook->assertDontSee('Outra Biblioteca');
    }

    public function test_admin_and_curator_can_see_all_copies_in_authenticated_catalog(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $libraryA = Library::create(['name' => 'Biblioteca Alpha', 'is_active' => true]);
        $libraryB = Library::create(['name' => 'Biblioteca Beta', 'is_active' => true]);

        $book = CentralItem::create([
            'title' => 'Atlas Global',
            'item_type' => 'book',
            'metadata' => ['title' => 'Atlas Global', 'subjects' => []]
        ]);

        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $libraryA->id,
            'call_number' => 'CALL-ALPHA',
            'quantity' => 1,
            'status' => 'available'
        ]);

        LocalCopy::create([
            'central_item_id' => $book->id,
            'library_id' => $libraryB->id,
            'call_number' => 'CALL-BETA',
            'quantity' => 2,
            'status' => 'available'
        ]);

        $response = $this->actingAs($admin)->get(route('catalog.show', $book->id));
        $response->assertStatus(200);
        $response->assertSee('Biblioteca Alpha');
        $response->assertSee('CALL-ALPHA');
        $response->assertSee('Biblioteca Beta');
        $response->assertSee('CALL-BETA');
    }
}
