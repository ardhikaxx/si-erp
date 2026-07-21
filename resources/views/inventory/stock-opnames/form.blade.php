@extends('layouts.app')

@section('title', $stockOpname ? 'Edit Stock Opname' : 'Buat Stock Opname')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Inventory'],
    ['label' => 'Stock Opname', 'url' => route('inventory.stock-opnames.index')],
    ['label' => $stockOpname ? 'Edit' : 'Buat'],
]])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">{{ $stockOpname ? 'Edit Stock Opname' : 'Buat Stock Opname' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ $stockOpname ? route('inventory.stock-opnames.update', $stockOpname->id) : route('inventory.stock-opnames.store') }}" method="POST">
            @csrf
            @if($stockOpname) @method('PUT') @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Gudang <span class="text-danger">*</span></label>
                    <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" {{ $stockOpname ? 'disabled' : '' }}>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('warehouse_id', $stockOpname->warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                           value="{{ old('date', $stockOpname->date ?? date('Y-m-d')) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Keterangan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="1">{{ old('notes', $stockOpname->notes ?? '') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div id="opname-items-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0">Item Stock Opname</h6>
                    <button type="button" class="btn btn-success btn-sm" id="addItemBtn" disabled>
                        <i class="fas fa-plus me-1"></i>Tambah Item
                    </button>
                </div>

                @if($stockOpname)
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Produk</th>
                                <th style="width:15%">Stok Sistem</th>
                                <th style="width:15%">Stok Aktual</th>
                                <th style="width:15%">Selisih</th>
                                <th style="width:10%">Keterangan</th>
                                <th style="width:10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @forelse($stockOpname->items ?? [] as $item)
                            <tr>
                                <td>
                                    <select name="items[{{ $loop->index }}][product_id]" class="form-select product-select" required>
                                        <option value="{{ $item->product_id }}" selected>{{ $item->product->name ?? 'Produk' }}</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $loop->index }}][system_qty]" class="form-control system-qty" value="{{ $item->system_qty ?? 0 }}" step="any" readonly>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $loop->index }}][actual_qty]" class="form-control actual-qty" value="{{ $item->actual_qty ?? 0 }}" step="any">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $loop->index }}][difference]" class="form-control difference" value="{{ ($item->actual_qty ?? 0) - ($item->system_qty ?? 0) }}" step="any" readonly>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $loop->index }}][notes]" class="form-control" value="{{ $item->notes ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr id="noItemsRow">
                                <td colspan="6" class="text-center text-muted py-3">Pilih gudang terlebih dahulu untuk memuat item</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Produk</th>
                                <th style="width:15%">Stok Sistem</th>
                                <th style="width:15%">Stok Aktual</th>
                                <th style="width:15%">Selisih</th>
                                <th style="width:10%">Keterangan</th>
                                <th style="width:10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr id="noItemsRow">
                                <td colspan="6" class="text-center text-muted py-3">Pilih gudang terlebih dahulu untuk memuat item</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ $stockOpname ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('inventory.stock-opnames.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var itemIndex = {{ count($stockOpname->items ?? []) }};

    function loadProducts(warehouseId) {
        if (!warehouseId) {
            $('#itemsBody').html('<tr id="noItemsRow"><td colspan="6" class="text-center text-muted py-3">Pilih gudang terlebih dahulu untuk memuat item</td></tr>');
            $('#addItemBtn').prop('disabled', true);
            return;
        }

        $.ajax({
            url: '{{ url('/inventory/stock-opnames/get-products') }}/' + warehouseId,
            type: 'GET',
            success: function (products) {
                $('#itemsBody').empty();
                itemIndex = 0;
                if (products.length === 0) {
                    $('#itemsBody').html('<tr id="noItemsRow"><td colspan="6" class="text-center text-muted py-3">Tidak ada produk di gudang ini</td></tr>');
                    $('#addItemBtn').prop('disabled', true);
                    return;
                }
                $.each(products, function (i, p) {
                    addItemRow(p.id, p.name, p.stock);
                });
                $('#addItemBtn').prop('disabled', false);
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat data produk',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        });
    }

    function addItemRow(productId, productName, systemQty) {
        var row = '<tr>';
        row += '<td>';
        row += '<select name="items[' + itemIndex + '][product_id]" class="form-select product-select" required>';
        row += '<option value="' + productId + '" selected>' + productName + '</option>';
        row += '</select></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][system_qty]" class="form-control system-qty" value="' + systemQty + '" step="any" readonly></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][actual_qty]" class="form-control actual-qty" value="' + systemQty + '" step="any"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][difference]" class="form-control difference" value="0" step="any" readonly></td>';
        row += '<td><input type="text" name="items[' + itemIndex + '][notes]" class="form-control"></td>';
        row += '<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>';
        row += '</tr>';
        $('#itemsBody').append(row);
        itemIndex++;
    }

    $('#warehouse_id').on('change', function () {
        var wh = $(this).val();
        loadProducts(wh);
    });

    @if(!$stockOpname)
    var initialWarehouse = $('#warehouse_id').val();
    if (initialWarehouse) {
        loadProducts(initialWarehouse);
    }
    @endif

    $('#addItemBtn').on('click', function () {
        $('#noItemsRow').remove();
        var row = '<tr>';
        row += '<td>';
        row += '<select name="items[' + itemIndex + '][product_id]" class="form-select product-select" required>';
        row += '<option value="">-- Pilih Produk --</option>';
        row += '</select></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][system_qty]" class="form-control system-qty" value="0" step="any" readonly></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][actual_qty]" class="form-control actual-qty" value="0" step="any"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][difference]" class="form-control difference" value="0" step="any" readonly></td>';
        row += '<td><input type="text" name="items[' + itemIndex + '][notes]" class="form-control"></td>';
        row += '<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>';
        row += '</tr>';
        $('#itemsBody').append(row);

        var $newSelect = $('#itemsBody tr:last .product-select');
        var warehouseId = $('#warehouse_id').val();
        $.ajax({
            url: '{{ url('/inventory/stock-opnames/get-products') }}/' + warehouseId,
            type: 'GET',
            success: function (products) {
                $newSelect.empty().append('<option value="">-- Pilih Produk --</option>');
                $.each(products, function (i, p) {
                    $newSelect.append('<option value="' + p.id + '" data-stock="' + p.stock + '">' + p.name + '</option>');
                });
            }
        });

        $newSelect.on('change', function () {
            var selected = $(this).find(':selected');
            var stock = selected.data('stock') || 0;
            var row = $(this).closest('tr');
            row.find('.system-qty').val(stock);
            row.find('.actual-qty').val(stock);
            row.find('.difference').val(0);
        });

        itemIndex++;
    });

    $(document).on('input', '.actual-qty', function () {
        var row = $(this).closest('tr');
        var systemQty = parseFloat(row.find('.system-qty').val()) || 0;
        var actualQty = parseFloat($(this).val()) || 0;
        var diff = actualQty - systemQty;
        row.find('.difference').val(diff);
    });

    $(document).on('click', '.remove-item', function () {
        var row = $(this).closest('tr');
        Swal.fire({
            title: 'Hapus Item?',
            text: 'Item ini akan dihapus dari daftar',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                row.remove();
                if ($('#itemsBody tr').length === 0) {
                    $('#itemsBody').html('<tr id="noItemsRow"><td colspan="6" class="text-center text-muted py-3">Belum ada item</td></tr>');
                }
            }
        });
    });
});
</script>
@endpush
