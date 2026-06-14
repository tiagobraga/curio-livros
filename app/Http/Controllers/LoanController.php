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
        if (!$user->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $search = $request->input('search');
        $statusFilter = $request->input('status'); // all, active, late, returned

        $query = Loan::with(['localCopy.centralItem', 'user', 'attendant']);

        // Se o atendente for associado a uma biblioteca específica, filtra os empréstimos daquela biblioteca
        if ($user->library_id) {
            $query->whereHas('localCopy', function($q) use ($user) {
                $q->where('library_id', $user->library_id);
            });
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

        return view('loans.index', compact('loans', 'search', 'statusFilter'));
    }

    /**
     * Show the form for creating a new loan.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $libraryId = $user->library_id ?: 1; // Admin sem biblioteca usa a central (ID 1)

        // Lista leitores (papel 'user')
        $readers = User::where('role', 'user')->orderBy('name', 'asc')->get();

        // Lista cópias locais desta unidade
        $localCopies = LocalCopy::with('centralItem')
            ->where('library_id', $libraryId)
            ->get();

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
        
        // Verifica se a cópia pertence à biblioteca do atendente (se houver essa restrição)
        if ($attendant->library_id && $copy->library_id !== $attendant->library_id) {
            abort(403, 'Você não tem permissão para emprestar exemplares de outras bibliotecas.');
        }

        // Verifica disponibilidade
        $activeLoansCount = Loan::where('local_copy_id', $copy->id)->active()->count();
        if ($activeLoansCount >= $copy->quantity) {
            return back()->with('error', 'Não há exemplares disponíveis deste livro no momento. Todos estão emprestados.')->withInput();
        }

        // Cria o registro de empréstimo
        Loan::create([
            'local_copy_id' => $copy->id,
            'user_id' => $reader->id,
            'attendant_id' => $attendant->id,
            'loan_date' => Carbon::today(),
            'due_date' => $validated['due_date'],
            'status' => 'borrowed',
        ]);

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
        if ($attendant->library_id && $loan->localCopy->library_id !== $attendant->library_id) {
            abort(403, 'Acesso negado.');
        }

        if ($loan->return_date) {
            return redirect()->route('loans.index')->with('error', 'Este empréstimo já foi devolvido anteriormente.');
        }

        $loan->update([
            'return_date' => Carbon::today(),
            'status' => 'returned',
        ]);

        return redirect()->route('loans.index')->with('success', 'Livro devolvido com sucesso!');
    }
}
