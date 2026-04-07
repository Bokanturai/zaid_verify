<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'performer']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('service_type')) {
             $query->where('service_type', $request->service_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Calculate stats for the cards
        $totalTransactions = Transaction::count();
        $totalCredits = Transaction::where('type', 'credit')->sum('amount');
        $totalDebits = Transaction::where('type', 'debit')->sum('amount');
        $successfulTransactions = Transaction::whereIn('status', ['completed', 'successful'])->count();

        $transactions = $query->latest()->paginate(10);

        return view('admin.transactions.index', compact(
            'transactions', 
            'totalTransactions', 
            'totalCredits', 
            'totalDebits', 
            'successfulTransactions'
        ));
    }
}
