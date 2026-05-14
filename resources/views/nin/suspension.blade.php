<x-app-layout>
    <title>Zaid Verify - {{ $title ?? 'Suspension NIN' }}</title>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row align-items-center">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-dark">Suspension NIN</h3>
                        <p class="text-muted small mb-0">Submit requests for NIN suspension.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-3">
            <div class="row">
                {{-- Request Form Column --}}
                <div class="col-xl-5 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-primary py-3 mb-0">
                            <h5 class="mb-0 fw-bold text-white"><i class="ti ti-shield-lock me-2"></i>New Suspension Request</h5>
                        </div>

                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('nin.suspension.store') }}" class="row g-4" id="suspensionForm" onsubmit="return handleFormSubmit(event)">
                                @csrf
                                <input type="hidden" name="field_id" value="{{ $fieldId }}">

                                {{-- NIN --}}
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted text-uppercase">11-Digit NIN <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle"><i class="ti ti-id-badge-2 text-muted"></i></span>
                                        <input type="text" name="nin" class="form-control border-light-subtle shadow-sm" 
                                               placeholder="00000000000" maxlength="11" pattern="\d{11}" required
                                               title="Please enter exactly 11 digits">
                                    </div>
                                </div>

                                {{-- Surname --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control border-light-subtle shadow-sm" placeholder="Surname" required>
                                </div>

                                {{-- First Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control border-light-subtle shadow-sm" placeholder="First Name" required>
                                </div>

                                {{-- Middle Name --}}
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Middle Name (Optional)</label>
                                    <input type="text" name="middle_name" class="form-control border-light-subtle shadow-sm" placeholder="Middle Name">
                                </div>

                                {{-- Phone Number --}}
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted text-uppercase">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-light-subtle"><i class="ti ti-phone text-muted"></i></span>
                                        <input type="text" name="phone_number" class="form-control border-light-subtle shadow-sm" 
                                               placeholder="08012345678" maxlength="11" pattern="\d{11}" required>
                                    </div>
                                </div>

                                {{-- Consent --}}
                                <div class="col-12">
                                    <div class="form-check custom-checkbox mb-0">
                                        <input class="form-check-input border-primary-subtle" type="checkbox" name="consent" id="consentCheck" required>
                                        <label class="form-check-label small text-dark" for="consentCheck">
                                            ✅ I confirm that the information provided is correct and I agree to proceed with this request.
                                        </label>
                                    </div>
                                </div>

                                {{-- Pricing --}}
                                <div class="col-12">
                                    <div class="card bg-primary bg-opacity-10 border-0 rounded-4 mt-2">
                                        <div class="card-body py-3">
                                            <div class="row align-items-center">
                                                <div class="col-6">
                                                    <small class="text-primary fw-bold text-uppercase small">Service Fee</small>
                                                    <h3 class="fw-bold text-primary mb-0">₦{{ number_format($price, 2) }}</h3>
                                                </div>
                                                <div class="col-6 text-end border-start border-primary border-opacity-25">
                                                    <small class="text-muted fw-bold text-uppercase small">Balance</small>
                                                    <h5 class="fw-bold text-success mb-0 d-flex align-items-center justify-content-end gap-1">
                                                        <i class="ti ti-wallet"></i> 
                                                        ₦{{ number_format($wallet->balance ?? 0, 2) }}
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="col-12 d-grid mt-2">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow hover-up" id="submitBtn">
                                        <span id="submitText">Submit Request</span>
                                        <i class="ti ti-send ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Submission History Column --}}
                <div class="col-xl-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-primary py-3 mb-0 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 text-white">
                                <i class="ti ti-history me-2"></i> Request History
                            </h5>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 text-muted small fw-bold text-uppercase">S/N</th>
                                            <th class="text-muted small fw-bold text-uppercase">NIN / Name</th>
                                            <th class="text-muted small fw-bold text-uppercase">Service</th>
                                            <th class="text-muted small fw-bold text-uppercase">Status</th>
                                            <th class="text-end pe-4 text-muted small fw-bold text-uppercase">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($submissions as $submission)
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="text-dark fw-bold">{{ ($submissions->currentPage() - 1) * $submissions->perPage() + $loop->iteration }}</span>
                                                </td>
                                                <td>
                                                    <span class="text-dark fw-bold d-block">{{ $submission->nin }}</span>
                                                    <small class="text-muted fs-11">{{ $submission->first_name }} {{ $submission->last_name }}</small>
                                                </td>
                                                <td>
                                                    <span class="d-block text-dark fs-13">{{ $submission->service_field_name }}</span>
                                                    <small class="text-muted fs-11">₦{{ number_format($submission->amount, 2) }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = strtolower($submission->status);
                                                        $badgeClass = match($status) {
                                                            'successful', 'success', 'resolved', 'completed' => 'bg-success-subtle text-success border-success-subtle',
                                                            'processing', 'in-progress' => 'bg-primary-subtle text-primary border-primary-subtle',
                                                            'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
                                                            'failed', 'rejected', 'error', 'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                                            default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                                        };
                                                        $icon = match($status) {
                                                            'successful', 'success', 'resolved', 'completed' => 'ti-circle-check-filled',
                                                            'processing', 'in-progress' => 'ti-loader-2',
                                                            'pending' => 'ti-clock-filled',
                                                            'failed', 'rejected', 'error', 'cancelled' => 'ti-circle-x-filled',
                                                            default => 'ti-help-circle',
                                                        };
                                                    @endphp
                                                    <span class="badge border rounded-pill px-2 py-1 {{ $badgeClass }}">
                                                        <i class="ti {{ $icon }} me-1"></i>{{ ucfirst($submission->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <small class="text-muted fs-11">{{ $submission->created_at->format('M d, Y') }}</small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <div class="py-4">
                                                        <div class="avatar avatar-xl bg-light rounded-circle mb-3 mx-auto">
                                                            <i class="ti ti-shield-lock fs-2 text-muted"></i>
                                                        </div>
                                                        <h6 class="fw-bold">No Requests Found</h6>
                                                        <p class="small text-muted mb-0">Your suspension requests will appear here.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($submissions->hasPages())
                            <div class="p-3 border-top">
                                {{ $submissions->withQueryString()->links('vendor.pagination.custom') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Form Submission Handler
        function handleFormSubmit(event) {
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            
            submitBtn.disabled = true;
            submitText.textContent = 'Processing...';

            Swal.fire({
                title: 'Submitting',
                text: 'Processing your suspension request...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            return true;
        }

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}",
                timer: 4000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        @endif
    </script>
    <style>
        .hover-up:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
    </style>
    @endpush
</x-app-layout>
