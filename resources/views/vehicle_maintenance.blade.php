<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vehicle Maintenance — San Juan CDRMMD</title>
    <!-- Favicon -->
    <link rel="icon" href="/images/favi.png" type="image/png">
    <link rel="apple-touch-icon" href="/images/favi.png">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}

        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.96);box-shadow:0 6px 24px rgba(2,6,23,0.06);z-index:60}
        .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}

        .app{display:flex;min-height:100vh}
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transform:translateX(-110%);transition:transform .22s ease;z-index:90}
        .sidebar.open{transform:translateX(0)}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:#0f172a;text-decoration:none;min-height:44px}
        .nav a:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 10px;font-size:13px}

        .main{flex:1;padding:16px;margin-top:var(--topbar-height)}
        .panel{background:var(--panel);padding:14px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:calc(100% - 24px);margin:10px auto}
        .form-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
        .field{display:flex;flex-direction:column;gap:6px}
        .field.full{grid-column:1/-1}
        input,select,textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font:inherit}
        textarea{min-height:90px;resize:vertical}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .btn.success{background:#10b981;border:none;color:#fff}
        .btn.warn{background:#f59e0b;border:none;color:#fff}
        .btn.danger{background:#ef4444;border:none;color:#fff}
        .actions{display:flex;gap:8px;flex-wrap:wrap}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:14px}
        th,td{padding:10px 8px;border-bottom:1px solid #edf2f7;text-align:left;vertical-align:top}
        th{font-size:12px;text-transform:uppercase;letter-spacing:.3px;color:var(--muted)}
        .badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;color:#fff}
        .badge.needed{background:#f59e0b}
        .badge.done{background:#10b981}
        .muted{color:var(--muted);font-size:13px}
        .toast{position:fixed;right:20px;bottom:20px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,.2);z-index:200;display:none}
        .toast.show{display:block}
        @media(max-width:980px){.form-grid{grid-template-columns:1fr}}
    </style>
    @include('partials._bg-preload')
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>

    <div class="topbar" role="banner">
        <div class="topbar-inner">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="burger-top" aria-label="Toggle menu" style="display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </button>
                <a href="/dashboard" style="display:flex;align-items:center;gap:6px;font-weight:700;text-decoration:none;color:inherit">
                    <img src="/images/favi.png" alt="Logo" width="40" height="40" />
                    <span>Vehicle Maintenance</span>
                </a>
            </div>
            <div style="text-align:right">
                <div style="font-size:12px;color:var(--muted)">Welcome</div>
                <div style="font-weight:700">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </div>

    <div class="app">
        <aside class="sidebar" id="sidebar">
            <a href="/dashboard" class="brand" style="text-decoration:none;color:inherit">
                <img src="/images/favi.png" alt="San Juan" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                <div class="text" style="font-size:14px">San Juan CDRMMD</div>
            </a>
            <nav class="nav">
                <a href="/dashboard"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3v6h8V3h-8zM3 21h8v-6H3v6z" fill="currentColor"/></svg><span class="label">Home</span></a>
                <a href="/inventory"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7a5 5 0 100 10 5 5 0 000-10zM2 12a10 10 0 1120 0A10 10 0 012 12z" fill="currentColor"/></svg><span class="label">Inventory</span></a>
                <a href="/vehicle" id="vehicle-nav-link" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13l1.5-4.5A2 2 0 016.4 7h11.2a2 2 0 011.9 1.5L21 13v5a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H6v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM6 14h12M7.5 10.5h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="label">Vehicle</span></a>
                <div id="vehicle-submenu" style="display:block">
                    <a href="/vehicle/maintenance" class="sub-link active"><span class="label">Maintenance</span></a>
                </div>
                <a href="/requests"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Logout</span></a>
            </nav>
        </aside>

        <main class="main">
            <div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
                    <h2 style="margin:0">Vehicle Maintenance</h2>
                    <a href="/vehicle" class="btn">Back to Vehicles</a>
                </div>
                <div class="muted" style="margin-top:6px">All maintenance records are shown below. Select a vehicle when adding a new maintenance task.</div>
            </div>

            <div class="panel">
                <h3 style="margin-top:0">Add Maintenance</h3>
                <form method="POST" action="/vehicle/maintenance">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="vehicle_id">Vehicle</label>
                            <select id="vehicle_id" name="vehicle_id" required>
                                @forelse($vehicles as $v)
                                    <option value="{{ $v->id }}" {{ ($selectedVehicle && $selectedVehicle->id === $v->id) ? 'selected' : '' }}>{{ $v->name }} ({{ $v->plate_number ?: 'No plate' }})</option>
                                @empty
                                    <option value="">No available vehicle</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="field">
                            <label for="task">Maintenance Task</label>
                            <input id="task" name="task" required placeholder="e.g., Oil change" {{ empty($vehicles) ? 'disabled' : '' }}>
                        </div>
                        <div class="field">
                            <label for="due_date">Due Date</label>
                            <input id="due_date" name="due_date" type="date" {{ empty($vehicles) ? 'disabled' : '' }}>
                        </div>
                        <div class="field">
                            <label for="maintenance-status">Status</label>
                            <select id="maintenance-status" name="status" {{ empty($vehicles) ? 'disabled' : '' }}>
                                <option value="needed">Needed</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div class="field full">
                            <label for="maintenance-notes">Notes</label>
                            <textarea id="maintenance-notes" name="notes" placeholder="Optional notes for this maintenance task" {{ empty($vehicles) ? 'disabled' : '' }}></textarea>
                        </div>
                        <div class="field full">
                            <button type="submit" class="btn primary" {{ empty($vehicles) ? 'disabled' : '' }}>Save Maintenance</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="panel">
                <h3 style="margin-top:0">All Maintenance List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Task</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Completed</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                            <tr>
                                <td>
                                    <div style="font-weight:700">{{ $maintenance->vehicle->name ?? '—' }}</div>
                                    <div class="muted">{{ $maintenance->vehicle->plate_number ?? 'No plate' }}</div>
                                </td>
                                <td>{{ $maintenance->task }}</td>
                                <td>{{ $maintenance->due_date ? $maintenance->due_date->format('Y-m-d') : '—' }}</td>
                                <td><span class="badge {{ $maintenance->status === 'done' ? 'done' : 'needed' }}">{{ ucfirst($maintenance->status) }}</span></td>
                                <td>{{ $maintenance->completed_at ? $maintenance->completed_at->format('Y-m-d H:i') : '—' }}</td>
                                <td>{{ $maintenance->notes ?: '—' }}</td>
                                <td>
                                    <div class="actions">
                                        @if($maintenance->status !== 'done')
                                            <form method="POST" action="/vehicle/{{ $maintenance->vehicle_id }}/maintenance/{{ $maintenance->id }}/mark">
                                                @csrf
                                                <input type="hidden" name="status" value="done">
                                                <button class="btn success" type="submit">Mark Done</button>
                                            </form>
                                        @else
                                            <form method="POST" action="/vehicle/{{ $maintenance->vehicle_id }}/maintenance/{{ $maintenance->id }}/mark">
                                                @csrf
                                                <input type="hidden" name="status" value="needed">
                                                <button class="btn warn" type="submit">Mark Needed</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="/vehicle/{{ $maintenance->vehicle_id }}/maintenance/{{ $maintenance->id }}/delete" onsubmit="return confirm('Delete this maintenance entry?')">
                                            @csrf
                                            <button class="btn danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="muted">No maintenance entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    @if(session('success'))
        <div id="success-toast" class="toast">{{ session('success') }}</div>
    @endif

    <form id="logout-form" method="POST" action="/logout" style="display:none">@csrf</form>

    <script>
        (function(){
            const sidebar = document.getElementById('sidebar');
            const burger = document.getElementById('burger-top');
            if(!sidebar || !burger) return;
            burger.addEventListener('click', function(){ sidebar.classList.toggle('open'); });
        })();

        (function(){
            const vehicleLink = document.getElementById('vehicle-nav-link');
            const submenu = document.getElementById('vehicle-submenu');
            if(!vehicleLink || !submenu) return;
            vehicleLink.addEventListener('click', function(e){
                e.preventDefault();
                submenu.style.display = submenu.style.display === 'none' ? '' : 'none';
            });
        })();

        (function(){
            const toast = document.getElementById('success-toast');
            if(!toast) return;
            toast.classList.add('show');
            setTimeout(()=> toast.classList.remove('show'), 3500);
        })();
    </script>
    <script>
        (function(){
            const sidebar = document.getElementById('sidebar');
            const burger = document.getElementById('burger-top');
            if(!sidebar || !burger) return;
            function closeOnOutside(e){
                if(!sidebar.classList.contains('open')) return;
                if(sidebar.contains(e.target) || burger.contains(e.target)) return;
                sidebar.classList.remove('open');
            }
            document.addEventListener('click', closeOnOutside);
            document.addEventListener('touchstart', closeOnOutside);
        })();
    </script>
</body>
</html>
