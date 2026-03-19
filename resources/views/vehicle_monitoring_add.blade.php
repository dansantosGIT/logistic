<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Add Monitoring Report — San Juan CDRMMD</title>
    <link rel="icon" href="/images/favi.png" type="image/png">
    <link rel="apple-touch-icon" href="/images/favi.png">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}
        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.95);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.08);z-index:60}
        .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;gap:16px}
        .topbar .brand-title{display:flex;align-items:center;gap:6px;font-weight:700}
        .topbar .brand-subtitle{font-size:12px;color:var(--muted)}
        .burger{display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer}
        .burger:hover{background:#eef2ff}
        .app{display:flex;min-height:100vh}
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transition:width .22s ease,transform .22s ease;z-index:50;height:calc(100vh - var(--topbar-height))}
        .sidebar.collapsed{width:64px}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a, .nav button.action{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px}
        .nav a svg, .nav button.action svg{display:block;width:18px;height:18px}
        .nav a:hover, .nav button.action:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 12px;font-size:13px;color:#64748b;justify-content:flex-start;text-align:left}
        .nav svg{flex-shrink:0}
        .nav .nav-with-toggle{position:relative;display:flex;align-items:center;border-radius:8px;min-height:44px}
        .nav .nav-with-toggle.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav .nav-with-toggle:not(.active):hover{background:#f1f5f9}
        .nav .nav-with-toggle .vehicle-link{display:flex;align-items:center;gap:12px;flex:1;color:inherit;text-decoration:none;padding:10px 36px 10px 12px;border-radius:8px}
        .nav .nav-with-toggle .toggle-btn{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;line-height:1;padding:2px 4px;opacity:1}
        .nav .nav-with-toggle:hover .toggle-btn{color:#334155}
        .nav .nav-with-toggle.active .toggle-btn{color:#fff}
        .nav .nav-with-toggle.open .toggle-btn{transform:translateY(-50%) rotate(180deg)}
        .main{flex:1;padding:20px;margin-top:var(--topbar-height)}
        .sidebar{transform:translateX(-110%);transition:transform .22s ease,width .22s ease}
        .sidebar.open{transform:translateX(0);z-index:90}
        .sidebar.collapsed{width:64px;transform:translateX(0)}
        .panel{background:var(--panel);padding:18px 20px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:min(1240px,calc(100% - 24px));margin:12px auto}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 16px;align-items:start}
        .field{display:flex;flex-direction:column;gap:6px}
        .field.full{grid-column:1/-1}
        .field label{font-size:13px;font-weight:600;color:#334155}
        input,select,textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font:inherit}
        textarea{min-height:140px;resize:vertical}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:12px}
        .page-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}
        .page-actions .btn{min-height:40px;font-size:14px;font-weight:600;display:inline-flex;align-items:center;justify-content:center}
        .section-title{margin:0;line-height:1.15}
        .muted{color:var(--muted);font-size:13px}
        .process{margin:0;padding-left:18px;color:#334155}
        .process li{margin-bottom:4px}
        .counter{font-size:12px;color:var(--muted);text-align:right;margin-top:6px}
        .toast{position:fixed;right:20px;bottom:20px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,.2);z-index:200;display:none}
        .toast.show{display:block}

        .notif-bell{position:relative;display:inline-flex;align-items:center;gap:8px;margin-right:12px}
        .notif-bell button{background:transparent;border:none;cursor:pointer;padding:8px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center}
        .notif-count{position:absolute;top:-6px;right:-6px;z-index:70;background:#ef4444;color:#fff;font-size:12px;padding:3px 6px;border-radius:999px;min-width:20px;text-align:center;box-shadow:0 6px 18px rgba(2,6,23,0.12)}
        .notif-dropdown{position:absolute;right:0;top:44px;width:360px;max-height:420px;background:linear-gradient(180deg,#ffffff,#fbfdff);border-radius:12px;box-shadow:0 18px 50px rgba(2,6,23,0.16);overflow:auto;display:none;z-index:120;padding:8px}
        .notif-dropdown.show{display:block}
        .notif-dropdown .item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;transition:background .12s ease,transform .12s ease;cursor:pointer}
        .notif-dropdown .item:hover{background:linear-gradient(90deg,rgba(37,99,235,0.04),rgba(124,58,237,0.02));transform:translateY(-2px)}
        .notif-dropdown .left{flex:0 0 44px;display:flex;align-items:center;justify-content:center}
        .notif-dropdown .avatar{width:44px;height:44px;border-radius:50%;display:inline-grid;place-items:center;background:linear-gradient(135deg,var(--accent),var(--accent-2));color:#fff;font-weight:700;box-shadow:0 8px 22px rgba(15,23,42,0.06)}
        .notif-dropdown .meta{flex:1;min-width:0}
        .notif-dropdown .meta .title{font-weight:700;color:#0f172a;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:8px}
        .notif-dropdown .meta .sub{font-size:12px;color:#94a3b8;margin-top:4px}
        .notif-dropdown .time{font-size:11px;color:#94a3b8;margin-left:6px}
        .notif-dropdown .actions{display:flex;gap:6px;flex-shrink:0}
        .notif-dropdown .empty{padding:12px;color:var(--muted);text-align:center}

        .nav-overlay{position:fixed;left:0;right:0;top:var(--topbar-height);bottom:0;background:rgba(2,6,23,0.45);opacity:0;visibility:hidden;transition:opacity .18s ease;z-index:80}
        .nav-overlay.show{opacity:1;visibility:visible}
        @media(max-width:980px){.form-grid{grid-template-columns:1fr}}
        @media(max-width:900px){.sidebar{position:fixed;left:0;top:0;bottom:0;z-index:80;transform:translateX(-110%);height:100vh}.sidebar.open{transform:translateX(0)}.main{padding:16px}.page-actions{width:100%}.page-actions .btn{flex:1}}
    </style>
    @include('partials._bg-preload')
    @include('partials._formatters')
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>

    <div class="topbar" role="banner">
        <div class="topbar-inner">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="burger-top" class="burger" aria-label="Toggle menu" title="Toggle menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button>
                <div style="display:flex;flex-direction:column">
                    <a href="/dashboard" class="brand-title" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:6px">
                        <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                        <span style="font-weight:700">San Juan CDRMMD Add Monitoring Report</span>
                    </a>
                    <div class="brand-subtitle">Create a new vehicle monitoring entry</div>
                </div>
            </div>
            <div style="text-align:right;display:flex;align-items:center;gap:12px;justify-content:flex-end">
                @include('partials._notifications')
                <div style="text-align:right">
                    <div style="font-size:13px;color:var(--muted-2)">Welcome!</div>
                    <div style="font-weight:700">{{ auth()->user()->name }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="app">
        @php
            $pendingAccounts = 0;
            try {
                $pendingAccounts = \App\Models\AccountRequest::where('status', 'pending')->count();
            } catch (Throwable $e) {
                $pendingAccounts = 0;
            }
        @endphp
        <aside class="sidebar" id="sidebar">
            <a href="/dashboard" class="brand" style="text-decoration:none;color:inherit">
                <img src="/images/favi.png" alt="San Juan" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                <div class="text" style="font-size:14px">San Juan CDRMMD</div>
            </a>
            <nav class="nav">
                <a href="/dashboard"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3v6h8V3h-8zM3 21h8v-6H3v6z" fill="currentColor"/></svg><span class="label">Home</span></a>
                <a href="/inventory"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7a5 5 0 100 10 5 5 0 000-10zM2 12a10 10 0 1120 0A10 10 0 012 12z" fill="currentColor"/></svg><span class="label">Inventory</span></a>
                <div id="vehicle-nav-group" class="nav-with-toggle active">
                    <a href="/vehicle" id="vehicle-nav-link" class="vehicle-link"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13l1.5-4.5A2 2 0 016.4 7h11.2a2 2 0 011.9 1.5L21 13v5a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H6v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM6 14h12M7.5 10.5h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="label">Vehicle</span></a>
                    <button id="vehicle-submenu-toggle" class="toggle-btn" type="button" aria-label="Toggle Vehicle submenu" title="Toggle Vehicle submenu">▾</button>
                </div>
                <div id="vehicle-submenu" style="display:none">
                    <a href="/vehicle/maintenance" class="sub-link"><span class="label">Maintenance</span></a>
                    <a href="/vehicle/monitoring" class="sub-link active"><span class="label">Monitoring</span></a>
                </div>
                <a href="/requests"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="/accounts?tab=accounts" class="{{ request()->is('accounts*') ? 'active' : '' }}"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 20a8 8 0 0116 0v1H4v-1z" fill="currentColor"/></svg><span class="label">Accounts</span>@if($pendingAccounts > 0)<span class="sidebar-badge" style="margin-left:8px">{{ $pendingAccounts }}</span>@endif</a>
                <a href="#"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zM3 13h3l1-3 2 2 3-4 2 4 3-2 1 3h3" stroke="currentColor" stroke-width="1" fill="none"/></svg><span class="label">Settings</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Logout</span></a>
            </nav>
        </aside>

        <main class="main">
            <div class="panel">
                <div class="page-head">
                    <h2 class="section-title">Add Monitoring Report</h2>
                    <div class="page-actions">
                        <a href="/vehicle/monitoring{{ $selectedVehicle ? '?vehicle=' . $selectedVehicle->id : '' }}" class="btn">View Monitoring History</a>
                        <a href="/vehicle" class="btn">Back to Vehicles</a>
                    </div>
                </div>
                <ol class="process" style="margin-top:10px">
                    <li>Select the target vehicle.</li>
                    <li>Write all completed work in the report textbox.</li>
                    <li>Click Save Report to register it with date and time.</li>
                </ol>
            </div>

            <div class="panel">
                <form method="POST" action="/vehicle/monitoring">
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
                        <div class="field full">
                            <label for="monitoring-report">Report</label>
                            <textarea id="monitoring-report" name="report" maxlength="2000" placeholder="Input all tasks/actions done for this vehicle" required {{ empty($vehicles) ? 'disabled' : '' }}>{{ old('report') }}</textarea>
                            <div id="report-counter" class="counter">0 / 2000</div>
                        </div>
                        <div class="field full">
                            <button type="submit" class="btn primary" style="min-height:40px;font-weight:600" {{ empty($vehicles) ? 'disabled' : '' }}>Save Report</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <form id="logout-form" method="POST" action="/logout" style="display:none">@csrf</form>

    <script>
        (function(){
            const sidebar = document.getElementById('sidebar');
            const burger = document.getElementById('burger-top');
            const topbar = document.querySelector('.topbar');
            let navOverlay = document.getElementById('nav-overlay');
            if(!navOverlay){
                navOverlay = document.createElement('div');
                navOverlay.id = 'nav-overlay';
                navOverlay.className = 'nav-overlay';
                document.body.appendChild(navOverlay);
            }
            if(!burger || !sidebar) return;
            function setOverlay(show){
                navOverlay.classList.toggle('show', !!show);
                document.body.style.overflow = show ? 'hidden' : '';
            }
            burger.addEventListener('click', function(e){
                e.stopPropagation();
                const willOpen = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open');
                sidebar.classList.remove('collapsed');
                setOverlay(willOpen);
            });
            document.addEventListener('click', function(e){
                if(sidebar.classList.contains('open')){
                    if(!sidebar.contains(e.target) && !burger.contains(e.target) && !topbar.contains(e.target)){
                        sidebar.classList.remove('open');
                        setOverlay(false);
                    }
                }
            });
            navOverlay.addEventListener('click', function(){
                sidebar.classList.remove('open');
                setOverlay(false);
            });
        })();

        (function(){
            const submenu = document.getElementById('vehicle-submenu');
            const submenuToggle = document.getElementById('vehicle-submenu-toggle');
            if(!submenu || !submenuToggle) return;
            submenuToggle.addEventListener('click', function(e){
                e.preventDefault();
                submenu.style.display = submenu.style.display === 'none' ? '' : 'none';
            });
        })();

        (function(){
            const textarea = document.getElementById('monitoring-report');
            const counter = document.getElementById('report-counter');
            if(!textarea || !counter) return;
            function sync(){
                counter.textContent = textarea.value.length + ' / 2000';
            }
            textarea.addEventListener('input', sync);
            sync();
        })();

        (function(){
            const dd = document.querySelector('.notif-dropdown');
            if(!dd) return;
            dd.addEventListener('click', function(e){
                if(e.target.closest('.actions')) return;
                const item = e.target.closest('.item');
                if(!item) return;
                const url = item.dataset.url || item.getAttribute('data-url');
                if(url) window.location.href = url;
                else {
                    const id = item.dataset.uuid || item.getAttribute('data-uuid') || item.getAttribute('data-id');
                    if(id) window.location.href = '/requests/' + id;
                }
            });
        })();
    </script>
</body>
</html>
