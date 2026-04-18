<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletSummaryController extends Controller
{
    /**
     * Display a comprehensive summary of the system's wallets and transactions.
     */
    public function index()
    {
        // 1. User Wallet Balances (Total)
        $systemStats = [
            'total_balance' => Wallet::sum('balance'),
            'total_hold' => Wallet::sum('hold_amount'),
            'total_users' => User::count(),
            'total_wallets' => Wallet::count(),
        ];

        // 2. Most Used Services
        $mostUsedServices = Transaction::select('service_type', DB::raw('count(*) as usage_count'), DB::raw('sum(amount) as total_amount'))
            ->whereNotNull('service_type')
            ->groupBy('service_type')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        // 3. Top 10 Users with High Wallet Balance
        $topBalanceUsers = User::with('wallet')
            ->join('wallets', 'users.id', '=', 'wallets.user_id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone_no', 'wallets.balance')
            ->orderByDesc('wallets.balance')
            ->limit(10)
            ->get();

        // 4. Top 10 Users that use most frequent services the most
        // We find users with highest transaction counts overall
        $serviceHeavyUsers = Transaction::select('user_id', DB::raw('count(*) as transaction_count'), DB::raw('sum(amount) as total_spent'))
            ->groupBy('user_id')
            ->orderByDesc('transaction_count')
            ->limit(10)
            ->with(['user:id,first_name,last_name,email,phone_no'])
            ->get();

        // 5. Complete Transaction Summary
        $transactionSummary = Transaction::select(
            DB::raw('count(*) as total_count'),
            DB::raw('sum(amount) as total_volume'),
            DB::raw("sum(case when status = 'completed' then 1 else 0 end) as success_count"),
            DB::raw("sum(case when status = 'failed' then 1 else 0 end) as failed_count"),
            DB::raw("sum(case when status = 'pending' then 1 else 0 end) as pending_count")
        )->first();

        // Recent Transactions for context
        $recentTransactions = Transaction::with('user:id,first_name,last_name')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.wallet.summary', compact(
            'systemStats',
            'mostUsedServices',
            'topBalanceUsers',
            'serviceHeavyUsers',
            'transactionSummary',
            'recentTransactions'
        ));
    }
}
