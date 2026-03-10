<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\AccountRequest;
use App\Models\User;

class AccountRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function ensureAdmin()
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403);
        }
    }

    public function index()
    {
        $this->ensureAdmin();
        $requests = AccountRequest::orderByDesc('created_at')->paginate(25);
        return view('accounts.index', compact('requests'));
    }

    public function show(AccountRequest $accountRequest)
    {
        $this->ensureAdmin();
        return view('accounts.show', ['request' => $accountRequest]);
    }

    public function approve(AccountRequest $accountRequest)
    {
        $this->ensureAdmin();

        if ($accountRequest->status !== 'pending') {
            return back()->with('error', 'Account request is not pending.');
        }

        // create user record
        $randomPassword = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = User::create([
            'name' => $accountRequest->name,
            'email' => $accountRequest->email,
            'phone' => $accountRequest->phone,
            'department' => $accountRequest->department,
            'role' => $accountRequest->requested_role ?? 'requestor',
            'avatar' => $accountRequest->proof_path ?? null,
            'is_approved' => true,
            'password' => Hash::make($randomPassword),
        ]);

        $accountRequest->status = 'approved';
        $accountRequest->save();

        try {
            Mail::raw("Your account request has been approved. You may now login with email: {$user->email} and password: {$randomPassword}", function ($m) use ($user) {
                $m->to($user->email)->subject('Account request approved');
            });
        } catch (\Exception $e) {
            // ignore
        }

        return redirect('/accounts')->with('success', 'Account approved and user created.');
    }

    public function deny(AccountRequest $accountRequest, Request $request)
    {
        $this->ensureAdmin();

        if ($accountRequest->status !== 'pending') {
            return back()->with('error', 'Account request is not pending.');
        }

        $accountRequest->status = 'denied';
        $accountRequest->save();

        try {
            $reason = $request->input('reason') ?? 'Your account request was denied.';
            Mail::raw($reason, function ($m) use ($accountRequest) {
                $m->to($accountRequest->email)->subject('Account request denied');
            });
        } catch (\Exception $e) {
            // ignore
        }

        return redirect('/accounts')->with('success', 'Account request denied.');
    }
}
