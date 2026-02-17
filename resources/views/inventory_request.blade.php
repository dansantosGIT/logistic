<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Request Equipment — San Juan CDRMMD</title>
    <link rel="icon" href="/images/favi.png" type="image/png">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}

        /* Full-bleed background image (slightly transparent via overlay) */
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}

        /* Topbar */
        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.96);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.06);z-index:60}
          /* make the topbar full-width so the burger can sit flush left
              and the welcome/admin stays right, maximizing header space */
          .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}
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
        .notif-dropdown .meta .sub{font-size:12px;color:var(--muted-2);margin-top:4px}
        .notif-dropdown .time{font-size:11px;color:var(--muted-2);margin-left:6px}
        .notif-dropdown .actions{display:flex;gap:6px;flex-shrink:0}
        .notif-dropdown .empty{padding:12px;color:var(--muted);text-align:center}

        .app{display:flex;min-height:100vh}

        /* Sidebar - same as dashboard */
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transition:width .22s ease,transform .22s ease;z-index:50;height:calc(100vh - var(--topbar-height))}
        .sidebar.collapsed{width:64px}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .brand .logo{width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:inline-flex;align-items:center;justify-content:center;color:white;font-weight:800}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a, .nav button.action{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px;width:100%;box-sizing:border-box}
        .nav a svg, .nav button.action svg{display:block;width:18px;height:18px;min-width:18px}
        .nav a:hover, .nav button.action:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:white}
        .nav svg{flex-shrink:0}
        .nav a .label{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

        /* Main area */
        .main{flex:1;padding:24px;margin-top:var(--topbar-height);margin-left:0;transition:margin .22s ease}
        /* Sidebar will act as an overlay by default (hidden). When opened it slides over content. */
        .sidebar{transform:translateX(-110%);transition:transform .22s ease,width .22s ease}
        .sidebar.open{transform:translateX(0);z-index:90}
        /* collapsed still supported (icon-only) */
        .sidebar.collapsed{width:64px;transform:translateX(0)}

        /* Panel and form polish */
        .panel{background:var(--panel);padding:22px;border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,0.06);max-width:980px;margin:20px auto}
        .panel h2, .panel h3{font-weight:700;margin:0}
        .row{display:flex;gap:12px;align-items:center}
        .tabs{display:flex;gap:6px;margin-bottom:12px}
        .tab{background:#f1f5f9;padding:8px 12px;border-radius:8px;font-size:13px;color:#0f172a}
        .tab.active{background:white;box-shadow:0 2px 8px rgba(2,6,23,0.04)}

        /* Form layout */
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:6px}
        .form-grid .field.full{grid-column:1 / -1}
        .field{display:block}
        .field label{display:block;font-weight:700;color:var(--muted);margin-bottom:8px}
        .field small{display:block;color:var(--muted-2);font-size:12px;margin-top:6px}
        input[type="text"], input[type="number"], input[type="date"], select, textarea{width:100%;padding:11px 12px;border:1px solid #e6e9ef;border-radius:10px;background:linear-gradient(180deg,#fff,#fbfdff);font-size:14px;color:#0f172a}
        input:focus, select:focus, textarea:focus{outline:none;box-shadow:0 8px 24px rgba(37,99,235,0.12);border-color:rgba(37,99,235,0.28)}
        textarea{min-height:140px;resize:vertical}
        .file-input{display:flex;gap:8px;align-items:center}
        .helper{font-size:12px;color:var(--muted-2)}
        .actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px}
        .btn.primary{background:#10b981;border:none;padding:10px 14px;font-weight:600;border-radius:10px}
        .btn{border-radius:10px}
        @media(max-width:860px){.form-grid{grid-template-columns:1fr}}

        /* Table styles */
        .search-row{display:flex;gap:12px;margin-bottom:12px}
        input.search{flex:1;padding:10px;border:1px solid #e6e9ef;border-radius:8px}
        select.page-size{padding:8px 10px;border-radius:8px;border:1px solid #e6e9ef}

        .table-wrap{background:linear-gradient(180deg,rgba(255,255,255,0.72),rgba(250,250,255,0.56));padding:16px;border-radius:12px;backdrop-filter:blur(6px);box-shadow:0 8px 36px rgba(2,6,23,0.04)}
        table.inventory-table{width:100%;border-collapse:separate;border-spacing:0;background:transparent;table-layout:auto}
        thead th{background:linear-gradient(90deg,#eef8ff,#f6f0ff);padding:18px 16px;text-align:left;font-size:14px;color:var(--muted);border-bottom:1px solid rgba(14,21,40,0.04)}
        tbody td{background:linear-gradient(180deg,#ffffff,#fbfdff);padding:18px 16px;vertical-align:middle;border-bottom:1px solid rgba(14,21,40,0.03);transition:transform .18s cubic-bezier(.2,.9,.2,1),box-shadow .18s ease,background .18s ease;font-size:15px;line-height:1.45}
        tbody tr:hover td{transform:translateY(-6px);box-shadow:0 14px 34px rgba(2,6,23,0.06)}
        tbody tr td:first-child{border-top-left-radius:8px;border-bottom-left-radius:8px}
        tbody tr td:last-child{border-top-right-radius:8px;border-bottom-right-radius:8px}
        tbody tr:nth-child(odd) td{background:linear-gradient(180deg,#ffffff,#fcfeff)}
        /* Column min-widths to avoid cramped content */
        thead th:nth-child(1), tbody td:nth-child(1){min-width:200px}
        thead th:nth-child(2), tbody td:nth-child(2){min-width:150px}
        thead th:nth-child(3), tbody td:nth-child(3){min-width:110px}
        thead th:nth-child(4), tbody td:nth-child(4){min-width:140px}
        thead th:nth-child(5), tbody td:nth-child(5){min-width:80px;text-align:center}
        thead th:nth-child(6), tbody td:nth-child(6){min-width:120px}
        thead th:nth-child(7), tbody td:nth-child(7){min-width:120px;text-align:center}
        thead th:nth-child(8), tbody td:nth-child(8){min-width:120px}
        thead th:nth-child(9), tbody td:nth-child(9){min-width:180px;text-align:right}

        .badge{display:inline-flex;align-items:center;justify-content:center;min-height:28px;padding:6px 12px;border-radius:999px;font-size:13px;color:white}
        .badge.consumable{background:#10b981}
        .badge.nonconsumable{background:#6b7280}
        /* Stock status badges */
        .badge.instock{background:#10b981}
        .badge.low{background:#f59e0b}
        .badge.out{background:#ef4444}

        .actions{display:flex;gap:8px;justify-content:flex-end}
        /* Buttons: smaller by default; primary is larger */
        .btn{padding:6px 10px;font-size:13px;border-radius:8px;border:1px solid #e6e9ef;background:white;cursor:pointer;transition:transform .08s ease,box-shadow .08s ease,background .08s ease,color .08s ease}
        .btn:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(15,23,42,0.06);background:#f8fafc}
        .btn.primary{padding:8px 14px;font-size:14px;background:#2563eb;color:white;border:none}
        .btn.primary:hover{background:#1e4fd8}
        /* Action button colors and spacing */
        .btn.request{background:#2563eb;color:white;border:none}
        .btn.request:hover{background:#1e4fd8}
        .btn.edit{background:white;color:#0f172a;border:1px solid #e6e9ef}
        .btn.edit:hover{background:#f8fafc}
        .btn.delete{background:#ef4444;color:white;border:none}
        .btn.delete:hover{background:#dc2626}
        /* make table row action buttons consistent and non-overlapping */
        tbody td .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:6px 10px;font-size:13px;border-radius:8px;margin-left:6px;white-space:nowrap;vertical-align:middle}
        tbody td .btn.edit{margin-left:0} /* keep edit closer to item */

        /* Tabs hover to match button feedback */
        .tabs .tab{transition:transform .08s ease,box-shadow .08s ease,background .08s ease}
        .tabs .tab:hover{cursor:pointer;transform:translateY(-1px);background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.04)}

        /* Collapsed state adjustments (same behavior as dashboard) */
        .sidebar.collapsed .brand .text,
        .sidebar.collapsed .nav a span.label,
        .sidebar.collapsed .nav button.action span.label{display:none}
        .sidebar.collapsed .nav a,
        .sidebar.collapsed .nav button.action{justify-content:center;padding-left:0;padding-right:0}
        .sidebar.collapsed .nav a svg,
        .sidebar.collapsed .nav button.action svg{margin:0 auto}
        .sidebar.collapsed .brand{justify-content:center}

        .pagination{display:flex;justify-content:flex-end;padding-top:12px;gap:6px}
        .page-pill{padding:6px 10px;border-radius:6px;background:#eef2f6}

        /* Toast success */
        .toast{position:fixed;right:24px;bottom:24px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,0.12);transform:translateY(12px);opacity:0;transition:opacity .22s,transform .22s;z-index:220;display:flex;align-items:center;gap:10px}
        .toast.show{opacity:1;transform:translateY(0)}
        .toast .close{cursor:pointer;padding:6px;border-radius:6px;background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.9)}

        /* Responsive */
        /* overlay backdrop for when sidebar opens */
        .nav-overlay{position:fixed;left:0;right:0;top:var(--topbar-height);bottom:0;background:rgba(2,6,23,0.45);opacity:0;visibility:hidden;transition:opacity .18s ease;z-index:80}
        .nav-overlay.show{opacity:1;visibility:visible}

        @media(max-width:900px){
            .sidebar{position:fixed;left:0;top:0;bottom:0;z-index:90;height:100vh}
            .sidebar.open{transform:translateX(0)}
            .main{padding:16px}
        }
    </style>
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
                        <div style="display:flex;align-items:center;gap:6px;font-weight:700">
                            <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                            <span>Request Equipment</span>
                        </div>
                        <div style="font-size:12px;color:var(--muted)">Request / New</div>
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
                    <div style="font-size:13px;color:var(--muted-2)">Welcome</div>
                    <div style="font-weight:700">{{ auth()->user()->name }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="app">
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <img src="/images/favi.png" alt="San Juan" class="logo-img" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                <div class="text" style="font-size:14px">San Juan CDRMMD</div>
            </div>
            <nav class="nav">
                <a href="/dashboard"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3v6h8V3h-8zM3 21h8v-6H3v6z" fill="currentColor"/></svg><span class="label">Home</span></a>
                <a href="/inventory" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7a5 5 0 100 10 5 5 0 000-10zM2 12a10 10 0 1120 0A10 10 0 012 12z" fill="currentColor"/></svg><span class="label">Inventory</span></a>
                <a href="/requests"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="#"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zM3 13h3l1-3 2 2 3-4 2 4 3-2 1 3h3" stroke="currentColor" stroke-width="1" fill="none"/></svg><span class="label">Settings</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg>
                    <span class="label">Logout</span>
                </a>
            </nav>
        </aside>
        <main class="main">
            <div class="panel" style="max-width:820px;padding:14px 14px">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                            <div style="flex:1">
                                <h3 style="margin:0 0 6px">{{ $item->name }}</h3>
                                <div style="color:var(--muted);font-size:13px;margin-bottom:8px">Type: <strong>{{ $item->type ?? '—' }}</strong> — Serial: {{ $item->serial ?? '—' }} — Available: <strong>{{ $item->quantity ?? 0 }}</strong></div>
                                <div style="font-size:13px;color:var(--muted);">Category: <strong style="color:var(--accent)">{{ $item->category ?? '—' }}</strong></div>
                            </div>

                            <div style="flex:0 0 140px;display:flex;align-items:center;justify-content:center">
                                <div style="width:120px;height:90px;border-radius:8px;background:#f6f8fb;border:1px dashed #e6e9ef;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:13px;overflow:hidden">
                                    @if(!empty($item->photo))
                                        <img src="{{ $item->photo }}" alt="{{ $item->name }}" style="width:100%;height:100%;object-fit:cover">
                                    @else
                                        No Photo
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form id="request-form" method="POST" action="/inventory/{{ $item->id }}/request" data-item-type="{{ strtolower(trim($item->type ?? '')) }}">
                            @csrf
                            <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:10px">
                                <div class="field">
                                    <label for="requester">Personnel Name</label>
                                    <input id="requester" name="requester" type="text" placeholder="e.g. Juan Dela Cruz" required>
                                </div>

                                <div class="field">
                                    <label for="role">Role</label>
                                    <select id="role" name="role">
                                        <option>Employee</option>
                                        <option>Volunteer</option>
                                        <option>Medic</option>
                                        <option>Intern</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="qty">Quantity Requesting</label>
                                    <input id="qty" name="quantity" type="number" min="1" value="1" required>
                                </div>

                                <div class="field" id="return-date-field">
                                    <label for="return_date">Date of Return</label>
                                    <input id="return_date" name="return_date" type="date">
                                </div>

                                <div class="field full">
                                    <label for="reason">Reason for Request</label>
                                    <textarea id="reason" name="reason" style="min-height:100px"></textarea>
                                    <small class="helper">Provide a short justification or usage details (optional).</small>
                                </div>
                            </div>

                            <div class="actions"><button type="submit" class="btn primary">Submit Request</button><a href="/inventory" class="btn" style="margin-left:8px">Cancel</a></div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <form id="logout-form" method="POST" action="/logout" style="display:none">@csrf</form>
    <script>
        (function(){
            const sidebar = document.getElementById('sidebar');
            const burger = document.getElementById('burger-top');
            let navOverlay = document.getElementById('nav-overlay');
            const topbar = document.querySelector('.topbar');
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
    </script>
    <script>
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
    <script>
        (function(){
            const bell = document.getElementById('notif-bell');
            const toggle = document.getElementById('notif-toggle');
            const dropdown = document.getElementById('notif-dropdown');
            const countEl = document.getElementById('notif-count');
            let visible = false;

            function csrf(){
                const m = document.querySelector('meta[name="csrf-token"]');
                return m ? m.getAttribute('content') : '';
            }

            function renderItems(items, isAdmin){
                if(!items || !items.length){
                    dropdown.innerHTML = '<div class="item"><div style="padding:12px;color:var(--muted)">No notifications</div></div>';
                    return;
                }
                dropdown.innerHTML = items.map(it=>{
                    const avatar = (it.item_name||'R').trim().charAt(0).toUpperCase();
                    const meta = `<div class=\"meta\"><div class=\"title\">${it.item_name} <span class=\"time\">${it.created_at}</span></div><div class=\"sub\">Requested by ${it.requester}</div></div>`;
                    const actions = isAdmin ? `<div class=\"actions\"><button data-id=\"${it.id}\" data-action=\"approve\" class=\"btn\" title=\"Approve\">✓</button><button data-id=\"${it.id}\" data-action=\"reject\" class=\"btn delete\" title=\"Reject\">✕</button></div>` : '';
                    return `<div class=\"item\" data-id=\"${it.id}\"><div class=\"left\"><div class=\"avatar\">${avatar}</div></div>${meta}${actions}</div>`;
                }).join('');
            }

            async function fetchNotifs(){
                try{
                    const res = await fetch('/notifications/requests', {credentials:'same-origin'});
                    if(!res.ok) return;
                    const data = await res.json();
                    const cnt = data.count || 0;
                    if(cnt){
                        countEl.style.display = '';
                        countEl.textContent = cnt;
                    } else {
                        countEl.style.display = 'none';
                    }
                    const isAdmin = ({{ auth()->user() ? 'true' : 'false' }} && '{{ auth()->user()->name }}'.toLowerCase() === 'admin');
                    renderItems(data.items || [], isAdmin);
                }catch(e){console.error(e)}
            }

            // delegate actions
            dropdown.addEventListener('click', async function(e){
                const btn = e.target.closest('button[data-id]');
                if(!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                try{
                    btn.disabled = true;
                    const res = await fetch('/notifications/requests/'+encodeURIComponent(id)+'/action', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf()
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ action })
                    });
                    if(res.ok){
                        await fetchNotifs();
                    } else {
                        alert('Action failed');
                    }
                }catch(err){console.error(err);alert('Action error')}
                finally{btn.disabled = false}
            });

            toggle.addEventListener('click', function(e){
                visible = !visible;
                dropdown.classList.toggle('show', visible);
                toggle.setAttribute('aria-expanded', visible ? 'true' : 'false');
            });

            // close on outside click
            document.addEventListener('click', function(e){
                if(!bell.contains(e.target)){
                    visible = false; dropdown.classList.remove('show');
                }
            });

            fetchNotifs();
            setInterval(fetchNotifs, 8000);
        })();
    </script>
    <script>
        // show/hide return date based on item type
        (function(){
            const form = document.getElementById('request-form');
            if(!form) return;
            const itemType = (form.dataset.itemType || '').toLowerCase();
            const returnField = document.getElementById('return-date-field');
            const returnInput = document.getElementById('return_date');

            function update(){
                if(itemType === 'consumable'){
                    // hide return date for consumable
                    if(returnField) returnField.style.display = 'none';
                    if(returnInput) { returnInput.removeAttribute('required'); returnInput.value = ''; }
                } else {
                    if(returnField) returnField.style.display = '';
                    if(returnInput) returnInput.setAttribute('required', 'required');
                    // set min date to today
                    const today = new Date().toISOString().split('T')[0];
                    returnInput.setAttribute('min', today);
                }
            }

            update();
        })();
    </script>
</body>
</html>
