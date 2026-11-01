<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LocalCopy;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Display a listing of the loans.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->isLibraryUser()) {
            $loans = Loan::with(['localCopy.centralItem', 'localCopy.library', 'attendant'])
                ->where('user_id', $user->id)
                ->orderBy('loan_date', 'desc')
                ->paginate(15)
                ->withQueryString();

            return view('loans.my_loans', compact('loans'));
        }

        if (!$user->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $search = $request->input('search');
        $statusFilter = $request->input('status');
        if (is_null($statusFilter) || $statusFilter === '') {
            $statusFilter = 'active';
        }
        $libraryFilter = $request->input('library_id');

        // Obter a lista de bibliotecas permitidas para o filtro (apenas onde é gestor ou atendente)
        $libraries = $user->getStaffLibraries();
        $allowedLibraryIds = $user->getStaffLibraryIds();

        $query = Loan::with(['localCopy.centralItem', 'user', 'attendant']);

        // Se o atendente for associado a bibliotecas específicas, filtra os empréstimos dessas bibliotecas
        if (!$user->isCurator()) {
            $query->whereHas('localCopy', function($q) use ($allowedLibraryIds) {
                $q->whereIn('library_id', $allowedLibraryIds);
            });
        }

        // Aplica o filtro de biblioteca selecionada se for válida
        if ($libraryFilter && in_array($libraryFilter, $allowedLibraryIds)) {
            $query->whereHas('localCopy', function($q) use ($libraryFilter) {
                $q->where('library_id', $libraryFilter);
            });
            $scopedLibraryIds = [$libraryFilter];
        } else {
            $scopedLibraryIds = $allowedLibraryIds;
        }

        // Filtro de Busca
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('localCopy.centralItem', function($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                })->orWhereHas('user', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Filtro de Status
        if ($statusFilter === 'active') {
            $query->active();
        } elseif ($statusFilter === 'late') {
            $query->late();
        } elseif ($statusFilter === 'returned') {
            $query->whereNotNull('return_date');
        }

        $loans = $query->orderBy('loan_date', 'desc')->paginate(15)->withQueryString();

        // Estatísticas de circulação sob o escopo selecionado
        $totalBooks = LocalCopy::whereIn('library_id', $scopedLibraryIds)->sum('quantity');
        $activeLoansCount = Loan::active()
            ->whereHas('localCopy', function($q) use ($scopedLibraryIds) {
                $q->whereIn('library_id', $scopedLibraryIds);
            })->count();
        $lateLoansCount = Loan::late()
            ->whereHas('localCopy', function($q) use ($scopedLibraryIds) {
                $q->whereIn('library_id', $scopedLibraryIds);
            })->count();
        $recentLoansCount = Loan::where('loan_date', '>=', now()->subDays(30))
            ->whereHas('localCopy', function($q) use ($scopedLibraryIds) {
                $q->whereIn('library_id', $scopedLibraryIds);
            })->count();

        return view('loans.index', compact(
            'loans', 'search', 'statusFilter', 'libraries', 'libraryFilter',
            'totalBooks', 'activeLoansCount', 'lateLoansCount', 'recentLoansCount'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $associatedLibraryIds = $user->getStaffLibraryIds();

        // Lista leitores (papel 'user') associados a alguma das unidades do atendente/gestor
        $readersQuery = User::where('role', 'user');
        if (!$user->isCurator()) {
            $readersQuery->whereHas('libraries', function($q) use ($associatedLibraryIds) {
                $q->whereIn('libraries.id', $associatedLibraryIds);
            });
        }
        $readers = $readersQuery->orderBy('name', 'asc')->get();

        // Lista cópias locais dessas unidades
        $localCopiesQuery = LocalCopy::with(['centralItem', 'library']);
        if (!$user->isCurator()) {
            $localCopiesQuery->whereIn('library_id', $associatedLibraryIds);
        }
        $localCopies = $localCopiesQuery->get();

        // Filtra quais cópias realmente possuem exemplares disponíveis fisicamente
        $availableCopies = $localCopies->filter(function($copy) {
            $activeLoansCount = Loan::where('local_copy_id', $copy->id)->active()->count();
            return $activeLoansCount < $copy->quantity;
        });

        // Sugestão de data de devolução: 14 dias a partir de hoje
        $defaultDueDate = Carbon::today()->addDays(14)->format('Y-m-d');

        return view('loans.create', compact('readers', 'availableCopies', 'defaultDueDate'));
    }

    /**
     * Store a newly created loan in storage.
     */
    public function store(Request $request)
    {
        $attendant = Auth::user();
        if (!$attendant->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'local_copy_id' => 'required|exists:local_copies,id',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $reader = User::findOrFail($validated['user_id']);
        if (!$reader->isLibraryUser()) {
            return back()->withErrors(['user_id' => 'O usuário selecionado deve ser um Leitor.'])->withInput();
        }

        $copy = LocalCopy::findOrFail($validated['local_copy_id']);
        
        // Verifica se a cópia e o leitor pertencem às bibliotecas do atendente (se houver essa restrição)
        if (!$attendant->isCurator()) {
            $associatedLibraryIds = $attendant->getStaffLibraryIds();
            
            // Valida unidade da cópia
            if (!in_array($copy->library_id, $associatedLibraryIds)) {
                abort(403, 'Você não tem permissão para emprestar exemplares de outras bibliotecas.');
            }

            // Valida unidade do leitor
            $readerHasAssociatedLibrary = $reader->libraries()->whereIn('libraries.id', $associatedLibraryIds)->exists();
            if (!$readerHasAssociatedLibrary) {
                return back()->withErrors(['user_id' => 'O leitor selecionado deve estar associado a uma das suas unidades.'])->withInput();
            }
        }

        // Verifica disponibilidade
        if ($copy->status === 'unavailable') {
            return back()->with('error', 'Este exemplar está marcado como indisponível na unidade no momento.')->withInput();
        }

        $activeLoansCount = Loan::where('local_copy_id', $copy->id)->active()->count();
        if ($activeLoansCount >= $copy->quantity) {
            return back()->with('error', 'Não há exemplares disponíveis deste livro no momento. Todos estão emprestados.')->withInput();
        }

        // Cria o registro de empréstimo
        $loan = Loan::create([
            'local_copy_id' => $copy->id,
            'user_id' => $reader->id,
            'attendant_id' => $attendant->id,
            'loan_date' => Carbon::today(),
            'due_date' => $validated['due_date'],
            'status' => 'borrowed',
        ]);

        $copy->syncStatus();

        // Enviar e-mail de notificação de empréstimo
        try {
            \Mail::to($reader->email)->send(new \App\Mail\BookBorrowedMail($loan));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de empréstimo: ' . $e->getMessage());
        }

        return redirect()->route('loans.index')->with('success', 'Empréstimo registrado com sucesso!');
    }

    /**
     * Mark the loan as returned.
     */
    public function returnLoan($id)
    {
        $attendant = Auth::user();
        if (!$attendant->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $loan = Loan::findOrFail($id);

        // Verifica se pertence à biblioteca do atendente
        if (!$attendant->isCurator()) {
            $associatedLibraryIds = $attendant->getStaffLibraryIds();
            if (!in_array($loan->localCopy->library_id, $associatedLibraryIds)) {
                abort(403, 'Acesso negado.');
            }
        }

        if ($loan->return_date) {
            return redirect()->route('loans.index')->with('error', 'Este empréstimo já foi devolvido anteriormente.');
        }

        $loan->update([
            'return_date' => Carbon::today(),
            'status' => 'returned',
        ]);

        $loan->localCopy->syncStatus();

        // Enviar e-mail de notificação de devolução
        try {
            \Mail::to($loan->user->email)->send(new \App\Mail\BookReturnedMail($loan));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de devolução: ' . $e->getMessage());
        }

        return redirect()->route('loans.index')->with('success', 'Livro devolvido com sucesso!');
    }
}
