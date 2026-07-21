@extends('layouts.app')

@section('title', 'Purchase Order')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Purchase Order'],
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
                    <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Diterima Sebagian</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
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
        <h5 class="mb-0 fw-semibold">Data Purchase Order</h5>
        <a href="{{ route('purchasing.purchase-orders.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat PO
        </a>
    </div>
    <div class="card-body">
        <table id="poTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode PO</th>
                    <th>Supplier</th>
                    <th>Tanggal</th>
                    <th>Gudang</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-medium">{{ $po->code }}</td>
                    <td>{{ $po->supplier->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                    <td>{{ $po->warehouse->name ?? '-' }}</td>
                    <td>
                        @php
                            $badge = match($po->status) {
                                'draft' => 'bg-secondary',
                                'submitted', 'pending_approval' => 'bg-warning text-dark',
                                'approved' => 'bg-info',
                                'ordered' => 'bg-primary',
                                'partially_received' => 'bg-warning text-dark',
                                'completed', 'received' => 'bg-success',
                                'rejected', 'cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $label = match($po->status) {
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
                                default => ucfirst($po->status)
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="fw-medium">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('purchasing.purchase-orders.show', $po->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($po->status === 'draft')
                        <a href="{{ route('purchasing.purchase-orders.edit', $po->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $po->id }}" data-code="{{ $po->code }}" data-bs-toggle="tooltip" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data purchase order</td>
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
    $('#poTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: 7 }
        ],
        order: [[3, 'desc']]
    });

    $('.btn-delete').on('click', function () {
        var id = $(this).data('id');
        var code = $(this).data('code');
        Swal.fire({
            title: 'Hapus PO',
            text: 'Apakah Anda yakin ingin menghapus PO "' + code + '"?',
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
                form.action = '{{ url('/purchasing/purchase-orders') }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
