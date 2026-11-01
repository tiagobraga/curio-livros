<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CentralItem;
use App\Models\LocalCopy;
use App\Models\Library;
use App\Services\IsbnLookupService;
use App\Services\CallNumberService;
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
        $libraryFilter = $request->input('library_id');
        $authorFilter = $request->input('author');
        $subjectFilter = $request->input('subject');
        $periodFilter = $request->input('period');
        $availableFilter = $request->boolean('available');
        
        $sort = $request->input('sort', 'title');
        $direction = $request->input('direction', 'asc');
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $defaultLayout = Auth::user()->isLibraryUser() ? 'list' : 'card';
        $layout = $request->input('layout', $defaultLayout);
        if (!in_array($layout, ['card', 'list'])) {
            $layout = $defaultLayout;
        }

        // Central Items query
        $query = CentralItem::query();

        if ($user->isAdmin() || $user->isCurator()) {
            $libraries = Library::where('is_active', true)->orderBy('name', 'asc')->get();
        } else {
            $libraries = $user->libraries()->where('is_active', true)->orderBy('name', 'asc')->get();
        }

        // Fetch unique subjects/genres for filter
        $uniqueSubjects = [];
        try {
            $uniqueSubjects = \Illuminate\Support\Facades\DB::table('central_items')
                ->select(\Illuminate\Support\Facades\DB::raw("distinct jsonb_array_elements_text(metadata->'subjects') as subject"))
                ->whereNotNull('metadata->subjects')
                ->whereNull('deleted_at')
                ->orderBy('subject')
                ->pluck('subject')
                ->toArray();
        } catch (\Exception $e) {
            // Fallback in case PostgreSQL jsonb functions fail
        }

        // Library unit scoped filtering
        if (!$user->isCurator()) {
            $associatedLibraryIds = $libraries->pluck('id')->toArray();
            if ($libraryFilter && in_array($libraryFilter, $associatedLibraryIds)) {
                $query->whereHas('localCopies', function($q) use ($libraryFilter) {
                    $q->where('library_id', $libraryFilter);
                });
            } else {
                $query->whereHas('localCopies', function($q) use ($associatedLibraryIds) {
                    $q->whereIn('library_id', $associatedLibraryIds);
                });
            }
        } elseif ($libraryFilter) {
            $query->whereHas('localCopies', function($q) use ($libraryFilter) {
                $q->where('library_id', $libraryFilter);
            });
        }

        // Search term (General search) - Case and Accent Insensitive
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw("unaccent(title) ILIKE unaccent(?)", ["%{$search}%"])
                  ->orWhereRaw("isbn ILIKE ?", ["%{$search}%"])
                  ->orWhereRaw("unaccent(metadata->>'author') ILIKE unaccent(?)", ["%{$search}%"]);
            });
        }

        // Author filter - Accent and Case Insensitive
        if ($authorFilter) {
            $query->whereRaw("unaccent(metadata->>'author') ILIKE unaccent(?)", ["%{$authorFilter}%"]);
        }

        // Subject filter
        if ($subjectFilter) {
            $query->whereJsonContains('metadata->subjects', $subjectFilter);
        }

        // Period filter
        if ($periodFilter === '30_days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($periodFilter === '90_days') {
            $query->where('created_at', '>=', now()->subDays(90));
        }

        // Availability filter
        if ($availableFilter) {
            $query->whereHas('localCopies', function($q) use ($user, $libraryFilter, $libraries) {
                $q->where('status', 'available');
                
                if (!$user->isCurator()) {
                    $associatedLibraryIds = $libraries->pluck('id')->toArray();
                    if ($libraryFilter && in_array($libraryFilter, $associatedLibraryIds)) {
                        $q->where('library_id', $libraryFilter);
                    } else {
                        $q->whereIn('library_id', $associatedLibraryIds);
                    }
                } elseif ($libraryFilter) {
                    $q->where('library_id', $libraryFilter);
                }
                
                $q->whereRaw('quantity > (select count(*) from loans where loans.local_copy_id = local_copies.id and return_date is null)');
            });
        }

        // Sorting
        if ($sort === 'title') {
            $query->orderByRaw("unaccent(title) " . $direction);
        } elseif ($sort === 'author') {
            $query->orderByRaw("unaccent(metadata->>'author') " . $direction);
        } elseif ($sort === 'publisher') {
            $query->orderByRaw("unaccent(metadata->>'publisher') " . $direction);
        } elseif ($sort === 'year') {
            $query->orderByRaw("(metadata->>'publication_year') " . $direction);
        } else {
            $query->orderByRaw("unaccent(title) asc");
        }

        // Fetch paginated central items
        $items = $query->paginate(12)->withQueryString();

        return view('catalog.index', compact(
            'items', 'search', 'libraries', 'libraryFilter',
            'uniqueSubjects', 'authorFilter', 'subjectFilter',
            'periodFilter', 'availableFilter', 'sort', 'direction', 'layout'
        ));
    }

    /**
     * Show the form for creating a new catalog item.
     */
    public function create()
    {
        $user = Auth::user();

        // Only attendants, managers, admins, or curators can catalog
        if (!$user->isAttendant() && !$user->isCurator()) {
            abort(403, 'Acesso negado. Apenas atendentes, curadores ou administradores podem catalogar.');
        }

        if ($user->isAdmin() || $user->isCurator()) {
            $libraries = Library::where('is_active', true)->orderBy('name', 'asc')->get();
        } else {
            $libraries = $user->getStaffLibraries();
        }

        return view('catalog.create', compact('libraries'));
    }

    /**
     * Store a newly created catalog item and its local copy.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAttendant() && !$user->isCurator()) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            // Book metadata
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:30',
            'publication_year' => 'nullable|string|max:4',
            'pages' => 'nullable|integer',
            'cover_url' => 'nullable|string|max:2048',
            'description' => 'nullable|string',
            'subjects' => 'nullable|string', // Comma separated tags
            
            // Local physical copy fields
            'library_id' => 'required|exists:libraries,id',
            'call_number' => 'nullable|string|max:100',
            'shelf_location' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'custom_fields_values' => 'nullable',
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

        $createdNewCentral = false;
        $suggestedEditCreated = false;

        if (!$centralItem) {
            $createdNewCentral = true;
            // Cria o registro centralizado
            $metadata = [
                'title' => $validated['title'],
                'author' => $validated['author'] ?? '',
                'publisher' => $validated['publisher'] ?? '',
                'publication_year' => $validated['publication_year'] ?? '',
                'pages' => $validated['pages'] ?? null,
                'isbn' => $cleanIsbn,
                'cover_url' => $validated['cover_url'] ?? null,
                'subjects' => $subjectsArray,
                'description' => $validated['description'] ?? '',
            ];

            $centralItem = CentralItem::create([
                'title' => $validated['title'],
                'isbn' => $cleanIsbn ?: null,
                'item_type' => 'book',
                'metadata' => $metadata,
            ]);
        } else {
            // Se a obra já existia na base interna e o gestor/atendente alterou metadados bibliográficos
            $existingMeta = $centralItem->metadata ?? [];
            $existingTitle = $centralItem->title;
            $existingAuthor = $existingMeta['author'] ?? '';
            $existingPublisher = $existingMeta['publisher'] ?? '';
            $existingYear = (string)($existingMeta['publication_year'] ?? '');
            $existingPages = (string)($existingMeta['pages'] ?? '');
            $existingDescription = $existingMeta['description'] ?? '';
            $existingCover = $existingMeta['cover_url'] ?? '';
            $existingSubjects = is_array($existingMeta['subjects'] ?? null) ? implode(', ', $existingMeta['subjects']) : '';

            $submittedTitle = $validated['title'];
            $submittedAuthor = $validated['author'] ?? '';
            $submittedPublisher = $validated['publisher'] ?? '';
            $submittedYear = (string)($validated['publication_year'] ?? '');
            $submittedPages = (string)($validated['pages'] ?? '');
            $submittedDescription = $validated['description'] ?? '';
            $submittedCover = $validated['cover_url'] ?? '';
            $submittedSubjects = $validated['subjects'] ?? '';

            $hasChanges = (
                trim($submittedTitle) !== trim($existingTitle) ||
                trim($submittedAuthor) !== trim($existingAuthor) ||
                trim($submittedPublisher) !== trim($existingPublisher) ||
                trim($submittedYear) !== trim($existingYear) ||
                trim($submittedPages) !== trim($existingPages) ||
                trim($submittedDescription) !== trim($existingDescription) ||
                trim($submittedCover) !== trim($existingCover) ||
                trim($submittedSubjects) !== trim($existingSubjects)
            );

            if ($hasChanges) {
                $metadata = [
                    'title' => $validated['title'],
                    'author' => $validated['author'] ?? '',
                    'publisher' => $validated['publisher'] ?? '',
                    'publication_year' => $validated['publication_year'] ?? '',
                    'pages' => $validated['pages'] ?? null,
                    'isbn' => $cleanIsbn,
                    'cover_url' => $validated['cover_url'] ?? null,
                    'subjects' => $subjectsArray,
                    'description' => $validated['description'] ?? '',
                ];

                if ($user->isCurator()) {
                    $centralItem->update([
                        'title' => $validated['title'],
                        'isbn' => $cleanIsbn ?: null,
                        'metadata' => $metadata,
                    ]);
                } else {
                    \App\Models\SuggestedEdit::create([
                        'central_item_id' => $centralItem->id,
                        'user_id' => $user->id,
                        'metadata' => $metadata,
                        'status' => 'pending',
                    ]);
                    $suggestedEditCreated = true;
                }
            }
        }

        // Verifica se esta biblioteca específica já possui exemplar físico deste livro cadastrado
        $libraryId = $validated['library_id'];
        if (!$user->isCurator()) {
            $associatedLibraryIds = $user->getStaffLibraryIds();
            if (!in_array($libraryId, $associatedLibraryIds)) {
                abort(403, 'Você não tem permissão para cadastrar exemplares nesta biblioteca.');
            }
        }

        $customValues = $request->input('custom_fields_values', []);
        if (is_string($customValues)) {
            $customValues = json_decode($customValues, true) ?: [];
        }
        if (!is_array($customValues)) {
            $customValues = [];
        }

        $targetLibrary = Library::find($libraryId);
        if ($targetLibrary) {
            foreach ($targetLibrary->getCustomFields() as $cf) {
                if (($cf['type'] ?? '') === 'integer' && !empty($cf['auto_increment'])) {
                    if (!isset($customValues[$cf['key']]) || $customValues[$cf['key']] === '' || $customValues[$cf['key']] === null) {
                        $customValues[$cf['key']] = $targetLibrary->getLowestAvailableAutoIncrementValue($cf['key']);
                    }
                }
            }
        }

        $callNumber = $validated['call_number'] ?? null;
        if ($targetLibrary && $targetLibrary->hasCallNumberFormat()) {
            $callNumberService = app(CallNumberService::class);
            if (empty($callNumber)) {
                $callNumber = $callNumberService->generate($targetLibrary, $centralItem, $customValues, true);
            } else {
                foreach ($targetLibrary->getCustomFields() as $cf) {
                    if (!empty($cf['auto_increment']) && isset($customValues[$cf['key']]) && is_numeric($customValues[$cf['key']])) {
                        $targetLibrary->advanceAutoIncrementCounter($cf['key'], (int)$customValues[$cf['key']]);
                    }
                }
            }
        }
        
        $localCopy = LocalCopy::where('central_item_id', $centralItem->id)
            ->where('library_id', $libraryId)
            ->first();

        if ($localCopy) {
            // Se já tem exemplar, apenas soma a quantidade
            $localCopy->quantity += $validated['quantity'];
            if (!empty($customValues)) {
                $localCopy->custom_fields_values = array_merge(
                    is_array($localCopy->custom_fields_values) ? $localCopy->custom_fields_values : [],
                    $customValues
                );
            }
            if (!empty($callNumber) && empty($localCopy->call_number)) {
                $localCopy->call_number = $callNumber;
            }
            $localCopy->save();
        } else {
            // Cria o registro físico (BIBFRAME Item) associado à biblioteca selecionada
            LocalCopy::create([
                'central_item_id' => $centralItem->id,
                'library_id' => $libraryId,
                'call_number' => $callNumber,
                'shelf_location' => $validated['shelf_location'] ?? null,
                'status' => 'available',
                'quantity' => $validated['quantity'],
                'custom_fields_values' => !empty($customValues) ? $customValues : null,
            ]);
        }

        if ($suggestedEditCreated) {
            $msg = 'Livro vinculado ao acervo da sua biblioteca! Como você alterou metadados de uma obra já existente no catálogo coletivo, suas alterações foram enviadas como sugestão de edição para a curadoria.';
        } elseif (!$createdNewCentral) {
            $msg = 'Livro vinculado com sucesso ao acervo da sua biblioteca a partir do catálogo coletivo!';
        } else {
            $msg = 'Livro cadastrado com sucesso no acervo!';
        }

        return redirect()->route('catalog.index')->with('success', $msg);
    }

    /**
     * Display the specified catalog item.
     */
    public function show($id)
    {
        $item = CentralItem::findOrFail($id);
        
        // Ensure book has a share_code
        if (empty($item->share_code)) {
            $item->share_code = CentralItem::generateUniqueShareCode();
            $item->save();
        }
        $shortUrl = request()->getSchemeAndHttpHost() . '/' . $item->share_code;

        // Carrega as cópias físicas nas bibliotecas
        $user = Auth::user();
        $copiesQuery = LocalCopy::with('library')
            ->where('central_item_id', $id);

        // Se o usuário for gestor, atendente ou leitor (não é admin nem curador),
        // ele vê apenas os exemplares disponíveis nas bibliotecas em que possui vínculo.
        if ($user && !$user->isAdmin() && !$user->isCurator()) {
            $associatedLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
            $copiesQuery->whereIn('library_id', $associatedLibraryIds)
                        ->where('status', 'available');
        }

        $copies = $copiesQuery->get();

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

        // Load reviews and stats
        $reviews = \App\Models\BookReview::with('user')
            ->where('central_item_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $avgRating = \App\Models\BookReview::where('central_item_id', $id)->avg('rating');
        $reviewsCount = \App\Models\BookReview::where('central_item_id', $id)->count();
        $userReview = \App\Models\BookReview::where('central_item_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        // Load loan history of the book for staff users (last 15, paginated)
        $loans = \App\Models\Loan::with(['user', 'localCopy.library', 'attendant'])
            ->whereHas('localCopy', function($q) use ($id) {
                $q->where('central_item_id', $id);
            })
            ->orderBy('loan_date', 'desc')
            ->paginate(15, ['*'], 'loans_page')
            ->withQueryString();

        $isShared = false;
        return view('catalog.show', compact('item', 'copies', 'dublinCore', 'reviews', 'avgRating', 'reviewsCount', 'userReview', 'loans', 'shortUrl', 'isShared'));
    }

    /**
     * Show a shared book catalog item publicly without login.
     */
    public function sharedView(Request $request, $code)
    {
        // 1. Try to find a book (CentralItem)
        $item = CentralItem::where('share_code', $code)->first();
        if ($item) {
            $copiesQuery = LocalCopy::with('library')
                ->where('central_item_id', $item->id)
                ->where('status', 'available');

            $targetLibrary = null;
            $libParam = $request->input('lib') ?: $request->input('library');
            if ($libParam) {
                $targetLibrary = Library::where('share_code', $libParam)
                    ->orWhere('id', is_numeric($libParam) ? $libParam : 0)
                    ->first();
                if ($targetLibrary) {
                    $copiesQuery->where('library_id', $targetLibrary->id);
                }
            }

            $copies = $copiesQuery->get();

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

            $reviews = \App\Models\BookReview::with('user')
                ->where('central_item_id', $item->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $avgRating = \App\Models\BookReview::where('central_item_id', $item->id)->avg('rating');
            $reviewsCount = \App\Models\BookReview::where('central_item_id', $item->id)->count();
            $userReview = Auth::check() 
                ? \App\Models\BookReview::where('central_item_id', $item->id)->where('user_id', Auth::id())->first()
                : null;

            $loans = \App\Models\Loan::with(['user', 'localCopy.library', 'attendant'])
                ->whereHas('localCopy', function($q) use ($item) {
                    $q->where('central_item_id', $item->id);
                })
                ->orderBy('loan_date', 'desc')
                ->paginate(15, ['*'], 'loans_page')
                ->withQueryString();

            $shortUrl = request()->getSchemeAndHttpHost() . '/' . $item->share_code;

            $isShared = true;
            return view('catalog.show', compact('item', 'copies', 'dublinCore', 'reviews', 'avgRating', 'reviewsCount', 'userReview', 'loans', 'shortUrl', 'isShared', 'targetLibrary'));
        }

        // 2. Try to find a public Library
        $library = Library::where('share_code', $code)->where('is_public_page', true)->first();
        if ($library) {
            // Load all local copies associated with this library
            $copies = LocalCopy::with('centralItem')
                ->where('library_id', $library->id)
                ->where('status', 'available')
                ->get();

            $shortUrl = request()->getSchemeAndHttpHost() . '/' . $library->share_code;

            return view('libraries.public_catalog', compact('library', 'copies', 'shortUrl'));
        }

        // 3. Not found
        abort(404, 'Código inválido ou catálogo privado.');
    }

    /**
     * Lookup metadata of a book via internal DB first, then ISBN APIs (JSON endpoint).
     */
    public function lookup(Request $request)
    {
        $isbn = $request->query('isbn');

        if (empty($isbn)) {
            return response()->json(['error' => 'ISBN é obrigatório'], 400);
        }

        $cleanIsbn = preg_replace('/[^0-9]/', '', $isbn);

        if (!empty($cleanIsbn)) {
            // 1. Busca primeiro na base de dados interna do Catálogo Coletivo
            $centralItem = CentralItem::where('isbn', $cleanIsbn)->first();

            if ($centralItem) {
                $metadata = $centralItem->metadata ?? [];
                return response()->json([
                    'title' => $metadata['title'] ?? $centralItem->title,
                    'subtitle' => $metadata['subtitle'] ?? '',
                    'author' => $metadata['author'] ?? '',
                    'publisher' => $metadata['publisher'] ?? '',
                    'publication_year' => (string)($metadata['publication_year'] ?? ''),
                    'pages' => $metadata['pages'] ?? null,
                    'isbn' => $cleanIsbn,
                    'cover_url' => $metadata['cover_url'] ?? null,
                    'subjects' => $metadata['subjects'] ?? [],
                    'description' => $metadata['description'] ?? '',
                    'source' => 'internal',
                    'central_item_id' => $centralItem->id,
                ]);
            }
        }

        // 2. Apenas se NÃO encontrar na base interna, faz a busca nas APIs públicas de internet
        $data = $this->isbnLookupService->lookup($isbn);

        if (!$data) {
            return response()->json(['error' => 'Livro não localizado nas bases de dados internas ou externas'], 404);
        }

        $data['source'] = 'external';
        return response()->json($data);
    }

    /**
     * Show the edit form for a catalog item.
     */
    public function edit($id)
    {
        $user = Auth::user();
        if (!$user->isAttendant() && !$user->isCurator()) {
            abort(403, 'Acesso negado.');
        }

        $item = CentralItem::findOrFail($id);

        if (!$user->isCurator()) {
            $associatedLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
            $hasCopy = $item->localCopies()->whereIn('library_id', $associatedLibraryIds)->exists();
            if (!$hasCopy) {
                abort(403, 'Acesso negado. Esta obra não está disponível nas suas bibliotecas associadas.');
            }
        }

        $metadata = $item->metadata;

        $fields = [
            'title' => $metadata['title'] ?? $item->title,
            'author' => $metadata['author'] ?? '',
            'publisher' => $metadata['publisher'] ?? '',
            'publication_year' => $metadata['publication_year'] ?? '',
            'pages' => $metadata['pages'] ?? null,
            'isbn' => $metadata['isbn'] ?? $item->isbn,
            'cover_url' => $metadata['cover_url'] ?? null,
            'subjects' => implode(', ', $metadata['subjects'] ?? []),
            'description' => $metadata['description'] ?? '',
        ];

        // Load existing physical copies and active libraries
        $localCopiesQuery = LocalCopy::where('central_item_id', $id)->with('library');
        if (!$user->isCurator()) {
            $associatedLibraryIds = $user->getStaffLibraryIds();
            $localCopiesQuery->whereIn('library_id', $associatedLibraryIds);
        }
        $localCopies = $localCopiesQuery->get();

        if ($user->isAdmin() || $user->isCurator()) {
            $libraries = Library::where('is_active', true)->orderBy('name', 'asc')->get();
        } else {
            $libraries = $user->getStaffLibraries();
        }

        return view('catalog.edit', compact('item', 'fields', 'localCopies', 'libraries'));
    }

    /**
     * Update the catalog item (directly or via suggestion queue).
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAttendant() && !$user->isCurator()) {
            abort(403, 'Acesso negado.');
        }

        $item = CentralItem::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|string|max:4',
            'pages' => 'nullable|integer',
            'cover_url' => 'nullable|string|max:2048',
            'description' => 'nullable|string',
            'subjects' => 'nullable|string',
            'cover_file' => 'nullable|image|max:2048',

            // Exemplares físicos existentes
            'copies' => 'nullable|array',
            'copies.*.library_id' => 'required|exists:libraries,id',
            'copies.*.call_number' => 'nullable|string|max:100',
            'copies.*.shelf_location' => 'nullable|string|max:100',
            'copies.*.quantity' => 'required|integer|min:0',
            'copies.*.is_available' => 'required|in:0,1',

            // Novo exemplar físico
            'new_copy_library_id' => 'nullable|exists:libraries,id',
            'new_copy_call_number' => 'nullable|string|max:100',
            'new_copy_shelf_location' => 'nullable|string|max:100',
            'new_copy_quantity' => 'nullable|integer|min:1',

            // Lista de IDs deletados
            'deleted_copies' => 'nullable|string',
        ]);

        // Trata os assuntos/tags (separa por vírgula)
        $subjectsArray = [];
        if (!empty($request->subjects)) {
            $subjectsArray = array_map('trim', explode(',', $request->subjects));
        }

        // Trata o upload da capa
        $coverUrl = $request->cover_url;
        if ($request->hasFile('cover_file')) {
            $file = $request->file('cover_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('covers');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $file->move($targetDir, $fileName);
            $coverUrl = '/covers/' . $fileName;
        }        // --- GERENCIAMENTO DE EXEMPLARES FÍSICOS (FASE 8) ---
        // Obter os IDs das bibliotecas associadas se não for curador
        $associatedLibraryIds = !$user->isCurator() ? $user->getStaffLibraryIds() : null;

        // Atualiza exemplares existentes
        if ($request->has('copies')) {
            foreach ($request->copies as $copyId => $copyData) {
                $localCopy = LocalCopy::where('central_item_id', $item->id)->find($copyId);
                if ($localCopy) {
                    if (!$user->isCurator()) {
                        if (!in_array($localCopy->library_id, $associatedLibraryIds)) {
                            continue;
                        }
                        if (!in_array($copyData['library_id'], $associatedLibraryIds)) {
                            abort(403, 'Acesso negado.');
                        }
                    }
                    $status = $copyData['is_available'] == '1' ? 'available' : 'unavailable';
                    $copyCustomValues = $copyData['custom_fields_values'] ?? null;
                    if (is_string($copyCustomValues)) {
                        $copyCustomValues = json_decode($copyCustomValues, true) ?: [];
                    }

                    if (is_array($copyCustomValues) && $localCopy->library) {
                        foreach ($localCopy->library->getCustomFields() as $cf) {
                            if (($cf['type'] ?? '') === 'integer' && !empty($cf['auto_increment'])) {
                                if (!isset($copyCustomValues[$cf['key']]) || $copyCustomValues[$cf['key']] === '' || $copyCustomValues[$cf['key']] === null) {
                                    $copyCustomValues[$cf['key']] = $localCopy->library->getLowestAvailableAutoIncrementValue($cf['key'], $localCopy->id);
                                }
                            }
                        }
                    }

                    $updateData = [
                        'library_id' => $copyData['library_id'],
                        'call_number' => $copyData['call_number'] ?? null,
                        'shelf_location' => $copyData['shelf_location'] ?? null,
                        'quantity' => $copyData['quantity'],
                        'status' => $status,
                    ];
                    if (is_array($copyCustomValues)) {
                        $updateData['custom_fields_values'] = $copyCustomValues;
                    }

                    $localCopy->update($updateData);
                    $localCopy->syncStatus();
                }
            }
        }

        // Processa exclusão de exemplares físicos
        if ($request->filled('deleted_copies')) {
            $deletedIds = explode(',', $request->deleted_copies);
            $deletedCopiesQuery = LocalCopy::where('central_item_id', $item->id)
                ->whereIn('id', $deletedIds);
            
            if (!$user->isCurator()) {
                $deletedCopiesQuery->whereIn('library_id', $associatedLibraryIds);
            }
            
            $deletedCopiesQuery->delete();
        }

        // Processa cadastro de novo exemplar físico
        if ($request->new_copy_library_id && $request->new_copy_quantity) {
            if (!$user->isCurator() && !in_array($request->new_copy_library_id, $associatedLibraryIds)) {
                abort(403, 'Acesso negado.');
            }

            $newCopyCustomValues = $request->input('new_copy_custom_fields_values', []);
            if (is_string($newCopyCustomValues)) {
                $newCopyCustomValues = json_decode($newCopyCustomValues, true) ?: [];
            }
            if (!is_array($newCopyCustomValues)) {
                $newCopyCustomValues = [];
            }

            $newTargetLibrary = Library::find($request->new_copy_library_id);
            if ($newTargetLibrary) {
                foreach ($newTargetLibrary->getCustomFields() as $cf) {
                    if (($cf['type'] ?? '') === 'integer' && !empty($cf['auto_increment'])) {
                        if (!isset($newCopyCustomValues[$cf['key']]) || $newCopyCustomValues[$cf['key']] === '' || $newCopyCustomValues[$cf['key']] === null) {
                            $newCopyCustomValues[$cf['key']] = $newTargetLibrary->getLowestAvailableAutoIncrementValue($cf['key']);
                        }
                    }
                }
            }

            $newCallNumber = $request->new_copy_call_number ?: null;
            if ($newTargetLibrary && $newTargetLibrary->hasCallNumberFormat()) {
                $callNumberService = app(CallNumberService::class);
                if (empty($newCallNumber)) {
                    $newCallNumber = $callNumberService->generate($newTargetLibrary, $item, $newCopyCustomValues, true);
                } else {
                    foreach ($newTargetLibrary->getCustomFields() as $cf) {
                        if (!empty($cf['auto_increment']) && isset($newCopyCustomValues[$cf['key']]) && is_numeric($newCopyCustomValues[$cf['key']])) {
                            $newTargetLibrary->advanceAutoIncrementCounter($cf['key'], (int)$newCopyCustomValues[$cf['key']]);
                        }
                    }
                }
            }

            $existingCopy = LocalCopy::where('central_item_id', $item->id)
                ->where('library_id', $request->new_copy_library_id)
                ->first();

            if ($existingCopy) {
                $existingCopy->update([
                    'quantity' => $existingCopy->quantity + $request->new_copy_quantity,
                    'call_number' => $newCallNumber ?: $existingCopy->call_number,
                    'shelf_location' => $request->new_copy_shelf_location ?: $existingCopy->shelf_location,
                    'custom_fields_values' => !empty($newCopyCustomValues) ? array_merge(is_array($existingCopy->custom_fields_values) ? $existingCopy->custom_fields_values : [], $newCopyCustomValues) : $existingCopy->custom_fields_values,
                ]);
                $existingCopy->syncStatus();
            } else {
                LocalCopy::create([
                    'central_item_id' => $item->id,
                    'library_id' => $request->new_copy_library_id,
                    'call_number' => $newCallNumber,
                    'shelf_location' => $request->new_copy_shelf_location ?? null,
                    'quantity' => $request->new_copy_quantity,
                    'status' => 'available',
                    'custom_fields_values' => !empty($newCopyCustomValues) ? $newCopyCustomValues : null,
                ]);
            }
        }
        // ---------------------------------------------------
        // Reconstrói a estrutura BIBFRAME JSON-LD
        $metadata = [
            '@context' => [
                'bf' => 'http://id.loc.gov/ontologies/bibframe/',
                'schema' => 'http://schema.org/'
            ],
            '@type' => 'bf:Instance',
            'title' => $request->title,
            'author' => $request->author ?? '',
            'publisher' => $request->publisher ?? '',
            'publication_year' => $request->publication_year ?? '',
            'pages' => $request->pages ?? null,
            'isbn' => $request->isbn ?? $item->isbn,
            'cover_url' => $coverUrl,
            'subjects' => $subjectsArray,
            'description' => $request->description ?? '',
            
            // Mapeamentos formais BIBFRAME
            'bf:hasWork' => [
                '@type' => 'bf:Work',
                'bf:title' => [
                    '@type' => 'bf:Title',
                    'bf:mainTitle' => $request->title
                ],
                'bf:contribution' => [
                    '@type' => 'bf:Contribution',
                    'bf:agent' => [
                        '@type' => 'bf:Agent',
                        'schema:name' => $request->author ?? ''
                    ]
                ],
                'bf:language' => 'http://id.loc.gov/vocabulary/languages/por',
                'bf:subject' => array_map(function($subject) {
                    return ['@type' => 'bf:Topic', 'schema:name' => $subject];
                }, $subjectsArray)
            ],
            'bf:provisionActivity' => [
                '@type' => 'bf:ProvisionActivity',
                'bf:agent' => ['@type' => 'bf:Agent', 'schema:name' => $request->publisher ?? ''],
                'bf:date' => $request->publication_year ?? ''
            ]
        ];

        if ($user->isCurator()) {
            // Curador/Admin: Atualiza diretamente o Catálogo Central
            $item->update([
                'title' => $request->title,
                'isbn' => $request->isbn ?: null,
                'metadata' => $metadata,
            ]);
            return redirect()->route('catalog.show', $item->id)->with('success', 'Livro atualizado com sucesso no catálogo central!');
        } else {
            // Gestor/Atendente: Cria uma sugestão de edição
            \App\Models\SuggestedEdit::create([
                'central_item_id' => $item->id,
                'user_id' => Auth::id(),
                'metadata' => $metadata,
                'status' => 'pending',
            ]);
            return redirect()->route('catalog.show', $item->id)->with('success', 'Sua sugestão de alteração foi enviada para análise da curadoria.');
        }
    }

    /**
     * Remove the specified catalog item from central storage (soft delete).
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->isCurator()) {
            abort(403, 'Acesso negado. Apenas curadores ou administradores podem excluir livros.');
        }

        $item = CentralItem::findOrFail($id);

        // Cascade soft delete to all local copies
        $item->localCopies()->delete();

        // Soft delete the central item itself
        $item->delete();

        return redirect()->route('catalog.index')->with('success', 'Livro excluído com sucesso do catálogo central (registros históricos de circulação preservados).');
    }

    /**
     * Store or update a book review.
     */
    public function storeReview(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isLibraryUser()) {
            abort(403, 'Apenas leitores podem avaliar livros.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $comment = $validated['comment'] ?? null;
        if ($comment) {
            $swearWords = [
                'filho da puta', 'filho da p.', 'puta', 'caralho', 'porra',
                'merda', 'foder', 'bosta', 'cuceta', 'cu', 'viado', 'cacete'
            ];
            foreach ($swearWords as $word) {
                $pattern = '/\b' . preg_quote($word, '/') . '\b/ui';
                $comment = preg_replace($pattern, '***', $comment);
            }
        }

        \App\Models\BookReview::updateOrCreate(
            [
                'user_id' => $user->id,
                'central_item_id' => $id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $comment,
            ]
        );

        return redirect()->back()->with('success', 'Sua avaliação foi publicada com sucesso!');
    }

    /**
     * Delete (soft delete) a book review comment.
     */
    public function destroyReview($id)
    {
        $user = Auth::user();
        if (!$user->isCurator()) {
            abort(403, 'Acesso negado. Apenas curadores ou administradores podem apagar comentários.');
        }

        $review = \App\Models\BookReview::findOrFail($id);
        $review->delete();
 
        return redirect()->back()->with('success', 'Comentário apagado com sucesso!');
    }


}
