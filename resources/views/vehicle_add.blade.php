<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Add Vehicle — San Juan CDRMMD</title>
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
        .panel{background:var(--panel);padding:14px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:calc(100% - 24px);margin:10px auto;max-width:920px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .field{display:flex;flex-direction:column;gap:6px}
        .field.full{grid-column:1/-1}
        input,textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font:inherit}
        textarea{min-height:90px;resize:vertical}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .actions{display:flex;justify-content:flex-end;gap:10px}
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
                    <span>Add Vehicle</span>
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
                <h2 style="margin:0 0 10px 0">Add Vehicle</h2>
                <form method="POST" action="/vehicle" enctype="multipart/form-data">
                    @csrf
                    <div class="form-grid">
                        <div class="field full">
                            <label for="vehicle-name">Vehicle Name / Call Sign</label>
                            <input id="vehicle-name" name="name" required placeholder="e.g., Alpha 01">
                        </div>
                        <div class="field">
                            <label for="vehicle-type">Vehicle Type</label>
                            <input id="vehicle-type" name="type" required placeholder="e.g., Ambulance">
                        </div>
                        <div class="field">
                            <label for="vehicle-brand">Brand</label>
                            <input id="vehicle-brand" name="brand" placeholder="e.g., Isuzu">
                        </div>
                        <div class="field">
                            <label for="vehicle-year">Year</label>
                            <input id="vehicle-year" name="year" type="number" min="1900" max="2100" placeholder="e.g., 2022">
                        </div>
                        <div class="field">
                            <label for="vehicle-plate">Plate Number</label>
                            <input id="vehicle-plate" name="plate_number" placeholder="e.g., ABC-1234">
                        </div>
                        <div class="field full">
                            <label for="vehicle-image">Vehicle Image</label>
                            <input id="vehicle-image" name="image" type="file" accept="image/*">
                        </div>
                        <div class="field full" style="flex-direction:row;align-items:center;gap:10px">
                            <input id="vehicle-firetruck" name="is_firetruck" type="checkbox" value="1" style="width:auto">
                            <label for="vehicle-firetruck" style="margin:0">This vehicle is a firetruck</label>
                        </div>
                        <div class="field full">
                            <label for="vehicle-notes">Notes</label>
                            <textarea id="vehicle-notes" name="notes" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                    <div class="actions" style="margin-top:12px">
                        <a href="/vehicle" class="btn">Back to Vehicle List</a>
                        <button class="btn primary" type="submit">Save Vehicle</button>
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
    </script>
</body>
</html>
