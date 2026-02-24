<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\AnalyticsController;
use App\Models\Equipment;
use Illuminate\Support\Facades\Storage;
use App\Models\InventoryRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/', function () {
    // show latest 5 equipment and simple stock analysis on the welcome page
    $recent = collect();
    $total_items = 0;
    $total_quantity = 0;
    $instock_count = 0;
    $low_count = 0;
    $out_count = 0;

    try {
        $recent = App\Models\Equipment::orderBy('created_at','desc')->limit(5)->get();
        $total_items = App\Models\Equipment::count();
        $total_quantity = App\Models\Equipment::sum('quantity');
        $instock_count = App\Models\Equipment::where('quantity', '>=', 10)->count();
        $low_count = App\Models\Equipment::where('quantity', '>', 0)->where('quantity', '<', 10)->count();
        $out_count = App\Models\Equipment::where('quantity', 0)->count();
    } catch (Throwable $e) {
        // equipment table may not exist yet (before migrate). swallow error and keep defaults.
        $recent = collect();
    }

    return view('welcome', compact('recent','total_items','total_quantity','instock_count','low_count','out_count'));
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

// Handle login POST
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $remember = $request->boolean('remember');

    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
});

// Handle register POST
Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:4'],
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    return redirect('/dashboard');
});

// Dashboard (protected)
Route::get('/dashboard', function () {
    $recent = collect();
    $total_items = 0;
    $total_quantity = 0;
    $instock_count = 0;
    $low_count = 0;
    $out_count = 0;
    try {
        $recent = App\Models\Equipment::orderBy('created_at','desc')->limit(8)->get();
        $total_items = App\Models\Equipment::count();
        $total_quantity = App\Models\Equipment::sum('quantity');
        $instock_count = App\Models\Equipment::where('quantity', '>=', 10)->count();
        $low_count = App\Models\Equipment::where('quantity', '>', 0)->where('quantity', '<', 10)->count();
        $out_count = App\Models\Equipment::where('quantity', 0)->count();
    } catch (Throwable $e) {
        $recent = collect();
    }

    return view('dashboard', compact('recent','total_items','total_quantity','instock_count','low_count','out_count'));
})->middleware('auth');

// Inventory (protected)
Route::get('/inventory', function () {
    $equipment = Equipment::orderBy('created_at','desc')->paginate(25);
    return view('inventory', compact('equipment'));
})->middleware('auth');

// Inventory search (AJAX): returns rendered table rows for the query
Route::get('/inventory/search', function (Request $request) {
    $q = trim((string) $request->query('q', ''));
    $page = max(1, (int) $request->query('page', 1));

    // If query too short, return empty result set to avoid heavy queries
    if ($q === '' || strlen($q) < 2) {
        $equipment = Equipment::orderBy('created_at','desc')->paginate(25);
        // Return the first page rows to keep behavior predictable
        return view('partials.inventory_rows', compact('equipment'));
    }

    $items = Equipment::query()
        ->where('name', 'like', "%{$q}%")
        ->orWhere('category', 'like', "%{$q}%")
        ->orWhere('location', 'like', "%{$q}%")
        ->orWhere('serial', 'like', "%{$q}%")
        ->orWhere('tag', 'like', "%{$q}%")
        ->orderBy('created_at', 'desc')
        ->paginate(25, ['*'], 'page', $page);

    return view('partials.inventory_rows', ['equipment' => $items]);
})->middleware('auth');

// Add equipment form
Route::get('/inventory/add', function () {
    // provide distinct existing categories from equipment table so new categories appear in the select
    $categories = [];
    try {
        $categories = App\Models\Equipment::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    } catch (Throwable $e) {
        $categories = collect();
    }

    return view('inventory_add', compact('categories'));
})->middleware('auth');

// Store new equipment
Route::post('/inventory/add', [EquipmentController::class, 'store'])->middleware('auth');

