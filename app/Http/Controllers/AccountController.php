<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function showJson($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'department' => $user->department,
            'role' => $user->role,
            'ops_role' => $user->ops_role,
            'active' => (bool) ($user->active ?? false),
            'created_at_iso' => $user->created_at ? $user->created_at->toIso8601String() : null,
            'created_at_display' => $user->created_at ? $user->created_at->setTimezone(config('app.timezone'))->format('F j, Y g:i A') : null,
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('accounts.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'ops_role' => ['nullable', Rule::in(['Alpha','Bravo','Charlie']), 'required_if:department,Operations'],
            'active' => 'sometimes|boolean',
        ]);

        if ($request->has('active')) {
            $data['active'] = $request->boolean('active');
        }

        $user->fill($data);
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'user' => $user->only(['id','name','email','department','role','ops_role','active'])]);
        }

        return redirect('/accounts')->with('status', 'Account updated');
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403);
        }

        $user = User::findOrFail($id);

        // Prevent self-delete
        if (auth()->id() === $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 422);
            }
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Protect configured MAIN ADMIN (by email) and primary user id=1 from deletion
        $mainAdminEmail = strtolower(env('MAIL_ADMIN', 'sjcdrrmdlogistics@gmail.com'));
        if (strtolower($user->email ?? '') === $mainAdminEmail || ($user->id ?? 0) === 1) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Main admin account cannot be deleted.'], 422);
            }
            return back()->with('error', 'Main admin account cannot be deleted.');
        }

        $user->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect('/accounts')->with('status', 'Account deleted');
    }

    /**
     * Toggle the active state of a user account (activate/deactivate).
     */
    public function toggle(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403);
        }

        $user = User::findOrFail($id);

        // Prevent self-deactivate (allow activating yourself if currently inactive)
        if (auth()->id() === $user->id && ($user->active ?? false)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'You cannot deactivate your own account.'], 422);
            }
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->active = !($user->active ?? false);
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'active' => (bool) $user->active]);
        }

        $msg = $user->active ? 'Account activated' : 'Account deactivated';
        $tab = $request->input('tab', 'accounts');
        return redirect('/accounts?tab=' . $tab)->with('status', $msg);
    }
}
