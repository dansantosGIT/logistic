<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountRequestReceived;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\AnalyticsController;
use App\Models\Equipment;
use Illuminate\Support\Facades\Storage;
use App\Models\InventoryRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\VehicleMonitoringReport;

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
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

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
    // basic validation for required signup fields
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'string', 'max:30'],
        'department' => ['required', 'string', 'max:191'],
        'role' => ['required', 'in:admin,requestor'],
        'password' => ['required', 'string', 'min:4', 'max:8', 'confirmed'],
        'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        'website' => ['nullable', 'max:0'], // honeypot must be empty
    ]);

    // optional reCAPTCHA validation if secret is configured
    if (env('RECAPTCHA_SECRET') && $request->filled('g-recaptcha-response')) {
        $resp = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!$resp->ok() || !($resp->json('success') ?? false)) {
            return back()->withErrors(['recaptcha' => 'reCAPTCHA verification failed'])->withInput();
        }
    }

    $proofPath = null;
    if ($request->hasFile('avatar')) {
        $proofPath = $request->file('avatar')->store('account_requests', 'public');
    }

    // prevent duplicate requests when a user already exists with that email
    if (User::where('email', $data['email'])->exists()) {
        return back()->withErrors(['email' => 'An account with this email already exists.'])->withInput();
    }

    // create or reuse an AccountRequest record. If a previous request was rejected,
    // update it with the new submission and mark it pending instead of inserting
    // a duplicate (preserves unique index and keeps history compact).
    $existing = \App\Models\AccountRequest::where('email', $data['email'])->first();
    // If there's an existing pending or approved request, do not allow duplicate submissions.
    if ($existing && in_array($existing->status, ['pending', 'approved'])) {
        return back()->withErrors(['email' => 'A request for this email is already pending review.'])->withInput();
    }

    if ($existing && $existing->status === 'rejected') {
        $existing->name = $data['name'];
        $existing->password_hash = Hash::make($data['password']);
        $existing->department = $data['department'];
        $existing->position = $request->input('position') ?? null;
        $existing->phone = $data['phone'];
        $existing->message = $request->input('message') ?? null;
        $existing->proof_path = $proofPath;
        $existing->status = 'pending';
        $existing->requested_role = $data['role'];
        $existing->justification = $request->input('justification') ?? null;
        $existing->save();
        $ar = $existing;
    } else {
        // create an AccountRequest record (tests and legacy code expect this table)
        $ar = \App\Models\AccountRequest::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'department' => $data['department'],
            'position' => $request->input('position') ?? null,
            'phone' => $data['phone'],
            'message' => $request->input('message') ?? null,
            'proof_path' => $proofPath,
            'status' => 'pending',
            'requested_role' => $data['role'],
            'justification' => $request->input('justification') ?? null,
        ]);
    }

    // notify main admin (configurable via MAIL_ADMIN, default to provided admin email)
    $mainAdmin = env('MAIL_ADMIN', 'sjcdrrmdlogistics@gmail.com');
    try {
        Mail::to($mainAdmin)->queue(new AccountRequestReceived($ar));
    } catch (\Exception $e) {
        // non-fatal
    }

    // notify existing approved admins by email (if any)
    $adminEmails = User::where('role', 'admin')->where('is_approved', true)->pluck('email')->toArray();
    if (!empty($adminEmails)) {
        foreach ($adminEmails as $addr) {
            try {
                Mail::to($addr)->queue(new AccountRequestReceived($ar));
            } catch (\Exception $e) {
                // non-fatal: don't block registration if mail fails
            }
        }
    }

    return back()->with('success', 'Registration successful — an admin will review your request and you will receive an email with the outcome.');
});

// Dashboard (protected)
Route::get('/dashboard', function () {
    $recent = collect();
    $recent_maintenances = collect();
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
        $recent_maintenances = App\Models\VehicleMaintenance::with('vehicle')->orderBy('created_at','desc')->limit(5)->get();
    } catch (Throwable $e) {
        $recent = collect();
    }

    return view('dashboard', compact('recent','total_items','total_quantity','instock_count','low_count','out_count','recent_maintenances'));
})->middleware('auth');

// Account requests (admin)
Route::get('/accounts', [\App\Http\Controllers\AccountRequestController::class, 'index'])->middleware('auth');
Route::get('/accounts/{accountRequest}', [\App\Http\Controllers\AccountRequestController::class, 'show'])->middleware('auth');
Route::get('/accounts/{accountRequest}/json', [\App\Http\Controllers\AccountRequestController::class, 'details'])->middleware('auth');
Route::post('/accounts/{accountRequest}/approve', [\App\Http\Controllers\AccountRequestController::class, 'approve'])->middleware('auth');
Route::post('/accounts/{accountRequest}/deny', [\App\Http\Controllers\AccountRequestController::class, 'deny'])->middleware('auth');
Route::post('/accounts/{accountRequest}/note', [\App\Http\Controllers\AccountRequestController::class, 'updateNote'])->middleware('auth');