// Edit equipment form
Route::get('/inventory/{id}/edit', function ($id) {
    $item = App\Models\Equipment::findOrFail($id);
    return view('inventory_edit', compact('item'));
})->middleware('auth');

// Request equipment form
Route::get('/inventory/{id}/request', function ($id) {
    $item = App\Models\Equipment::findOrFail($id);
    return view('inventory_request', compact('item'));
})->middleware('auth');

// Update equipment (handler with robust image handling)
Route::post('/inventory/{id}/update', function (Request $request, $id) {
    $item = App\Models\Equipment::findOrFail($id);
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'category' => 'nullable|string|max:255',
        'type' => 'nullable|string|max:255',
        'location' => 'nullable|string|max:255',
        'quantity' => 'nullable|integer|min:0',
        'date_added' => 'nullable|date',
        'notes' => 'nullable|string|max:500',
        'image' => 'nullable|file|image|max:5120',
        'existing_image' => 'nullable|string'
    ]);

    // remove image from $data so fill() doesn't attempt to set it
    if (array_key_exists('image', $data)) {
        unset($data['image']);
    }

    // handle image upload if provided and valid
    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        try {
            $newPath = $request->file('image')->store('equipment', 'public');
            if ($newPath) {
                // delete old image if it exists and is different
                try {
                    if ($item->image_path && $item->image_path !== $newPath && Storage::disk('public')->exists($item->image_path)) {
                        Storage::disk('public')->delete($item->image_path);
                    }
                } catch (Throwable $__e) {
                    // ignore deletion errors
                }
                $item->image_path = $newPath;
            }
        } catch (Throwable $e) {
            // ignore storage failures but continue with other updates
        }
    }

    // If no new upload but form provided existing_image, preserve it
    if ((!$request->hasFile('image') || !$request->file('image')->isValid()) && $request->filled('existing_image')) {
        // only set if model doesn't already have a value
        if (!$item->image_path) {
            $item->image_path = $request->input('existing_image');
        }
    }

    $item->fill($data);
    $item->save();

    // Redirect back to edit page so user sees the updated preview
    return redirect('/inventory/' . $item->id . '/edit')->with('success', 'Equipment updated');
})->middleware('auth');

// Delete equipment (supports AJAX fetch deletion and form fallback)
// Accept both POST and DELETE so method-spoofed forms and direct DELETE requests work
Route::match(['post','delete'], '/inventory/{id}/delete', function (Request $request, $id) {
    $item = App\Models\Equipment::findOrFail($id);
    try {
        if ($item->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($item->image_path);
        }
    } catch (Throwable $__e) {
        // ignore image deletion errors
    }

    $item->delete();

    // If client expects JSON (AJAX), return JSON OK so frontend can handle UI updates
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json(['ok' => true]);
    }

    return redirect('/inventory')->with('success', 'Equipment deleted');
})->middleware('auth');

