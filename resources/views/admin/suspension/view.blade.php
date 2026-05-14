<x-app-layout>
    <title>Zaid verify - Suspension Details</title>

    <div class="content p-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 fw-bold text-primary">Suspension Request Details</h4>
                            <p class="text-muted mb-0">View and manage NIN suspension request</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.suspension.index') }}" class="btn btn-light border">
                                <i class="ti ti-arrow-left me-1"></i> Back to List
                            </a>
                            <button type="button" class="btn btn-primary shadow" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                <i class="ti ti-edit me-1"></i> Update Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('errorMessage'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ti ti-alert-circle fs-4 me-3"></i>
                    <div><strong>Error!</strong> {{ session('errorMessage') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('successMessage'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ti ti-circle-check fs-4 me-3"></i>
                    <div><strong>Success!</strong> {{ session('successMessage') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="ti ti-info-circle me-2 text-primary"></i>Request Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            {{-- Request ID --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Reference</label>
                                <p class="fw-medium mb-0">{{ $enrollmentInfo->reference }}</p>
                            </div>

                            {{-- NIN --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">NIN Number</label>
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-id-badge text-primary fs-20 me-2"></i>
                                    <span class="fw-bold text-dark fs-16">{{ $enrollmentInfo->nin }}</span>
                                </div>
                            </div>

                            {{-- Name --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Full Name</label>
                                <p class="fw-medium mb-0">{{ $enrollmentInfo->first_name }} {{ $enrollmentInfo->middle_name }} {{ $enrollmentInfo->last_name }}</p>
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Phone Number</label>
                                <p class="fw-medium mb-0">{{ $enrollmentInfo->phone_number }}</p>
                            </div>

                            {{-- Amount --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Amount Charged</label>
                                <p class="fw-bold text-success mb-0">₦{{ number_format($enrollmentInfo->amount, 2) }}</p>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Current Status</label>
                                <div>
                                    @php
                                        $statusClass = match($enrollmentInfo->status) {
                                            'pending' => 'bg-warning-subtle text-warning',
                                            'processing' => 'bg-info-subtle text-info',
                                            'resolved', 'successful', 'completed' => 'bg-success-subtle text-success',
                                            'rejected', 'failed' => 'bg-danger-subtle text-danger',
                                            default => 'bg-light text-dark'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">
                                        {{ ucfirst($enrollmentInfo->status) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Admin Comment --}}
                            <div class="col-12">
                                <label class="form-label text-muted small text-uppercase fw-bold">Admin Comment</label>
                                <div class="p-3 bg-light rounded border">
                                    {{ $enrollmentInfo->comment ?? 'No comments yet.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Agent Information --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="ti ti-user me-2 text-primary"></i>Agent Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold fs-10">Agent Name</small>
                                <span class="fw-bold text-dark">{{ $user->first_name }} {{ $user->last_name }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold fs-10">Email Address</small>
                                <span class="text-dark">{{ $user->email }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold fs-10">Wallet Balance</small>
                                <span class="fw-bold text-success">₦{{ number_format($user->wallet->balance ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Update Status Modal --}}
        <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Update Request Status</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.suspension.update', $enrollmentInfo->id) }}">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">New Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="pending" {{ $enrollmentInfo->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $enrollmentInfo->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed" {{ $enrollmentInfo->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="resolved" {{ $enrollmentInfo->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="rejected" {{ $enrollmentInfo->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="failed" {{ $enrollmentInfo->status == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Comment</label>
                                <textarea class="form-control" name="comment" rows="4" placeholder="Enter processing details or rejection reason...">{{ $enrollmentInfo->comment }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 p-4">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 shadow">Update Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
