<div class="inventory-show">
    @php
        $statusLabel = $item->status ?: ($item->quantity <= 0 ? 'Out of stock' : 'Available');
        $statusClass = strtolower(str_replace(' ', '-', $statusLabel));
    @endphp
    <div class="drawer-body">
        @if($item->image_path)
            <img class="drawer-img" src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" />
        @else
            <div class="drawer-img">{{ strtoupper(substr($item->name,0,1)) }}</div>
        @endif

        <div class="meta">
            <div class="name">{{ $item->name }}</div>
            <div class="drawer-sub">{{ $item->category ?? '—' }} · {{ $item->type ?? '—' }}</div>
            <div class="serial">Serial: <strong>{{ $item->serial ?? '—' }}</strong></div>

            <div class="meta-row">
                <div class="pill">Quantity: <strong>{{ $item->quantity }}</strong></div>
                <div class="pill">Location: <strong>{{ $item->location ?? '—' }}</strong></div>
                <div class="status-badge {{ $statusClass }}">{{ $statusLabel }}</div>
            </div>

            @if(!empty($item->notes))
                <div class="notes">{{ $item->notes }}</div>
            @endif

            <div class="inventory-actions">
                <a href="/inventory/{{ $item->id }}/request" class="btn request">Request</a>
                <a href="/inventory/{{ $item->id }}/edit" class="btn edit">Edit</a>
                <a href="/inventory" class="btn">Back</a>
            </div>
        </div>
    </div>
</div>
