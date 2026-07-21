@extends('layouts.app')

@section('title', 'Stock Opname')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Inventory'],
    ['label' => 'Stock Opname'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Gudang</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
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
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('inventory.stock-opnames.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Buat Stock Opname
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">Data Stock Opname</h5>
    </div>
    <div class="card-body">
        <table id="stockOpnameTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Gudang</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockOpnames as $so)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-medium">{{ $so->code }}</td>
                    <td>{{ $so->warehouse->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($so->date)->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $statusBadge = match($so->status) {
                                'draft' => 'bg-secondary',
                                'completed', 'received' => 'bg-success',
                                'cancelled' => 'bg-danger',
                                default => 'bg-warning text-dark'
                            };
                            $statusLabel = match($so->status) {
                                'draft' => 'Draft',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($so->status)
                            };
                        @endphp
                        <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        <a href="{{ route('inventory.stock-opnames.show', $so->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($so->status === 'draft')
                        <a href="{{ route('inventory.stock-opnames.edit', $so->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $so->id }}" data-code="{{ $so->code }}" data-bs-toggle="tooltip" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data stock opname</td>
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
    $('#stockOpnameTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: 5 }
        ]
    });

    $('.btn-delete').on('click', function () {
        var id = $(this).data('id');
        var code = $(this).data('code');
        Swal.fire({
            title: 'Hapus Stock Opname',
            text: 'Apakah Anda yakin ingin menghapus stock opname "' + code + '"?',
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
                form.action = '{{ url('/inventory/stock-opnames') }}/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
