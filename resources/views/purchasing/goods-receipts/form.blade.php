@extends('layouts.app')

@section('title', $goodsReceipt ? 'Edit Goods Receipt' : 'Buat Goods Receipt')

@section('content')
@include('components.breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Purchasing'],
    ['label' => 'Goods Receipt', 'url' => route('purchasing.goods-receipts.index')],
    ['label' => $goodsReceipt ? 'Edit' : 'Buat'],
]])

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">{{ $goodsReceipt ? 'Edit Goods Receipt' : 'Buat Goods Receipt' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ $goodsReceipt ? route('purchasing.goods-receipts.update', $goodsReceipt->id) : route('purchasing.goods-receipts.store') }}" method="POST">
            @csrf
            @if($goodsReceipt) @method('PUT') @endif

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Referensi PO <span class="text-danger">*</span></label>
                    <select name="purchase_order_id" id="purchase_order_id" class="form-select @error('purchase_order_id') is-invalid @enderror" {{ $goodsReceipt ? 'disabled' : '' }}>
                        <option value="">-- Pilih PO --</option>
                        @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}" {{ old('purchase_order_id', $goodsReceipt->purchase_order_id ?? request('po_id')) == $po->id ? 'selected' : '' }}>
                            {{ $po->code }} - {{ $po->supplier->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('purchase_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
                    <input type="date" name="received_date" class="form-control @error('received_date') is-invalid @enderror"
                           value="{{ old('received_date', $goodsReceipt->received_date ?? date('Y-m-d')) }}" required>
                    @error('received_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="1">{{ old('notes', $goodsReceipt->notes ?? '') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="fw-semibold mb-3">Item Penerimaan</h6>
            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30%">Produk</th>
                            <th style="width:15%">Qty PO</th>
                            <th style="width:15%">Sudah Diterima</th>
                            <th style="width:15%">Qty Diterima</th>
                            <th style="width:15%">Sisa PO</th>
                            <th style="width:10%">Satuan</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @if($goodsReceipt && $goodsReceipt->items->count() > 0)
                            @foreach($goodsReceipt->items as $i => $item)
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                                    <input type="hidden" name="items[{{ $i }}][po_item_id]" value="{{ $item->po_item_id }}">
                                    <span>{{ $item->product->name ?? '-' }}</span>
                                </td>
                                <td>{{ $item->poItem->quantity ?? 0 }}</td>
                                <td>{{ ($item->poItem->received_qty ?? 0) - $item->quantity }}</td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][quantity]" class="form-control received-qty"
                                           value="{{ $item->quantity }}" min="0" step="any"
                                           max="{{ ($item->poItem->quantity ?? 0) - (($item->poItem->received_qty ?? 0) - $item->quantity) }}" required>
                                </td>
                                <td class="remaining-qty">{{ ($item->poItem->quantity ?? 0) - ($item->poItem->received_qty ?? 0) }}</td>
                                <td>{{ $item->poItem->unit ?? '-' }}</td>
                            </tr>
                            @endforeach
                        @else
                        <tr id="noItemsRow">
                            <td colspan="6" class="text-center text-muted py-4">Pilih PO untuk memuat item</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ $goodsReceipt ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('purchasing.goods-receipts.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#purchase_order_id').on('change', function () {
        var poId = $(this).val();
        if (!poId) {
            $('#itemsBody').html('<tr id="noItemsRow"><td colspan="6" class="text-center text-muted py-4">Pilih PO untuk memuat item</td></tr>');
            return;
        }

        $.ajax({
            url: '{{ url('/purchasing/goods-receipts/get-po-items') }}/' + poId,
            type: 'GET',
            success: function (data) {
                $('#itemsBody').empty();
                if (data.items.length === 0) {
                    $('#itemsBody').html('<tr id="noItemsRow"><td colspan="6" class="text-center text-muted py-4">Tidak ada item yang dapat diterima</td></tr>');
                    return;
                }
                $.each(data.items, function (i, item) {
                    var remaining = item.quantity - item.received_qty;
                    if (remaining <= 0) return;
                    var row = '<tr>';
                    row += '<td>';
                    row += '<input type="hidden" name="items[' + i + '][product_id]" value="' + item.product_id + '">';
                    row += '<input type="hidden" name="items[' + i + '][po_item_id]" value="' + item.po_item_id + '">';
                    row += '<span>' + item.product_name + '</span></td>';
                    row += '<td>' + item.quantity + '</td>';
                    row += '<td>' + item.received_qty + '</td>';
                    row += '<td><input type="number" name="items[' + i + '][quantity]" class="form-control received-qty" value="' + remaining + '" min="0" max="' + remaining + '" step="any" required></td>';
                    row += '<td class="remaining-qty">' + remaining + '</td>';
                    row += '<td>' + (item.unit || '-') + '</td>';
                    row += '</tr>';
                    $('#itemsBody').append(row);
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat item PO',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        });
    });

    @if(!$goodsReceipt)
    var initialPo = $('#purchase_order_id').val();
    if (initialPo) {
        $('#purchase_order_id').trigger('change');
    }
    @endif

    $(document).on('input', '.received-qty', function () {
        var $row = $(this).closest('tr');
        var max = parseFloat($(this).attr('max')) || 0;
        var val = parseFloat($(this).val()) || 0;
        if (val > max) {
            $(this).val(max);
        }
    });
});
</script>
@endpush
