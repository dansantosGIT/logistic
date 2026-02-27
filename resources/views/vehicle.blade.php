<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vehicles — San Juan CDRMMD</title>
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
        .grid{display:grid;grid-template-columns:1fr 2fr;gap:14px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
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
        .badge.active{background:#10b981}
        .badge.inactive{background:#6b7280}
        .muted{color:var(--muted);font-size:13px}

        .toast{position:fixed;right:20px;bottom:20px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,.2);z-index:200;display:none}
        .toast.show{display:block}
        .modal-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;z-index:210}
        .modal-backdrop.show{display:block}
        .quick-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:min(820px,94vw);background:#fff;border-radius:14px;box-shadow:0 24px 60px rgba(2,6,23,.25);display:none;z-index:220;overflow:hidden}
        .quick-modal.show{display:block}
        .quick-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .quick-close{border:none;background:rgba(255,255,255,.2);color:#fff;width:34px;height:34px;border-radius:8px;cursor:pointer}
        .quick-body{display:grid;grid-template-columns:280px 1fr;gap:16px;padding:14px}
        .quick-img{width:100%;height:220px;object-fit:cover;border-radius:10px;border:1px solid #e6e9ef;background:#f1f5f9}
        .quick-noimg{width:100%;height:220px;border-radius:10px;border:1px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;color:#64748b;background:#f8fafc}
        .quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .quick-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px}
        .quick-label{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.4px}
        .quick-value{font-weight:700;color:#0f172a;font-size:14px;margin-top:4px}
        tbody tr.vehicle-row{cursor:pointer}

        @media(max-width:980px){
            .grid{grid-template-columns:1fr}
        }
        @media(max-width:760px){.quick-body{grid-template-columns:1fr}}
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
                    <span>San Juan CDRMMD Vehicles</span>
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
                <div id="vehicle-submenu" style="display:none">
                    <a href="/vehicle/maintenance" class="sub-link"><span class="label">Maintenance</span></a>
                </div>
                <a href="/requests"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Logout</span></a>
            </nav>
        </aside>

        <main class="main">
            <div class="panel">
                <h2 style="margin:0 0 12px 0">Vehicle Monitoring</h2>
                <p class="muted" style="margin-top:0">View all available vehicles and monitor maintenance items as Needed or Done.</p>
                <section>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
                        <h3 style="margin:0">Available Vehicle List</h3>
                        <a href="/vehicle/add" class="btn primary">Add Vehicle</a>
                    </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type / Brand / Year</th>
                                    <th>Class</th>
                                    <th>Maintenance</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $vehicle)
                                    <tr class="vehicle-row" onclick="openVehicleQuickView(this)" data-vehicle='{{ json_encode(["name" => $vehicle->name, "plate_number" => $vehicle->plate_number, "image_path" => $vehicle->image_path, "type" => $vehicle->type, "brand" => $vehicle->brand, "year" => $vehicle->year, "is_firetruck" => $vehicle->is_firetruck, "status" => $vehicle->status, "needed_count" => $vehicle->needed_count ?? 0, "done_count" => $vehicle->done_count ?? 0]) }}'>
                                        <td>
                                            <div style="font-weight:700">{{ $vehicle->name }}</div>
                                            <div class="muted">{{ $vehicle->plate_number ?: 'No plate' }}</div>
                                        </td>
                                        <td>{{ $vehicle->type ?: '—' }} / {{ $vehicle->brand ?: '—' }} / {{ $vehicle->year ?: '—' }}</td>
                                        <td><span class="badge {{ $vehicle->is_firetruck ? 'done' : 'active' }}">{{ $vehicle->is_firetruck ? 'Firetruck' : 'Standard' }}</span></td>
                                        <td>
                                            <span class="badge needed">Needed: {{ $vehicle->needed_count ?? 0 }}</span>
                                            <span class="badge done" style="margin-left:6px">Done: {{ $vehicle->done_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <a class="btn" href="/vehicle/maintenance?vehicle={{ $vehicle->id }}" onclick="event.stopPropagation()">Maintenance</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="muted">No available vehicles yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                </section>
            </div>
        </main>
    </div>

    <div id="vehicle-quick-backdrop" class="modal-backdrop"></div>
    <div id="vehicle-quick-modal" class="quick-modal">
        <div class="quick-head">
            <div id="quick-title" style="font-weight:700">Vehicle Details</div>
            <button id="quick-close" class="quick-close" type="button">✕</button>
        </div>
        <div class="quick-body">
            <div>
                <img id="quick-image" class="quick-img" style="display:none" alt="Vehicle image">
                <div id="quick-no-image" class="quick-noimg">No vehicle image</div>
            </div>
            <div class="quick-grid">
                <div class="quick-card"><div class="quick-label">Plate Number</div><div id="quick-plate" class="quick-value">—</div></div>
                <div class="quick-card"><div class="quick-label">Type</div><div id="quick-type" class="quick-value">—</div></div>
                <div class="quick-card"><div class="quick-label">Brand</div><div id="quick-brand" class="quick-value">—</div></div>
                <div class="quick-card"><div class="quick-label">Year</div><div id="quick-year" class="quick-value">—</div></div>
                <div class="quick-card"><div class="quick-label">Vehicle Class</div><div id="quick-class" class="quick-value">—</div></div>
                <div class="quick-card"><div class="quick-label">Maintenance</div><div id="quick-maint" class="quick-value">—</div></div>
            </div>
        </div>
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

        function openVehicleQuickView(row){
            let data = {};
            try{ data = JSON.parse(row.dataset.vehicle || '{}'); }catch(e){ data = {}; }

            document.getElementById('quick-title').textContent = data.name || 'Vehicle Details';
            document.getElementById('quick-plate').textContent = data.plate_number || 'No plate';
            document.getElementById('quick-type').textContent = data.type || '—';
            document.getElementById('quick-brand').textContent = data.brand || '—';
            document.getElementById('quick-year').textContent = data.year || '—';
            document.getElementById('quick-class').textContent = data.is_firetruck ? 'Firetruck' : 'Standard';
            document.getElementById('quick-maint').textContent = 'Needed: ' + (data.needed_count || 0) + ' • Done: ' + (data.done_count || 0);

            const image = document.getElementById('quick-image');
            const noImage = document.getElementById('quick-no-image');
            if(data.image_path){
                image.src = '/storage/' + data.image_path;
                image.style.display = 'block';
                noImage.style.display = 'none';
            } else {
                image.style.display = 'none';
                noImage.style.display = 'flex';
            }

            document.getElementById('vehicle-quick-backdrop').classList.add('show');
            document.getElementById('vehicle-quick-modal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        (function(){
            const closeBtn = document.getElementById('quick-close');
            const backdrop = document.getElementById('vehicle-quick-backdrop');
            const modal = document.getElementById('vehicle-quick-modal');
            function closeQuick(){
                backdrop.classList.remove('show');
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
            closeBtn && closeBtn.addEventListener('click', closeQuick);
            backdrop && backdrop.addEventListener('click', closeQuick);
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape' && modal.classList.contains('show')) closeQuick();
            });
        })();
    </script>
</body>
</html>