// Inventory (protected)
Route::get('/inventory', function () {
    $equipment = Equipment::orderBy('created_at','desc')->paginate(25);
    return view('inventory', compact('equipment'));
})->middleware('auth');

Route::get('/vehicle', function (Request $request) {
    $selectedVehicleId = (int) $request->query('vehicle', 0);

    $vehicles = Vehicle::where('status', 'active')
        ->withCount(['maintenances as maintenance_count' => function ($query) {
            $query->where('status', 'needed');
        }])
        ->orderBy('name')
        ->get();

    $selectedVehicle = $selectedVehicleId > 0
        ? $vehicles->firstWhere('id', $selectedVehicleId)
        : $vehicles->first();

    $maintenances = collect();
    if ($selectedVehicle) {
        $maintenances = VehicleMaintenance::where('vehicle_id', $selectedVehicle->id)
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->get();
    }

    return view('vehicle', compact('vehicles', 'selectedVehicle', 'maintenances'));
})->middleware('auth');

Route::get('/vehicle/add', function () {
    return view('vehicle_add');
})->middleware('auth');

Route::get('/vehicle/{vehicle}/edit', function (Vehicle $vehicle) {
    return view('vehicle_edit', compact('vehicle'));
})->middleware('auth');

Route::get('/vehicle/maintenance', function (Request $request) {
    $selectedVehicleId = (int) $request->query('vehicle', 0);
    $highlightedMaintenanceId = (int) $request->query('maintenance', 0);

    $vehicles = Vehicle::where('status', 'active')->orderBy('name')->get(['id', 'name', 'plate_number']);
    $selectedVehicle = $selectedVehicleId > 0
        ? $vehicles->firstWhere('id', $selectedVehicleId)
        : null;

    $maintenances = VehicleMaintenance::with('vehicle')
        ->whereHas('vehicle', function ($q) {
            $q->where('status', 'active');
        });

    if ($selectedVehicleId > 0) {
        $maintenances->where('vehicle_id', $selectedVehicleId);
    }

    $maintenances = $maintenances
        ->orderBy('due_date')
        ->orderByDesc('created_at')
        ->get();

    $user = auth()->user();
    $isAdmin = $user && (
        strtolower((string) ($user->role ?? '')) === 'admin'
        || (($user->id ?? 0) === 1)
        || strcasecmp((string) ($user->name ?? ''), 'admin') === 0
    );

    return view('vehicle_maintenance', compact('maintenances', 'selectedVehicle', 'highlightedMaintenanceId', 'isAdmin'));
})->middleware('auth');

Route::get('/vehicle/maintenance/add', function (Request $request) {
    $selectedVehicleId = (int) $request->query('vehicle', 0);
    $vehicles = Vehicle::where('status', 'active')->orderBy('name')->get();
    $selectedVehicle = $selectedVehicleId > 0
        ? $vehicles->firstWhere('id', $selectedVehicleId)
        : $vehicles->first();

    return view('vehicle_maintenance_add', compact('vehicles', 'selectedVehicle'));
})->middleware('auth');

Route::get('/vehicle/monitoring', function (Request $request) {
    $selectedVehicleId = (int) $request->query('vehicle', 0);
    $vehicles = Vehicle::where('status', 'active')->orderBy('name')->get();
    $selectedVehicle = $selectedVehicleId > 0
        ? $vehicles->firstWhere('id', $selectedVehicleId)
        : null;

    $reports = collect();
    if ($selectedVehicle) {
        $reports = VehicleMonitoringReport::where('vehicle_id', $selectedVehicle->id)
            ->orderByDesc('created_at')
            ->get();
    }

    return view('vehicle_monitoring', compact('vehicles', 'selectedVehicle', 'reports'));
})->middleware('auth');

Route::get('/vehicle/monitoring/add', function (Request $request) {
    $selectedVehicleId = (int) $request->query('vehicle', 0);
    $vehicles = Vehicle::where('status', 'active')->orderBy('name')->get();
    $selectedVehicle = $selectedVehicleId > 0
        ? $vehicles->firstWhere('id', $selectedVehicleId)
        : $vehicles->first();

    return view('vehicle_monitoring_add', compact('vehicles', 'selectedVehicle'));
})->middleware('auth');

Route::get('/vehicle/{vehicle}/monitoring', function (Vehicle $vehicle) {
    return redirect('/vehicle/monitoring?vehicle=' . $vehicle->id);
})->middleware('auth');

