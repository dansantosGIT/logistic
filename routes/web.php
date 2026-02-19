<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EquipmentController;
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

// Update equipment (simple handler)
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
    ]);

    // handle image upload if provided
    if ($request->hasFile('image')) {
        try {
            $imagePath = $request->file('image')->store('equipment', 'public');
            // update image_path on model
            $item->image_path = $imagePath;
        } catch (Throwable $e) {
            // ignore storage failures but continue with other updates
        }
    }

    $item->fill($data);
    $item->save();
    return redirect('/inventory')->with('success', 'Equipment updated');
})->middleware('auth');

// Handle request submission (persist to storage/requests.json)
Route::post('/inventory/{id}/request', function (Request $request, $id) {
    $item = App\Models\Equipment::findOrFail($id);
    $rules = [
        'requester' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'role' => 'nullable|string|max:100',
        'reason' => 'nullable|string|max:1000',
        'department' => 'required_if:role,Operations|in:Alpha,Bravo,Charlie',
    ];
    // require return_date for non-consumable items
    if (strtolower(trim($item->type ?? '')) !== 'consumable') {
        $rules['return_date'] = 'required|date';
    } else {
        $rules['return_date'] = 'nullable|date';
    }
    $request->validate($rules);

    // persist to DB
    $user = auth()->user();
    $req = InventoryRequest::create([
        'uuid' => (string) uniqid('r', true),
        'item_id' => $item->id,
        'item_name' => $item->name,
        'requester' => $request->input('requester'),
        'requester_user_id' => $user ? $user->id : null,
        'quantity' => (int) $request->input('quantity', 1),
        'role' => $request->input('role') ?? null,
        'department' => $request->input('department') ?: null,
        'reason' => $request->input('reason'),
        'return_date' => $request->input('return_date') ?: null,
        'status' => 'pending',
    ]);

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

    // Handle approval - deduct from inventory
    if ($action === 'approve') {
        $quantity = intval($request->input('quantity', 0));
        $equipmentId = $request->input('equipment_id');
        $notes = $request->input('notes', '');

        if ($quantity > 0 && $equipmentId) {
            $equipment = Equipment::find($equipmentId);
            if ($equipment) {
                // Validate sufficient stock
                if ($equipment->quantity < $quantity) {
                    return response()->json(['error' => 'Insufficient stock'], 400);
                }
                // Deduct from inventory
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

Route::get('/requests', function (Request $request) {
    $tab = $request->query('tab', 'pending');

    $q = InventoryRequest::query();
    if ($tab === 'pending') {
        $q->where('status', 'pending');
    } elseif ($tab === 'waiting') {
        $q->where('status', 'approved');
    } elseif ($tab === 'history') {
        $q->whereIn('status', ['approved','rejected']);
    }

    $items = $q->orderBy('created_at', 'desc')->get();

    return view('requests', ['items' => $items, 'tab' => $tab]);
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
