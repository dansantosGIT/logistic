<div style="font-family:Inter,system-ui,Arial,Helvetica;color:#0f172a">
    <h2>New account request</h2>
    <p>A new account request was submitted by <strong>{{ $request->name }}</strong>.</p>
    <ul>
        <li><strong>Email:</strong> {{ $request->email }}</li>
        <li><strong>Phone:</strong> {{ $request->phone ?? '—' }}</li>
        <li><strong>Department:</strong> {{ $request->department ?? '—' }}</li>
        <li><strong>Requested role:</strong> {{ $request->requested_role ?? 'requestor' }}</li>
        <li><strong>Submitted:</strong> {{ $request->created_at }}</li>
    </ul>
    <p>Message:<br><pre style="white-space:pre-wrap">{{ $request->message ?? $request->justification ?? '—' }}</pre></p>
    <p>Please review the request in the admin panel: <a href="{{ url('/accounts') }}">View requests</a></p>
</div>
