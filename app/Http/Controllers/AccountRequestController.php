<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\AccountRequest;
use App\Models\User;
use App\Mail\AccountRequestDecision;

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
        return view('accounts.account_page', compact('requests'));
    }

    public function show(AccountRequest $accountRequest)
    {
        $this->ensureAdmin();
        // show route now uses modal/detail UI; preserve fallback to account_page with anchor
        return view('accounts.account_page', ['requests' => AccountRequest::where('id', $accountRequest->id)->paginate(25)]);
    }

    /**
     * Return JSON details for an account request (used by AJAX modal).
     */
    public function details(AccountRequest $accountRequest)
    {
        $this->ensureAdmin();
        $data = $accountRequest->toArray();
        if ($accountRequest->created_at) {
            // provide a machine-readable ISO timestamp in UTC so the browser
            // can convert it to the viewer's local timezone for correct display
            $data['created_at_utc'] = $accountRequest->created_at->toIso8601String();
            $data['created_at_iso'] = $accountRequest->created_at->toIso8601String();
            // keep a server-side display string (optional) for debugging/backup
            $data['created_at_display_server'] = $accountRequest->created_at->setTimezone(config('app.timezone') ?: 'UTC')->format('F j, Y g:i A');
            $data['admin_note'] = $accountRequest->admin_note ?? null;
        }
        return response()->json($data);
    }

    public function approve(AccountRequest $accountRequest, Request $request)
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

        $admin_note = $request->input('admin_note') ?? null;
        $accountRequest->status = 'approved';
        $accountRequest->admin_note = $admin_note;
        $accountRequest->save();

        try {
            $admin_note = $request->input('admin_note') ?? null;
            Mail::to($user->email)->queue(new AccountRequestDecision($accountRequest, 'approved', $admin_note, $randomPassword));
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

        // use the DB enum-friendly value 'rejected' (schema uses: pending, approved, rejected, cancelled)
        $accountRequest->status = 'rejected';
        $accountRequest->save();

        try {
            $admin_note = $request->input('admin_note') ?? $request->input('reason') ?? null;
            $accountRequest->admin_note = $admin_note;
            $accountRequest->status = 'rejected';
            $accountRequest->save();
            Mail::to($accountRequest->email)->queue(new AccountRequestDecision($accountRequest, 'denied', $admin_note));
        } catch (\Exception $e) {
            // ignore
        }

        return redirect('/accounts')->with('success', 'Account request denied.');
    }

    /**
     * Update the admin note for an account request (AJAX friendly).
     */
    public function updateNote(AccountRequest $accountRequest, Request $request)
    {
        $this->ensureAdmin();

        $note = $request->input('admin_note') ?? null;
        $accountRequest->admin_note = $note;
        $accountRequest->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'admin_note' => $accountRequest->admin_note]);
        }

        return back()->with('success', 'Admin note updated.');
    }
}