Route::post('/vehicle/monitoring', function (Request $request) {
    $data = $request->validate([
        'vehicle_id' => 'required|integer|exists:vehicles,id',
        'report' => 'required|string|max:2000',
    ]);

    $vehicle = Vehicle::findOrFail($data['vehicle_id']);
    abort_if($vehicle->status !== 'active', 422);

    VehicleMonitoringReport::create([
        'vehicle_id' => $vehicle->id,
        'report' => trim($data['report']),
    ]);

    return redirect('/vehicle/monitoring?vehicle=' . $vehicle->id)->with('success', 'Monitoring report saved.');
})->middleware('auth');

Route::post('/vehicle/monitoring/{report}/update', function (Request $request, VehicleMonitoringReport $report) {
    $data = $request->validate([
        'report' => 'required|string|max:2000',
    ]);

    $vehicle = Vehicle::findOrFail($report->vehicle_id);
    abort_if($vehicle->status !== 'active', 422);

    $report->report = trim($data['report']);
    $report->save();

    return redirect('/vehicle/monitoring?vehicle=' . $report->vehicle_id)->with('success', 'Monitoring report updated.');
})->middleware('auth');

Route::post('/vehicle/monitoring/{report}/delete', function (VehicleMonitoringReport $report) {
    $vehicleId = $report->vehicle_id;
    $report->delete();

    return redirect('/vehicle/monitoring?vehicle=' . $vehicleId)->with('success', 'Monitoring report deleted.');
})->middleware('auth');

Route::get('/vehicle/{vehicle}/maintenance', function (Vehicle $vehicle) {
    return redirect('/vehicle/maintenance/add?vehicle=' . $vehicle->id);
})->middleware('auth');

Route::get('/vehicle/{vehicle}/orcr', function (Vehicle $vehicle) {
    return view('vehicle_orcr', compact('vehicle'));
})->middleware('auth');

