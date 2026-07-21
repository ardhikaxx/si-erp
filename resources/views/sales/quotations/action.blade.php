<a href="{{ route('sales.quotations.show', $q->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Detail">
    <i class="fas fa-eye"></i>
</a>
@if(in_array($q->status, ['draft', 'sent']))
<a href="{{ route('sales.quotations.edit', $q->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit">
    <i class="fas fa-edit"></i>
</a>
<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $q->id }}" data-code="{{ $q->code }}" data-bs-toggle="tooltip" title="Hapus">
    <i class="fas fa-trash"></i>
</button>
@endif
