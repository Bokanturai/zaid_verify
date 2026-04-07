<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\BonusHistory;
use App\Models\VirtualAccount;
use App\Models\Announcement;
use App\Services\Admin\DashboardService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $dashboardService;

    /**
     * Inject DashboardService for statistics calculation.
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();
        $announcement = Announcement::getActiveAnnouncement();

        $virtualAccount = VirtualAccount::where('user_id', $user->id)->first();
        $bonusHistory = BonusHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Date Filtering Logic
        $isFiltered = $request->has('start_date') && $request->has('end_date');

        if ($isFiltered) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        // Fetch optimized and cached dashboard data via Service
        $statsData = $this->dashboardService->getDashboardData($startDate, $endDate, $isFiltered);

        return view('admin.dashboard', array_merge($statsData, [
            'user' => $user,
            'wallet' => $wallet,
            'virtualAccount' => $virtualAccount,
            'bonusHistory' => $bonusHistory,
            'announcement' => $announcement,
            'isFiltered' => $isFiltered,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]));
    }
}
