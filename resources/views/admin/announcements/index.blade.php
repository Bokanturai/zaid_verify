<x-app-layout>
    <x-slot name="title">Manage Announcements</x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-primary py-3 d-flex align-items-center justify-content-between border-bottom-0">
                        <h5 class="mb-0 fw-bold text-white"><i class="ti ti-bullhorn me-2"></i>Announcements</h5>
                        <a href="{{ route('admin.announcements.create') }}" class="btn btn-light btn-sm fw-bold">
                            <i class="ti ti-plus me-1"></i>New Announcement
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 text-muted small fw-bold text-uppercase">#</th>
                                        <th class="text-muted small fw-bold text-uppercase">Message</th>
                                        <th class="text-muted small fw-bold text-uppercase">Created By</th>
                                        <th class="text-muted small fw-bold text-uppercase">Status</th>
                                        <th class="text-muted small fw-bold text-uppercase">Date</th>
                                        <th class="text-end pe-4 text-muted small fw-bold text-uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($announcements as $announcement)
                                    <tr>
                                        <td class="ps-4">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="text-dark fw-medium">{{ Str::limit($announcement->message, 80) }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border">{{ $announcement->performed_by }}</span></td>
                                        <td>
                                            @if($announcement->is_active)
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">{{ $announcement->created_at->format('M d, Y') }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-light rounded-circle" title="{{ $announcement->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ti {{ $announcement->is_active ? 'ti-eye-off' : 'ti-eye' }} text-{{ $announcement->is_active ? 'warning' : 'success' }}"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-light rounded-circle" title="Edit">
                                                    <i class="ti ti-edit text-primary"></i>
                                                </a>
                                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Delete this announcement?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light rounded-circle" title="Delete">
                                                        <i class="ti ti-trash text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="ti ti-bullhorn fs-1 mb-2"></i>
                                                <p class="mb-0">No announcements found. Create one to broadcast messages to users.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $announcements->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