// Handle request submission (persist to storage/requests.json)
Route::post('/inventory/{id}/request', function (Request $request, $id) {
    $item = App\Models\Equipment::findOrFail($id);

    $rules = [
        'requester' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'role' => 'required|string|max:100',
        'role_other' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:100',
        'reason' => 'nullable|string|max:1000',
    ];

    // require return_date for non-consumable items
    if (strtolower(trim($item->type ?? '')) !== 'consumable') {
        $rules['return_date'] = 'required|date';
    } else {
        $rules['return_date'] = 'nullable|date';
    }

    // additional conditional requirements
    if ($request->input('role') === 'Others') {
        $rules['role_other'] = 'required|string|max:255';
    }
    // Require department for staff and operations roles
    if (in_array($request->input('role'), ['Employee','Volunteer','Intern','Operations'])) {
        $rules['department'] = 'required|string|max:100';
    }

    $request->validate($rules);

    // ensure requested quantity does not exceed available stock
    $requestedQty = (int) $request->input('quantity', 1);
    if ($requestedQty > ($item->quantity ?? 0)) {
        return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock.'])->withInput();
    }

    // persist to DB
    $user = auth()->user();
    $departmentValue = $request->input('department');

    // build payload and only include `department` if the column exists in DB
    $payload = [
        'uuid' => (string) uniqid('r', true),
        'item_id' => $item->id,
        'item_name' => $item->name,
        'requester' => $request->input('requester'),
        'requester_user_id' => $user ? $user->id : null,
        'quantity' => (int) $request->input('quantity', 1),
        'role' => $request->input('role') ?? null,
        'reason' => $request->input('reason'),
        'return_date' => $request->input('return_date') ?: null,
        'status' => 'pending',
    ];

    try {
        if (\Illuminate\Support\Facades\Schema::hasColumn('inventory_requests', 'department')) {
            $payload['department'] = $departmentValue ?: null;
        }
    } catch (Throwable $e) {
        // schema check failed (e.g., DB not available) — skip adding department
    }

    $req = InventoryRequest::create($payload);

    // append role_other into reason for admin visibility
    if ($request->filled('role_other')) {
        $req->reason = trim(($req->reason ? $req->reason . "\n" : '') . "Role detail: " . $request->input('role_other'));
        $req->save();
    }

    // also append to legacy JSON for compatibility
    try {
        $path = 'requests.json';
        $list = [];
        if (Storage::exists($path)) {
            $list = json_decode(Storage::get($path), true) ?: [];
        }
        $entry = $req->toArray();
        $entry['id'] = $req->uuid;
        array_unshift($list, $entry);
        Storage::put($path, json_encode($list, JSON_PRETTY_PRINT));
    } catch (\Throwable $e) {
        // ignore legacy write failures
    }

    return redirect('/inventory')->with('success', 'Request submitted');
})->middleware('auth');

