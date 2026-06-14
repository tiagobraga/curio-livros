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

        // Filters metrics by local library context if user is associated with a branch
        if ($user->library_id) {
            $library = Library::find($user->library_id);
            $totalBooks = LocalCopy::where('library_id', $user->library_id)->sum('quantity');
            $activeLoansCount = Loan::whereHas('localCopy', function($q) use ($user) {
                $q->where('library_id', $user->library_id);
            })->active()->count();
            
            $lateLoansCount = Loan::whereHas('localCopy', function($q) use ($user) {
                $q->where('library_id', $user->library_id);
            })->late()->count();

            $totalUsers = User::where('library_id', $user->library_id)->count();

            $recentLoans = Loan::with(['localCopy.centralItem', 'user'])
                ->whereHas('localCopy', function($q) use ($user) {
                    $q->where('library_id', $user->library_id);
                })
                ->orderBy('loan_date', 'desc')
                ->take(5)
                ->get();
        } else {
            // Global catalog and metrics for global admins/curators
            $totalBooks = CentralItem::count();
            $activeLoansCount = Loan::active()->count();
            $lateLoansCount = Loan::late()->count();
            $totalUsers = User::count();
            $library = null;

            $recentLoans = Loan::with(['localCopy.centralItem', 'user'])
                ->orderBy('loan_date', 'desc')
                ->take(5)
                ->get();
        }

        return view('dashboard', compact(
            'totalBooks',
            'activeLoansCount',
            'lateLoansCount',
            'totalUsers',
            'recentLoans',
            'library'
        ));
    }
}
