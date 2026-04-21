<x-app-layout>
    <title>Zaid Verify - {{ $title ?? 'Dashboard' }}</title>

    <!-- Announcement Banner -->
    @if(isset($announcement) && $announcement)
        <div class="notification-container mt-3 mb-2">
            <div class="scrolling-text-container bg-primary text-white shadow-sm rounded-4 py-2">
                <div class="scrolling-text">
                    <span class="fw-bold me-3"><i class="fas fa-bullhorn"></i> ANNOUNCEMENT:</span>
                    {{ $announcement->message }}
                </div>
            </div>
        </div>
    @endif

    <div class="mt-4">
        <!-- User + Wallet Section -->
        <div class="card border-0 shadow-sm rounded-0 rounded-md-4 mb-3">
            <div class="card-body user-wallet-wrap">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <!-- User Image -->
                    <div class="avatar flex-shrink-0">
                        <img src="{{ Auth::user()->photo ?? asset('assets/img/profiles/avatar-31.jpg') }}"
                            class="rounded-circle border border-3 border-primary shadow-sm user-avatar"
                            alt="User Avatar">
                    </div>

                    <!-- Welcome Message -->
                    <div class="me-auto">
                        <h4 class="fw-semibold text-dark mb-1 welcome-text">
                            Welcome back, {{ Auth::user()->first_name . ' ' . Auth::user()->surname ?? 'User' }} 👋
                        </h4>
                        <small class="text-success">Account ID: {{ $virtualAccount->accountNo ?? 'N/A' }}
                            {{ $virtualAccount->bankName ?? 'N/A' }}</small>
                    </div>

                    <!-- Wallet Info -->
                    <div class="d-flex align-items-center gap-2 ms-2">
                        <span class="fw-medium text-muted small mb-0">Balance:</span>
                        <h5 id="wallet-balance" class="mb-0 text-success fw-bold balance-text">
                            ₦{{ number_format($wallet->balance ?? 0, 2) }}
                        </h5>

                        <!-- Toggle Balance Button -->
                        <button id="toggle-balance" class="btn btn-sm btn-outline-secondary ms-1 p-1 toggle-btn"
                            aria-pressed="true" title="Toggle balance visibility">
                            <i class="fas fa-eye eye-icon" aria-hidden="true"></i>
                        </button>

                        <!-- Wallet Icon -->
                        <a href="{{ route('wallet') }}" class="btn btn-light ms-1 border-0 p-0 wallet-btn"
                            title="View Wallet Details" aria-label="View wallet">
                            <i class="fas fa-wallet wallet-icon text-primary"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @include('pages.alart')

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Wallet Balance -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-0 rounded-md-4 stat-card card-gradient-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-title">Wallet Balance</p>
                                <h4 class="stat-value">₦{{ number_format($totalWalletBalance, 2) }}</h4>
                            </div>
                            <div class="icon-container">
                                <i class="ti ti-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Credit -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-0 rounded-md-4 stat-card card-gradient-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-title">Today's Credit</p>
                                <h4 class="stat-value">₦{{ number_format($dailyCredit, 2) }}</h4>
                            </div>
                            <div class="icon-container">
                                <i class="ti ti-arrow-down-left"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Debit -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-0 rounded-md-4 stat-card card-gradient-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-title">Today's Debit</p>
                                <h4 class="stat-value">₦{{ number_format($dailyDebit, 2) }}</h4>
                            </div>
                            <div class="icon-container">
                                <i class="ti ti-arrow-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-0 rounded-md-4 stat-card card-gradient-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-title">Total Users</p>
                                <h4 class="stat-value">{{ number_format($totalUsers) }}</h4>
                            </div>
                            <div class="icon-container">
                                <i class="ti ti-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Transactions & Statistics Row -->
        <div class="row g-4">
            <!-- Recent Transactions -->
            <div class="col-xxl-8 col-xl-7 d-flex">
                <div class="card flex-fill border-0 shadow-sm rounded-0 rounded-md-4 overflow-hidden">
                    <div
                        class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap border-bottom-0">
                        <h5 class="mb-0 fw-bold text-dark">
                            @if($isFiltered)
                                Transaction History ({{ $startDate->format('d M') }} - {{ $endDate->format('d M') }})
                            @else
                                Today's Transaction History
                            @endif
                        </h5>
                        <a href="{{ route('admin.transactions.index') }}"
                            class="btn btn-sm btn-light text-primary fw-medium">View
                            All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-secondary small fw-semibold ps-4">#</th>
                                        <th class="text-secondary small fw-semibold">Ref ID</th>
                                        <th class="text-secondary small fw-semibold">performed By</th>
                                        <th class="text-secondary small fw-semibold">Type</th>
                                        <th class="text-secondary small fw-semibold">Amount</th>
                                        <th class="text-secondary small fw-semibold">Time</th>
                                        <th class="text-secondary small fw-semibold pe-4 text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTransactions as $transaction)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="text-muted small">{{ $loop->iteration }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-medium text-dark">#{{ substr($transaction->transaction_ref, 0, 8) }}...</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-dark small fw-semibold">{{ $transaction->user->first_name ?? 'N/A' }}
                                                    {{ $transaction->user->last_name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($transaction->type == 'credit')
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                        <i class="ti ti-arrow-down-left me-1"></i>Credit
                                                    </span>
                                                @else
                                                    <span
                                                        class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                                        <i class="ti ti-arrow-up-right me-1"></i>Debit
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-bold {{ $transaction->type == 'credit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->type == 'credit' ? '+' : '-' }}₦{{ number_format($transaction->amount, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-muted small">{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                @if($transaction->status == 'completed' || $transaction->status == 'successful')
                                                    <span class="badge bg-success text-white rounded-pill px-3">Success</span>
                                                @elseif($transaction->status == 'pending')
                                                    <span class="badge bg-warning text-white rounded-pill px-3">Pending</span>
                                                @else
                                                    <span class="badge bg-success text-white rounded-pill px-3">Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ti ti-receipt-off fs-1 text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">No transactions found for this period.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Statistics -->
            <div class="col-xxl-4 col-xl-5 d-none d-xl-flex">
                <div class="card flex-fill border-0 shadow-sm rounded-0 rounded-md-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold text-dark">Today's Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="position-relative mb-4 d-flex justify-content-center">
                            <div style="height: 200px; width: 200px;">
                                <canvas id="transactionChart" data-completed="{{ $completedTransactions }}"
                                    data-pending="{{ $pendingTransactions }}"
                                    data-failed="{{ $failedTransactions }}"></canvas>
                            </div>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <p class="fs-12 text-muted mb-0">Total</p>
                                <h3 class="fw-bold text-dark mb-0">{{ $totalTransactions }}</h3>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-success-subtle text-center h-100">
                                    <i class="ti ti-circle-check-filled fs-4 text-success mb-2"></i>
                                    <h6 class="fw-bold text-dark mb-1">{{ $completedPercentage }}%</h6>
                                    <span class="fs-11 text-muted text-uppercase fw-semibold">Success</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-warning-subtle text-center h-100">
                                    <i class="ti ti-clock-filled fs-4 text-warning mb-2"></i>
                                    <h6 class="fw-bold text-dark mb-1">{{ $pendingPercentage }}%</h6>
                                    <span class="fs-11 text-muted text-uppercase fw-semibold">Pending</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-danger-subtle text-center h-100">
                                    <i class="ti ti-circle-x-filled fs-4 text-danger mb-2"></i>
                                    <h6 class="fw-bold text-dark mb-1">{{ $failedPercentage }}%</h6>
                                    <span class="fs-11 text-muted text-uppercase fw-semibold">Failed</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold text-primary mb-1">₦{{ number_format($totalTransactionAmount, 2) }}
                                </h5>
                                <p class="fs-12 text-muted mb-0">Total Spent Today</p>
                            </div>
                            <a href="{{ route('admin.transactions.index') }}"
                                class="btn btn-primary btn-sm rounded-pill px-3">
                                View Report <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    @endpush
</x-app-layout>