// Notifications: list recent requests (JSON)
Route::get('/notifications/requests', function (Request $request) {
    $user = auth()->user();

    // If DB table empty but legacy JSON exists, import it once
    try {
        $dbCount = InventoryRequest::count();
    } catch (\Throwable $e) {
        $dbCount = 0;
    }
    $path = 'requests.json';
    if ($dbCount === 0 && Storage::exists($path)) {
        try {
            $list = json_decode(Storage::get($path), true) ?: [];
            foreach ($list as $l) {
                InventoryRequest::firstOrCreate(
                    ['uuid' => $l['id'] ?? (string) uniqid('r', true)],
                    [
                        'item_id' => $l['item_id'] ?? null,
                        'item_name' => $l['item_name'] ?? null,
                        'requester' => $l['requester'] ?? null,
                        'requester_user_id' => $l['requester_user_id'] ?? null,
                        'quantity' => $l['quantity'] ?? 1,
                        'role' => $l['role'] ?? null,
                        'department' => $l['department'] ?? null,
                        'reason' => $l['reason'] ?? null,
                        'return_date' => $l['return_date'] ?? null,
                        'status' => $l['status'] ?? 'pending',
                        'created_at' => $l['created_at'] ?? now()->toDateTimeString(),
                        'updated_at' => $l['updated_at'] ?? now()->toDateTimeString(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            // ignore import failures
        }
    }

    // build query from DB
    // consider user id 1 or name 'admin' as admin for now
    $isAdmin = $user && ( ($user->id ?? 0) === 1 || strcasecmp($user->name ?? '', 'admin') === 0 );
    if ($isAdmin) {
        $items = InventoryRequest::where('status', 'pending')->orderBy('created_at','desc')->limit(8)->get();
    } else {
        $items = InventoryRequest::where('requester_user_id', $user ? $user->id : 0)
            ->where('status', '!=', 'pending')
            ->orderBy('created_at','desc')
            ->limit(8)->get();
    }

    return response()->json([
        'count' => $items->count(),
        'items' => $items->map(function($r){
            return [
                'id' => $r->uuid,
                'item_name' => $r->item_name,
                'requester' => $r->requester,
                'department' => $r->department ?? null,
                'status' => $r->status,
                'created_at' => $r->created_at->toDateTimeString(),
            ];
        })->toArray(),
    ]);
})->middleware('auth');

// Analytics: department requests (used by dashboard Chart.js)
Route::get('/api/analytics/department-requests', [AnalyticsController::class, 'departmentRequests'])->middleware('auth');


// Notifications action: approve or reject (admin only)
Route::post('/notifications/requests/{id}/action', function (Request $request, $id) {
    $user = auth()->user();
    $isAdmin = $user && ( ($user->id ?? 0) === 1 || strcasecmp($user->name ?? '', 'admin') === 0 );
    if (!$isAdmin) {
        return response()->json(['error' => 'Forbidden'], 403);
    }
    $action = $request->input('action');
    if (!in_array($action, ['approve','reject'])) {
        return response()->json(['error' => 'Invalid action'], 400);
    }

    $r = InventoryRequest::where('uuid', $id)->first();
    if (!$r) {
        return response()->json(['error' => 'Not found'], 404);
    }

    // Support per-item approvals when `request_item_id` is provided.
    $requestItemId = $request->input('request_item_id');
    $equipmentId = $request->input('equipment_id');
    $notes = $request->input('notes', '');
    $quantity = intval($request->input('quantity', 0));

    // If the client sent a `request_item_id` key (including null/empty), treat this as a per-item request.
    if ($request->exists('request_item_id')) {
        // validate presence (non-empty, non-null)
        if ($requestItemId === null || $requestItemId === '') {
            return response()->json(['error' => 'request_item_id missing'], 400);
        }
        
        // continue handling per-item
        
        
    
        $item = \App\Models\InventoryRequestItem::find($requestItemId);
        
        if (!$item || $item->inventory_request_id != $r->id) {
            return response()->json(['error' => 'Request item not found'], 404);
        }

        if ($action === 'approve') {
            $issueQty = $quantity > 0 ? $quantity : intval($item->quantity);
            $equipment = $equipmentId ? Equipment::find($equipmentId) : ($item->equipment_id ? Equipment::find($item->equipment_id) : null);
            if ($issueQty > 0 && $equipment) {
                if ($equipment->quantity < $issueQty) {
                    return response()->json(['error' => 'Insufficient stock'], 400);
                }
                $equipment->quantity -= $issueQty;
                $equipment->save();
                $item->issued_quantity = $issueQty;
            }
            $item->status = 'approved';
        } else {
            $item->status = 'rejected';
        }

        $item->handled_by = $user->id ?? null;
        $item->handled_at = now();
        $item->save();

        // Recompute parent status based on child items
        $total = $r->items()->count();
        $approved = $r->items()->where('status', 'approved')->count();
        $rejected = $r->items()->where('status', 'rejected')->count();

        if ($total > 0) {
            if ($approved === $total) $r->status = 'approved';
            elseif ($rejected === $total) $r->status = 'rejected';
            else $r->status = 'partial';
        }
        $r->handled_by = $user->id ?? null;
        $r->updated_at = now();
        $r->save();

        return response()->json(['ok' => true]);
    }

    // Fallback: act on parent request (legacy single-item behavior)
    if ($action === 'approve') {
        if ($quantity > 0 && $equipmentId) {
            $equipment = Equipment::find($equipmentId);
            if ($equipment) {
                if ($equipment->quantity < $quantity) {
                    return response()->json(['error' => 'Insufficient stock'], 400);
                }
                $equipment->quantity -= $quantity;
                $equipment->save();
            }
        }
        $r->status = 'approved';
    } else {
        $r->status = 'rejected';
    }

    $r->handled_by = $user->id ?? null;
    $r->updated_at = now();
    $r->save();

    return response()->json(['ok' => true]);
})->middleware('auth');

// Mark a request as returned (admin only)
Route::post('/requests/{id}/return', function (Request $request, $id) {
    $user = auth()->user();
    $isAdmin = $user && ( ($user->id ?? 0) === 1 || strcasecmp($user->name ?? '', 'admin') === 0 );
    if (!$isAdmin) {
        return back()->withErrors(['permission' => 'Forbidden']);
    }

    $r = InventoryRequest::where('uuid', $id)->firstOrFail();

    // Only approved, non-consumable requests may be marked returned
    if ($r->status !== 'approved') {
        return back()->withErrors(['status' => 'Only approved requests can be marked returned.']);
    }

    $equipment = $r->item_id ? Equipment::find($r->item_id) : null;
    if ($equipment && strtolower(trim($equipment->type ?? '')) === 'consumable') {
        return back()->withErrors(['status' => 'Consumable requests do not require return.']);
    }

    // mark returned
    $r->status = 'returned';
    $r->handled_by = $user->id ?? null;
    $r->updated_at = now();
    $r->save();

    // If this is a multi-item request, update each child item and restore stock per item
    try {
        if (isset($r->items) && is_countable($r->items) && $r->items->count() > 0) {
            foreach ($r->items as $it) {
                try {
                    $equip = $it->equipment_id ? Equipment::find($it->equipment_id) : null;
                    // determine quantity to restore: prefer issued_quantity, fall back to requested quantity
                    $restoreQty = intval($it->issued_quantity ?? $it->quantity ?? 0);
                    if ($equip && $restoreQty > 0) {
                        $equip->quantity = intval($equip->quantity ?? 0) + $restoreQty;
                        $equip->save();
                    }
                    $it->status = 'returned';
                    $it->handled_by = $user->id ?? null;
                    $it->handled_at = now();
                    $it->save();
                } catch (Throwable $_) {
                    // continue with other items even if one fails
                }
            }
        } else {
            // single-item (legacy) behavior: restore stock for parent equipment if present
            if ($equipment) {
                $qty = intval($r->quantity ?? 0);
                if ($qty > 0) {
                    $equipment->quantity = intval($equipment->quantity ?? 0) + $qty;
                    $equipment->save();
                }
            }
        }
    } catch (Throwable $e) {
        // ignore stock restore failures but keep request marked returned
    }

    return back()->with('success', 'Request marked returned');
})->middleware('auth');

Route::get('/requests', function (Request $request) {
    $tab = $request->query('tab', 'pending');

    $q = InventoryRequest::query();
    if ($tab === 'pending') {
        $q->where('status', 'pending');
    } elseif ($tab === 'waiting') {
        // Only show approved requests for non-consumable items (waiting for return)
        $q->where('status', 'approved')
          ->where(function($sub){
              $sub->whereHas('equipment', function($qq){
                  $qq->whereRaw('LOWER(COALESCE(`type`, "")) <> ?', ['consumable']);
              })->orWhereHas('items', function($qq){
                  $qq->whereHas('equipment', function($q2){
                      $q2->whereRaw('LOWER(COALESCE(`type`, "")) <> ?', ['consumable']);
                  });
              });
          });
    } elseif ($tab === 'history') {
        // History includes rejected, returned, and approved (if desired)
        $q->whereIn('status', ['approved','rejected','returned']);
    }

    $items = $q->orderBy('created_at', 'desc')->get();

    return view('requests', ['items' => $items, 'tab' => $tab]);
})->middleware('auth');

// Request multiple items (form)
Route::get('/requests/multiple', function () {
    $equipment = Equipment::orderBy('name')->get();
    return view('requests_multiple', compact('equipment'));
})->middleware('auth');

// Handle multiple requests submission
Route::post('/requests/multiple', function (Request $request) {
    $data = $request->all();
    $rules = [
        'requester' => 'required|string|max:255',
        'role' => 'required|string|max:100',
        'role_other' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:100',
        'items' => 'required|array|min:1',
        'items.*.equipment_id' => 'required|integer|exists:equipment,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.notes' => 'nullable|string|max:1000',
        'items.*.return_date' => 'nullable|date',
    ];

    if ($request->input('role') === 'Others') {
        $rules['role_other'] = 'required|string|max:255';
    }

    $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // server-side quantity checks
    $items = $request->input('items', []);
    $errors = [];
    foreach ($items as $idx => $it) {
        $equip = Equipment::find($it['equipment_id']);
        if (!$equip) {
            $errors["items.$idx.equipment_id"] = 'Selected equipment not found.';
            continue;
        }
        $reqQty = intval($it['quantity'] ?? 0);
        if ($reqQty > intval($equip->quantity ?? 0)) {
            $errors["items.$idx.quantity"] = 'Requested quantity exceeds available stock for "' . $equip->name . '".';
        }
        // require return_date for non-consumable items
        $type = strtolower(trim($equip->type ?? ''));
        if ($type !== 'consumable') {
            $rd = trim($it['return_date'] ?? '');
            if (empty($rd)) {
                $errors["items.$idx.return_date"] = 'Return date is required for non-consumable item "' . $equip->name . '".';
            } else {
                try {
                    \Carbon\Carbon::parse($rd);
                } catch (Throwable $e) {
                    $errors["items.$idx.return_date"] = 'Return date is not a valid date for "' . $equip->name . '".';
                }
            }
        }
    }
    if (!empty($errors)) {
        return back()->withErrors($errors)->withInput();
    }

    $user = auth()->user();

    // Create a parent InventoryRequest to group these items
    \DB::beginTransaction();
    try {
        $totalQty = array_sum(array_map(function($i){ return intval($i['quantity'] ?? 0); }, $items));

        $parent = InventoryRequest::create([
            'uuid' => (string) uniqid('R', true),
            'item_id' => null,
            'item_name' => 'Multiple items',
            'requester' => $request->input('requester'),
            'requester_user_id' => $user ? $user->id : null,
            'quantity' => $totalQty > 0 ? $totalQty : 1,
            'role' => $request->input('role') ?? null,
            'department' => $request->input('department') ?: null,
            'reason' => null,
            'return_date' => null,
            'status' => 'pending',
        ]);

        foreach ($items as $it) {
            \App\Models\InventoryRequestItem::create([
                'inventory_request_id' => $parent->id,
                'equipment_id' => $it['equipment_id'],
                'quantity' => intval($it['quantity']),
                'notes' => $it['notes'] ?? null,
                'return_date' => (!empty($it['return_date']) ? $it['return_date'] : null),
                'location' => $it['location'] ?? null,
            ]);
        }

        // append parent to legacy JSON as well
        try {
            $path = 'requests.json';
            $list = [];
            if (Storage::exists($path)) {
                $list = json_decode(Storage::get($path), true) ?: [];
            }
            $entry = $parent->toArray();
            $entry['id'] = $parent->uuid;
            array_unshift($list, $entry);
            Storage::put($path, json_encode($list, JSON_PRETTY_PRINT));
        } catch (Throwable $e) {
            // ignore
        }

        \DB::commit();
    } catch (Throwable $e) {
        \DB::rollBack();
        try { \Log::error('requests/multiple create error', ['exception' => $e]); } catch (Throwable $_) {}
        return back()->withErrors(['server' => 'Failed to create request group'])->withInput();
    }

    return redirect('/requests')->with('success', 'Request submitted');
})->middleware('auth');

// Show single request (detail / review)
Route::get('/requests/{id}', function (Request $request, $id) {
    $r = InventoryRequest::where('uuid', $id)->firstOrFail();
    // Load equipment relationship
    $equipment = $r->item_id ? Equipment::find($r->item_id) : null;
    $user = auth()->user();
    $isAdmin = $user && ( ($user->id ?? 0) === 1 || strcasecmp($user->name ?? '', 'admin') === 0 );
    return view('requests_show', ['r' => $r, 'equipment' => $equipment, 'isAdmin' => $isAdmin]);
})->middleware('auth');
// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});
