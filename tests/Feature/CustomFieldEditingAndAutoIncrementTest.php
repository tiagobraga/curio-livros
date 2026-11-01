<?php

namespace Tests\Feature;

use App\Models\CentralItem;
use App\Models\Library;
use App\Models\LocalCopy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFieldEditingAndAutoIncrementTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private Library $library;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->library = Library::create([
            'name' => 'Biblioteca Municipal',
            'code' => 'BM01',
            'is_active' => true,
            'custom_fields' => [
                [
                    'key' => 'cf_tombo_1',
                    'label' => 'Número de Tombo',
                    'type' => 'integer',
                    'auto_increment' => true,
                    'options' => [],
                ],
                [
                    'key' => 'cf_categoria_2',
                    'label' => 'Categoria Local',
                    'type' => 'select',
                    'auto_increment' => false,
                    'options' => ['Ficção', 'Técnico'],
                ]
            ],
            'call_number_format' => [
                ['type' => 'field', 'source' => 'custom', 'key' => 'cf_tombo_1', 'label' => 'Número de Tombo', 'length' => null, 'casing' => 'upper'],
                ['type' => 'static', 'value' => '-'],
                ['type' => 'field', 'source' => 'standard', 'key' => 'title', 'label' => 'Título', 'length' => 4, 'casing' => 'upper'],
            ]
        ]);

        $this->manager->libraries()->attach($this->library->id, ['role' => 'manager']);
    }

    public function test_custom_fields_can_be_edited_preserving_key_and_updating_attributes(): void
    {
        // Edit custom field cf_tombo_1 (change label) and cf_categoria_2 (add option)
        $updatedFields = [
            [
                'key' => 'cf_tombo_1', // Preserved key
                'label' => 'Tombo Patrimonial',
                'type' => 'integer',
                'auto_increment' => true,
                'options' => [],
            ],
            [
                'key' => 'cf_categoria_2', // Preserved key
                'label' => 'Setor/Categoria',
                'type' => 'select',
                'auto_increment' => false,
                'options' => ['Ficção', 'Técnico', 'Infantojuvenil'],
            ]
        ];

        $response = $this->actingAs($this->manager)->put(route('libraries.update', $this->library->id), [
            'name' => $this->library->name,
            'is_active' => 1,
            'custom_fields' => json_encode($updatedFields),
            'call_number_format' => json_encode($this->library->getCallNumberFormat()),
        ]);

        $response->assertRedirect(route('libraries.index'));

        $this->library->refresh();
        $fields = $this->library->getCustomFields();

        $this->assertCount(2, $fields);
        $this->assertEquals('cf_tombo_1', $fields[0]['key']);
        $this->assertEquals('Tombo Patrimonial', $fields[0]['label']);
        $this->assertTrue($fields[0]['auto_increment']);

        $this->assertEquals('cf_categoria_2', $fields[1]['key']);
        $this->assertEquals('Setor/Categoria', $fields[1]['label']);
        $this->assertEquals(['Ficção', 'Técnico', 'Infantojuvenil'], $fields[1]['options']);
    }

    public function test_lowest_available_auto_increment_value_gap_filling(): void
    {
        // 1. When empty, lowest is 1
        $this->assertEquals(1, $this->library->getLowestAvailableAutoIncrementValue('cf_tombo_1'));

        $item = CentralItem::create([
            'title' => 'Livro Teste',
            'authors' => ['Autor A'],
            'status' => 'approved',
        ]);

        // 2. Create copies with tombo 1, 2, 4 (leaving gap at 3)
        $copy1 = LocalCopy::create([
            'central_item_id' => $item->id,
            'library_id' => $this->library->id,
            'status' => 'available',
            'custom_fields_values' => ['cf_tombo_1' => 1],
        ]);

        $copy2 = LocalCopy::create([
            'central_item_id' => $item->id,
            'library_id' => $this->library->id,
            'status' => 'available',
            'custom_fields_values' => ['cf_tombo_1' => 2],
        ]);

        $copy4 = LocalCopy::create([
            'central_item_id' => $item->id,
            'library_id' => $this->library->id,
            'status' => 'available',
            'custom_fields_values' => ['cf_tombo_1' => 4],
        ]);

        // Gap filling: 3 is the lowest available!
        $this->assertEquals(3, $this->library->getLowestAvailableAutoIncrementValue('cf_tombo_1'));

        // 3. If ignoring copy2 (which has value 2), lowest available is 2
        $this->assertEquals(2, $this->library->getLowestAvailableAutoIncrementValue('cf_tombo_1', $copy2->id));

        // 4. Fill gap 3
        LocalCopy::create([
            'central_item_id' => $item->id,
            'library_id' => $this->library->id,
            'status' => 'available',
            'custom_fields_values' => ['cf_tombo_1' => 3],
        ]);

        // Now lowest available is 5
        $this->assertEquals(5, $this->library->getLowestAvailableAutoIncrementValue('cf_tombo_1'));
    }

    public function test_custom_fields_api_endpoint_returns_suggested_value(): void
    {
        $item = CentralItem::create([
            'title' => 'Obra Existente',
            'authors' => ['Autor B'],
            'status' => 'approved',
        ]);

        LocalCopy::create([
            'central_item_id' => $item->id,
            'library_id' => $this->library->id,
            'status' => 'available',
            'custom_fields_values' => ['cf_tombo_1' => 1],
        ]);

        $response = $this->actingAs($this->manager)->get(route('libraries.custom_fields', $this->library->id));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotEmpty($data['custom_fields']);
        $tomboField = null;
        foreach ($data['custom_fields'] as $f) {
            if ($f['key'] === 'cf_tombo_1') {
                $tomboField = $f;
                break;
            }
        }

        $this->assertNotNull($tomboField);
        $this->assertEquals(2, $tomboField['suggested_value']);
    }

    public function test_catalog_store_auto_assigns_lowest_available_when_omitted(): void
    {
        // First copy without explicit cf_tombo_1 value
        $response = $this->actingAs($this->manager)->post(route('catalog.store'), [
            'title' => 'Livro Automático 1',
            'authors' => ['Autor Auto'],
            'library_id' => $this->library->id,
            'copies_count' => 1,
            'status' => 'available',
            'custom_fields' => [
                'cf_categoria_2' => 'Ficção'
                // cf_tombo_1 is omitted!
            ]
        ]);

        $response->assertRedirect();

        $copy1 = LocalCopy::where('library_id', $this->library->id)->first();
        $this->assertNotNull($copy1);
        $this->assertEquals(1, $copy1->getCustomFieldValue('cf_tombo_1'));
        $this->assertEquals('1-LIVR', $copy1->call_number);

        // Second copy without explicit cf_tombo_1
        $response2 = $this->actingAs($this->manager)->post(route('catalog.store'), [
            'title' => 'Livro Automático 2',
            'authors' => ['Autor Auto 2'],
            'library_id' => $this->library->id,
            'copies_count' => 1,
            'status' => 'available',
            'custom_fields' => []
        ]);

        $response2->assertRedirect();

        $copy2 = LocalCopy::where('library_id', $this->library->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($copy2);
        $this->assertEquals(2, $copy2->getCustomFieldValue('cf_tombo_1'));
        $this->assertEquals('2-LIVR', $copy2->call_number);
    }
}
