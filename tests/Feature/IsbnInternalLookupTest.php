<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Library;
use App\Models\CentralItem;
use App\Models\LocalCopy;
use App\Models\SuggestedEdit;

class IsbnInternalLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_returns_internal_catalog_item_if_exists()
    {
        $user = User::factory()->create(['role' => 'attendant']);
        $library = Library::create(['name' => 'Biblioteca Teste', 'is_active' => true]);
        $user->libraries()->attach($library->id);

        $centralItem = CentralItem::create([
            'title' => 'Dom Casmurro',
            'isbn' => '9788535902778',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'publisher' => 'Companhia das Letras',
                'publication_year' => '2019',
                'pages' => 256,
                'isbn' => '9788535902778',
                'description' => 'Romance clássico.',
                'subjects' => ['Literatura Brasileira', 'Romance'],
            ]
        ]);

        $response = $this->actingAs($user)->get('/catalog/lookup?isbn=9788535902778');

        $response->assertStatus(200);
        $response->assertJson([
            'source' => 'internal',
            'title' => 'Dom Casmurro',
            'author' => 'Machado de Assis',
            'publisher' => 'Companhia das Letras',
            'isbn' => '9788535902778',
            'central_item_id' => $centralItem->id,
        ]);
    }

    public function test_store_reuses_existing_central_item_without_creating_suggested_edit_if_no_metadata_changed()
    {
        $user = User::factory()->create(['role' => 'attendant']);
        $library = Library::create(['name' => 'Biblioteca Teste', 'is_active' => true]);
        $user->libraries()->attach($library->id);

        $centralItem = CentralItem::create([
            'title' => 'Dom Casmurro',
            'isbn' => '9788535902778',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'publisher' => 'Companhia das Letras',
                'publication_year' => '2019',
                'pages' => 256,
                'isbn' => '9788535902778',
                'description' => 'Romance clássico.',
                'subjects' => ['Literatura Brasileira'],
            ]
        ]);

        $response = $this->actingAs($user)->post('/catalog', [
            'title' => 'Dom Casmurro',
            'author' => 'Machado de Assis',
            'publisher' => 'Companhia das Letras',
            'publication_year' => '2019',
            'pages' => 256,
            'isbn' => '9788535902778',
            'description' => 'Romance clássico.',
            'subjects' => 'Literatura Brasileira',
            'item_type' => 'book',
            'library_id' => $library->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect('/catalog');
        $this->assertDatabaseHas('local_copies', [
            'central_item_id' => $centralItem->id,
            'library_id' => $library->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(0, SuggestedEdit::count());
    }

    public function test_store_creates_suggested_edit_when_attendant_modifies_existing_internal_book()
    {
        $user = User::factory()->create(['role' => 'attendant']);
        $library = Library::create(['name' => 'Biblioteca Teste', 'is_active' => true]);
        $user->libraries()->attach($library->id);

        $centralItem = CentralItem::create([
            'title' => 'Dom Casmurro',
            'isbn' => '9788535902778',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'publisher' => 'Companhia das Letras',
                'publication_year' => '2019',
                'pages' => 256,
                'isbn' => '9788535902778',
                'description' => 'Romance clássico.',
                'subjects' => ['Literatura Brasileira'],
            ]
        ]);

        $response = $this->actingAs($user)->post('/catalog', [
            'title' => 'Dom Casmurro (Edição Comentada)',
            'author' => 'Machado de Assis',
            'publisher' => 'Companhia das Letras',
            'publication_year' => '2021',
            'pages' => 300,
            'isbn' => '9788535902778',
            'description' => 'Romance clássico com notas explicativas.',
            'subjects' => 'Literatura Brasileira, Análise',
            'item_type' => 'book',
            'library_id' => $library->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/catalog');
        
        $this->assertDatabaseHas('local_copies', [
            'central_item_id' => $centralItem->id,
            'library_id' => $library->id,
        ]);

        $this->assertDatabaseHas('suggested_edits', [
            'central_item_id' => $centralItem->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $suggestion = SuggestedEdit::first();
        $this->assertEquals('Dom Casmurro (Edição Comentada)', $suggestion->metadata['title']);
    }

    public function test_store_updates_directly_when_curator_modifies_existing_internal_book()
    {
        $curator = User::factory()->create(['role' => 'curator']);
        $library = Library::create(['name' => 'Biblioteca Teste', 'is_active' => true]);

        $centralItem = CentralItem::create([
            'title' => 'Dom Casmurro',
            'isbn' => '9788535902778',
            'item_type' => 'book',
            'metadata' => [
                'title' => 'Dom Casmurro',
                'author' => 'Machado de Assis',
                'publisher' => 'Companhia das Letras',
                'publication_year' => '2019',
                'pages' => 256,
                'isbn' => '9788535902778',
            ]
        ]);

        $response = $this->actingAs($curator)->post('/catalog', [
            'title' => 'Dom Casmurro (Edição Definitiva Curada)',
            'author' => 'Machado de Assis',
            'publisher' => 'Companhia das Letras',
            'publication_year' => '2023',
            'pages' => 320,
            'isbn' => '9788535902778',
            'item_type' => 'book',
            'library_id' => $library->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/catalog');

        $this->assertEquals('Dom Casmurro (Edição Definitiva Curada)', $centralItem->fresh()->title);
        $this->assertEquals(0, SuggestedEdit::count());
    }
}
