<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CentralItem;
use App\Models\LocalCopy;
use App\Models\Library;
use App\Services\IsbnLookupService;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    protected IsbnLookupService $isbnLookupService;

    public function __construct(IsbnLookupService $isbnLookupService)
    {
        $this->isbnLookupService = $isbnLookupService;
    }

    /**
     * Display a listing of cataloged items.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');

        // Central Items query
        $query = CentralItem::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('metadata->author', 'like', "%{$search}%");
            });
        }

        // Fetch paginated central items
        $items = $query->orderBy('title', 'asc')->paginate(12)->withQueryString();

        return view('catalog.index', compact('items', 'search'));
    }

    /**
     * Show the form for creating a new catalog item.
     */
    public function create()
    {
        $user = Auth::user();

        // Only attendants, managers, or admins can catalog
        if (!$user->isAttendant()) {
            abort(403, 'Acesso negado. Apenas atendentes ou administradores podem catalogar.');
        }

        return view('catalog.create');
    }

    /**
     * Store a newly created catalog item and its local copy.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'item_type' => 'required|string',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|string|max:4',
            'pages' => 'nullable|integer',
            'cover_url' => 'nullable|url',
            'description' => 'nullable|string',
            'subjects' => 'nullable|string', // Comma separated tags
            
            // Local physical copy fields
            'call_number' => 'nullable|string|max:50',
            'shelf_location' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
        ]);

        // Trata os assuntos/tags (separa por vírgula)
        $subjectsArray = [];
        if (!empty($validated['subjects'])) {
            $subjectsArray = array_map('trim', explode(',', $validated['subjects']));
        }

        // Limpa o ISBN
        $cleanIsbn = preg_replace('/[^0-9]/', '', $validated['isbn'] ?? '');

        // Modelo de catálogo coletivo: se o livro já existe centralizado (por ISBN), reutiliza o registro
        $centralItem = null;
        if (!empty($cleanIsbn)) {
            $centralItem = CentralItem::where('isbn', $cleanIsbn)->first();
        }

        if (!$centralItem) {
            // Cria o registro centralizado estruturado no modelo BIBFRAME (JSON-LD)
            $metadata = [
                '@context' => [
                    'bf' => 'http://id.loc.gov/ontologies/bibframe/',
                    'schema' => 'http://schema.org/'
                ],
                '@type' => 'bf:Instance',
                'title' => $validated['title'],
                'author' => $validated['author'] ?? '',
                'publisher' => $validated['publisher'] ?? '',
                'publication_year' => $validated['publication_year'] ?? '',
                'pages' => $validated['pages'] ?? null,
                'isbn' => $cleanIsbn,
                'cover_url' => $validated['cover_url'] ?? null,
                'subjects' => $subjectsArray,
                'description' => $validated['description'] ?? '',
                
                // Mapeamentos formais BIBFRAME
                'bf:hasWork' => [
                    '@type' => 'bf:Work',
                    'bf:title' => [
                        '@type' => 'bf:Title',
                        'bf:mainTitle' => $validated['title']
                    ],
                    'bf:contribution' => [
                        '@type' => 'bf:Contribution',
                        'bf:agent' => [
                            '@type' => 'bf:Agent',
                            'schema:name' => $validated['author'] ?? ''
                        ]
                    ],
                    'bf:language' => 'http://id.loc.gov/vocabulary/languages/por',
                    'bf:subject' => array_map(function($subject) {
                        return ['@type' => 'bf:Topic', 'schema:name' => $subject];
                    }, $subjectsArray)
                ],
                'bf:provisionActivity' => [
                    '@type' => 'bf:ProvisionActivity',
                    'bf:agent' => ['@type' => 'bf:Agent', 'schema:name' => $validated['publisher'] ?? ''],
                    'bf:date' => $validated['publication_year'] ?? ''
                ]
            ];

            $centralItem = CentralItem::create([
                'title' => $validated['title'],
                'isbn' => $cleanIsbn ?: null,
                'item_type' => $validated['item_type'],
                'metadata' => $metadata,
            ]);
        }

        // Verifica se esta biblioteca específica já possui exemplar físico deste livro cadastrado
        $libraryId = $user->library_id ?: 1; // Admins sem biblioteca cadastram na central (ID 1)
        
        $localCopy = LocalCopy::where('central_item_id', $centralItem->id)
            ->where('library_id', $libraryId)
            ->first();

        if ($localCopy) {
            // Se já tem exemplar, apenas soma a quantidade
            $localCopy->quantity += $validated['quantity'];
            $localCopy->save();
        } else {
            // Cria o registro físico (BIBFRAME Item) associado à biblioteca do atendente
            LocalCopy::create([
                'central_item_id' => $centralItem->id,
                'library_id' => $libraryId,
                'call_number' => $validated['call_number'] ?? null,
                'shelf_location' => $validated['shelf_location'] ?? null,
                'status' => 'available',
                'quantity' => $validated['quantity'],
            ]);
        }

        return redirect()->route('catalog.index')->with('success', 'Livro cadastrado com sucesso no acervo!');
    }

    /**
     * Display the specified catalog item.
     */
    public function show($id)
    {
        $item = CentralItem::findOrFail($id);
        
        // Carrega todas as cópias físicas nas diferentes bibliotecas com os dados da biblioteca vinculada
        $copies = LocalCopy::with('library')
            ->where('central_item_id', $id)
            ->get();

        // Mapeia metadados para Dublin Core facilitado para exibição
        $metadata = $item->metadata;
        $dublinCore = [
            'title' => $metadata['title'] ?? $item->title,
            'creator' => $metadata['author'] ?? 'Autor desconhecido',
            'publisher' => $metadata['publisher'] ?? 'Editora não catalogada',
            'date' => $metadata['publication_year'] ?? 'Ano desconhecido',
            'description' => $metadata['description'] ?? 'Sem sinopse.',
            'subject' => implode(', ', $metadata['subjects'] ?? []),
            'identifier' => $metadata['isbn'] ?? 'N/A',
            'pages' => $metadata['pages'] ?? 'N/A',
            'cover_url' => $metadata['cover_url'] ?? null
        ];

        return view('catalog.show', compact('item', 'copies', 'dublinCore'));
    }

    /**
     * Lookup metadata of a book via ISBN APIs (JSON endpoint).
     */
    public function lookup(Request $request)
    {
        $isbn = $request->query('isbn');

        if (empty($isbn)) {
            return response()->json(['error' => 'ISBN é obrigatório'], 400);
        }

        $data = $this->isbnLookupService->lookup($isbn);

        if (!$data) {
            return response()->json(['error' => 'Livro não localizado nas bases de dados externas'], 44);
        }

        return response()->json($data);
    }
}
