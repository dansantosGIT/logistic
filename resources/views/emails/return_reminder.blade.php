<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>body{font-family:Arial,Helvetica,sans-serif;color:#0f172a}</style>
    <title>Return Reminder</title>
</head>
<body>
    <h2>{{ $item->request->item_name ?? ($item->equipment?->name ?? 'Equipment') }}</h2>
    @if($days < 0)
        <p>This item was expected to be returned {{ abs($days) }} day{{ abs($days) > 1 ? 's' : '' }} ago.</p>
    @elseif($days === 0)
        <p>This item is expected to be returned today.</p>
    @else
        <p>This is a reminder that the following item is expected to be returned in {{ $days }} day{{ $days>1 ? 's' : '' }}.</p>
    @endif
    <ul>
        <li><strong>Request:</strong> #{{ $item->request->id ?? $item->inventory_request_id }}</li>
        <li><strong>Item:</strong> {{ $item->equipment?->name ?? $item->request->item_name ?? '—' }}</li>
        <li><strong>Requested by:</strong> {{ $item->request->requester ?? '—' }}</li>
        <li><strong>Expected return:</strong> {{ $item->return_date?->format('F j, Y') ?? '—' }}</li>
    </ul>

    <p>
        You can view the request in the system here:
        <a href="{{ url('/requests/'.($item->request->uuid ?? $item->request->id)) }}">Open request</a>
    </p>

    <p>Thanks,<br/>Logistic team</p>
</body>
</html>