Route::post('/vehicle', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'plate_number' => 'nullable|string|max:255',
        'image' => 'nullable|file|image|max:5120',
        'orcr_image' => 'nullable|file|image|max:5120',
        'type' => 'required|string|max:255',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'year' => 'nullable|integer|min:1900|max:2100',
        'status' => 'nullable|in:active,inactive',
        'is_firetruck' => 'nullable|boolean',
        'notes' => 'nullable|string|max:500',
    ]);

    $imagePath = null;
    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        $imagePath = $request->file('image')->store('vehicles', 'public');
    }

    $vehicle = Vehicle::create([
        'name' => $data['name'],
        'plate_number' => $data['plate_number'] ?? null,
        'image_path' => $imagePath,
        'orcr_image_path' => null,
        'type' => $data['type'],
        'brand' => $data['brand'] ?? null,
        'model' => $data['model'] ?? null,
        'year' => $data['year'] ?? null,
        'is_firetruck' => $request->boolean('is_firetruck'),
        'status' => $data['status'] ?? 'active',
        'notes' => $data['notes'] ?? null,
    ]);

    if ($request->hasFile('orcr_image') && $request->file('orcr_image')->isValid()) {
        $vehicle->orcr_image_path = $request->file('orcr_image')->store('vehicles/orcr/' . $vehicle->id, 'public');
        $vehicle->save();
    }

    return redirect('/vehicle')->with('success', 'Vehicle added.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/update', function (Request $request, Vehicle $vehicle) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'plate_number' => 'nullable|string|max:255',
        'image' => 'nullable|file|image|max:5120',
        'orcr_image' => 'nullable|file|image|max:5120',
        'type' => 'required|string|max:255',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'year' => 'nullable|integer|min:1900|max:2100',
        'status' => 'nullable|in:active,inactive',
        'is_firetruck' => 'nullable|boolean',
        'notes' => 'nullable|string|max:500',
    ]);

    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        if ($vehicle->image_path && Storage::disk('public')->exists($vehicle->image_path)) {
            Storage::disk('public')->delete($vehicle->image_path);
        }
        $vehicle->image_path = $request->file('image')->store('vehicles', 'public');
    }

    if ($request->hasFile('orcr_image') && $request->file('orcr_image')->isValid()) {
        if ($vehicle->orcr_image_path && Storage::disk('public')->exists($vehicle->orcr_image_path)) {
            Storage::disk('public')->delete($vehicle->orcr_image_path);
        }
        $vehicle->orcr_image_path = $request->file('orcr_image')->store('vehicles/orcr/' . $vehicle->id, 'public');
    }

    $vehicle->name = $data['name'];
    $vehicle->plate_number = $data['plate_number'] ?? null;
    $vehicle->type = $data['type'];
    $vehicle->brand = $data['brand'] ?? null;
    $vehicle->model = $data['model'] ?? null;
    $vehicle->year = $data['year'] ?? null;
    $vehicle->status = $data['status'] ?? 'active';
    $vehicle->is_firetruck = $request->boolean('is_firetruck');
    $vehicle->notes = $data['notes'] ?? null;
    $vehicle->save();

    return redirect('/vehicle')->with('success', 'Vehicle details updated.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/orcr', function (Request $request, Vehicle $vehicle) {
    $data = $request->validate([
        'orcr_image' => 'required|file|image|max:5120',
    ]);

    if ($vehicle->orcr_image_path && Storage::disk('public')->exists($vehicle->orcr_image_path)) {
        Storage::disk('public')->delete($vehicle->orcr_image_path);
    }

    $orcrImagePath = $request->file('orcr_image')->store('vehicles/orcr/' . $vehicle->id, 'public');
    $vehicle->orcr_image_path = $orcrImagePath;
    $vehicle->save();

    return redirect('/vehicle/' . $vehicle->id . '/orcr')->with('success', 'OR/CR photo uploaded.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/maintenance', function (Request $request, Vehicle $vehicle) {
    $data = $request->validate([
        'task' => 'required|string|max:2000',
        'due_date' => 'nullable|date',
        'supervisor_photo' => 'nullable|file|image|max:5120',
        'notes' => 'nullable|string|max:500',
    ]);

    $evidenceImagePath = null;
    $photoField = $request->hasFile('supervisor_photo') ? 'supervisor_photo' : ($request->hasFile('evidence_image') ? 'evidence_image' : null);
    if ($photoField && $request->file($photoField)->isValid()) {
        $evidenceImagePath = $request->file($photoField)->store('vehicles/maintenance/' . $vehicle->id, 'public');
    }

    $maintenance = VehicleMaintenance::create([
        'vehicle_id' => $vehicle->id,
        'task' => $data['task'],
        'due_date' => $data['due_date'] ?? null,
        'status' => 'pending',
        'evidence_image_path' => $evidenceImagePath,
        'notes' => $data['notes'] ?? null,
    ]);

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id)->with('success', 'Maintenance entry saved.');
})->middleware('auth');

Route::post('/vehicle/maintenance', function (Request $request) {
    $data = $request->validate([
        'vehicle_id' => 'required|integer|exists:vehicles,id',
        'task' => 'required|string|max:2000',
        'due_date' => 'nullable|date',
        'supervisor_photo' => 'nullable|file|image|max:5120',
        'notes' => 'nullable|string|max:500',
    ]);

    $vehicle = Vehicle::findOrFail($data['vehicle_id']);
    abort_if($vehicle->status !== 'active', 422);

    $evidenceImagePath = null;
    $photoField = $request->hasFile('supervisor_photo') ? 'supervisor_photo' : ($request->hasFile('evidence_image') ? 'evidence_image' : null);
    if ($photoField && $request->file($photoField)->isValid()) {
        $evidenceImagePath = $request->file($photoField)->store('vehicles/maintenance/' . $vehicle->id, 'public');
    }

    $maintenance = VehicleMaintenance::create([
        'vehicle_id' => $vehicle->id,
        'task' => $data['task'],
        'due_date' => $data['due_date'] ?? null,
        'status' => 'pending',
        'evidence_image_path' => $evidenceImagePath,
        'notes' => $data['notes'] ?? null,
    ]);

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id)->with('success', 'Maintenance entry saved.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/maintenance/{maintenance}/approve', function (Vehicle $vehicle, VehicleMaintenance $maintenance) {
    abort_if($maintenance->vehicle_id !== $vehicle->id, 404);

    $user = auth()->user();
    $isAdmin = $user && (
        strtolower((string) ($user->role ?? '')) === 'admin'
        || (($user->id ?? 0) === 1)
        || strcasecmp((string) ($user->name ?? ''), 'admin') === 0
    );
    abort_unless($isAdmin, 403);

    if ($maintenance->status === 'pending') {
        $maintenance->status = 'needed';
        $maintenance->reviewed_at = $maintenance->reviewed_at ?? now();
        $maintenance->save();

        $autoReportParts = [
            'Maintenance approved: ' . $maintenance->task,
            'Due: ' . ($maintenance->due_date ? $maintenance->due_date->format('Y-m-d') : 'No due date'),
            'Notes: ' . ($maintenance->notes ?: 'None'),
            'Photo: ' . ($maintenance->evidence_image_path ? 'Uploaded' : 'None'),
        ];

        VehicleMonitoringReport::create([
            'vehicle_id' => $vehicle->id,
            'report' => implode("\n", $autoReportParts),
        ]);
    }

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id . '&maintenance=' . $maintenance->id)->with('success', 'Maintenance request approved.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/maintenance/{maintenance}/deny', function (Vehicle $vehicle, VehicleMaintenance $maintenance) {
    abort_if($maintenance->vehicle_id !== $vehicle->id, 404);

    $user = auth()->user();
    $isAdmin = $user && (
        strtolower((string) ($user->role ?? '')) === 'admin'
        || (($user->id ?? 0) === 1)
        || strcasecmp((string) ($user->name ?? ''), 'admin') === 0
    );
    abort_unless($isAdmin, 403);

    if ($maintenance->status === 'pending') {
        $maintenance->status = 'denied';
        $maintenance->reviewed_at = $maintenance->reviewed_at ?? now();
        $maintenance->save();
    }

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id . '&maintenance=' . $maintenance->id)->with('success', 'Maintenance request denied.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/maintenance/{maintenance}/delete', function (Vehicle $vehicle, VehicleMaintenance $maintenance) {
    abort_if($maintenance->vehicle_id !== $vehicle->id, 404);

    $user = auth()->user();
    $isAdmin = $user && (
        strtolower((string) ($user->role ?? '')) === 'admin'
        || (($user->id ?? 0) === 1)
        || strcasecmp((string) ($user->name ?? ''), 'admin') === 0
    );
    abort_unless($isAdmin, 403);

    $maintenance->delete();

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id)->with('success', 'Maintenance entry deleted.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/maintenance/{maintenance}/reviewed', function (Vehicle $vehicle, VehicleMaintenance $maintenance) {
    abort_if($maintenance->vehicle_id !== $vehicle->id, 404);

    $user = auth()->user();
    $isAdmin = $user && (
        strtolower((string) ($user->role ?? '')) === 'admin'
        || (($user->id ?? 0) === 1)
        || strcasecmp((string) ($user->name ?? ''), 'admin') === 0
    );
    abort_unless($isAdmin, 403);

    if (!$maintenance->reviewed_at) {
        $maintenance->reviewed_at = now();
        $maintenance->save();
    }

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id)->with('success', 'Marked as reviewed.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/maintenance/{maintenance}/checked', function (Vehicle $vehicle, VehicleMaintenance $maintenance) {
    abort_if($maintenance->vehicle_id !== $vehicle->id, 404);

    $user = auth()->user();
    $isAdmin = $user && (
        strtolower((string) ($user->role ?? '')) === 'admin'
        || (($user->id ?? 0) === 1)
        || strcasecmp((string) ($user->name ?? ''), 'admin') === 0
    );
    abort_unless($isAdmin, 403);

    if (!$maintenance->checked_at) {
        $maintenance->checked_at = now();
        $maintenance->save();
    }

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id)->with('success', 'Marked as checked.');
})->middleware('auth');

Route::post('/vehicle/{vehicle}/maintenance/{maintenance}/updated', function (Vehicle $vehicle, VehicleMaintenance $maintenance) {
    abort_if($maintenance->vehicle_id !== $vehicle->id, 404);

    $user = auth()->user();
    $isAdmin = $user && (
        strtolower((string) ($user->role ?? '')) === 'admin'
        || (($user->id ?? 0) === 1)
        || strcasecmp((string) ($user->name ?? ''), 'admin') === 0
    );
    abort_unless($isAdmin, 403);

    if (!$maintenance->updated_marker_at) {
        $maintenance->updated_marker_at = now();
        $maintenance->save();
    }

    return redirect('/vehicle/maintenance?vehicle=' . $vehicle->id)->with('success', 'Marked as updated.');
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

// Show equipment details (partial view used by AJAX side-drawer)
Route::get('/inventory/{id}', function ($id) {
    $item = App\Models\Equipment::findOrFail($id);
    // return a partial HTML snippet so front-end can load it into a drawer/modal
    return view('partials.inventory_show', compact('item'));
})->middleware('auth');

// Update equipment (handler with robust image handling)
Route::post('/inventory/{id}/update', function (Request $request, $id) {
    $item = App\Models\Equipment::findOrFail($id);
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'date_added' => 'nullable|date',
        'category' => 'nullable|string|max:255',
        'type' => 'nullable|string|max:255',
        'status' => 'nullable|in:available,not_working,missing',
        'location' => 'nullable|string|max:255',
        'quantity' => 'nullable|integer|min:0',
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

    $effectiveCategory = $data['category'] ?? $item->category ?? '';
    $categorySlug = strtolower(str_replace(' ', '-', trim((string) $effectiveCategory)));
    $isSpecialCategory = in_array($categorySlug, ['power-tool', 'power-tools', 'electronics'], true);
    if ($isSpecialCategory) {
        if (!array_key_exists('status', $data) || $data['status'] === null || $data['status'] === '') {
            $data['status'] = $item->status ?: 'available';
        }
    } else {
        $data['status'] = null;
    }

    $item->fill($data);
    $item->save();

    // Redirect to all inventory after successful update
    return redirect('/inventory')->with('success', 'Equipment updated');
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

    // Legacy JSON import disabled to avoid accidental re-population of requests.
    // Previously the app auto-imported `requests.json` into the DB when the
    // `inventory_requests` table was empty. That behavior has been disabled
    // to prevent legacy files from recreating test/request data.

    // build query from DB
    // consider user id 1 or name 'admin' as admin for now
    $isAdmin = $user && ( ($user->id ?? 0) === 1 || strcasecmp($user->name ?? '', 'admin') === 0 );
    if ($isAdmin) {
        $requestItems = InventoryRequest::where('status', 'pending')->orderBy('created_at','desc')->limit(8)->get();
    } else {
        $requestItems = InventoryRequest::where('requester_user_id', $user ? $user->id : 0)
            ->where('status', '!=', 'pending')
            ->orderBy('created_at','desc')
            ->limit(8)->get();
    }

    $mappedRequestItems = $requestItems->map(function($r) use ($isAdmin){
        return [
            'id' => $r->uuid,
            'item_name' => $r->item_name,
            'requester' => $r->requester,
            'subtitle' => 'Requested by ' . ($r->requester ?: 'Unknown'),
            'department' => $r->department ?? null,
            'status' => $r->status,
            'created_at' => $r->created_at ? $r->created_at->toIso8601String() : now()->toIso8601String(),
            'actionable' => $isAdmin,
            'url' => '/requests/' . $r->uuid,
        ];
    });

    $maintenanceItems = collect();
    if ($isAdmin) {
        $maintenanceItems = VehicleMaintenance::with('vehicle')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($m) {
                $vehicleName = $m->vehicle->name ?? ('Vehicle #' . $m->vehicle_id);
                $plate = $m->vehicle->plate_number ?? 'No plate';
                return [
                    'id' => null,
                    'item_name' => 'Maintenance Request: ' . $vehicleName,
                    'requester' => 'Vehicle Module',
                    'subtitle' => 'Pending review · ' . $plate,
                    'department' => null,
                    'status' => 'pending',
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : now()->toIso8601String(),
                    'actionable' => false,
                    'url' => '/vehicle/maintenance?vehicle=' . $m->vehicle_id . '&maintenance=' . $m->id,
                ];
            });
    }

    $items = $mappedRequestItems
        ->concat($maintenanceItems)
        ->sortByDesc('created_at')
        ->take(8)
        ->values();

    return response()->json([
        'count' => $items->count(),
        'items' => $items->values()->toArray(),
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

    // Debug: log incoming approval action payload for troubleshooting
    try {
        \Log::debug('Approval endpoint payload', [
            'user_id' => $user->id ?? null,
            'isAdmin' => $isAdmin,
            'action' => $action,
            'request' => $request->all()
        ]);
    } catch (Throwable $_logEx) {
        // ignore logging failures
    }

    $r = InventoryRequest::where('uuid', $id)->first();
    if (!$r) {
        return response()->json(['error' => 'Not found'], 404);
    }

    try {
        \Log::debug('Found InventoryRequest', ['id' => $r->id ?? null, 'uuid' => $r->uuid ?? $id, 'items_count' => (isset($r->items) && is_countable($r->items) ? $r->items->count() : 0)]);
    } catch (Throwable $_logEx) {
        // ignore
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
        
        
    
        try {
            \Log::debug('Per-item approval incoming', ['request_item_id' => $requestItemId, 'quantity' => $quantity, 'equipment_id' => $equipmentId]);
        } catch (Throwable $_logEx) {
        }

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
        $pending = max(0, $total - $approved - $rejected);

        if ($total > 0) {
            // If any approved item is non-consumable, the request is waiting for return
            $waitingCount = $r->items()
                ->where('status', 'approved')
                ->whereHas('equipment', function($q){
                    $q->whereRaw('LOWER(COALESCE(`type`, "")) <> ?', ['consumable']);
                })->count();

            if ($waitingCount > 0) {
                $r->status = 'waiting';
            } elseif ($pending > 0) {
                // there are still undecided child items
                $r->status = 'partial';
            } else {
                // all child items decided and no waiting: finalize parent
                if ($approved === $total) {
                    $r->status = 'approved';
                } elseif ($rejected === $total) {
                    $r->status = 'rejected';
                } elseif ($approved > 0) {
                    // mixed approve/reject (or other combinations) -> mark done
                    $r->status = 'done';
                } else {
                    // fallback to done for safety
                    $r->status = 'done';
                }
            }
        }
        $r->handled_by = $user->id ?? null;
        $r->updated_at = now();
        $r->save();

        return response()->json(['ok' => true]);
    }

    // Fallback: act on parent request (legacy single-item behavior)
    if ($action === 'approve') {
        // If this request contains child items, treat approval as per-item approvals:
        // set each item's issued_quantity to its requested quantity (or the provided quantity when appropriate),
        // and decrement the related equipment stock per item.
        try {
            if (isset($r->items) && is_countable($r->items) && $r->items->count() > 0) {
                foreach ($r->items as $item) {
                    $issueQty = $quantity > 0 ? $quantity : intval($item->quantity ?? 0);
                    $equipmentForItem = $item->equipment_id ? Equipment::find($item->equipment_id) : (isset($item->equipment) ? $item->equipment : null);
                    if ($issueQty > 0 && $equipmentForItem) {
                        if ($equipmentForItem->quantity < $issueQty) {
                            // cap to available stock rather than failing the whole request
                            $issueQty = intval($equipmentForItem->quantity);
                        }
                        $equipmentForItem->quantity = intval($equipmentForItem->quantity) - $issueQty;
                        $equipmentForItem->save();
                        $item->issued_quantity = $issueQty;
                    } else {
                        $item->issued_quantity = 0;
                    }
                    $item->status = 'approved';
                    $item->handled_by = $user->id ?? null;
                    $item->handled_at = now();
                    $item->save();
                }
                // After approving child items, recompute parent status
                $total = $r->items()->count();
                $approved = $r->items()->where('status', 'approved')->count();
                $rejected = $r->items()->where('status', 'rejected')->count();
                $pending = max(0, $total - $approved - $rejected);

                $waitingCount = $r->items()
                    ->where('status', 'approved')
                    ->whereHas('equipment', function($q){
                        $q->whereRaw('LOWER(COALESCE(`type`, "")) <> ?', ['consumable']);
                    })->count();

                if ($waitingCount > 0) {
                    $r->status = 'waiting';
                } elseif ($pending > 0) {
                    $r->status = 'partial';
                } else {
                    if ($approved === $total) {
                        $r->status = 'approved';
                    } elseif ($rejected === $total) {
                        $r->status = 'rejected';
                    } elseif ($approved > 0) {
                        $r->status = 'done';
                    } else {
                        $r->status = 'done';
                    }
                }
                } else {
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
                    // Record issued quantity for legacy single-item requests by creating a child item
                    try {
                        $issueQty = $quantity > 0 ? $quantity : intval($r->quantity ?? 0);
                        $itemModelClass = '\\App\\Models\\InventoryRequestItem';
                        if (class_exists($itemModelClass)) {
                            $child = new $itemModelClass();
                            $child->inventory_request_id = $r->id;
                            $child->equipment_id = $equipmentId ?: ($r->item_id ?? null);
                            $child->quantity = intval($r->quantity ?? $quantity ?? 0);
                            $child->issued_quantity = $issueQty;
                            $child->status = 'approved';
                            $child->handled_by = $user->id ?? null;
                            $child->handled_at = now();
                            $child->save();
                        } else {
                            \Log::warning('InventoryRequestItem model missing; cannot create child item for legacy request', ['request_id' => $r->id ?? null]);
                        }
                    } catch (Throwable $__childEx) {
                        try { \Log::error('Failed creating child item for legacy approval', ['error' => $__childEx->getMessage()]); } catch (Throwable $_) {}
                    }
                    $r->status = 'approved';
            }
        } catch (Throwable $__e) {
            try {
                \Log::error('Error during per-item approval handling', ['message' => $__e->getMessage(), 'trace' => $__e->getTraceAsString()]);
            } catch (Throwable $_logEx) {
            }
            // If anything fails during per-item handling, fall back to approving the parent request
            $r->status = 'approved';
        }
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
        if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Forbidden'], 403);
        return back()->withErrors(['permission' => 'Forbidden']);
    }

    $r = InventoryRequest::where('uuid', $id)->firstOrFail();

    // Only approved/waiting, non-consumable requests may be marked returned
    if (!in_array($r->status, ['approved','waiting'])) {
        return back()->withErrors(['status' => 'Only approved or waiting requests can be marked returned.']);
    }

    $equipment = $r->item_id ? Equipment::find($r->item_id) : null;
    if ($equipment && strtolower(trim($equipment->type ?? '')) === 'consumable') {
        return back()->withErrors(['status' => 'Consumable requests do not require return.']);
    }

    // Support single-item return when `request_item_id` provided (AJAX-friendly)
    if ($request->exists('request_item_id')) {
        $data = $request->validate([
            'request_item_id' => 'required|integer',
            'equipment_id' => 'nullable|integer'
        ]);

        $item = \App\Models\InventoryRequestItem::find($data['request_item_id']);
        if (!$item || $item->inventory_request_id != $r->id) {
            if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Request item not found'], 404);
            return back()->withErrors(['item' => 'Request item not found']);
        }

        try {
            $equip = $data['equipment_id'] ? Equipment::find($data['equipment_id']) : ($item->equipment_id ? Equipment::find($item->equipment_id) : null);
            $type = $equip ? strtolower(trim($equip->type ?? '')) : '';
            if ($item->status !== 'approved') {
                if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Item not approved or already processed'], 400);
                return back()->withErrors(['status' => 'Item not approved or already processed']);
            }

            if ($type !== 'consumable') {
                $restoreQty = intval($item->issued_quantity ?? $item->quantity ?? 0);
                if ($equip && $restoreQty > 0) {
                    $equip->quantity = intval($equip->quantity ?? 0) + $restoreQty;
                    $equip->save();
                }
            }

            $item->status = 'returned';
            $item->handled_by = $user->id ?? null;
            $item->handled_at = now();
            $item->return_date = now();
            $item->save();

            // recompute parent request status
            $remainingApproved = $r->items()->where('status', 'approved')->count();
            $r->status = ($remainingApproved === 0) ? 'returned' : 'waiting';
            $r->handled_by = $user->id ?? null;
            $r->updated_at = now();
            $r->save();

            if ($request->wantsJson() || $request->ajax()) return response()->json(['ok' => true]);
            return back()->with('success', 'Item marked returned');
        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) return response()->json(['error' => 'Failed to process return'], 500);
            return back()->withErrors(['server' => 'Failed to process return']);
        }
    }

    // mark returned for whole request (existing batch behavior)
    $r->status = 'returned';
    $r->handled_by = $user->id ?? null;
    $r->updated_at = now();
    $r->save();

    // If this is a multi-item request, update each child item and restore stock per item
    try {
        if (isset($r->items) && is_countable($r->items) && $r->items->count() > 0) {
            foreach ($r->items as $it) {
                try {
                    // Only mark non-consumable, previously-approved items as returned and restore stock
                    $equip = $it->equipment_id ? Equipment::find($it->equipment_id) : null;
                    $type = $equip ? strtolower(trim($equip->type ?? '')) : '';
                    if ($it->status === 'approved' && $type !== 'consumable') {
                        // determine quantity to restore: prefer issued_quantity, fall back to requested quantity
                        $restoreQty = intval($it->issued_quantity ?? $it->quantity ?? 0);
                        if ($equip && $restoreQty > 0) {
                            $equip->quantity = intval($equip->quantity ?? 0) + $restoreQty;
                            $equip->save();
                        }
                        $it->status = 'returned';
                        $it->handled_by = $user->id ?? null;
                        $it->handled_at = now();
                        $it->return_date = now();
                        $it->save();
                    }
                } catch (Throwable $_) {
                    // continue with other items even if one fails
                }
            }
            // Recompute request-level status: if no approved items remain, mark returned, else keep waiting
            $remainingApproved = $r->items()->where('status', 'approved')->count();
            if ($remainingApproved === 0) {
                $r->status = 'returned';
            } else {
                $r->status = 'waiting';
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

    if ($request->wantsJson() || $request->ajax()) return response()->json(['ok' => true]);
    return back()->with('success', 'Request marked returned');
})->middleware('auth');

Route::get('/requests', function (Request $request) {
    $tab = $request->query('tab', 'pending');

    $q = InventoryRequest::query();
    if ($tab === 'pending') {
        $q->where('status', 'pending');
    } elseif ($tab === 'waiting') {
        // Show approved or waiting requests that include non-consumable items (waiting for return)
        $q->whereIn('status', ['approved','waiting'])
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
        // History includes rejected, returned, approved, and done
        $q->whereIn('status', ['approved','rejected','returned','done']);
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

// Printable hard-copy form (A4)
Route::get('/requests/{id}/print', function (Request $request, $id) {
    $r = InventoryRequest::where('uuid', $id)->firstOrFail();
    $equipment = $r->item_id ? Equipment::find($r->item_id) : null;
    $user = auth()->user();
    $isAdmin = $user && ( ($user->id ?? 0) === 1 || strcasecmp($user->name ?? '', 'admin') === 0 );
    return view('requests_print', ['r' => $r, 'equipment' => $equipment, 'isAdmin' => $isAdmin]);
})->middleware('auth');

// (server-side PDF export removed — print view served by browser)

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});
