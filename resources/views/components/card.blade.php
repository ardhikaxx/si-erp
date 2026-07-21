@props(['title' => '', 'icon' => '', 'class' => ''])

<div class="card {{ $class }} mb-4">
    @if ($title)
        <div class="card-header d-flex align-items-center py-3">
            @if ($icon)
                <i class="{{ $icon }} me-2"></i>
            @endif
            <h5 class="card-title mb-0">{{ $title }}</h5>
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
