@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Account Request: {{ $request->name }}</h1>

    <div class="card">
        <div class="card-body">
            <p><strong>Email:</strong> {{ $request->email }}</p>
            <p><strong>Department:</strong> {{ $request->department }}</p>
            <p><strong>Position:</strong> {{ $request->position }}</p>
            <p><strong>Phone:</strong> {{ $request->phone }}</p>
            <p><strong>Requested Role:</strong> {{ $request->requested_role }}</p>
            <p><strong>Message:</strong><br>{{ $request->message }}</p>
            <p><strong>Status:</strong> {{ $request->status }}</p>
            <div style="margin-top:12px">
                @if($request->status === 'pending')
                    <form action="/accounts/{{ $request->id }}/approve" method="POST" style="display:inline">@csrf<button class="btn btn-sm">Approve</button></form>
                    <form action="/accounts/{{ $request->id }}/deny" method="POST" style="display:inline;margin-left:6px">@csrf<button class="btn btn-sm">Deny</button></form>
                @endif
                <a href="/accounts" class="btn btn-sm" style="margin-left:8px">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
