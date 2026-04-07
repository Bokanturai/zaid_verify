<?php

namespace App\Services\Admin;

use App\Models\Transaction;
use App\Models\AgentService;
use App\Models\BonusHistory;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get all dashboard data including filtered stats and global overview.
     */
    public function getDashboardData(Carbon $startDate, Carbon $endDate, bool $isFiltered): array
    {
        $cacheKey = "admin_dashboard_stats_" . $startDate->format('Y-m-d') . "_" . $endDate->format('Y-m-d');
        
        // Cache filtered stats for 10 minutes
        $stats = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate, $endDate, $isFiltered) {
            return $this->calculateStats($startDate, $endDate, $isFiltered);
        });

        // Global stats (not date-filtered, short cache)
        $global = Cache::remember('admin_dashboard_global', now()->addMinutes(5), function () {
            return [
                'totalUsers' => User::count(),
                'totalWalletBalance' => Wallet::sum('balance'),
            ];
        });

        // Recent transactions (usually not cached or cached very briefly)
        $recentTransactions = Transaction::with('user')
            ->betweenDates($startDate, $endDate)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return array_merge($stats, $global, ['recentTransactions' => $recentTransactions]);
    }

    /**
     * Calculate statistics for a given date range.
     */
    private function calculateStats(Carbon $startDate, Carbon $endDate, bool $isFiltered): array
    {
        // 1. Transaction Amounts
        $totalTransactionAmount = Transaction::where('type', 'debit')
            ->betweenDates($startDate, $endDate)
            ->sum('amount');

        $totalFundedAmount = Transaction::where('type', 'credit')
            ->betweenDates($startDate, $endDate)
            ->sum('amount');

        // 2. Agency Requests
        $totalAgencyRequests = AgentService::betweenDates($startDate, $endDate)
            ->count();

        // 3. Referrals
        $referralQuery = BonusHistory::query();
        if ($isFiltered) {
            $referralQuery->betweenDates($startDate, $endDate);
        }
        $totalReferrals = $referralQuery->count();

        // 4. Transaction Status Breakdown
        $transactionStats = Transaction::selectRaw('count(*) as total')
            ->selectRaw("count(case when status = 'completed' then 1 end) as completed")
            ->selectRaw("count(case when status = 'pending' then 1 end) as pending")
            ->selectRaw("count(case when status = 'failed' then 1 end) as failed")
            ->betweenDates($startDate, $endDate)
            ->first();

        $totalTransactions = $transactionStats->total ?? 0;
        $completedTransactions = $transactionStats->completed ?? 0;
        $pendingTransactions = $transactionStats->pending ?? 0;
        $failedTransactions = $transactionStats->failed ?? 0;

        // Calculate percentages
        $completedPercentage = $totalTransactions > 0 ? round(($completedTransactions / $totalTransactions) * 100) : 0;
        $pendingPercentage = $totalTransactions > 0 ? round(($pendingTransactions / $totalTransactions) * 100) : 0;
        $failedPercentage = $totalTransactions > 0 ? round(($failedTransactions / $totalTransactions) * 100) : 0;

        return [
            'totalTransactionAmount' => $totalTransactionAmount,
            'totalFundedAmount' => $totalFundedAmount,
            'totalAgencyRequests' => $totalAgencyRequests,
            'totalReferrals' => $totalReferrals,
            'totalTransactions' => $totalTransactions,
            'completedTransactions' => $completedTransactions,
            'pendingTransactions' => $pendingTransactions,
            'failedTransactions' => $failedTransactions,
            'completedPercentage' => $completedPercentage,
            'pendingPercentage' => $pendingPercentage,
            'failedPercentage' => $failedPercentage,
            'dailyCredit' => $totalFundedAmount, // Re-mapped for compatibility
            'dailyDebit' => $totalTransactionAmount, // Re-mapped for compatibility
        ];
    }
}
