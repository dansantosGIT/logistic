<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Add Maintenance — San Juan CDRMMD</title>
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
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transition:width .22s ease,transform .22s ease;z-index:50;height:calc(100vh - var(--topbar-height))}
        .main{flex:1;padding:16px;margin-top:var(--topbar-height)}
        .sidebar{transform:translateX(-110%);transition:transform .22s ease,width .22s ease}
        .sidebar.open{transform:translateX(0);z-index:90}
        .panel{background:var(--panel);padding:14px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:calc(100% - 24px);margin:10px auto}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .field{display:flex;flex-direction:column;gap:6px}
        .field.full{grid-column:1/-1}
        input,select,textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font:inherit}
        textarea{min-height:90px;resize:vertical}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .btn.ghost{background:#fff;border:1px solid #d1d5db;color:#334155}
        .actions{display:flex;gap:8px;flex-wrap:wrap}
        .page-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
        .page-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .page-actions .btn{min-height:38px;font-size:14px;font-weight:600}
        .muted{color:var(--muted);font-size:13px}
        .photo-upload{border:1px dashed #cbd5e1;border-radius:10px;padding:10px;background:#f8fafc}
        .photo-upload.disabled{opacity:.65}
        .photo-upload-top{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
        .photo-file-name{font-size:12px;color:var(--muted);max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .photo-preview{margin-top:10px;max-width:100%;max-height:220px;border-radius:10px;border:1px solid #e2e8f0;display:none;background:#fff}
        .photo-upload-actions{display:flex;align-items:center;gap:8px;margin-top:10px}
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
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a,.nav button.action{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px}
        .nav a:hover,.nav button.action:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 12px;font-size:13px;justify-content:flex-start;text-align:left}
        .nav .nav-with-toggle{position:relative;display:flex;align-items:center;border-radius:8px;min-height:44px}
        .nav .nav-with-toggle.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav .nav-with-toggle .vehicle-link{display:flex;align-items:center;gap:12px;flex:1;color:inherit;text-decoration:none;padding:10px 36px 10px 12px;border-radius:8px}
        .nav .nav-with-toggle .toggle-btn{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;line-height:1;padding:2px 4px;opacity:1}
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
            <div class="left-area">
                <button id="burger-top" class="burger" aria-label="Toggle menu" title="Toggle menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button>
                <div class="branding">
                    <a href="/dashboard" class="brand-title" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:6px">
                        <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                        <span style="font-weight:700">San Juan CDRMMD Add Maintenance</span>
                    </a>
                    <div class="brand-subtitle">Create a new maintenance record</div>
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
        <aside class="sidebar" id="sidebar">
            <a href="/dashboard" class="brand" style="text-decoration:none;color:inherit">
                <img src="/images/favi.png" alt="San Juan" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                <div class="text" style="font-size:14px">San Juan CDRMMD</div>
            </a>
            <nav class="nav">
                <a href="/dashboard"><span class="label">Home</span></a>
                <a href="/inventory"><span class="label">Inventory</span></a>
                <div id="vehicle-nav-group" class="nav-with-toggle active">
                    <a href="/vehicle" id="vehicle-nav-link" class="vehicle-link"><span class="label">Vehicle</span></a>
                    <button id="vehicle-submenu-toggle" class="toggle-btn" type="button" aria-label="Toggle Vehicle submenu" title="Toggle Vehicle submenu">▾</button>
                </div>
                <div id="vehicle-submenu" style="display:none">
                    <a href="/vehicle/maintenance" class="sub-link active"><span class="label">Maintenance</span></a>
                    <a href="/vehicle/monitoring" class="sub-link"><span class="label">Monitoring</span></a>
                </div>
                <a href="/requests"><span class="label">Request</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><span class="label">Logout</span></a>
            </nav>
        </aside>

        <main class="main">
            <div class="panel">
                <div class="page-head">
                    <h2 style="margin:0">Add Maintenance</h2>
                    <div class="page-actions">
                        <a href="/vehicle/maintenance" class="btn">View Maintenance List</a>
                        <a href="/vehicle" class="btn">Back to Vehicles</a>
                    </div>
                </div>
                <div class="muted" style="margin-top:6px">Fill in the details below to add a new maintenance record.</div>
            </div>

            <div class="panel">
                <form method="POST" action="/vehicle/maintenance" enctype="multipart/form-data">
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
                            <label for="task">Maintenance Task</label>
                            <textarea id="task" name="task" required placeholder="Type full maintenance task details" {{ empty($vehicles) ? 'disabled' : '' }}></textarea>
                        </div>
                        <div class="field">
                            <label for="due_date">Due Date</label>
                            <input id="due_date" name="due_date" type="date" {{ empty($vehicles) ? 'disabled' : '' }}>
                        </div>
                        <div class="field">
                            <label for="maintenance-supervisor-photo">Upload Photo (optional)</label>
                            <div class="photo-upload {{ empty($vehicles) ? 'disabled' : '' }}">
                                <input id="maintenance-supervisor-photo" name="supervisor_photo" type="file" accept="image/*" {{ empty($vehicles) ? 'disabled' : '' }}>
                                <div class="photo-upload-top"><span id="supervisor-photo-file-name" class="photo-file-name">No image selected</span></div>
                                <img id="supervisor-photo-preview" class="photo-preview" alt="Upload photo preview">
                                <div class="photo-upload-actions"><button id="supervisor-photo-clear" type="button" class="btn ghost" {{ empty($vehicles) ? 'disabled' : '' }}>Remove Image</button></div>
                                <div class="muted" style="margin-top:8px">Image only (JPG, PNG, WEBP) up to 5MB.</div>
                            </div>
                        </div>
                        <div class="field full">
                            <label for="maintenance-notes">Notes</label>
                            <textarea id="maintenance-notes" name="notes" placeholder="Optional notes for this maintenance task" {{ empty($vehicles) ? 'disabled' : '' }}></textarea>
                        </div>
                        <div class="field full"><button type="submit" class="btn primary" style="min-height:40px;font-weight:600" {{ empty($vehicles) ? 'disabled' : '' }}>Save Maintenance</button></div>
                    </div>
                </form>
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
            const topbar = document.querySelector('.topbar');
            let navOverlay = document.getElementById('nav-overlay');
            if(!navOverlay){ navOverlay = document.createElement('div'); navOverlay.id = 'nav-overlay'; navOverlay.className = 'nav-overlay'; document.body.appendChild(navOverlay); }
            if(!burger || !sidebar) return;
            function setOverlay(show){ navOverlay.classList.toggle('show', !!show); document.body.style.overflow = show ? 'hidden' : ''; }
            burger.addEventListener('click', function(e){ e.stopPropagation(); const willOpen = !sidebar.classList.contains('open'); sidebar.classList.toggle('open'); setOverlay(willOpen); });
            document.addEventListener('click', function(e){ if(sidebar.classList.contains('open') && !sidebar.contains(e.target) && !burger.contains(e.target) && !topbar.contains(e.target)){ sidebar.classList.remove('open'); setOverlay(false); } });
            navOverlay.addEventListener('click', function(){ sidebar.classList.remove('open'); setOverlay(false); });
        })();

        (function(){
            const submenu = document.getElementById('vehicle-submenu');
            const submenuToggle = document.getElementById('vehicle-submenu-toggle');
            if(!submenu || !submenuToggle) return;
            submenuToggle.addEventListener('click', function(e){ e.preventDefault(); submenu.style.display = submenu.style.display === 'none' ? '' : 'none'; });
        })();

        (function(){ const toast = document.getElementById('success-toast'); if(!toast) return; toast.classList.add('show'); setTimeout(()=> toast.classList.remove('show'), 3500); })();

        (function(){
            const input = document.getElementById('maintenance-supervisor-photo');
            const preview = document.getElementById('supervisor-photo-preview');
            const fileName = document.getElementById('supervisor-photo-file-name');
            const clearBtn = document.getElementById('supervisor-photo-clear');
            if(!input || !preview || !fileName || !clearBtn) return;
            function resetPreview(){ input.value = ''; preview.removeAttribute('src'); preview.style.display = 'none'; fileName.textContent = 'No image selected'; }
            input.addEventListener('change', function(){
                const file = input.files && input.files[0] ? input.files[0] : null;
                if(!file){ resetPreview(); return; }
                if(!file.type || !file.type.startsWith('image/')){ alert('Please upload an image file only.'); resetPreview(); return; }
                const maxBytes = 5 * 1024 * 1024;
                if(file.size > maxBytes){ alert('Image must be 5MB or smaller.'); resetPreview(); return; }
                fileName.textContent = file.name;
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            });
            clearBtn.addEventListener('click', function(){ resetPreview(); });
        })();

        (function(){
            const dd = document.querySelector('.notif-dropdown');
            if(!dd) return;
            dd.addEventListener('click', function(e){
                if(e.target.closest('.actions')) return;
                const item = e.target.closest('.item');
                if(!item) return;
                const id = item.dataset.uuid || item.getAttribute('data-uuid') || item.getAttribute('data-id');
                if(id) window.location.href = '/requests/' + id;
            });
        })();
    </script>
</body>
</html>
