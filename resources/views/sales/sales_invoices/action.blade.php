<a href="{{ route('sales.sales-invoices.show', $i->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Detail">
    <i class="fas fa-eye"></i>
</a>
@if($i->payment_status !== 'paid' && $i->status !== 'cancelled')
<a href="{{ route('sales.sales-invoices.edit', $i->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
    <i class="fas fa-edit"></i>
</a>
<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $i->id }}" data-code="{{ $i->code }}" data-bs-toggle="tooltip" title="Hapus">
    <i class="fas fa-trash"></i>
</button>
@endif
