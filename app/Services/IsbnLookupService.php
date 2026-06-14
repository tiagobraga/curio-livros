<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IsbnLookupService
{
    /**
     * Look up book metadata by ISBN.
     *
     * @param string $isbn
     * @return array|null
     */
    public function lookup(string $isbn): ?array
    {
        // Limpa o ISBN (deixa apenas números)
        $cleanIsbn = preg_replace('/[^0-9]/', '', $isbn);

        if (empty($cleanIsbn)) {
            return null;
        }

        // Tenta buscar primeiro no Google Books
        $data = $this->fetchFromGoogleBooks($cleanIsbn);

        // Se não encontrar, tenta Open Library
        if (!$data) {
            $data = $this->fetchFromOpenLibrary($cleanIsbn);
        }

        return $data;
    }

    /**
     * Fetch metadata from Google Books API.
     */
    private function fetchFromGoogleBooks(string $isbn): ?array
    {
        try {
            $response = Http::timeout(5)->get("https://www.googleapis.com/books/v1/volumes", [
                'q' => "isbn:{$isbn}"
            ]);

            if ($response->successful() && isset($response['items'][0]['volumeInfo'])) {
                $info = $response['items'][0]['volumeInfo'];

                // Estrutura os dados mapeando para terminologia BIBFRAME (JSON-LD)
                return [
                    '@context' => [
                        'bf' => 'http://id.loc.gov/ontologies/bibframe/',
                        'schema' => 'http://schema.org/'
                    ],
                    '@type' => 'bf:Instance',
                    'title' => $info['title'] ?? '',
                    'subtitle' => $info['subtitle'] ?? '',
                    'author' => implode(', ', $info['authors'] ?? []),
                    'publisher' => $info['publisher'] ?? '',
                    'publication_year' => isset($info['publishedDate']) ? substr($info['publishedDate'], 0, 4) : '',
                    'pages' => $info['pageCount'] ?? null,
                    'isbn' => $isbn,
                    'cover_url' => $info['imageLinks']['thumbnail'] ?? ($info['imageLinks']['smallThumbnail'] ?? null),
                    'subjects' => $info['categories'] ?? [],
                    'description' => $info['description'] ?? '',
                    'bf:hasWork' => [
                        '@type' => 'bf:Work',
                        'bf:title' => [
                            '@type' => 'bf:Title',
                            'bf:mainTitle' => $info['title'] ?? ''
                        ],
                        'bf:contribution' => [
                            '@type' => 'bf:Contribution',
                            'bf:agent' => [
                                '@type' => 'bf:Agent',
                                'schema:name' => implode(', ', $info['authors'] ?? [])
                            ]
                        ],
                        'bf:language' => 'http://id.loc.gov/vocabulary/languages/' . ($info['language'] ?? 'por'),
                        'bf:subject' => array_map(function($subject) {
                            return ['@type' => 'bf:Topic', 'schema:name' => $subject];
                        }, $info['categories'] ?? [])
                    ],
                    'bf:provisionActivity' => [
                        '@type' => 'bf:ProvisionActivity',
                        'bf:agent' => ['@type' => 'bf:Agent', 'schema:name' => $info['publisher'] ?? ''],
                        'bf:date' => isset($info['publishedDate']) ? substr($info['publishedDate'], 0, 4) : '',
                        'bf:place' => '' // Google Books não fornece cidade nativamente
                    ],
                    'bf:identifiedBy' => [
                        ['@type' => 'bf:Isbn', 'rdf:value' => $isbn]
                    ],
                    'bf:extent' => [
                        '@type' => 'bf:Extent',
                        'rdf:value' => isset($info['pageCount']) ? $info['pageCount'] . ' p.' : ''
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error("Erro ao consultar Google Books para ISBN {$isbn}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch metadata from Open Library API.
     */
    private function fetchFromOpenLibrary(string $isbn): ?array
    {
        try {
            $key = "ISBN:{$isbn}";
            $response = Http::timeout(5)->get("https://openlibrary.org/api/books", [
                'bibkeys' => $key,
                'format' => 'json',
                'jscmd' => 'data'
            ]);

            if ($response->successful() && isset($response[$key])) {
                $info = $response[$key];

                $authorsList = array_map(function($author) {
                    return $author['name'];
                }, $info['authors'] ?? []);

                $subjectsList = array_map(function($subject) {
                    return $subject['name'];
                }, $info['subjects'] ?? []);

                $pubDate = isset($info['publish_date']) ? preg_replace('/[^0-9]/', '', $info['publish_date']) : '';
                $pubYear = strlen($pubDate) >= 4 ? substr($pubDate, -4) : $pubDate;

                return [
                    '@context' => [
                        'bf' => 'http://id.loc.gov/ontologies/bibframe/',
                        'schema' => 'http://schema.org/'
                    ],
                    '@type' => 'bf:Instance',
                    'title' => $info['title'] ?? '',
                    'subtitle' => $info['subtitle'] ?? '',
                    'author' => implode(', ', $authorsList),
                    'publisher' => implode(', ', array_map(function($p) { return $p['name']; }, $info['publishers'] ?? [])),
                    'publication_year' => $pubYear,
                    'pages' => $info['number_of_pages'] ?? null,
                    'isbn' => $isbn,
                    'cover_url' => $info['cover']['large'] ?? ($info['cover']['medium'] ?? ($info['cover']['small'] ?? null)),
                    'subjects' => $subjectsList,
                    'description' => $info['notes'] ?? '',
                    'bf:hasWork' => [
                        '@type' => 'bf:Work',
                        'bf:title' => [
                            '@type' => 'bf:Title',
                            'bf:mainTitle' => $info['title'] ?? ''
                        ],
                        'bf:contribution' => [
                            '@type' => 'bf:Contribution',
                            'bf:agent' => [
                                '@type' => 'bf:Agent',
                                'schema:name' => implode(', ', $authorsList)
                            ]
                        ],
                        'bf:language' => 'http://id.loc.gov/vocabulary/languages/por', // Padrão
                        'bf:subject' => array_map(function($subject) {
                            return ['@type' => 'bf:Topic', 'schema:name' => $subject];
                        }, $subjectsList)
                    ],
                    'bf:provisionActivity' => [
                        '@type' => 'bf:ProvisionActivity',
                        'bf:agent' => ['@type' => 'bf:Agent', 'schema:name' => implode(', ', array_map(function($p) { return $p['name']; }, $info['publishers'] ?? []))],
                        'bf:date' => $pubYear,
                        'bf:place' => implode(', ', array_map(function($pl) { return $pl['name']; }, $info['publish_places'] ?? []))
                    ],
                    'bf:identifiedBy' => [
                        ['@type' => 'bf:Isbn', 'rdf:value' => $isbn]
                    ],
                    'bf:extent' => [
                        '@type' => 'bf:Extent',
                        'rdf:value' => isset($info['number_of_pages']) ? $info['number_of_pages'] . ' p.' : ''
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error("Erro ao consultar Open Library para ISBN {$isbn}: " . $e->getMessage());
        }

        return null;
    }
}
