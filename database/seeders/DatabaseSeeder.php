<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Library;
use App\Models\CentralItem;
use App\Models\LocalCopy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar as Bibliotecas (Libraries)
        $central = Library::create([
            'name' => 'Biblioteca Central Curió',
            'is_active' => true,
        ]);

        $lapa = Library::create([
            'name' => 'Biblioteca Setorial da Lapa',
            'is_active' => true,
        ]);

        // 2. Criar os Usuários (Users)
        User::create([
            'name' => 'Tiago Braga (Administrador)',
            'email' => 'admin@curio.ia.br',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'library_id' => null,
        ]);

        User::create([
            'name' => 'Maria Curadora',
            'email' => 'curador@curio.ia.br',
            'password' => Hash::make('curador123'),
            'role' => 'curator',
            'library_id' => null,
        ]);

        User::create([
            'name' => 'Pedro Gestor Central',
            'email' => 'gestor.central@curio.ia.br',
            'password' => Hash::make('gestor123'),
            'role' => 'manager',
            'library_id' => $central->id,
        ]);

        User::create([
            'name' => 'Ana Atendente Central',
            'email' => 'atendente.central@curio.ia.br',
            'password' => Hash::make('atendente123'),
            'role' => 'attendant',
            'library_id' => $central->id,
        ]);

        User::create([
            'name' => 'Lucas Atendente Lapa',
            'email' => 'atendente.lapa@curio.ia.br',
            'password' => Hash::make('atendente123'),
            'role' => 'attendant',
            'library_id' => $lapa->id,
        ]);

        User::create([
            'name' => 'João Leitor Central',
            'email' => 'leitor1@curio.ia.br',
            'password' => Hash::make('leitor123'),
            'role' => 'user',
            'library_id' => $central->id,
        ]);

        User::create([
            'name' => 'Fernanda Leitora Lapa',
            'email' => 'leitor2@curio.ia.br',
            'password' => Hash::make('leitor123'),
            'role' => 'user',
            'library_id' => $lapa->id,
        ]);

        // 3. Criar Itens Centrais (BIBFRAME Work/Instance)
        $domCasmurro = CentralItem::create([
            'title' => 'Dom Casmurro',
            'isbn' => '9788572327428',
            'item_type' => 'book',
            'metadata' => [
                '@context' => [
                    'bf' => 'http://id.loc.gov/ontologies/bibframe/',
                    'schema' => 'http://schema.org/'
                ],
                '@type' => 'bf:Instance',
                'bf:hasWork' => [
                    '@type' => 'bf:Work',
                    'bf:title' => [
                        '@type' => 'bf:Title',
                        'bf:mainTitle' => 'Dom Casmurro'
                    ],
                    'bf:contribution' => [
                        '@type' => 'bf:Contribution',
                        'bf:agent' => [
                            '@type' => 'bf:Agent',
                            'schema:name' => 'Machado de Assis'
                        ]
                    ],
                    'bf:language' => 'http://id.loc.gov/vocabulary/languages/por',
                    'bf:subject' => [
                        ['@type' => 'bf:Topic', 'schema:name' => 'Literatura Brasileira'],
                        ['@type' => 'bf:Topic', 'schema:name' => 'Realismo']
                    ]
                ],
                'bf:provisionActivity' => [
                    '@type' => 'bf:ProvisionActivity',
                    'bf:agent' => ['@type' => 'bf:Agent', 'schema:name' => 'Livraria Garnier'],
                    'bf:date' => '1899',
                    'bf:place' => 'Rio de Janeiro'
                ],
                'bf:identifiedBy' => [
                    ['@type' => 'bf:Isbn', 'rdf:value' => '9788572327428']
                ],
                'bf:extent' => ['@type' => 'bf:Extent', 'rdf:value' => '256 p.'],
                'bf:editionStatement' => '1ª ed.'
            ]
        ]);

        $memoriasPostumas = CentralItem::create([
            'title' => 'Memórias Póstumas de Brás Cubas',
            'isbn' => '9788572327435',
            'item_type' => 'book',
            'metadata' => [
                '@context' => [
                    'bf' => 'http://id.loc.gov/ontologies/bibframe/',
                    'schema' => 'http://schema.org/'
                ],
                '@type' => 'bf:Instance',
                'bf:hasWork' => [
                    '@type' => 'bf:Work',
                    'bf:title' => [
                        '@type' => 'bf:Title',
                        'bf:mainTitle' => 'Memórias Póstumas de Brás Cubas'
                    ],
                    'bf:contribution' => [
                        '@type' => 'bf:Contribution',
                        'bf:agent' => [
                            '@type' => 'bf:Agent',
                            'schema:name' => 'Machado de Assis'
                        ]
                    ],
                    'bf:language' => 'http://id.loc.gov/vocabulary/languages/por',
                    'bf:subject' => [
                        ['@type' => 'bf:Topic', 'schema:name' => 'Literatura Brasileira'],
                        ['@type' => 'bf:Topic', 'schema:name' => 'Realismo/Romantismo']
                    ]
                ],
                'bf:provisionActivity' => [
                    '@type' => 'bf:ProvisionActivity',
                    'bf:agent' => ['@type' => 'bf:Agent', 'schema:name' => 'Tipografia Nacional'],
                    'bf:date' => '1881',
                    'bf:place' => 'Rio de Janeiro'
                ],
                'bf:identifiedBy' => [
                    ['@type' => 'bf:Isbn', 'rdf:value' => '9788572327435']
                ],
                'bf:extent' => ['@type' => 'bf:Extent', 'rdf:value' => '312 p.'],
                'bf:editionStatement' => '1ª ed.'
            ]
        ]);

        // 4. Criar Cópias Locais (BIBFRAME Item)
        LocalCopy::create([
            'central_item_id' => $domCasmurro->id,
            'library_id' => $central->id,
            'call_number' => '869 M149d',
            'shelf_location' => 'Estante A, Prateleira 3',
            'status' => 'available',
            'quantity' => 2,
        ]);

        LocalCopy::create([
            'central_item_id' => $domCasmurro->id,
            'library_id' => $lapa->id,
            'call_number' => '869 M149d_Lapa',
            'shelf_location' => 'Estante de Literatura, Prateleira 1',
            'status' => 'available',
            'quantity' => 1,
        ]);

        LocalCopy::create([
            'central_item_id' => $memoriasPostumas->id,
            'library_id' => $central->id,
            'call_number' => '869 M149m',
            'shelf_location' => 'Estante A, Prateleira 4',
            'status' => 'available',
            'quantity' => 1,
        ]);
    }
}
