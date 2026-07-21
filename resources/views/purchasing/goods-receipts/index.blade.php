@extends('layouts.app')

@section('title', 'Goods Receipt')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Goods Receipt'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
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
        <h5 class="mb-0 fw-semibold">Data Goods Receipt</h5>
        <a href="{{ route('purchasing.goods-receipts.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Buat GR
        </a>
    </div>
    <div class="card-body">
        <table id="grTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode GR</th>
                    <th>Referensi PO</th>
                    <th>Tanggal</th>
                    <th>Gudang</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($goodsReceipts as $gr)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-medium">{{ $gr->code }}</td>
                    <td>{{ $gr->purchaseOrder->code ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($gr->received_date)->format('d/m/Y') }}</td>
                    <td>{{ $gr->warehouse->name ?? '-' }}</td>
                    <td>
                        @php
                            $badge = match($gr->status) {
                                'draft' => 'bg-secondary',
                                'completed', 'received' => 'bg-success',
                                'cancelled' => 'bg-danger',
                                default => 'bg-warning text-dark'
                            };
                            $label = match($gr->status) {
                                'draft' => 'Draft',
                                'completed' => 'Selesai',
                                'received' => 'Diterima',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($gr->status)
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td>
                        <a href="{{ route('purchasing.goods-receipts.show', $gr->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($gr->status === 'draft')
                        <a href="{{ route('purchasing.goods-receipts.edit', $gr->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $gr->id }}" data-code="{{ $gr->code }}" data-bs-toggle="tooltip" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data goods receipt</td>
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
    $('#grTable').DataTable({
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
            title: 'Hapus GR',
            text: 'Apakah Anda yakin ingin menghapus GR "' + code + '"?',
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
                form.action = '{{ url('/purchasing/goods-receipts') }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
