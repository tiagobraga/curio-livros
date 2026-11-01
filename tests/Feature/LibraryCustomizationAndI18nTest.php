<?php

namespace Tests\Feature;

use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryCustomizationAndI18nTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_can_be_created_with_customization_fields()
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        $logoFile = UploadedFile::fake()->image('create_logo.png', 150, 150);

        $response = $this->actingAs($user)->post(route('libraries.store'), [
            'name' => 'Biblioteca Nova Criada',
            'subtitle' => 'Subtítulo da Nova Biblioteca',
            'color_scheme' => 'deep_violet',
            'custom_logo_file' => $logoFile,
            'description' => 'Biblioteca nova para testes de criação',
            'address' => 'Av. Central, 500',
            'phone' => '11888888888',
            'library_type' => 'Pública',
            'is_public_page' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('libraries.index'));

        $library = Library::where('name', 'Biblioteca Nova Criada')->first();
        $this->assertNotNull($library);
        $this->assertEquals('Subtítulo da Nova Biblioteca', $library->subtitle);
        $this->assertEquals('deep_violet', $library->color_scheme);
        $this->assertNotNull($library->custom_logo_url);
    }

    public function test_library_can_be_customized_with_color_scheme_subtitle_and_logo()
    {
        Storage::fake('public');

        $manager = User::factory()->create(['role' => 'manager']);
        $library = Library::create([
            'name' => 'Biblioteca Alpha',
            'is_active' => true,
            'owner_id' => $manager->id,
            'share_code' => 'ALPHA1',
            'color_scheme' => 'emerald',
            'subtitle' => 'Subtítulo Antigo',
        ]);
        $manager->libraries()->attach($library->id, ['role' => 'manager']);

        $logoFile = UploadedFile::fake()->image('custom_logo.png', 200, 200);

        $response = $this->actingAs($manager)->put(route('libraries.update', $library), [
            'name' => 'Biblioteca Alpha Renomeada',
            'subtitle' => 'Conhecimento Libertador',
            'color_scheme' => 'navy_blue',
            'custom_logo_file' => $logoFile,
            'description' => 'Descrição da biblioteca',
            'address' => 'Rua Principal, 123',
            'phone' => '11999999999',
            'library_type' => 'Pública',
            'is_public_page' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect();

        $library->refresh();
        $this->assertEquals('Biblioteca Alpha Renomeada', $library->name);
        $this->assertEquals('Conhecimento Libertador', $library->subtitle);
        $this->assertEquals('navy_blue', $library->color_scheme);
        $this->assertNotNull($library->custom_logo_url);
        $this->assertStringContainsString('logos', $library->custom_logo_url);
    }

    public function test_language_can_be_switched_via_controller()
    {
        $response = $this->get(route('language.switch', ['locale' => 'en_US']));
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en_US');

        $responseEs = $this->get(route('language.switch', ['locale' => 'es']));
        $responseEs->assertRedirect();
        $responseEs->assertSessionHas('locale', 'es');
    }

    public function test_public_catalog_renders_customized_theme_logo_subtitle_and_open_source_footer()
    {
        $library = Library::create([
            'name' => 'Biblioteca Curió Central',
            'is_active' => true,
            'share_code' => 'CNTRL1',
            'color_scheme' => 'royal_burgundy',
            'subtitle' => 'Leitura para Todos',
            'custom_logo_url' => 'http://example.com/logo.png',
            'is_public_page' => true,
        ]);

        $response = $this->withSession(['locale' => 'pt_BR'])->get('/CNTRL1');

        $response->assertStatus(200);
        $response->assertSee('Biblioteca Curió Central');
        $response->assertSee('Leitura para Todos');
        $response->assertSee('http://example.com/logo.png');
        // Check dynamic color variable injection (#581845 is primary for royal_burgundy)
        $response->assertSee('#581845');
        // Check open source attribution footer
        $response->assertSee('Uma iniciativa open source do grupo de pesquisa');
    }
}
