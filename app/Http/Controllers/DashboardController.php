<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CentralItem;
use App\Models\LocalCopy;
use App\Models\Loan;
use App\Models\User;
use App\Models\Library;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isLibraryUser()) {
            $associatedLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
            
            $totalBooks = LocalCopy::whereIn('library_id', $associatedLibraryIds)->sum('quantity');
            
            // 10 most recent books in their associated libraries
            $recentBooks = CentralItem::whereHas('localCopies', function($q) use ($associatedLibraryIds) {
                $q->whereIn('library_id', $associatedLibraryIds);
            })->orderBy('created_at', 'desc')->take(10)->get();

            // Top rated books in their associated libraries
            $topRatedBooks = CentralItem::whereHas('localCopies', function($q) use ($associatedLibraryIds) {
                $q->whereIn('library_id', $associatedLibraryIds);
            })
            ->whereHas('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(5)
            ->get();

            // Comments on those top rated books
            $topRatedBookIds = $topRatedBooks->pluck('id')->toArray();
            $recentComments = \App\Models\BookReview::with(['user', 'centralItem'])
                ->whereIn('central_item_id', $topRatedBookIds)
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Reader's own loans
            $myLoans = Loan::with(['localCopy.centralItem'])
                ->where('user_id', $user->id)
                ->orderBy('loan_date', 'desc')
                ->get();

            $libraryNames = $user->libraries()->pluck('name')->implode(', ');
            $library = $libraryNames ? (object)['name' => $libraryNames] : null;

            return view('dashboard', compact(
                'totalBooks',
                'recentBooks',
                'topRatedBooks',
                'recentComments',
                'myLoans',
                'library'
            ));
        }

        // Filters metrics by local library context if user is associated with a branch
        if (!$user->isCurator()) {
            $associatedLibraryIds = $user->getStaffLibraryIds();
            
            $totalBooks = LocalCopy::whereIn('library_id', $associatedLibraryIds)->sum('quantity');
            $activeLoansCount = Loan::whereHas('localCopy', function($q) use ($associatedLibraryIds) {
                $q->whereIn('library_id', $associatedLibraryIds);
            })->active()->count();
            
            $lateLoansCount = Loan::whereHas('localCopy', function($q) use ($associatedLibraryIds) {
                $q->whereIn('library_id', $associatedLibraryIds);
            })->late()->count();

            $totalUsers = User::whereHas('libraries', function($q) use ($associatedLibraryIds) {
                $q->whereIn('libraries.id', $associatedLibraryIds);
            })->count();

            $libraryNames = $user->getStaffLibraries()->pluck('name')->implode(', ');
            $library = $libraryNames ? (object)['name' => $libraryNames] : null;
        } else {
            // Global catalog and metrics for global admins/curators
            $associatedLibraryIds = [];
            $totalBooks = CentralItem::count();
            $activeLoansCount = Loan::active()->count();
            $lateLoansCount = Loan::late()->count();
            $totalUsers = User::count();
            $library = null;
        }

        // 30 Days daily chart (loans vs returns)
        $chartLabels = [];
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->toDateString();
            $displayStr = now()->subDays($i)->format('d/m');
            $chartLabels[] = $displayStr;
            $chartData[$dateStr] = ['loans' => 0, 'returns' => 0];
        }

        $loansData = Loan::selectRaw('DATE(loan_date) as date, count(*) as count')
            ->where('loan_date', '>=', now()->subDays(30)->toDateString())
            ->when(!$user->isCurator(), function($q) use ($associatedLibraryIds) {
                $q->whereHas('localCopy', function($sub) use ($associatedLibraryIds) {
                    $sub->whereIn('library_id', $associatedLibraryIds);
                });
            })
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $returnsData = Loan::selectRaw('DATE(return_date) as date, count(*) as count')
            ->whereNotNull('return_date')
            ->where('return_date', '>=', now()->subDays(30)->toDateString())
            ->when(!$user->isCurator(), function($q) use ($associatedLibraryIds) {
                $q->whereHas('localCopy', function($sub) use ($associatedLibraryIds) {
                    $sub->whereIn('library_id', $associatedLibraryIds);
                });
            })
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        foreach ($loansData as $date => $count) {
            if (isset($chartData[$date])) {
                $chartData[$date]['loans'] = $count;
            }
        }
        foreach ($returnsData as $date => $count) {
            if (isset($chartData[$date])) {
                $chartData[$date]['returns'] = $count;
            }
        }

        $loansChartValues = [];
        $returnsChartValues = [];
        foreach ($chartData as $date => $values) {
            $loansChartValues[] = $values['loans'];
            $returnsChartValues[] = $values['returns'];
        }

        // Compile Recent Transactions for Dashboard
        $recentTransactions = collect();

        // 1. Recent Loans
        $dashboardLoans = Loan::with(['localCopy.centralItem', 'localCopy.library', 'user'])
            ->when(!$user->isCurator(), function($q) use ($associatedLibraryIds) {
                $q->whereHas('localCopy', function($sub) use ($associatedLibraryIds) {
                    $sub->whereIn('library_id', $associatedLibraryIds);
                });
            })
            ->orderBy('loan_date', 'desc')
            ->take(10)
            ->get();
        foreach ($dashboardLoans as $loan) {
            $recentTransactions->push((object)[
                'type' => 'loan',
                'type_label' => 'Empréstimo',
                'date' => \Carbon\Carbon::parse($loan->loan_date),
                'description' => "Livro '{$loan->localCopy->centralItem->title}' emprestado para o leitor {$loan->user->name}.",
                'book_title' => $loan->localCopy->centralItem->title,
                'book_id' => $loan->localCopy->centralItem->id,
                'user_name' => $loan->user->name,
                'user_id' => $loan->user->id,
                'library_name' => $loan->localCopy->library->name,
                'created_at' => $loan->created_at,
            ]);
        }

        // 2. Recent Returns
        $dashboardReturns = Loan::with(['localCopy.centralItem', 'localCopy.library', 'user'])
            ->when(!$user->isCurator(), function($q) use ($associatedLibraryIds) {
                $q->whereHas('localCopy', function($sub) use ($associatedLibraryIds) {
                    $sub->whereIn('library_id', $associatedLibraryIds);
                });
            })
            ->whereNotNull('return_date')
            ->orderBy('return_date', 'desc')
            ->take(10)
            ->get();
        foreach ($dashboardReturns as $loan) {
            $recentTransactions->push((object)[
                'type' => 'return',
                'type_label' => 'Devolução',
                'date' => \Carbon\Carbon::parse($loan->return_date),
                'description' => "Livro '{$loan->localCopy->centralItem->title}' devolvido pelo leitor {$loan->user->name}.",
                'book_title' => $loan->localCopy->centralItem->title,
                'book_id' => $loan->localCopy->centralItem->id,
                'user_name' => $loan->user->name,
                'user_id' => $loan->user->id,
                'library_name' => $loan->localCopy->library->name,
                'created_at' => $loan->updated_at,
            ]);
        }

        // 3. Recent Overdues
        $dashboardOverdues = Loan::with(['localCopy.centralItem', 'localCopy.library', 'user'])
            ->when(!$user->isCurator(), function($q) use ($associatedLibraryIds) {
                $q->whereHas('localCopy', function($sub) use ($associatedLibraryIds) {
                    $sub->whereIn('library_id', $associatedLibraryIds);
                });
            })
            ->whereNull('return_date')
            ->where('due_date', '<', now()->toDateString())
            ->orderBy('due_date', 'desc')
            ->take(10)
            ->get();
        foreach ($dashboardOverdues as $loan) {
            $recentTransactions->push((object)[
                'type' => 'overdue',
                'type_label' => 'Atraso',
                'date' => \Carbon\Carbon::parse($loan->due_date),
                'description' => "Prazo de devolução de '{$loan->localCopy->centralItem->title}' expirou para o leitor {$loan->user->name}.",
                'book_title' => $loan->localCopy->centralItem->title,
                'book_id' => $loan->localCopy->centralItem->id,
                'user_name' => $loan->user->name,
                'user_id' => $loan->user->id,
                'library_name' => $loan->localCopy->library->name,
                'created_at' => $loan->updated_at,
            ]);
        }

        // 4. Recent New Books
        $dashboardNewBooks = LocalCopy::with(['centralItem', 'library'])
            ->when(!$user->isCurator(), function($q) use ($associatedLibraryIds) {
                $q->whereIn('library_id', $associatedLibraryIds);
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        foreach ($dashboardNewBooks as $copy) {
            $recentTransactions->push((object)[
                'type' => 'new_book',
                'type_label' => 'Novo Livro',
                'date' => $copy->created_at,
                'description' => "Novo exemplar de '{$copy->centralItem->title}' cadastrado na unidade {$copy->library->name}.",
                'book_title' => $copy->centralItem->title,
                'book_id' => $copy->centralItem->id,
                'user_name' => null,
                'user_id' => null,
                'library_name' => $copy->library->name,
                'created_at' => $copy->created_at,
            ]);
        }

        // 5. Recent New Users
        $dashboardNewUsers = User::with('libraries')
            ->where('role', 'user')
            ->when(!$user->isCurator(), function($q) use ($associatedLibraryIds) {
                $q->whereHas('libraries', function($sub) use ($associatedLibraryIds) {
                    $sub->whereIn('libraries.id', $associatedLibraryIds);
                });
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        foreach ($dashboardNewUsers as $u) {
            $firstLib = !$user->isCurator() 
                ? ($u->libraries->whereIn('id', $associatedLibraryIds)->first() ?: $u->libraries->first())
                : $u->libraries->first();
            $recentTransactions->push((object)[
                'type' => 'new_user',
                'type_label' => 'Novo Usuário',
                'date' => $u->created_at,
                'description' => "Novo leitor {$u->name} cadastrado no sistema.",
                'book_title' => null,
                'book_id' => null,
                'user_name' => $u->name,
                'user_id' => $u->id,
                'library_name' => $firstLib ? $firstLib->name : 'N/A',
                'created_at' => $u->created_at,
            ]);
        }

        $recentTransactions = $recentTransactions->sortByDesc(function($t) {
            return $t->date->format('Y-m-d H:i:s');
        })->take(5);

        return view('dashboard', compact(
            'totalBooks',
            'activeLoansCount',
            'lateLoansCount',
            'totalUsers',
            'recentTransactions',
            'library',
            'chartLabels',
            'loansChartValues',
            'returnsChartValues'
        ));
    }

    /**
     * Display a listing of all transactions with filters.
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $libraryFilter = $request->input('library_id');
        $typeFilter = $request->input('type'); // loan, return, overdue, new_book, new_user

        if ($user->isAdmin() || $user->isCurator()) {
            $libraries = Library::where('is_active', true)->orderBy('name', 'asc')->get();
            $associatedLibraryIds = $libraryFilter ? [$libraryFilter] : $libraries->pluck('id')->toArray();
        } else {
            $libraries = $user->getStaffLibraries();
            $userLibraryIds = $user->getStaffLibraryIds();
            if ($libraryFilter && in_array($libraryFilter, $userLibraryIds)) {
                $associatedLibraryIds = [$libraryFilter];
            } else {
                $associatedLibraryIds = $userLibraryIds;
            }
        }

        $transactionsList = collect();

        // 1. Query Loans
        if (!$typeFilter || $typeFilter === 'loan') {
            $loans = Loan::with(['localCopy.centralItem', 'localCopy.library', 'user'])
                ->whereHas('localCopy', function($q) use ($associatedLibraryIds) {
                    $q->whereIn('library_id', $associatedLibraryIds);
                })
                ->orderBy('loan_date', 'desc')
                ->take(200)
                ->get();
            
            foreach ($loans as $loan) {
                $transactionsList->push((object)[
                    'type' => 'loan',
                    'type_label' => 'Empréstimo',
                    'date' => \Carbon\Carbon::parse($loan->loan_date),
                    'description' => "Livro '{$loan->localCopy->centralItem->title}' emprestado para o leitor {$loan->user->name}.",
                    'book_title' => $loan->localCopy->centralItem->title,
                    'book_id' => $loan->localCopy->centralItem->id,
                    'user_name' => $loan->user->name,
                    'user_id' => $loan->user->id,
                    'library_name' => $loan->localCopy->library->name,
                    'created_at' => $loan->created_at,
                ]);
            }
        }

        // 2. Query Returns
        if (!$typeFilter || $typeFilter === 'return') {
            $returns = Loan::with(['localCopy.centralItem', 'localCopy.library', 'user'])
                ->whereHas('localCopy', function($q) use ($associatedLibraryIds) {
                    $q->whereIn('library_id', $associatedLibraryIds);
                })
                ->whereNotNull('return_date')
                ->orderBy('return_date', 'desc')
                ->take(200)
                ->get();

            foreach ($returns as $loan) {
                $transactionsList->push((object)[
                    'type' => 'return',
                    'type_label' => 'Devolução',
                    'date' => \Carbon\Carbon::parse($loan->return_date),
                    'description' => "Livro '{$loan->localCopy->centralItem->title}' devolvido pelo leitor {$loan->user->name}.",
                    'book_title' => $loan->localCopy->centralItem->title,
                    'book_id' => $loan->localCopy->centralItem->id,
                    'user_name' => $loan->user->name,
                    'user_id' => $loan->user->id,
                    'library_name' => $loan->localCopy->library->name,
                    'created_at' => $loan->updated_at,
                ]);
            }
        }

        // 3. Query Overdues (Atrasos)
        if (!$typeFilter || $typeFilter === 'overdue') {
            $overdues = Loan::with(['localCopy.centralItem', 'localCopy.library', 'user'])
                ->whereHas('localCopy', function($q) use ($associatedLibraryIds) {
                    $q->whereIn('library_id', $associatedLibraryIds);
                })
                ->whereNull('return_date')
                ->where('due_date', '<', now()->toDateString())
                ->orderBy('due_date', 'desc')
                ->take(200)
                ->get();

            foreach ($overdues as $loan) {
                $transactionsList->push((object)[
                    'type' => 'overdue',
                    'type_label' => 'Atraso',
                    'date' => \Carbon\Carbon::parse($loan->due_date),
                    'description' => "Prazo de devolução de '{$loan->localCopy->centralItem->title}' expirou para o leitor {$loan->user->name}.",
                    'book_title' => $loan->localCopy->centralItem->title,
                    'book_id' => $loan->localCopy->centralItem->id,
                    'user_name' => $loan->user->name,
                    'user_id' => $loan->user->id,
                    'library_name' => $loan->localCopy->library->name,
                    'created_at' => $loan->updated_at,
                ]);
            }
        }

        // 4. Query New Books (Cadastros de Livros)
        if (!$typeFilter || $typeFilter === 'new_book') {
            $newBooks = LocalCopy::with(['centralItem', 'library'])
                ->whereIn('library_id', $associatedLibraryIds)
                ->orderBy('created_at', 'desc')
                ->take(200)
                ->get();

            foreach ($newBooks as $copy) {
                $transactionsList->push((object)[
                    'type' => 'new_book',
                    'type_label' => 'Novo Livro',
                    'date' => $copy->created_at,
                    'description' => "Novo exemplar de '{$copy->centralItem->title}' cadastrado na unidade {$copy->library->name}.",
                    'book_title' => $copy->centralItem->title,
                    'book_id' => $copy->centralItem->id,
                    'user_name' => null,
                    'user_id' => null,
                    'library_name' => $copy->library->name,
                    'created_at' => $copy->created_at,
                ]);
            }
        }

        // 5. Query New Users (Novos Usuários)
        if (!$typeFilter || $typeFilter === 'new_user') {
            $newUsers = User::with('libraries')
                ->where('role', 'user')
                ->whereHas('libraries', function($q) use ($associatedLibraryIds) {
                    $q->whereIn('libraries.id', $associatedLibraryIds);
                })
                ->orderBy('created_at', 'desc')
                ->take(200)
                ->get();

            foreach ($newUsers as $u) {
                $firstLib = !$user->isCurator() 
                    ? ($u->libraries->whereIn('id', $associatedLibraryIds)->first() ?: $u->libraries->first())
                    : $u->libraries->first();
                $transactionsList->push((object)[
                    'type' => 'new_user',
                    'type_label' => 'Novo Usuário',
                    'date' => $u->created_at,
                    'description' => "Novo leitor {$u->name} cadastrado no sistema.",
                    'book_title' => null,
                    'book_id' => null,
                    'user_name' => $u->name,
                    'user_id' => $u->id,
                    'library_name' => $firstLib ? $firstLib->name : 'N/A',
                    'created_at' => $u->created_at,
                ]);
            }
        }

        // Sort all by date desc
        $sortedTransactions = $transactionsList->sortByDesc(function($t) {
            return $t->date->format('Y-m-d H:i:s');
        });

        // Paginate manually using LengthAwarePaginator
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $currentPageItems = $sortedTransactions->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedTransactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $sortedTransactions->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        $paginatedTransactions->withQueryString();

        return view('loans.transactions', [
            'transactions' => $paginatedTransactions,
            'libraries' => $libraries,
            'libraryFilter' => $libraryFilter,
            'typeFilter' => $typeFilter,
        ]);
    }
}
