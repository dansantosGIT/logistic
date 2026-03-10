<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vehicle OR/CR — San Juan CDRMMD</title>
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

        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.95);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.08);z-index:60}
        .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}
        .topbar .brand-title{display:flex;align-items:center;gap:6px;font-weight:700}
        .topbar .brand-subtitle{font-size:12px;color:var(--muted)}
        .burger{display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer}
        .burger:hover{background:#eef2ff}

        .app{display:flex;min-height:100vh}
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transform:translateX(-110%);transition:transform .22s ease;z-index:90}
        .sidebar.open{transform:translateX(0)}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:#0f172a;text-decoration:none;min-height:44px}
        .nav a:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 12px;font-size:13px;justify-content:flex-start;text-align:left}
        .nav .nav-with-toggle{display:flex;align-items:center;gap:6px;padding:10px 12px;border-radius:8px;min-height:44px}
        .nav .nav-with-toggle.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav .nav-with-toggle:not(.active):hover{background:#f1f5f9}
        .nav .nav-with-toggle .vehicle-link{display:flex;align-items:center;gap:12px;flex:1;color:inherit;text-decoration:none}
        .nav .nav-with-toggle .toggle-btn{width:28px;height:28px;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;line-height:1}
        .nav .nav-with-toggle.active .toggle-btn{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.35);color:#fff}

        .main{flex:1;padding:16px;margin-top:var(--topbar-height)}
        .panel{background:var(--panel);padding:18px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);max-width:980px;margin:10px auto}
        .muted{color:var(--muted);font-size:13px}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a;display:inline-flex;align-items:center}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .btn.light{background:#f3f4f6;border-color:#d1d5db;color:#374151}
        .actions{display:flex;gap:10px;flex-wrap:wrap}
        .upload-form{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between}
        .upload-form .btn{margin-left:auto}
        .replace-wrap{display:flex;justify-content:center;margin-top:12px}
        .replace-form{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:center}
        .preview{margin-top:14px}
        .preview img{max-width:100%;width:auto;max-height:520px;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc;display:block}
        .empty{padding:16px;border:1px dashed #cbd5e1;border-radius:10px;background:#f8fafc;color:#64748b}
        .toast{position:fixed;right:20px;bottom:20px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,.2);z-index:200;display:none}
        .toast.show{display:block}
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
                <button id="burger-top" class="burger" aria-label="Toggle menu" title="Toggle menu" style="display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </button>
                <div style="display:flex;flex-direction:column">
                        <a href="/dashboard" class="brand-title" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:6px">
                            <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                            <span>San Juan CDRRMD Vehicle ORCR</span>
                        </a>
                    <div class="brand-subtitle">Verified OR/CR profile and registration details</div>
                </div>
            </div>
            <div style="text-align:right;display:flex;align-items:center;gap:12px;justify-content:flex-end">
                <div class="notif-bell" id="notif-bell">
                    <button id="notif-toggle" aria-haspopup="true" aria-expanded="false" title="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="notif-count" id="notif-count" style="display:none">0</div>
                    <div class="notif-dropdown" id="notif-dropdown" aria-hidden="true"></div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:13px;color:var(--muted-2)">Welcome!</div>
                    <div style="font-weight:700">{{ auth()->user()->name }}</div>
                </div>
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
                <div class="nav-with-toggle active">
                    <a href="/vehicle" id="vehicle-nav-link" class="vehicle-link"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13l1.5-4.5A2 2 0 016.4 7h11.2a2 2 0 011.9 1.5L21 13v5a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H6v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM6 14h12M7.5 10.5h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="label">Vehicle</span></a>
                    <button id="vehicle-submenu-toggle" class="toggle-btn" type="button" aria-label="Toggle Maintenance menu" title="Toggle Maintenance menu">▾</button>
                </div>
                <div id="vehicle-submenu" style="display:none">
                    <a href="/vehicle/monitoring" class="sub-link {{ request()->is('vehicle/monitoring*') ? 'active' : '' }}"><span class="label">Monitoring</span></a>
                    <a href="/vehicle/maintenance" class="sub-link {{ request()->is('vehicle/maintenance*') ? 'active' : '' }}"><span class="label">Maintenance</span></a>
                </div>
                <a href="/requests"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Logout</span></a>
            </nav>
        </aside>

        <main class="main">
            <div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
                    <h2 style="margin:0">{{ $vehicle->name }} OR/CR</h2>
                    <a href="/vehicle" class="btn light">Back to Vehicles</a>
                </div>
                <p class="muted" style="margin:6px 0 14px 0">Plate: {{ $vehicle->plate_number ?: 'No plate' }}</p>

                @if($vehicle->orcr_image_path)
                    <div class="actions" style="margin-bottom:10px">
                        <a class="btn" href="{{ asset('storage/' . $vehicle->orcr_image_path) }}" target="_blank" rel="noopener">Open Full Image</a>
                    </div>
                    <div class="preview">
                        <img src="{{ asset('storage/' . $vehicle->orcr_image_path) }}" alt="OR/CR Photo">
                    </div>
                    <div class="replace-wrap">
                        <form method="POST" action="/vehicle/{{ $vehicle->id }}/orcr" enctype="multipart/form-data" class="replace-form">
                            @csrf
                            <input name="orcr_image" type="file" accept="image/*" required>
                            <button class="btn primary" type="submit">Replace OR/CR Photo</button>
                        </form>
                    </div>
                @else
                    <div class="empty">No OR/CR photo uploaded yet.</div>
                    <form method="POST" action="/vehicle/{{ $vehicle->id }}/orcr" enctype="multipart/form-data" class="upload-form" style="margin-top:12px">
                        @csrf
                        <input name="orcr_image" type="file" accept="image/*" required>
                        <button class="btn primary" type="submit">Upload OR/CR Photo</button>
                    </form>
                @endif
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
            const submenu = document.getElementById('vehicle-submenu');
            const submenuToggle = document.getElementById('vehicle-submenu-toggle');
            if(!submenu || !submenuToggle) return;
            submenuToggle.textContent = '▾';
            submenuToggle.addEventListener('click', function(e){
                e.preventDefault();
                submenu.style.display = submenu.style.display === 'none' ? '' : 'none';
            });
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
            const toast = document.getElementById('success-toast');
            if(!toast) return;
            toast.classList.add('show');
            setTimeout(()=> toast.classList.remove('show'), 3500);
        })();
    </script>
</body>
</html>
