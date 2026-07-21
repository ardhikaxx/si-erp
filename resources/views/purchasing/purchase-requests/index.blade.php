@extends('layouts.app')

@section('title', 'Purchase Request')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Purchase Request'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">Data Purchase Request</h5>
        <a href="{{ route('purchasing.purchase-requests.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat PR
        </a>
    </div>
    <div class="card-body">
        <table id="prTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode PR</th>
                    <th>Supplier</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseRequests as $pr)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-medium">{{ $pr->code }}</td>
                    <td>{{ $pr->supplier->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($pr->request_date)->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $badge = match($pr->status) {
                                'draft' => 'bg-secondary',
                                'submitted', 'pending_approval' => 'bg-warning text-dark',
                                'approved' => 'bg-info',
                                'ordered' => 'bg-primary',
                                'partially_received' => 'bg-warning text-dark',
                                'completed', 'received' => 'bg-success',
                                'rejected', 'cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $label = match($pr->status) {
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'pending_approval' => 'Menunggu Approve',
                                'approved' => 'Disetujui',
                                'ordered' => 'Diproses',
                                'partially_received' => 'Diterima Sebagian',
                                'completed' => 'Selesai',
                                'received' => 'Diterima',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($pr->status)
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="fw-medium">Rp {{ number_format($pr->total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('purchasing.purchase-requests.show', $pr->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($pr->status === 'draft')
                        <a href="{{ route('purchasing.purchase-requests.edit', $pr->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $pr->id }}" data-code="{{ $pr->code }}" data-bs-toggle="tooltip" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data purchase request</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#prTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: 6 }
        ],
        order: [[3, 'desc']]
    });

    $('.btn-delete').on('click', function () {
        var id = $(this).data('id');
        var code = $(this).data('code');
        Swal.fire({
            title: 'Hapus PR',
            text: 'Apakah Anda yakin ingin menghapus PR "' + code + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('/purchasing/purchase-requests') }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
