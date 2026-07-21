@extends('layouts.app')

@section('title', 'Buat Invoice Penjualan')

@section('content')
@include('components.breadcrumb', ['breadcrumbs' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Sales'],
    ['label' => 'Invoice Penjualan', 'url' => route('sales.sales-invoices.index')],
    ['label' => 'Buat'],
]])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">Buat Invoice Penjualan</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('sales.sales-invoices.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ref. Sales Order</label>
                    <select name="sales_order_id" class="form-select @error('sales_order_id') is-invalid @enderror" id="soSelect">
                        <option value="">-- Pilih SO (Opsional) --</option>
                        @foreach($salesOrders as $so)
                        <option value="{{ $so->id }}" {{ old('sales_order_id') == $so->id ? 'selected' : '' }}>
                            {{ $so->code }} - {{ $so->customer->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('sales_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ref. Quotation</label>
                    <select name="quotation_id" class="form-select @error('quotation_id') is-invalid @enderror">
                        <option value="">-- Pilih Quotation (Opsional) --</option>
                        @foreach($quotations as $qt)
                        <option value="{{ $qt->id }}" {{ old('quotation_id') == $qt->id ? 'selected' : '' }}>
                            {{ $qt->code }} - {{ $qt->customer->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('quotation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tgl. Invoice <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                           value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-1">
                    <label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                           value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-semibold mb-3">Item Invoice</h6>
            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:22%">Produk</th>
                            <th style="width:18%">Deskripsi</th>
                            <th style="width:10%">Qty</th>
                            <th style="width:13%">Harga</th>
                            <th style="width:10%">Diskon %</th>
                            <th style="width:13%">Subtotal</th>
                            <th style="width:5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr id="noItemsRow">
                            <td colspan="7" class="text-center text-muted py-3">Belum ada item. Klik "Tambah Item" untuk menambahkan.</td>
                        </tr>
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
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-end text-muted">Subtotal</td>
                            <td style="width:150px">
                                <input type="text" id="displaySubtotal" class="form-control form-control-sm text-end" value="Rp 0" readonly>
                                <input type="hidden" name="subtotal" id="hiddenSubtotal" value="0">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end text-muted">Diskon</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="discount" id="discount" class="form-control text-end" value="{{ old('discount', 0) }}" min="0" step="any">
                                    <span class="input-group-text">Rp</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end text-muted">Pajak</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="tax" id="tax" class="form-control text-end" value="{{ old('tax', 0) }}" min="0" step="any">
                                    <span class="input-group-text">Rp</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end text-muted">Biaya Kirim</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="shipping_cost" id="shipping_cost" class="form-control text-end" value="{{ old('shipping_cost', 0) }}" min="0" step="any">
                                    <span class="input-group-text">Rp</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold">Total</td>
                            <td>
                                <input type="text" id="displayTotal" class="form-control form-control-sm text-end fw-bold" value="Rp 0" readonly>
                                <input type="hidden" name="total" id="hiddenTotal" value="0">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
                <a href="{{ route('sales.sales-invoices.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var itemIndex = 0;

    function calculateRow($row) {
        var qty = parseFloat($row.find('.qty').val()) || 0;
        var price = parseFloat($row.find('.price').val()) || 0;
        var discPct = parseFloat($row.find('.discount-pct').val()) || 0;
        var subtotal = qty * price * (1 - discPct / 100);
        $row.find('.subtotal').val(subtotal.toFixed(2));
        return subtotal;
    }

    function calculateTotal() {
        var subtotal = 0;
        $('#itemsBody .subtotal').each(function () {
            subtotal += parseFloat($(this).val()) || 0;
        });
        var discount = parseFloat($('#discount').val()) || 0;
        var tax = parseFloat($('#tax').val()) || 0;
        var shipping = parseFloat($('#shipping_cost').val()) || 0;
        var total = subtotal - discount + tax + shipping;

        $('#displaySubtotal').val('Rp ' + numberFormat(subtotal));
        $('#hiddenSubtotal').val(subtotal.toFixed(2));
        $('#displayTotal').val('Rp ' + numberFormat(total));
        $('#hiddenTotal').val(total.toFixed(2));
    }

    function numberFormat(n) {
        return n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $('#addItemBtn').on('click', function () {
        $('#noItemsRow').remove();
        var row = '<tr>';
        row += '<td>';
        row += '<select name="items[' + itemIndex + '][product_id]" class="form-select product-select">';
        row += '<option value="">-- Pilih Produk --</option>';
        @foreach($products as $product)
        row += '<option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">{{ $product->name }}</option>';
        @endforeach
        row += '</select></td>';
        row += '<td><input type="text" name="items[' + itemIndex + '][description]" class="form-control"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][quantity]" class="form-control qty" value="1" min="0.01" step="any"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][price]" class="form-control price" value="0" min="0" step="any"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][discount]" class="form-control discount-pct" value="0" min="0" max="100" step="any"></td>';
        row += '<td><input type="number" name="items[' + itemIndex + '][subtotal]" class="form-control subtotal" value="0" readonly></td>';
        row += '<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>';
        row += '</tr>';
        $('#itemsBody').append(row);
        itemIndex++;
    });

    $(document).on('input', '.qty, .price, .discount-pct', function () {
        var $row = $(this).closest('tr');
        calculateRow($row);
        calculateTotal();
    });

    $(document).on('change', '.product-select', function () {
        var $row = $(this).closest('tr');
        var selected = $(this).find(':selected');
        if (selected.val()) {
            var price = selected.data('price') || 0;
            $row.find('.price').val(price);
            calculateRow($row);
            calculateTotal();
        }
    });

    $('#soSelect').on('change', function () {
        var soId = $(this).val();
        if (!soId) return;
        $.ajax({
            url: '{{ url('/sales/sales-orders') }}/' + soId,
            type: 'GET',
            success: function (data) {
                if (data.items && data.items.length > 0) {
                    $('#itemsBody').empty();
                    itemIndex = 0;
                    $.each(data.items, function (i, item) {
                        var row = '<tr>';
                        row += '<td><select name="items[' + itemIndex + '][product_id]" class="form-select product-select">';
                        row += '<option value="">-- Pilih Produk --</option>';
                        @foreach($products as $product)
                        row += '<option value="{{ $product->id }}" ' + (item.product_id == {{ $product->id }} ? 'selected' : '') + ' data-price="{{ $product->selling_price }}">{{ $product->name }}</option>';
                        @endforeach
                        row += '</select></td>';
                        row += '<td><input type="text" name="items[' + itemIndex + '][description]" class="form-control" value="' + (item.description || '') + '"></td>';
                        row += '<td><input type="number" name="items[' + itemIndex + '][quantity]" class="form-control qty" value="' + item.quantity + '" min="0.01" step="any"></td>';
                        row += '<td><input type="number" name="items[' + itemIndex + '][price]" class="form-control price" value="' + item.price + '" min="0" step="any"></td>';
                        row += '<td><input type="number" name="items[' + itemIndex + '][discount]" class="form-control discount-pct" value="' + (item.discount || 0) + '" min="0" max="100" step="any"></td>';
                        row += '<td><input type="number" name="items[' + itemIndex + '][subtotal]" class="form-control subtotal" value="' + item.subtotal + '" readonly></td>';
                        row += '<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>';
                        row += '</tr>';
                        $('#itemsBody').append(row);
                        itemIndex++;
                    });
                    calculateTotal();
                }
            },
            error: function () {
                // silently fail
            }
        });
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

    $('#discount, #tax, #shipping_cost').on('input', function () {
        calculateTotal();
    });
});
</script>
@endpush
