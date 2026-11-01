<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\Library;
use App\Models\LocalCopy;
use App\Models\User;
use App\Services\CallNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFieldsAndCallNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_can_be_created_with_custom_fields_and_call_number_format(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $customFields = [
            [
                'key' => 'cf_tombo_1234',
                'label' => 'Código do Tombo',
                'type' => 'integer',
                'auto_increment' => true,
                'options' => [],
            ],
            [
                'key' => 'cf_colecao_5678',
                'label' => 'Coleção Especial',
                'type' => 'select',
                'auto_increment' => false,
                'options' => ['Raros', 'Geral', 'Infantil'],
            ]
        ];

        $callNumberFormat = [
            ['type' => 'field', 'source' => 'standard', 'key' => 'subjects', 'label' => 'Assuntos', 'length' => 3, 'casing' => 'lower'],
            ['type' => 'static', 'value' => '-'],
            ['type' => 'field', 'source' => 'standard', 'key' => 'publication_year', 'label' => 'Ano', 'length' => null, 'casing' => 'lower'],
            ['type' => 'field', 'source' => 'standard', 'key' => 'author', 'label' => 'Autor', 'length' => 4, 'casing' => 'lower'],
            ['type' => 'static', 'value' => '/'],
            ['type' => 'field', 'source' => 'standard', 'key' => 'title', 'label' => 'Título', 'length' => 2, 'casing' => 'lower'],
        ];

        $response = $this->actingAs($user)->post(route('libraries.store'), [
            'name' => 'Biblioteca Curió Teste',
            'is_active' => 1,
            'custom_fields' => json_encode($customFields),
            'call_number_format' => json_encode($callNumberFormat),
        ]);

        $response->assertRedirect(route('libraries.index'));

        $library = Library::where('name', 'Biblioteca Curió Teste')->first();
        $this->assertNotNull($library);
        $this->assertCount(2, $library->getCustomFields());
        $this->assertEquals('Código do Tombo', $library->getCustomFields()[0]['label']);
        $this->assertTrue($library->getCustomFields()[0]['auto_increment']);
        $this->assertCount(6, $library->getCallNumberFormat());
    }

    public function test_get_custom_fields_json_endpoint_returns_data(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $library = Library::create([
            'name' => 'Biblioteca API Test',
            'is_active' => true,
            'custom_fields' => [
                ['key' => 'cf_setor', 'label' => 'Setor', 'type' => 'string']
            ],
            'call_number_format' => [
                ['type' => 'field', 'source' => 'standard', 'key' => 'author', 'length' => 3, 'casing' => 'upper']
            ]
        ]);
        $manager->libraries()->attach($library->id, ['role' => 'manager']);

        $response = $this->actingAs($manager)->get(route('libraries.custom_fields', $library->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'custom_fields',
            'call_number_format',
            'has_call_number_format',
            'standard_fields',
        ]);
        $response->assertJson([
            'id' => $library->id,
            'has_call_number_format' => true,
        ]);
    }

    public function test_call_number_generation_matches_user_specification(): void
    {
        $library = Library::create([
            'name' => 'Biblioteca Curió Formato',
            'is_active' => true,
            'call_number_format' => [
                ['type' => 'field', 'source' => 'standard', 'key' => 'subjects', 'length' => 3, 'casing' => 'lower'],
                ['type' => 'static', 'value' => '-'],
                ['type' => 'field', 'source' => 'standard', 'key' => 'publication_year', 'length' => null, 'casing' => 'lower'],
                ['type' => 'field', 'source' => 'standard', 'key' => 'author', 'length' => 4, 'casing' => 'lower'],
                ['type' => 'static', 'value' => '/'],
                ['type' => 'field', 'source' => 'standard', 'key' => 'title', 'length' => 2, 'casing' => 'lower'],
            ]
        ]);

        $book = CentralItem::create([
            'title' => 'My Book Title',
            'item_type' => 'book',
            'isbn' => '9788535902778',
            'metadata' => [
                'title' => 'My Book Title',
                'author' => 'Aronofsky',
                'publisher' => 'Companhia',
                'publication_year' => '2007',
                'pages' => 320,
                'subjects' => ['Che Guevara', 'Biografia'],
                'description' => 'Biografia de liderança histórica.',
            ]
        ]);

        $service = new CallNumberService();
        $generated = $service->generate($library, $book);

        // Subjects: 'Che Guevara, Biografia' -> 3 chars: 'che'
        // Static: '-'
        // Year: '2007'
        // Author: 'Aronofsky' -> 4 chars: 'aron'
        // Static: '/'
        // Title: 'My Book Title' -> 2 chars: 'my'
        // Expected: 'che-2007aron/my'
        $this->assertEquals('che-2007aron/my', $generated);
    }

    public function test_auto_increment_custom_field_increments_properly(): void
    {
        $library = Library::create([
            'name' => 'Biblioteca com Tombo',
            'is_active' => true,
            'custom_fields' => [
                [
                    'key' => 'cf_tombo',
                    'label' => 'Tombo',
                    'type' => 'integer',
                    'auto_increment' => true,
                ]
            ],
            'call_number_format' => [
                ['type' => 'static', 'value' => 'TB-'],
                ['type' => 'field', 'source' => 'custom', 'key' => 'cf_tombo', 'length' => null, 'casing' => 'upper'],
            ],
            'call_number_counters' => []
        ]);

        $book1 = CentralItem::create([
            'title' => 'Primeiro Livro',
            'item_type' => 'book',
            'metadata' => ['title' => 'Primeiro Livro']
        ]);

        $book2 = CentralItem::create([
            'title' => 'Segundo Livro',
            'item_type' => 'book',
            'metadata' => ['title' => 'Segundo Livro']
        ]);

        $service = new CallNumberService();

        $call1 = $service->generate($library, $book1, [], true);
        $this->assertEquals('TB-1', $call1);

        $call2 = $service->generate($library, $book2, [], true);
        $this->assertEquals('TB-2', $call2);
    }

    public function test_catalog_store_generates_call_number_and_saves_custom_field_values(): void
    {
        $curator = User::factory()->create(['role' => 'curator']);
        $library = Library::create([
            'name' => 'Biblioteca Central de Testes',
            'is_active' => true,
            'custom_fields' => [
                ['key' => 'cf_setor', 'label' => 'Setor', 'type' => 'string']
            ],
            'call_number_format' => [
                ['type' => 'field', 'source' => 'standard', 'key' => 'author', 'length' => 4, 'casing' => 'lower'],
                ['type' => 'static', 'value' => '.'],
                ['type' => 'field', 'source' => 'standard', 'key' => 'publication_year', 'length' => null, 'casing' => 'lower'],
            ]
        ]);

        $response = $this->actingAs($curator)->post(route('catalog.store'), [
            'title' => 'Dom Casmurro',
            'author' => 'Machado de Assis',
            'publication_year' => '1899',
            'item_type' => 'book',
            'library_id' => $library->id,
            'quantity' => 2,
            'custom_fields_values' => json_encode(['cf_setor' => 'Literatura']),
        ]);

        $response->assertRedirect(route('catalog.index'));

        $item = CentralItem::where('title', 'Dom Casmurro')->first();
        $this->assertNotNull($item);

        $copy = LocalCopy::where('central_item_id', $item->id)->where('library_id', $library->id)->first();
        $this->assertNotNull($copy);

        // Machado -> mach . 1899 -> mach.1899
        $this->assertEquals('mach.1899', $copy->call_number);
        $this->assertEquals(['cf_setor' => 'Literatura'], $copy->custom_fields_values);
    }

    public function test_batch_recalculate_call_numbers_for_library(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $library = Library::create([
            'name' => 'Biblioteca Batch Recalculate',
            'is_active' => true,
            'call_number_format' => [
                ['type' => 'field', 'source' => 'standard', 'key' => 'author', 'length' => 3, 'casing' => 'upper'],
                ['type' => 'static', 'value' => '-'],
                ['type' => 'field', 'source' => 'standard', 'key' => 'publication_year', 'length' => 4, 'casing' => 'lower'],
            ]
        ]);
        $manager->libraries()->attach($library->id, ['role' => 'manager']);

        $book1 = CentralItem::create([
            'title' => 'Obra Antiga 1',
            'item_type' => 'book',
            'metadata' => ['title' => 'Obra Antiga 1', 'author' => 'Clarice Lispector', 'publication_year' => '1977']
        ]);

        $book2 = CentralItem::create([
            'title' => 'Obra Antiga 2',
            'item_type' => 'book',
            'metadata' => ['title' => 'Obra Antiga 2', 'author' => 'Guimarães Rosa', 'publication_year' => '1956']
        ]);

        // Existing copies with old manual call numbers
        $copy1 = LocalCopy::create([
            'central_item_id' => $book1->id,
            'library_id' => $library->id,
            'call_number' => 'OLD-001',
            'quantity' => 1,
            'status' => 'available'
        ]);

        $copy2 = LocalCopy::create([
            'central_item_id' => $book2->id,
            'library_id' => $library->id,
            'call_number' => 'OLD-002',
            'quantity' => 1,
            'status' => 'available'
        ]);

        $response = $this->actingAs($manager)->post(route('libraries.recalculate_call_numbers', $library->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $copy1->refresh();
        $copy2->refresh();

        // Clarice -> CLA - 1977 -> CLA-1977
        $this->assertEquals('CLA-1977', $copy1->call_number);

        // Guimarães -> GUI (ASCII transliteration of Guimarães) - 1956 -> GUI-1956
        $this->assertEquals('GUI-1956', $copy2->call_number);
    }
}
