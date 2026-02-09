<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Requests — San Juan CDRMMD</title>
    <link rel="icon" href="/images/favi.png" type="image/png">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}

        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}

        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.96);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.06);z-index:60}
        .topbar-inner{max-width:1200px;margin:0 auto;padding:12px 20px;display:flex;justify-content:space-between;align-items:center}

        .app{display:flex;min-height:100vh}

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

        .main{flex:1;padding:24px;margin-top:var(--topbar-height);margin-left:0;transition:margin .22s ease}
        .sidebar{transform:translateX(-110%);transition:transform .22s ease,width .22s ease}
        .sidebar.open{transform:translateX(0);z-index:90}
        .sidebar.collapsed{width:64px;transform:translateX(0)}

        .panel{background:var(--panel);padding:22px;border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,0.06);max-width:1100px;margin:20px auto}
        .tabs{display:flex;gap:6px;margin-bottom:12px}
        .tab{background:transparent;padding:8px 12px;border-radius:999px;font-size:13px;color:#64748b;border:1px solid transparent}
        .tab.active{background:#2563eb;color:white;border:1px solid rgba(37,99,235,0.12);box-shadow:0 6px 18px rgba(37,99,235,0.12)}
        .tab.alt{background:transparent;border:1px solid #e6e9ef;color:#64748b}

        table.inventory-table{width:100%;border-collapse:separate;border-spacing:0;background:transparent;table-layout:auto}
        thead th{background:linear-gradient(90deg,#eef8ff,#f6f0ff);padding:14px 16px;text-align:left;font-size:14px;color:var(--muted);border-bottom:1px solid rgba(14,21,40,0.04)}
        /* card-style row */
        .row-card{display:flex;align-items:center;gap:12px;padding:14px;background:#ffffff;border-radius:8px;box-shadow:0 6px 18px rgba(2,6,23,0.04)}
        .rc-left{min-width:220px}
        .rc-left .requested{font-weight:700}
        .rc-mid{flex:1;display:flex;gap:12px;align-items:center}
        .rc-mid a.link{color:#2563eb;font-weight:600}
        .rc-col{min-width:110px;color:var(--muted)}
        tbody tr td{padding:10px;border-bottom:none}
        .badge{display:inline-flex;align-items:center;justify-content:center;min-height:24px;padding:4px 8px;border-radius:999px;font-size:12px;color:white}
        .badge.pending{background:#ffebc2;color:#92400e}

        .small{font-size:12px;color:var(--muted-2)}
        .actions{display:flex;gap:6px;align-items:center}
        .rc-actions{display:flex;gap:8px;align-items:center;margin-left:auto}
        .btn{padding:4px 6px;font-size:12px;border-radius:6px;border:1px solid #e6e9ef;background:white;cursor:pointer;min-width:32px;display:inline-flex;align-items:center;justify-content:center}
        .btn.view{background:#fff;border:1px solid #e6eef9;padding:4px 6px;border-radius:6px}
        .btn.ok{background:#10b981;color:#fff;border:none;border-radius:6px;padding:4px 8px}
        .btn.rej{background:#ef4444;color:#fff;border:none;border-radius:6px;padding:4px 8px}
        .badge.pending{padding:4px 8px;font-weight:700;border-radius:999px}

        /* Segment control (right side) */
        .segment{display:inline-flex;border-radius:999px;overflow:hidden;border:1px solid #e6e9ef;background:#fff;gap:6px}
        .seg-btn{display:inline-flex;align-items:center;padding:8px 12px;font-size:13px;color:#475569;text-decoration:none;border-left:1px solid transparent;white-space:nowrap}
        .seg-btn:first-child{border-left:none}
        .seg-btn.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:white}
        .seg-btn.alt{background:#f8fafc;color:#334155}

        /* Use semantic table for reliable alignment */
        .inventory-table{margin-top:8px;width:100%;box-sizing:border-box;overflow-x:auto}
        .inventory-table table{width:100%;border-collapse:separate;border-spacing:0;background:transparent}
        .inventory-table thead th{background:linear-gradient(90deg,#eef8ff,#f6f0ff);padding:14px 16px;text-align:left;font-size:14px;color:var(--muted);border-bottom:1px solid rgba(14,21,40,0.04)}
        .inventory-table tbody tr{background:#fff;border-radius:8px;box-shadow:0 6px 18px rgba(2,6,23,0.04);transition:transform .12s ease;display:table-row}
        .inventory-table tbody tr:hover{transform:translateY(-2px)}
        .inventory-table tbody td{padding:12px 16px;vertical-align:middle;font-size:13px;color:#0f172a;border-bottom:1px solid rgba(14,21,40,0.04)}
        .inventory-table tbody td.center{text-align:center;color:var(--muted)}
        .inventory-table tbody td.actions{text-align:right;white-space:nowrap}
        .badge.pending{display:inline-block;padding:6px 10px;border-radius:999px;background:#ffebc2;color:#92400e;font-weight:700}
        .btn{padding:6px 10px;font-size:13px;border-radius:8px;border:1px solid #e6e9ef;background:white;cursor:pointer;display:inline-flex;align-items:center;justify-content:center}
        .btn.view{background:#fff;border:1px solid #e6eef9}
        .btn.ok{background:#10b981;color:#fff;border:none}
        .btn.rej{background:#ef4444;color:#fff;border:none}
        /* icon-only action buttons */
        .icon-btn{width:36px;height:36px;padding:0;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;background:transparent;color:inherit;transition:transform .14s ease,box-shadow .14s ease;will-change:transform}
        .icon-btn svg{width:18px;height:18px}
        .icon-btn.view{border:1px solid #e6eef9;background:#fff}
        .icon-btn.ok{background:#10b981;color:#fff;border:none}
        .icon-btn.rej{background:#ef4444;color:#fff;border:none}
        .icon-btn:hover{transform:translateY(-4px);box-shadow:0 10px 24px rgba(2,6,23,0.12);z-index:220}
        .icon-btn:active{transform:translateY(-1px)}
        .icon-btn:focus{outline:none;box-shadow:0 0 0 4px rgba(37,99,235,0.12)}

        @media(max-width:900px){
            .inventory-table thead th:nth-child(4), .inventory-table thead th:nth-child(6){display:none}
            .inventory-table tbody td:nth-child(4), .inventory-table tbody td:nth-child(6){display:none}
        }
        @media(max-width:560px){
            .inventory-table thead{display:none}
            .inventory-table tbody tr{display:block;margin-bottom:12px;padding:12px}
            .inventory-table tbody td{display:block;padding:6px 0;border-bottom:none}
            .inventory-table tbody td.actions{text-align:left}
        }

        /* table head / body layout */
        .inventory-table{margin-top:8px;width:100%;box-sizing:border-box;overflow-x:auto}
        .table-head{display:grid;grid-template-columns: minmax(140px,200px) minmax(120px,220px) minmax(120px,160px) minmax(60px,120px) minmax(60px,100px) minmax(140px,1fr) minmax(100px,140px);gap:12px;padding:10px 14px;border-radius:8px;background:linear-gradient(90deg,#f6f9ff,#fbf6ff);align-items:center;margin-bottom:8px;box-sizing:border-box}
        .table-head .th{font-weight:700;color:var(--muted);padding:8px 4px}
        .table-head .th:nth-child(4), .table-head .th:nth-child(5), .table-head .th:nth-child(6){text-align:center}
        .table-body{display:flex;flex-direction:column;gap:12px}
        .empty-state{text-align:center;padding:28px;color:var(--muted-2)}

        /* notification bell (matches dashboard) */
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

        .sort-link{color:#2563eb;font-weight:800;text-decoration:underline;margin-bottom:10px;display:inline-block;font-size:16px}

        @media(max-width:900px){.sidebar{position:fixed;left:0;top:0;bottom:0;z-index:90;height:100vh}.sidebar.open{transform:translateX(0)}.main{padding:16px}}
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
                    <div style="font-weight:700">Pending Requests</div>
                    <div style="font-size:12px;color:var(--muted)">Requests / Pending</div>
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
                <a href="/inventory"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7a5 5 0 100 10 5 5 0 000-10zM2 12a10 10 0 1120 0A10 10 0 012 12z" fill="currentColor"/></svg><span class="label">Inventory</span></a>
                <a href="/requests" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="#"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zM3 13h3l1-3 2 2 3-4 2 4 3-2 1 3h3" stroke="currentColor" stroke-width="1" fill="none"/></svg><span class="label">Settings</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg>
                    <span class="label">Logout</span>
                </a>
            </nav>
        </aside>
        <main class="main">
            <div class="panel">
                <div class="hdr">
                    <div style="display:flex;flex-direction:column">
                        <div style="font-weight:700">{{ ucfirst($tab) === 'Pending' ? 'Pending Requests' : 'Requests' }}</div>
                        <div class="small">Manage equipment requests and approvals</div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center"></div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px">
                    <a href="#" class="sort-link">Requested ▾</a>
                    <div style="margin-left:auto"></div>
                    <div class="segment" role="tablist" aria-label="Request filters">
                        <a href="/requests?tab=pending" class="seg-btn {{ $tab==='pending' ? 'active' : '' }}">Pending</a>
                        <a href="/requests?tab=waiting" class="seg-btn {{ $tab==='waiting' ? 'active' : '' }}">Waiting</a>
                        <a href="/requests?tab=all" class="seg-btn {{ $tab==='all' ? 'active' : '' }}">All</a>
                        <a href="/requests?tab=history" class="seg-btn {{ $tab==='history' ? 'active alt' : '' }}">History</a>
                    </div>
                    <a href="/inventory" class="tab alt back-btn" style="background:#f3f4f6;color:#111;padding:8px 10px;margin-left:12px;white-space:nowrap">Back</a>
                </div>
                <div class="inventory-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Requested</th>
                                <th>Equipment</th>
                                <th>Personnel</th>
                                <th>Role</th>
                                <th style="text-align:center">Qty</th>
                                <th>Return</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $r)
                                <tr data-uuid="{{ $r->uuid }}">
                                    <td>{{ $r->created_at->format('F j, Y, g:i A') }}</td>
                                    <td><a class="link" href="/inventory/{{ $r->item_id }}">{{ $r->item_name }}</a></td>
                                    <td>{{ $r->requester }}</td>
                                    <td class="center">{{ $r->role ?? '—' }}</td>
                                    <td class="center">{{ $r->quantity }}</td>
                                    <td>{{ $r->return_date ? $r->return_date->format('F j, Y') : 'Consumable — N/A' }}</td>
                                    <td class="actions">
                                        <span class="badge pending">{{ ucfirst($r->status) }}</span>
                                        <button class="icon-btn view" title="View request" aria-label="View request" onclick="viewRequest('{{ $r->uuid }}')">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
                                        </button>
                                        @if($r->status==='pending')
                                            <button class="icon-btn ok" title="Approve request" aria-label="Approve request" onclick="actionRequest('{{ $r->uuid }}','approve', this)">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                            <button class="icon-btn rej" title="Reject request" aria-label="Reject request" onclick="actionRequest('{{ $r->uuid }}','reject', this)">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty-state">No pending requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <form id="logout-form" method="POST" action="/logout" style="display:none">@csrf</form>
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
                    // normalize
                    let items = [];
                    if(Array.isArray(data)) items = data;
                    else if(data.items) items = data.items;
                    else if(data.pending) items = data.pending;

                    const cnt = (data.count !== undefined) ? data.count : items.length;
                    if(cnt){
                        countEl.style.display = '';
                        countEl.textContent = cnt;
                    } else {
                        countEl.style.display = 'none';
                    }

                    const isAdmin = ({{ auth()->user() ? 'true' : 'false' }} && '{{ auth()->user()->name }}'.toLowerCase() === 'admin');
                    renderItems(items, isAdmin);
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
                const isMobile = window.matchMedia('(max-width:900px)').matches;
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

        function actionRequest(id, action, btn) {
            if (!confirm('Are you sure?')) return;
            btn.disabled = true;
            fetch('/notifications/requests/'+id+'/action', {
                method: 'POST',
                headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ action: action })
            }).then(r=>r.json()).then(j=>{
                if (j.ok) location.reload(); else { alert('Failed'); btn.disabled=false }
            }).catch(e=>{alert('Error');btn.disabled=false});
        }
        function viewRequest(id){ window.location = '/requests/'+id; }
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
</body>
</html>
