<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vehicle Maintenance — San Juan CDRMMD</title>
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
        .sidebar.collapsed{width:64px}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a,.nav button.action{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px}
        .nav a:hover,.nav button.action:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 12px;font-size:13px;justify-content:flex-start;text-align:left}
        .nav .nav-with-toggle{position:relative;display:flex;align-items:center;border-radius:8px;min-height:44px}
        .nav .nav-with-toggle.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff}
        .nav .nav-with-toggle .vehicle-link{display:flex;align-items:center;gap:12px;flex:1;color:inherit;text-decoration:none;padding:10px 36px 10px 12px;border-radius:8px}
        .nav .nav-with-toggle .toggle-btn{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;line-height:1;padding:2px 4px;opacity:1}
        .main{flex:1;padding:16px;margin-top:var(--topbar-height)}
        .sidebar{transform:translateX(-110%);transition:transform .22s ease,width .22s ease}
        .sidebar.open{transform:translateX(0);z-index:90}
        .sidebar.collapsed{width:64px;transform:translateX(0)}
        .panel{background:var(--panel);padding:14px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:min(1240px,calc(100% - 24px));margin:10px auto}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .btn.success{background:#10b981;border:none;color:#fff}
        .btn.warn{background:#f59e0b;border:none;color:#fff}
        .btn.danger{background:#ef4444;border:none;color:#fff}
        .actions{display:flex;gap:8px;flex-wrap:wrap}
        .page-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
        .page-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .page-actions .btn{min-height:38px;font-size:14px;font-weight:600}
        .delete-header{text-align:right}
        .delete-cell{text-align:right;white-space:nowrap}
        .timeline-meta{font-size:12px;line-height:1.45}
        .row-text{display:block;max-width:100%;word-break:break-word}
        .row-text .row-full{display:none}
        .row-text.expanded .row-short{display:none}
        .row-text.expanded .row-full{display:inline}
        .view-more-btn{border:none;background:transparent;color:var(--accent);cursor:pointer;font-size:12px;font-weight:600;padding:0;margin-left:6px;text-decoration:underline}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:14px}
        th,td{padding:10px 8px;border-bottom:1px solid #edf2f7;text-align:left;vertical-align:top}
        th{font-size:12px;text-transform:uppercase;letter-spacing:.3px;color:var(--muted);font-weight:700}
        .muted{color:var(--muted);font-size:13px}
        .maintenance-row{cursor:pointer}
        .modal-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;z-index:210}
        .modal-backdrop.show{display:block}
        .modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:min(900px,94vw);max-height:90vh;background:#fff;border-radius:14px;box-shadow:0 30px 70px rgba(2,6,23,.3);display:none;z-index:220;overflow:auto}
        .modal.show{display:block}
        .modal-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #e5e7eb}
        .modal-close{border:none;background:#f3f4f6;color:#111827;width:32px;height:32px;border-radius:8px;cursor:pointer}
        .modal-body{padding:14px 16px}
        .modal-img{max-width:100%;max-height:72vh;display:block;margin:0 auto;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc}
        .confirm-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.45);display:none;z-index:230}
        .confirm-backdrop.show{display:block}
        .confirm-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:min(420px,92vw);background:#fff;border-radius:12px;box-shadow:0 24px 60px rgba(2,6,23,.28);display:none;z-index:240;padding:16px}
        .confirm-modal.show{display:block}
        .confirm-title{font-weight:700;font-size:16px;margin:0 0 8px 0}
        .confirm-text{color:var(--muted);font-size:14px;line-height:1.45;margin:0}
        .confirm-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:14px}
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
        @media(max-width:900px){.sidebar{position:fixed;left:0;top:0;bottom:0;z-index:80;transform:translateX(-110%);height:100vh}.sidebar.open{transform:translateX(0)}.main{padding:16px}}
        @media(max-width:900px){.page-actions{width:100%}.page-actions .btn{flex:1}}
        /* Mobile: stack maintenance table rows into cards */
        @media (max-width:900px) {
            table thead { display: none; }
            table, table tbody, table tr { display: block; width: 100%; }
            table tbody tr { margin-bottom: 12px; background: #fff; padding: 12px; border-radius: 10px; box-shadow: 0 8px 20px rgba(2,6,23,0.04); border: 1px solid rgba(14,21,40,0.04); }
            table tbody td { display: block; padding: 6px 0; border: none; }
            table tbody td:first-child { font-weight:700; margin-bottom:6px }
            table tbody td:nth-child(2)::before { content: 'Task: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(3)::before { content: 'Due: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(4)::before { content: 'Notes: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(5)::before { content: 'Timeline: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(6)::before { content: 'Delete: '; font-weight:700; color:var(--muted); }
            table tbody td::before { display:inline-block; margin-right:6px }
            .delete-header{text-align:left}
            .delete-cell{text-align:left}
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
                <button id="burger-top" class="burger" aria-label="Toggle menu" title="Toggle menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></button>
                <div class="branding">
                    <a href="/dashboard" class="brand-title" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:6px">
                        <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                        <span style="font-weight:700">San Juan CDRMMD Vehicle Maintenance</span>
                    </a>
                    <div class="brand-subtitle">View all maintenance records</div>
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
                    <h2 style="margin:0">Vehicle Maintenance</h2>
                    <div class="page-actions">
                        <a href="/vehicle/maintenance/add" class="btn primary">Add Maintenance</a>
                        <a href="/vehicle" class="btn">Back to Vehicles</a>
                    </div>
                </div>
                <div class="muted" style="margin-top:6px">All maintenance records are shown below.</div>
            </div>

            <div class="panel">
                <h3 style="margin-top:0">All Maintenance List</h3>
                <div style="overflow:auto">
                <table>
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Maintenance Task</th>
                            <th>Due</th>
                            <th>Notes</th>
                            <th>Timeline</th>
                            <th class="delete-header">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                            <tr class="maintenance-row" onclick="openUploadedPhoto(this)" data-photo-url="{{ $maintenance->evidence_image_path ? asset('storage/' . $maintenance->evidence_image_path) : '' }}">
                                <td>
                                    <div style="font-weight:700">{{ $maintenance->vehicle->name ?? '—' }}</div>
                                    <div class="muted">{{ $maintenance->vehicle->plate_number ?? 'No plate' }}</div>
                                </td>
                                <td>
                                    @php
                                        $taskText = (string) ($maintenance->task ?? '—');
                                        $taskIsLong = \Illuminate\Support\Str::length($taskText) > 70;
                                    @endphp
                                    <span class="row-text">
                                        <span class="row-short">{{ $taskIsLong ? \Illuminate\Support\Str::limit($taskText, 70) : $taskText }}</span>
                                        @if($taskIsLong)
                                            <span class="row-full">{{ $taskText }}</span>
                                            <button type="button" class="view-more-btn" onclick="toggleRowText(this, event)">View more</button>
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $maintenance->due_date ? $maintenance->due_date->format('Y-m-d') : '—' }}</td>
                                <td>
                                    @php
                                        $notesText = (string) ($maintenance->notes ?: '—');
                                        $notesIsLong = \Illuminate\Support\Str::length($notesText) > 90;
                                    @endphp
                                    <span class="row-text">
                                        <span class="row-short">{{ $notesIsLong ? \Illuminate\Support\Str::limit($notesText, 90) : $notesText }}</span>
                                        @if($notesIsLong)
                                            <span class="row-full">{{ $notesText }}</span>
                                            <button type="button" class="view-more-btn" onclick="toggleRowText(this, event)">View more</button>
                                        @endif
                                    </span>
                                </td>
                                <td onclick="event.stopPropagation()">
                                    <div class="muted timeline-meta">Reviewed: {{ $maintenance->reviewed_at ? $maintenance->reviewed_at->format('Y-m-d H:i') : '—' }}<br>Checked: {{ $maintenance->checked_at ? $maintenance->checked_at->format('Y-m-d H:i') : '—' }}<br>Updated: {{ $maintenance->updated_marker_at ? $maintenance->updated_marker_at->format('Y-m-d H:i') : '—' }}</div>
                                </td>
                                <td class="delete-cell" onclick="event.stopPropagation()">
                                    <form class="js-delete-maintenance-form" method="POST" action="/vehicle/{{ $maintenance->vehicle_id }}/maintenance/{{ $maintenance->id }}/delete">@csrf<button class="btn danger" type="submit">Delete</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="muted">No maintenance entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </main>
    </div>

    <div id="photo-modal-backdrop" class="modal-backdrop"></div>
    <div id="photo-modal" class="modal">
        <div class="modal-head">
            <div style="font-weight:700">Uploaded Photo</div>
            <button id="photo-modal-close" class="modal-close" type="button">✕</button>
        </div>
        <div class="modal-body">
            <img id="photo-modal-img" class="modal-img" alt="Uploaded photo" style="display:none">
            <div id="photo-modal-empty" class="muted" style="text-align:center;padding:10px 0">No uploaded photo for this maintenance item.</div>
        </div>
    </div>

    <div id="delete-confirm-backdrop" class="confirm-backdrop"></div>
    <div id="delete-confirm-modal" class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title">
        <h4 id="delete-confirm-title" class="confirm-title">Confirm Deletion</h4>
        <p class="confirm-text">Are you sure you want to delete this maintenance record? This action cannot be undone.</p>
        <div class="confirm-actions">
            <button id="delete-confirm-no" type="button" class="btn">No</button>
            <button id="delete-confirm-yes" type="button" class="btn danger">Yes, Delete</button>
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
            const forms = document.querySelectorAll('.js-delete-maintenance-form');
            const backdrop = document.getElementById('delete-confirm-backdrop');
            const modal = document.getElementById('delete-confirm-modal');
            const yesBtn = document.getElementById('delete-confirm-yes');
            const noBtn = document.getElementById('delete-confirm-no');
            let targetForm = null;
            if(!forms.length || !backdrop || !modal || !yesBtn || !noBtn) return;

            function openConfirm(form){
                targetForm = form;
                backdrop.classList.add('show');
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeConfirm(){
                backdrop.classList.remove('show');
                modal.classList.remove('show');
                document.body.style.overflow = '';
                targetForm = null;
            }

            forms.forEach(function(form){
                form.addEventListener('submit', function(e){
                    if(form.dataset.confirmed === '1') return;
                    e.preventDefault();
                    openConfirm(form);
                });
            });

            yesBtn.addEventListener('click', function(){
                if(!targetForm) return closeConfirm();
                targetForm.dataset.confirmed = '1';
                targetForm.submit();
            });

            noBtn.addEventListener('click', closeConfirm);
            backdrop.addEventListener('click', closeConfirm);
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape' && modal.classList.contains('show')) closeConfirm();
            });
        })();

        function openUploadedPhoto(row){
            const url = row.dataset.photoUrl || '';
            const backdrop = document.getElementById('photo-modal-backdrop');
            const modal = document.getElementById('photo-modal');
            const img = document.getElementById('photo-modal-img');
            const empty = document.getElementById('photo-modal-empty');
            if(!backdrop || !modal || !img || !empty) return;
            if(url){ img.src = url; img.style.display = 'block'; empty.style.display = 'none'; }
            else { img.style.display = 'none'; empty.style.display = 'block'; }
            backdrop.classList.add('show');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function toggleRowText(button, event){
            if(event) event.stopPropagation();
            const wrapper = button.closest('.row-text');
            if(!wrapper) return;
            const expanded = wrapper.classList.toggle('expanded');
            button.textContent = expanded ? 'View less' : 'View more';
        }

        (function(){
            const backdrop = document.getElementById('photo-modal-backdrop');
            const modal = document.getElementById('photo-modal');
            const closeBtn = document.getElementById('photo-modal-close');
            if(!backdrop || !modal || !closeBtn) return;
            function closeModal(){ backdrop.classList.remove('show'); modal.classList.remove('show'); document.body.style.overflow = ''; }
            closeBtn.addEventListener('click', closeModal);
            backdrop.addEventListener('click', closeModal);
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && modal.classList.contains('show')) closeModal(); });
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
