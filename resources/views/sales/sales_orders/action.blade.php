<a href="{{ route('sales.sales-orders.show', $o->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Detail">
    <i class="fas fa-eye"></i>
</a>
@if($o->status === 'draft')
<a href="{{ route('sales.sales-orders.edit', $o->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
    <i class="fas fa-edit"></i>
</a>
<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $o->id }}" data-code="{{ $o->code }}" data-bs-toggle="tooltip" title="Hapus">
    <i class="fas fa-trash"></i>
</button>
@endif
