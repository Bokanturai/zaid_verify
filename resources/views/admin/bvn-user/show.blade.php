<x-app-layout>
    <title>Quick Slip - BVN User Details</title>

    <div class="content">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 fw-bold text-primary">BVN User Request Details</h4>
                            <p class="text-muted mb-0">View and manage BVN user request</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.bvn-user.index') }}" class="btn btn-light">
                                <i class="ti ti-arrow-left me-1"></i> Back to List
                            </a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                <i class="ti ti-edit me-1"></i> Update Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('errorMessage'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                    <div>
                        <strong>Error!</strong> {{ session('errorMessage') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('successMessage'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fs-4 me-3"></i>
                    <div>
                        <strong>Success!</strong> {{ session('successMessage') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- Left Column - Request Info --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="ti ti-info-circle me-2 text-primary"></i>Request Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            {{-- Agent / User --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Agent / User</label>
                                <div class="d-flex align-items-center">
                                    <span class="fw-medium">{{ $enrollmentInfo->user->first_name }} {{ $enrollmentInfo->user->last_name }} (#{{ $enrollmentInfo->user_id }})</span>
                                    @if (!empty($user))
                                        <button type="button" class="btn btn-sm btn-light ms-2 text-primary"
                                            data-bs-toggle="modal" data-bs-target="#agentInfoModal">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Reference --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Reference</label>
                                <p class="fw-medium mb-0">{{ $enrollmentInfo->reference }}</p>
                            </div>

                            {{-- Amount Charged --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Amount Charged</label>
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-wallet text-success fs-20 me-2"></i>
                                    <span class="text-uppercase fw-bold text-dark">₦{{ number_format($enrollmentInfo->amount, 2) }}</span>
                                </div>
                            </div>

                            {{-- BVN --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">BVN</label>
                                <p class="fw-medium mb-0">{{ $enrollmentInfo->bvn }}</p>
                            </div>

                            {{-- Personal Details --}}
                            <div class="col-md-12">
                                <hr class="my-2">
                                <h6 class="fw-bold text-primary mb-3">Personal Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase fw-bold">Full Name</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->first_name }} {{ $enrollmentInfo->middle_name }} {{ $enrollmentInfo->last_name }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase fw-bold">Email</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->email }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase fw-bold">Phone</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->phone_no }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase fw-bold">Date of Birth</small>
                                        <span class="fw-medium">{{ \Carbon\Carbon::parse($enrollmentInfo->dob)->format('M j, Y') }}</span>
                                    </div>
                                    <div class="col-md-8">
                                        <small class="text-muted d-block text-uppercase fw-bold">Address</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->address }}, {{ $enrollmentInfo->lga }}, {{ $enrollmentInfo->state }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Bank Details --}}
                            <div class="col-md-12">
                                <hr class="my-2">
                                <h6 class="fw-bold text-primary mb-3">Bank Account Details</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase fw-bold">Bank Name</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->bank_name }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase fw-bold">Account Number</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->account_no }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block text-uppercase fw-bold">Account Name</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->account_name }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Additional Info --}}
                            <div class="col-md-12">
                                <hr class="my-2">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block text-uppercase fw-bold">Agent Location</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->agent_location }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block text-uppercase fw-bold">Performed By</small>
                                        <span class="fw-medium">{{ $enrollmentInfo->performed_by }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Current Status --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Current Status</label>
                                <div>
                                    @php
                                        $statusClass = match($enrollmentInfo->status) {
                                            'pending' => 'bg-warning-subtle text-warning',
                                            'processing' => 'bg-info-subtle text-info',
                                            'successful' => 'bg-success-subtle text-success',
                                            'failed' => 'bg-danger-subtle text-danger',
                                            'query' => 'bg-warning-subtle text-warning',
                                            'remark' => 'bg-secondary-subtle text-secondary',
                                            default => 'bg-light text-dark'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">
                                        {{ ucfirst($enrollmentInfo->status) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Date Submitted --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Date Submitted</label>
                                <p class="fw-medium mb-0">
                                    {{ $enrollmentInfo->submission_date ? \Carbon\Carbon::parse($enrollmentInfo->submission_date)->format('M j, Y g:i A') : 'N/A' }}
                                </p>
                            </div>

                            {{-- Comment --}}
                            <div class="col-12">
                                <label class="form-label text-muted small text-uppercase fw-bold">Admin Comment</label>
                                <div class="p-3 bg-light rounded border">
                                    {{ $enrollmentInfo->comment ?? 'No comment provided.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Status History --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="ti ti-history me-2 text-primary"></i>Status History
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($statusHistory->isNotEmpty())
                            <div class="timeline">
                                @foreach ($statusHistory as $history)
                                    <div class="timeline-item pb-4 border-start ps-4 position-relative">
                                        @php
                                            $historyStatusColor = match($history['status']) {
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'successful' => 'success',
                                                'failed' => 'danger',
                                                'query' => 'warning',
                                                'remark' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="position-absolute top-0 start-0 translate-middle p-2 bg-{{ $historyStatusColor }} border border-light rounded-circle"></span>
                                        
                                        <div class="mb-1">
                                            <span class="badge bg-{{ $historyStatusColor }}-subtle text-{{ $historyStatusColor }} mb-1">
                                                {{ ucfirst($history['status']) }}
                                            </span>
                                            <span class="text-muted small d-block">
                                                {{ \Carbon\Carbon::parse($history['submission_date'])->format('M j, Y g:i A') }}
                                            </span>
                                        </div>
                                        
                                        @if (!empty($history['comment']))
                                            <div class="bg-light p-2 rounded small text-muted mt-2">
                                                {{ $history['comment'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="ti ti-clock-off fs-1 mb-2 d-block"></i>
                                No status history available
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
            
    {{-- Update Status Modal --}}
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="ti ti-edit me-2"></i>Update Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.bvn-user.update', $enrollmentInfo->id) }}">
                    @csrf
                    <div class="modal-body p-4">
                        {{-- Status --}}
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">New Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" {{ old('status', $enrollmentInfo->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ old('status', $enrollmentInfo->status) === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="successful" {{ old('status', $enrollmentInfo->status) === 'successful' ? 'selected' : '' }}>Successful</option>
                                <option value="query" {{ old('status', $enrollmentInfo->status) === 'query' ? 'selected' : '' }}>Query</option>
                                <option value="failed" {{ old('status', $enrollmentInfo->status) === 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="remark" {{ old('status', $enrollmentInfo->status) === 'remark' ? 'selected' : '' }}>Remark</option>
                            </select>
                        </div>

                        {{-- Comment --}}
                        <div class="mb-3">
                            <label for="comment" class="form-label fw-semibold">Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" 
                                placeholder="Enter your comment here...">{{ old('comment', $enrollmentInfo->comment) }}</textarea>
                            <small class="text-muted">
                                <i class="ti ti-info-circle me-1"></i>Visible to the agent/user
                            </small>
                        </div>

                        {{-- Force Refund --}}
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="force_refund" name="force_refund" value="1">
                            <label class="form-check-label text-danger fw-bold" for="force_refund">
                                Force Refund (Process again even if already refunded)
                            </label>
                            <small class="form-text text-muted d-block">
                                Check this ONLY if you need to credit the user again manually. Automatically refunds 80% on 'Failed' status.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Agent Info Modal --}}
    @if(!empty($user))
        <div class="modal fade" id="agentInfoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="agentInfoModalLabel">
                            <i class="ti ti-user me-2"></i> Agent Information
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body bg-light p-4">
                        <div class="text-center mb-4">
                            @php
                                if (!empty($user->profile_photo_url)) {
                                    if (filter_var($user->profile_photo_url, FILTER_VALIDATE_URL)) {
                                        $profileImage = $user->profile_photo_url;
                                    } else {
                                        $profileImage = asset('storage/' . $user->profile_photo_url);
                                    }
                                } else {
                                    $profileImage = asset('assets/img/users/user-01.jpg');
                                }
                            @endphp
                            <img src="{{ $profileImage }}" 
                                 alt="{{ $user->first_name }}" 
                                 class="rounded-circle shadow border border-3 border-white" 
                                 width="100" 
                                 height="100"
                                 style="object-fit: cover;"
                                 onerror="this.src='{{ asset('assets/img/users/user-01.jpg') }}'">
                            <h5 class="mt-3 mb-1 fw-bold">{{ $user->first_name }} {{ $user->last_name }}</h5>
                            <span class="badge bg-primary-subtle text-primary">{{ ucfirst($user->role ?? 'User') }}</span>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="mb-3 border-bottom pb-2">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Email Address</small>
                                    <span class="fw-medium">{{ $user->email }}</span>
                                </div>
                                <div class="mb-3 border-bottom pb-2">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Phone Number</small>
                                    <span class="fw-medium">{{ $user->phone_no ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Account Balance</small>
                                    <span class="fw-bold text-success">₦{{ number_format($user->wallet->balance ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- JavaScript for Auto Messages --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const statusField = document.getElementById("status");
            const commentField = document.getElementById("comment");

            const messages = {
                pending: "Your request is pending. Our team will review it shortly.",
                processing: "Your request is currently being processed. Please hold on.",
                "in-progress": "Your request is in progress. We are working on it and will update you soon.",
                resolved: "✅ Your Request Has Been Successfully Treated!\n\nHello 👋,\n\nWe're glad to inform you that your recent request has been successfully processed. Thank you for trusting us!\n🎯 Don't stop here — we're always ready to serve you better. Feel free to send in more requests anytime.\n\nYour satisfaction is our priority!",
                successful: "✅ Success! Your request has been completed successfully. Thank you for using our service!",
                query: "We require additional information regarding your request. Kindly respond promptly.",
                rejected: "Unfortunately, your request has been rejected. Please review and try again.",
                failed: "Your request could not be completed due to an error. Please contact support for assistance.",
                remark: "A remark has been added to your request. Kindly review the details provided."
            };

            // Auto update comment when status changes
            statusField.addEventListener("change", function () {
                const selectedStatus = statusField.value;
                if (messages[selectedStatus]) {
                    commentField.value = messages[selectedStatus];
                } else {
                    commentField.value = "";
                }
            });
        });
    </script>
</x-app-layout>
