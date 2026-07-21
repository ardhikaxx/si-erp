@extends('layouts.app')

@section('title', 'Stok Masuk')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Inventory'],
    ['label' => 'Stok Masuk'],
]])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">Form Stok Masuk</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.stock-in.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Produk <span class="text-danger">*</span></label>
                    <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">-- Cari & Pilih Produk --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->code ? '['.$product->code.'] ' : '' }}{{ $product->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gudang <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                           value="{{ old('quantity', 1) }}" min="1" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga Satuan</label>
                    <input type="number" name="unit_price" class="form-control @error('unit_price') is-invalid @enderror"
                           value="{{ old('unit_price', 0) }}" min="0" step="any">
                    @error('unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">No. Referensi</label>
                    <input type="text" name="reference_no" class="form-control @error('reference_no') is-invalid @enderror"
                           value="{{ old('reference_no') }}">
                    @error('reference_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="1">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Stok Masuk
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">Riwayat Stok Masuk</h5>
    </div>
    <div class="card-body">
        <table id="stockInTable" class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Gudang</th>
                    <th>Jumlah</th>
                    <th>Referensi</th>
                    <th>Catatan</th>
                    <th>Pengguna</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockIns as $si)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($si->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $si->product->name ?? '-' }}</td>
                    <td>{{ $si->warehouse->name ?? '-' }}</td>
                    <td class="fw-medium text-success">+{{ $si->quantity }}</td>
                    <td>{{ $si->reference_no ?? '-' }}</td>
                    <td>{{ $si->notes ?? '-' }}</td>
                    <td>{{ $si->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat stok masuk</td>
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
    $('#stockInTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: [6, 7] }
        ],
        order: [[1, 'desc']]
    });
});
</script>
@endpush
