<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vehicles — San Juan CDRMMD</title>
    <!-- Favicon -->
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
        .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}
        .topbar .left-area{display:flex;align-items:center;gap:12px}
        .topbar .branding{display:flex;flex-direction:column}
        .topbar .brand-title{display:flex;align-items:center;gap:6px;font-weight:700}
        .topbar .brand-subtitle{font-size:12px;color:var(--muted)}
        .burger{display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer}
        .burger:hover{background:#eef2ff}

        .app{display:flex;min-height:100vh}
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transform:translateX(-110%);transition:width .22s ease,transform .22s ease;z-index:50;height:calc(100vh - var(--topbar-height))}
        .sidebar.collapsed{width:64px}
        .sidebar.open{transform:translateX(0);z-index:90}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a, .nav button.action{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px}
        .nav a svg, .nav button.action svg{display:block;width:18px;height:18px}
        .nav a:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 12px;font-size:13px;color:#64748b;justify-content:flex-start;text-align:left}
        .nav a.sub-link:hover{background:transparent;color:#334155}
        .nav .nav-with-toggle{position:relative;display:flex;align-items:center;border-radius:8px;min-height:44px}
        .nav .nav-with-toggle.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav .nav-with-toggle:not(.active):hover{background:#f1f5f9}
        .nav .nav-with-toggle .vehicle-link{display:flex;align-items:center;gap:12px;flex:1;color:inherit;text-decoration:none;padding:10px 36px 10px 12px;border-radius:8px}
        .nav .nav-with-toggle .toggle-btn{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;line-height:1;padding:2px 4px;opacity:1}
        .nav .nav-with-toggle:hover .toggle-btn{color:#334155}
        .nav .nav-with-toggle.active .toggle-btn{color:#fff}
        .nav .nav-with-toggle.open .toggle-btn{transform:translateY(-50%) rotate(180deg)}

        /* Match dashboard hover behaviour for the vehicle link */
        .nav .nav-with-toggle:not(.active) .vehicle-link:hover{background:#f1f5f9;color:inherit}
        .nav .nav-with-toggle.active .vehicle-link:hover{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}

        .main{flex:1;padding:16px;margin-top:var(--topbar-height)}
        .panel{background:var(--panel);padding:14px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:min(1240px,calc(100% - 24px));margin:10px auto}
        .grid{display:grid;grid-template-columns:1fr 2fr;gap:14px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .field{display:flex;flex-direction:column;gap:6px}
        .field.full{grid-column:1/-1}
        input,select,textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font:inherit}
        textarea{min-height:90px;resize:vertical}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .btn.add-vehicle{background:#6b7280;border:none;color:#fff;display:inline-flex;align-items:center;gap:6px}
        .btn.add-vehicle:hover{background:#4b5563}
        .btn.quick-maintenance{background:#fff;color:#0f172a;border:1px solid #e6e9ef;display:inline-flex;align-items:center;gap:6px}
        .btn.quick-maintenance:hover{background:#f8fafc}
        .btn.quick-monitoring{background:#fff;color:#0f172a;border:1px solid #e6e9ef;display:inline-flex;align-items:center;gap:6px}
        .btn.quick-monitoring:hover{background:#f8fafc}
        .top-section{margin-top:16px;padding-bottom:14px;margin-bottom:16px;border-bottom:3px solid #e5e7eb}
        .btn.success{background:#10b981;border:none;color:#fff}
        .btn.warn{background:#f59e0b;border:none;color:#fff}
        .btn.danger{background:#ef4444;border:none;color:#fff}
        .actions{display:flex;gap:8px;flex-wrap:wrap}
        .page-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px}
        .page-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .page-actions .btn{min-height:38px;font-size:14px;font-weight:600}
        .table-tools{display:flex;justify-content:space-between;align-items:flex-end;gap:10px;flex-wrap:wrap;margin:10px 0 12px}
        .tool-grid{display:grid;grid-template-columns:repeat(4,minmax(140px,1fr));gap:8px;min-width:min(760px,100%)}
        .tool-grid .field{gap:4px}
        .tool-grid .field label{font-size:12px;color:var(--muted);font-weight:600}
        .sort-btn{border:none;background:transparent;color:inherit;font:inherit;text-transform:uppercase;letter-spacing:.3px;font-size:12px;cursor:pointer;padding:0;display:inline-flex;align-items:center;gap:4px}
        .sort-btn:hover{color:#334155}
        .sort-btn .sort-arrow{font-size:11px;opacity:.75}

        table{width:100%;border-collapse:separate;border-spacing:0;font-size:14px}
        th,td{padding:14px 10px;border-bottom:1px solid #edf2f7;text-align:left;vertical-align:middle}
        th{font-size:12px;text-transform:uppercase;letter-spacing:.3px;color:var(--muted);font-weight:700}
        .badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;color:#fff}
        .badge.needed{background:#f59e0b}
        .badge.done{background:#10b981}
        .badge.active{background:#10b981}
        .badge.inactive{background:#6b7280}
        .muted{color:var(--muted);font-size:13px}
        .name-cell{display:flex;align-items:center;gap:12px}
        .name-col-header{padding-left:110px}
        .name-main{font-size:20px;font-weight:700;line-height:1.15}
        .plate-sub{font-size:15px;color:var(--muted);margin-top:2px}
        .vehicle-meta{font-size:15px;line-height:1.35}
        .thumb-box{width:88px;height:60px;border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .thumb-box img{width:100%;height:100%;object-fit:cover;display:block}
        .thumb-fallback{font-size:12px;color:#64748b;font-weight:600}

        .toast{position:fixed;right:20px;bottom:20px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,.2);z-index:200;display:none}
        .toast.show{display:block}
        .modal-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;z-index:210}
        .modal-backdrop.show{display:block}
        .quick-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:min(980px,95vw);max-height:90vh;background:linear-gradient(180deg,#ffffff,#f8fafc);border-radius:16px;box-shadow:0 30px 70px rgba(2,6,23,.28);display:none;z-index:220;overflow:auto}
        .quick-modal.show{display:block}
        .quick-head{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .quick-close{border:none;background:rgba(255,255,255,.2);color:#fff;width:34px;height:34px;border-radius:8px;cursor:pointer}
        .quick-body{display:grid;grid-template-columns:340px 1fr;gap:20px;padding:20px}
        .quick-img{width:100%;height:260px;object-fit:cover;border-radius:12px;border:1px solid #dbe2ea;background:#f1f5f9}
        .quick-noimg{width:100%;height:260px;border-radius:12px;border:1px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;color:#64748b;background:#f8fafc}
        .quick-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .quick-card{background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:12px;box-shadow:0 4px 12px rgba(15,23,42,.04)}
        .quick-label{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.4px}
        .quick-value{font-weight:700;color:#0f172a;font-size:14px;margin-top:4px}
        .quick-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px;flex-wrap:wrap}
        .quick-action{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border-radius:10px;border:1px solid #d7dde7;background:#fff;color:#0f172a;text-decoration:none;font-weight:600}
        .quick-action:hover{background:#f8fafc}
        .quick-action.primary{background:#2563eb;border:none;color:#fff}
        .quick-action.primary:hover{background:#1e4fd8}
        tbody tr.vehicle-row{cursor:pointer}

        @media(max-width:980px){
            .grid{grid-template-columns:1fr}
        }
        @media(max-width:760px){.quick-body{grid-template-columns:1fr}.quick-actions{justify-content:stretch}.quick-action{flex:1}}
        /* Notification bell styles (copied from dashboard for consistent topbar) */
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
        /* Mobile: make vehicle table rows stack as cards */
        @media (max-width:900px) {
            table thead { display: none; }
            table, table tbody, table tr { display: block; width: 100%; }
            table tbody tr { margin-bottom: 12px; background: #fff; padding: 12px; border-radius: 10px; box-shadow: 0 8px 20px rgba(2,6,23,0.04); border: 1px solid rgba(14,21,40,0.04); }
            table tbody td { display: block; padding: 6px 0; border: none; }
            table tbody td:first-child { font-weight:700; margin-bottom:6px }
            table tbody td:nth-child(2)::before { content: 'Type / Brand / Model / Year: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(3)::before { content: 'Status: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(4)::before { content: 'Maintenance: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(5)::before { content: ''; }
            table tbody td::before { display:inline-block; margin-right:6px }
            .name-main{font-size:18px}
            .plate-sub{font-size:14px}
            .thumb-box{width:86px;height:56px}
            .table-tools{align-items:stretch}
            .tool-grid{grid-template-columns:1fr;min-width:100%}
            .page-actions{width:100%}
            .page-actions .btn{flex:1}
        }
    </style>
    @include('partials._bg-preload')
    @include('partials._formatters')
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>

    <div class="topbar" role="banner">
        <div class="topbar-inner">
            <div class="left-area">
                <button id="burger-top" class="burger" aria-label="Toggle menu" title="Toggle menu" style="display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </button>
                <div class="branding">
                        <a href="/dashboard" class="brand-title" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:6px">
                            <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                            <span>San Juan CDRMMD Vehicles</span>
                        </a>
                    <div class="brand-subtitle">Overview of Vehicles</div>
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
                    <button id="vehicle-submenu-toggle" class="toggle-btn" type="button" aria-label="Toggle Maintenance menu" title="Toggle Maintenance menu">⌄</button>
                </div>
                <div id="vehicle-submenu" style="display:none">
                    <a href="/vehicle/maintenance" class="sub-link"><span class="label">Maintenance</span></a>
                    <a href="/vehicle/monitoring" class="sub-link"><span class="label">Monitoring</span></a>
                </div>
                <a href="/requests"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="/accounts" class="{{ request()->is('accounts*') ? 'active' : '' }}"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 20a8 8 0 0116 0v1H4v-1z" fill="currentColor"/></svg><span class="label">Accounts</span>@if($pendingAccounts > 0)<span class="sidebar-badge" style="margin-left:8px">{{ $pendingAccounts }}</span>@endif</a>
                <a href="#"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zM3 13h3l1-3 2 2 3-4 2 4 3-2 1 3h3" stroke="currentColor" stroke-width="1" fill="none"/></svg><span class="label">Settings</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Logout</span></a>
            </nav>
        </aside>

        <main class="main">
            <div class="panel">
                <div class="top-section">
                    <div class="page-head">
                        <h2 style="margin:0">Vehicle Management</h2>
                        <div class="page-actions">
                            <a href="/vehicle/maintenance" class="btn quick-maintenance" title="Go to maintenance page">Maintenance</a>
                            <a href="/vehicle/monitoring" class="btn quick-monitoring" title="Go to monitoring page">Monitoring</a>
                            <a href="/vehicle/add" class="btn add-vehicle"><span aria-hidden="true">+</span><span>Add Vehicle</span></a>
                        </div>
                    </div>
                    <p class="muted" style="margin-top:0;margin-bottom:0">View all available vehicles and monitor maintenance items as Needed or Done.</p>
                </div>
                <section>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
                        <h3 style="margin:0">Available Vehicle List</h3>
                    </div>
                        <div class="table-tools">
                            <div class="tool-grid">
                                <div class="field">
                                    <label for="filter-name">Search Name</label>
                                    <input id="filter-name" type="text" placeholder="Type vehicle name">
                                </div>
                                <div class="field">
                                    <label for="filter-status">Status</label>
                                    <select id="filter-status">
                                        <option value="all">All</option>
                                        <option value="serviceable">Serviceable</option>
                                        <option value="for maintenance">For Maintenance</option>
                                        <option value="not available">Not Available</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="filter-type">Vehicle Type</label>
                                    <select id="filter-type">
                                        <option value="all">All</option>
                                        @php
                                            $vehicleTypes = $vehicles->pluck('type')->filter()->unique()->sort()->values();
                                        @endphp
                                        @foreach($vehicleTypes as $type)
                                            <option value="{{ strtolower($type) }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="filter-photo">Photo</label>
                                    <select id="filter-photo">
                                        <option value="all">All</option>
                                        <option value="has">Has photo</option>
                                        <option value="none">No photo</option>
                                    </select>
                                </div>
                            </div>
                            <button id="clear-vehicle-filters" type="button" class="btn">Clear Filters</button>
                        </div>
                        <div style="overflow:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th class="name-col-header"><button type="button" class="sort-btn" data-sort="name">Name <span class="sort-arrow">↕</span></button></th>
                                    <th>Type / Brand / Model / Year</th>
                                    <th><button type="button" class="sort-btn" data-sort="status">Status <span class="sort-arrow">↕</span></button></th>
                                    <th><button type="button" class="sort-btn" data-sort="maintenance">Maintenance <span class="sort-arrow">↕</span></button></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $vehicle)
                                    @php
                                        $maintenanceCount = (int) ($vehicle->maintenance_count ?? 0);
                                        $statusRaw = strtolower((string) ($vehicle->status ?? 'active'));
                                        $isNotAvailable = in_array($statusRaw, ['inactive', 'not_available', 'not available', 'unavailable'], true);
                                        if ($maintenanceCount > 0) {
                                            $displayStatus = 'For Maintenance';
                                            $statusBadgeClass = 'needed';
                                        } elseif ($isNotAvailable) {
                                            $displayStatus = 'Not Available';
                                            $statusBadgeClass = 'inactive';
                                        } else {
                                            $displayStatus = 'Serviceable';
                                            $statusBadgeClass = 'active';
                                        }
                                    @endphp
                                    <tr class="vehicle-row"
                                        onclick="openVehicleQuickView(this)"
                                        data-name="{{ strtolower((string) $vehicle->name) }}"
                                        data-type="{{ strtolower((string) ($vehicle->type ?? '')) }}"
                                        data-status-label="{{ strtolower($displayStatus) }}"
                                        data-has-image="{{ $vehicle->image_path ? '1' : '0' }}"
                                        data-maintenance="{{ (int) ($vehicle->maintenance_count ?? 0) }}"
                                        data-vehicle='{{ json_encode(["id" => $vehicle->id, "name" => $vehicle->name, "plate_number" => $vehicle->plate_number, "image_path" => $vehicle->image_path, "orcr_image_path" => $vehicle->orcr_image_path, "type" => $vehicle->type, "brand" => $vehicle->brand, "model" => $vehicle->model, "year" => $vehicle->year, "is_firetruck" => $vehicle->is_firetruck, "status" => $vehicle->status, "maintenance_count" => $vehicle->maintenance_count ?? 0]) }}'>
                                        <td>
                                            <div class="name-cell">
                                                <div class="thumb-box">
                                                    @if($vehicle->image_path)
                                                        <img src="{{ asset('storage/' . $vehicle->image_path) }}" alt="{{ $vehicle->name }} image" loading="lazy">
                                                    @else
                                                        <span class="thumb-fallback">No image</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="name-main">{{ $vehicle->name }}</div>
                                                    <div class="plate-sub">{{ $vehicle->plate_number ?: 'No plate' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="vehicle-meta">{{ $vehicle->type ?: '—' }} / {{ $vehicle->brand ?: '—' }} / {{ $vehicle->model ?: '—' }} / {{ $vehicle->year ?: '—' }}</td>
                                        <td><span class="badge {{ $statusBadgeClass }}">{{ $displayStatus }}</span></td>
                                        <td>
                                            <span class="badge active">Total: {{ $vehicle->maintenance_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <div class="actions" onclick="event.stopPropagation()">
                                                <a class="btn warn" href="/vehicle/{{ $vehicle->id }}/orcr" onclick="event.stopPropagation()">OR/CR</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="muted">No available vehicles yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
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
            <div>
                <div class="quick-grid">
                    <div class="quick-card"><div class="quick-label">Plate Number</div><div id="quick-plate" class="quick-value">—</div></div>
                    <div class="quick-card"><div class="quick-label">Type</div><div id="quick-type" class="quick-value">—</div></div>
                    <div class="quick-card"><div class="quick-label">Brand</div><div id="quick-brand" class="quick-value">—</div></div>
                    <div class="quick-card"><div class="quick-label">Model</div><div id="quick-model" class="quick-value">—</div></div>
                    <div class="quick-card"><div class="quick-label">Year</div><div id="quick-year" class="quick-value">—</div></div>
                    <div class="quick-card"><div class="quick-label">Status</div><div id="quick-status" class="quick-value">—</div></div>
                    <div class="quick-card"><div class="quick-label">Maintenance</div><div id="quick-maint" class="quick-value">—</div></div>
                </div>
                <div class="quick-actions">
                    <a id="quick-edit-link" class="quick-action" href="/vehicle/add">Edit Details</a>
                    <a id="quick-monitoring-link" class="quick-action" href="/vehicle/monitoring">Monitoring</a>
                    <a id="quick-maintenance-link" class="quick-action primary" href="/vehicle/maintenance">Maintenance</a>
                </div>
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
            const navGroup = document.getElementById('vehicle-nav-group');
            if(!submenu || !submenuToggle || !navGroup) return;
            function syncOpenState(){
                navGroup.classList.toggle('open', submenu.style.display !== 'none');
            }
            submenuToggle.addEventListener('click', function(e){
                e.preventDefault();
                submenu.style.display = submenu.style.display === 'none' ? '' : 'none';
                syncOpenState();
            });
            syncOpenState();
        })();

        (function(){
            const toast = document.getElementById('success-toast');
            if(!toast) return;
            toast.classList.add('show');
            setTimeout(()=> toast.classList.remove('show'), 3500);
        })();

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

        (function(){
            const rows = Array.from(document.querySelectorAll('tbody tr.vehicle-row'));
            const statusFilter = document.getElementById('filter-status');
            const typeFilter = document.getElementById('filter-type');
            const photoFilter = document.getElementById('filter-photo');
            const nameFilter = document.getElementById('filter-name');
            const clearBtn = document.getElementById('clear-vehicle-filters');
            const sortButtons = document.querySelectorAll('.sort-btn[data-sort]');
            let currentSort = { key: '', direction: 'asc' };
            if(!rows.length || !statusFilter || !typeFilter || !photoFilter || !nameFilter) return;

            function applyFilters(){
                const statusValue = statusFilter.value;
                const typeValue = typeFilter.value;
                const photoValue = photoFilter.value;
                const nameValue = nameFilter.value.trim().toLowerCase();
                rows.forEach(function(row){
                    const rowStatus = (row.dataset.statusLabel || '').toLowerCase();
                    const rowType = (row.dataset.type || '').toLowerCase();
                    const rowName = (row.dataset.name || '').toLowerCase();
                    const hasImage = (row.dataset.hasImage || '0') === '1';
                    const statusMatch = statusValue === 'all' || rowStatus === statusValue;
                    const typeMatch = typeValue === 'all' || rowType === typeValue;
                    const photoMatch = photoValue === 'all' || (photoValue === 'has' ? hasImage : !hasImage);
                    const nameMatch = !nameValue || rowName.includes(nameValue);
                    row.style.display = (statusMatch && typeMatch && photoMatch && nameMatch) ? '' : 'none';
                });
            }

            function sortRows(key){
                if(currentSort.key === key){
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.key = key;
                    currentSort.direction = 'asc';
                }
                const multiplier = currentSort.direction === 'asc' ? 1 : -1;
                rows.sort(function(a, b){
                    let aVal = '';
                    let bVal = '';
                    if(key === 'maintenance'){
                        aVal = Number(a.dataset.maintenance || 0);
                        bVal = Number(b.dataset.maintenance || 0);
                        return (aVal - bVal) * multiplier;
                    }
                    if(key === 'status'){
                        aVal = (a.dataset.statusLabel || '').toLowerCase();
                        bVal = (b.dataset.statusLabel || '').toLowerCase();
                    } else {
                        aVal = (a.dataset.name || '').toLowerCase();
                        bVal = (b.dataset.name || '').toLowerCase();
                    }
                    if(aVal < bVal) return -1 * multiplier;
                    if(aVal > bVal) return 1 * multiplier;
                    return 0;
                });
                const tbody = rows[0].parentElement;
                rows.forEach(function(row){ tbody.appendChild(row); });
            }

            statusFilter.addEventListener('change', applyFilters);
            typeFilter.addEventListener('change', applyFilters);
            photoFilter.addEventListener('change', applyFilters);
            nameFilter.addEventListener('input', applyFilters);
            clearBtn && clearBtn.addEventListener('click', function(){
                statusFilter.value = 'all';
                typeFilter.value = 'all';
                photoFilter.value = 'all';
                nameFilter.value = '';
                applyFilters();
            });
            sortButtons.forEach(function(btn){
                btn.addEventListener('click', function(){ sortRows(btn.dataset.sort); });
            });
        })();

        function openVehicleQuickView(row){
            let data = {};
            try{ data = JSON.parse(row.dataset.vehicle || '{}'); }catch(e){ data = {}; }

            document.getElementById('quick-title').textContent = data.name || 'Vehicle Details';
            document.getElementById('quick-plate').textContent = data.plate_number || 'No plate';
            document.getElementById('quick-type').textContent = data.type || '—';
            document.getElementById('quick-brand').textContent = data.brand || '—';
            document.getElementById('quick-model').textContent = data.model || '—';
            document.getElementById('quick-year').textContent = data.year || '—';
            const maintenanceCount = Number(data.maintenance_count || 0);
            const rawStatus = String(data.status || 'active').toLowerCase();
            const isNotAvailable = ['inactive','not_available','not available','unavailable'].includes(rawStatus);
            const quickStatus = maintenanceCount > 0
                ? 'For Maintenance'
                : (isNotAvailable ? 'Not Available' : 'Serviceable');
            document.getElementById('quick-status').textContent = quickStatus;
            document.getElementById('quick-maint').textContent = 'Total entries: ' + (data.maintenance_count || 0);

            const image = document.getElementById('quick-image');
            const noImage = document.getElementById('quick-no-image');
            const maintenanceLink = document.getElementById('quick-maintenance-link');
            const monitoringLink = document.getElementById('quick-monitoring-link');
            const editLink = document.getElementById('quick-edit-link');
            if(data.image_path){
                image.src = '/storage/' + data.image_path;
                image.style.display = 'block';
                noImage.style.display = 'none';
            } else {
                image.style.display = 'none';
                noImage.style.display = 'flex';
            }

            if(maintenanceLink){
                maintenanceLink.href = data.id ? ('/vehicle/maintenance?vehicle=' + encodeURIComponent(data.id)) : '/vehicle/maintenance';
            }
            if(monitoringLink){
                monitoringLink.href = data.id ? ('/vehicle/monitoring?vehicle=' + encodeURIComponent(data.id)) : '/vehicle/monitoring';
            }
            if(editLink){
                editLink.href = data.id ? ('/vehicle/' + encodeURIComponent(data.id) + '/edit') : '/vehicle/add';
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
    <script>
        (function(){
            const dd = document.querySelector('.notif-dropdown');
            if(!dd) return;
            dd.addEventListener('click', function(e){
                if(e.target.closest('.actions')) return; // ignore action button clicks
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
