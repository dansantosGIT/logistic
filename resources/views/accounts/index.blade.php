@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Account Requests</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Requested Role</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($requests as $r)
            <tr>
                <td>{{ $r->name }}</td>
                <td>{{ $r->email }}</td>
                <td>{{ $r->department }}</td>
                <td>{{ $r->requested_role }}</td>
                <td>{{ $r->status }}</td>
                <td>{{ $r->created_at->diffForHumans() }}</td>
                <td class="actions">
                    <a href="/accounts/{{ $r->id }}" class="btn btn-sm">View</a>
                    @if($r->status === 'pending')
                        <form action="/accounts/{{ $r->id }}/approve" method="POST" style="display:inline">@csrf<button class="btn btn-sm">Approve</button></form>
                        <form action="/accounts/{{ $r->id }}/deny" method="POST" style="display:inline;margin-left:6px">@csrf<button class="btn btn-sm">Deny</button></form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $requests->links() }}
</div>
@endsection
