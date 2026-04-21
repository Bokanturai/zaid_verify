<x-app-layout>
    <x-slot name="title">New Announcement</x-slot>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-primary py-3 d-flex align-items-center justify-content-between border-bottom-0">
                        <h5 class="mb-0 fw-bold text-white"><i class="ti ti-plus me-2"></i>Create Announcement</h5>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-light btn-sm fw-bold">Back</a>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.announcements.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Broadcast Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control border-light-subtle rounded-3 shadow-none" rows="5" placeholder="Enter the announcement message here..." required>{{ old('message') }}</textarea>
                                <small class="text-muted mt-2 d-block fst-italic">This text will scroll horizontally on the user dashboard.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Initial Status</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" checked>
                                        <label class="form-check-label" for="statusActive">Active (Display immediately)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive">
                                        <label class="form-check-label" for="statusInactive">Inactive (Save as draft)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid pt-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm py-3 fw-bold">
                                    Broadcast Announcement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
