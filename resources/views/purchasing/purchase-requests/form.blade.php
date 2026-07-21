@extends('layouts.app')

@section('title', $purchaseRequest ? 'Edit Purchase Request' : 'Buat Purchase Request')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Purchase Request', 'url' => route('purchasing.purchase-requests.index')],
    ['label' => $purchaseRequest ? 'Edit' : 'Buat'],
]])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">{{ $purchaseRequest ? 'Edit Purchase Request' : 'Buat Purchase Request' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ $purchaseRequest ? route('purchasing.purchase-requests.update', $purchaseRequest->id) : route('purchasing.purchase-requests.store') }}" method="POST">
            @csrf
            @if($purchaseRequest) @method('PUT') @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseRequest->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Departemen</label>
                    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $purchaseRequest->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Request <span class="text-danger">*</span></label>
                    <input type="date" name="request_date" class="form-control @error('request_date') is-invalid @enderror"
                           value="{{ old('request_date', $purchaseRequest->request_date ?? date('Y-m-d')) }}" required>
                    @error('request_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tgl. Diharapkan</label>
                    <input type="date" name="expected_date" class="form-control @error('expected_date') is-invalid @enderror"
                           value="{{ old('expected_date', $purchaseRequest->expected_date ?? '') }}">
                    @error('expected_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-semibold mb-3">Item Purchase Request</h6>
            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:25%">Produk</th>
                            <th style="width:20%">Deskripsi</th>
                            <th style="width:10%">Qty</th>
                            <th style="width:10%">Satuan</th>
                            <th style="width:15%">Harga</th>
                            <th style="width:15%">Subtotal</th>
                            <th style="width:5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @php
                            $items = old('items', $purchaseRequest->items ?? []);
                            $oldItems = old('items');
                            if ($oldItems) {
                                $items = [];
                                foreach ($oldItems as $i => $item) {
                                    $itemObj = new stdClass();
                                    $itemObj->product_id = $item['product_id'] ?? null;
                                    $itemObj->description = $item['description'] ?? '';
                                    $itemObj->quantity = $item['quantity'] ?? 1;
                                    $itemObj->unit = $item['unit'] ?? '';
                                    $itemObj->unit_price = $item['unit_price'] ?? 0;
                                    $itemObj->subtotal = $item['subtotal'] ?? 0;
                                    $items[] = $itemObj;
                                }
                            }
                        @endphp
                        @forelse($items as $i => $item)
                        <tr>
                            <td>
                                <select name="items[{{ $i }}][product_id]" class="form-select product-select">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="items[{{ $i }}][description]" class="form-control" value="{{ $item->description ?? '' }}"></td>
                            <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control qty" value="{{ $item->quantity ?? 1 }}" min="1" step="any"></td>
                            <td><input type="text" name="items[{{ $i }}][unit]" class="form-control unit" value="{{ $item->unit ?? '' }}"></td>
                            <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control price" value="{{ $item->unit_price ?? 0 }}" min="0" step="any"></td>
                            <td><input type="number" name="items[{{ $i }}][subtotal]" class="form-control subtotal" value="{{ $item->subtotal ?? 0 }}" readonly></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr id="noItemsRow">
                            <td colspan="7" class="text-center text-muted py-3">Belum ada item. Klik "Tambah Item" untuk menambahkan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end">
                                <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                    <i class="fas fa-plus me-1"></i>Tambah Item
                                </button>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-4 offset-md-8">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-end text-muted">Subtotal</td>
                            <td style="width:150px">
                                <input type="text" id="displaySubtotal" class="form-control form-control-sm text-end" value="Rp 0" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end text-muted">Diskon</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="discount" id="discount" class="form-control text-end" value="{{ old('discount', $purchaseRequest->discount ?? 0) }}" min="0" step="any">
                                    <span class="input-group-text">Rp</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end text-muted">Pajak</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="tax" id="tax" class="form-control text-end" value="{{ old('tax', $purchaseRequest->tax ?? 0) }}" min="0" step="any">
                                    <span class="input-group-text">Rp</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold">Total</td>
                            <td>
                                <input type="text" id="displayTotal" class="form-control form-control-sm text-end fw-bold" value="Rp 0" readonly>
                                <input type="hidden" name="total" id="hiddenTotal" value="{{ old('total', $purchaseRequest->total ?? 0) }}">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ $purchaseRequest ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('purchasing.purchase-requests.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var itemIndex = {{ count($items) }};

    function calculateRow($row) {
        var qty = parseFloat($row.find('.qty').val()) || 0;
        var price = parseFloat($row.find('.price').val()) || 0;
        var subtotal = qty * price;
        $row.find('.subtotal').val(subtotal);
        return subtotal;
    }

    function calculateTotal() {
        var subtotal = 0;
        $('#itemsBody .subtotal').each(function () {
            subtotal += parseFloat($(this).val()) || 0;
        });
        var discount = parseFloat($('#discount').val()) || 0;
        var tax = parseFloat($('#tax').val()) || 0;
        var total = subtotal - discount + tax;

        $('#displaySubtotal').val('Rp ' + numberFormat(subtotal));
        $('#displayTotal').val('Rp ' + numberFormat(total));
        $('#hiddenTotal').val(total);
    }

    function numberFormat(n) {
        return n.toLocaleString('id-ID');
    }

    $('#addItemBtn').on('click', function () {
        $('#noItemsRow').remove();
        var row = '<tr>';
        row += '<td>';
        row += '<select name="items[' + itemIndex + '][product_id]" class="form-select product-select">';
        row += '<option value="">-- Pilih Produk --</option>';
        @foreach($products as $product)
        row += '<option value="{{ $product->id }}">{{ $product->name }}</option>';
        @endforeach
        row += '</select></td>';
        row += '<td><input type="text" name="items[' + itemIndex + '][description]" class="form-control"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][quantity]" class="form-control qty" value="1" min="1" step="any"></td>';
        row += '<td><input type="text" name="items[' + itemIndex + '][unit]" class="form-control unit"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][unit_price]" class="form-control price" value="0" min="0" step="any"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][subtotal]" class="form-control subtotal" value="0" readonly></td>';
        row += '<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>';
        row += '</tr>';
        $('#itemsBody').append(row);
        itemIndex++;
    });

    $(document).on('input', '.qty, .price', function () {
        var $row = $(this).closest('tr');
        calculateRow($row);
        calculateTotal();
    });

    $(document).on('change', '.product-select', function () {
        var $row = $(this).closest('tr');
        var selected = $(this).find(':selected');
        if (selected.val()) {
            $.ajax({
                url: '{{ url('/master/produk') }}/' + selected.val() + '/detail',
                type: 'GET',
                success: function (product) {
                    $row.find('.unit').val(product.unit || '');
                    $row.find('.price').val(product.purchase_price || 0);
                    calculateRow($row);
                    calculateTotal();
                },
                error: function () {
                    // silently fail
                }
            });
        }
    });

    $(document).on('click', '.remove-item', function () {
        var $row = $(this).closest('tr');
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
                $row.remove();
                calculateTotal();
                if ($('#itemsBody tr').length === 0) {
                    $('#itemsBody').html('<tr id="noItemsRow"><td colspan="7" class="text-center text-muted py-3">Belum ada item</td></tr>');
                }
            }
        });
    });

    $('#discount, #tax').on('input', function () {
        calculateTotal();
    });

    calculateTotal();
});
</script>
@endpush
