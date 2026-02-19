<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard — San Juan CDRMMD Inventory</title>
    <!-- Favicon -->
    <link rel="icon" href="/images/favi.png" type="image/png">
    <link rel="apple-touch-icon" href="/images/favi.png">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}
        /* Full-bleed background image (slightly transparent via overlay) */
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}
        .app{display:flex;min-height:100vh}

        /* Sidebar - fixed below the topbar to avoid overlap */
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transition:width .22s ease,transform .22s ease;z-index:50;height:calc(100vh - var(--topbar-height))}
        .sidebar.collapsed{width:64px}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .brand .logo{width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:inline-flex;align-items:center;justify-content:center;color:white;font-weight:800}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a, .nav button.action{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px}
        .nav a svg, .nav button.action svg{display:block;width:18px;height:18px}
        .nav a:hover, .nav button.action:hover{background:#f1f5f9}
        .nav a.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:white}
        .nav svg{flex-shrink:0}

        /* Topbar */
        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.95);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.08);z-index:60}
        .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}
        .topbar .left-area{display:flex;align-items:center;gap:12px}
        .topbar .branding{display:flex;flex-direction:column}
        .topbar .brand-title{display:flex;align-items:center;gap:6px;font-weight:700}
        .topbar .brand-subtitle{font-size:12px;color:var(--muted)}
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

        /* Main area (push down for topbar). Sidebar is overlay by default */
        .main{flex:1;padding:24px;margin-top:var(--topbar-height);margin-left:0;transition:margin .22s ease}
        .sidebar{transform:translateX(-110%);transition:transform .22s ease,width .22s ease}
        .sidebar.open{transform:translateX(0);z-index:90}
        .sidebar.collapsed{width:64px;transform:translateX(0)}
        /* Header becomes a white panel inside the main area to visually join with the sidebar */
        header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px;background:var(--panel);padding:12px 16px;border-radius:10px;box-shadow:0 6px 20px rgba(2,6,23,0.08)}
        header h1{color:#0f172a;margin:0}
        header .subtitle{color:var(--muted)}
        .header-left{display:flex;align-items:center;gap:16px}
        .burger{display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer}
        .burger:hover{background:#eef2ff}
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-top:6px;margin-bottom:18px}
        .card{background:var(--panel);padding:18px;border-radius:12px;box-shadow:0 8px 30px rgba(15,23,42,0.06)}
        .card .title{font-size:13px;color:var(--muted)}
        .card .value{font-size:22px;font-weight:700;margin-top:6px}

        .center{display:grid;grid-template-columns:1fr 360px;gap:14px}
        .list{background:var(--panel);padding:14px;border-radius:10px;min-height:180px;box-shadow:0 6px 20px rgba(15,23,42,0.04)}
        .recent-list{max-height:220px;overflow:auto;padding-right:6px}
        .recent-item a{display:flex;justify-content:space-between;gap:12px;padding:10px;border-radius:6px;background:#fff;border:1px solid #eef2f6;color:inherit;text-decoration:none}
        .recent-item a:hover{background:#f8fafc}
        .recent-item .meta{min-width:0}
        .recent-item .title{font-weight:700}
        .recent-item .sub{font-size:12px;color:var(--muted);margin-top:4px}
        .placeholder{height:200px;border-radius:8px;background:linear-gradient(90deg,#eef2ff,#f0fdf4);display:flex;align-items:center;justify-content:center;color:var(--muted)}

        /* Modal backdrop and styling */
        .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;z-index:999;animation:fadeIn 0.2s ease-out}
        .modal-backdrop.show{display:block}
        #equipmentModal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);background:white;border-radius:16px;box-shadow:0 25px 50px rgba(0,0,0,0.3);width:90%;max-width:700px;max-height:90vh;overflow-y:auto;display:none;z-index:1000;animation:slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1)}
        #equipmentModal.show{display:block;transform:translate(-50%,-50%) scale(1)}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        @keyframes slideUp{from{transform:translate(-50%,-55%) scale(0.95);opacity:0}to{transform:translate(-50%,-50%) scale(1);opacity:1}}
        .modal-header{display:flex;justify-content:space-between;align-items:center;padding:24px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:linear-gradient(135deg,var(--accent),var(--accent-2));color:white}
        .modal-header h2{margin:0;font-size:22px;font-weight:700}
        .modal-close{background:rgba(255,255,255,0.2);border:none;cursor:pointer;font-size:28px;color:white;padding:0;width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:background 0.2s;font-weight:300}
        .modal-close:hover{background:rgba(255,255,255,0.3)}
        .modal-body{padding:24px}
        .modal-image{width:100%;height:300px;object-fit:cover;border-radius:12px;margin-bottom:24px;background:#f3f4f6}
        .modal-section{margin-bottom:24px}
        .modal-section:last-child{margin-bottom:0}
        .modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px}
        .modal-info{padding:16px;background:#f9fafb;border-radius:10px;border-left:4px solid var(--accent)}
        .modal-label{font-weight:700;color:var(--accent);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;display:block}
        .modal-value{color:#0f172a;font-size:15px;line-height:1.6;font-weight:500}
        .modal-divider{height:1px;background:#e5e7eb;margin:24px 0}
        .recent-item a{cursor:pointer}
        @media(max-width:768px){#equipmentModal{width:95%;max-height:85vh}.modal-grid{grid-template-columns:1fr}.modal-image{height:200px}}

        /* Collapsed state adjustments */
        .sidebar.collapsed .brand .text,
        .sidebar.collapsed .nav a span.label,
        .sidebar.collapsed .nav button.action span.label{display:none}
        .sidebar.collapsed .nav a,
        .sidebar.collapsed .nav button.action{justify-content:center}
        /* ensure svg centers inside collapsed button */
        .sidebar.collapsed .nav a svg,
        .sidebar.collapsed .nav button.action svg{margin:0 auto}
        .sidebar.collapsed .brand{justify-content:center}

        /* Responsive */
        @media(max-width:900px){
            .sidebar{position:fixed;left:0;top:0;bottom:0;z-index:80;transform:translateX(-110%);height:100vh}
            .sidebar.open{transform:translateX(0)}
            .sidebar + .main{margin-left:0}
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
                    <div class="brand-title">
                        <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                        <span>San Juan CDRMMD Inventory</span>
                    </div>
                    <div class="brand-subtitle">Overview of Stocks</div>
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
        <aside id="sidebar" class="sidebar">
            <div class="brand">
                <img src="/images/favi.png" alt="San Juan" class="logo-img" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                <div class="text" style="font-size:14px">San Juan CDRMMD</div>
            </div>
            <nav class="nav">
                <a href="/dashboard" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3v6h8V3h-8zM3 21h8v-6H3v6z" fill="currentColor"/></svg><span class="label">Home</span></a>
                <a href="/inventory"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7a5 5 0 100 10 5 5 0 000-10zM2 12a10 10 0 1120 0A10 10 0 012 12z" fill="currentColor"/></svg><span class="label">Inventory</span></a>
                <a href="/requests"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="#"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zM3 13h3l1-3 2 2 3-4 2 4 3-2 1 3h3" stroke="currentColor" stroke-width="1" fill="none"/></svg><span class="label">Settings</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg>
                    <span class="label">Logout</span>
                </a>
            </nav>
        </aside>

        <main class="main">
            <!-- main header removed; topbar contains global header -->

            <section class="cards">
                <div class="card">
                    <div style="font-size:13px;color:var(--muted)">Total Items</div>
                    <div style="font-size:24px;font-weight:700">{{ $total_items ?? '—' }}</div>
                </div>
                <div class="card">
                    <div style="font-size:13px;color:var(--muted)">Low Stock (&lt;10)</div>
                    <div style="font-size:24px;font-weight:700">{{ $low_count ?? '—' }}</div>
                </div>
                <div class="card">
                    <div style="font-size:13px;color:var(--muted)">Out of Stock</div>
                    <div style="font-size:24px;font-weight:700">{{ $out_count ?? '—' }}</div>
                </div>
            </section>

            <section class="center">
                <div>
                    <div class="list">
                        <h3 style="margin:0 0 8px">Recent Requests</h3>
                        @php
                            $recentRequests = $recentRequests ?? (\App\Models\InventoryRequest::orderBy('created_at','desc')->take(10)->get());
                        @endphp
                        @if($recentRequests && $recentRequests->count())
                            <div class="recent-list">
                                <ul style="list-style:none;padding:0;margin:0;display:grid;gap:8px">
                                    @foreach($recentRequests as $req)
                                        <li class="recent-item">
                                            <a href="/requests/{{ $req->uuid }}">
                                                <div class="meta">
                                                    <div class="title">{{ $req->item_name }}</div>
                                                    <div class="sub">
                                                        {{ $req->requester }} · {{ $req->role ?? '—' }}
                                                        @if(($req->role ?? '') === 'Operations' && !empty($req->department))
                                                            · {{ $req->department }}
                                                        @endif
                                                        · Qty: {{ $req->quantity ?? 1 }}
                                                    </div>
                                                </div>
                                                <div style="text-align:right;font-size:12px;color:var(--muted)">
                                                    <div>{{ $req->created_at->diffForHumans() }}</div>
                                                    <div style="margin-top:6px"><span class="badge {{ $req->status }}">{{ ucfirst(strtolower($req->status)) }}</span></div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="placeholder">No recent requests</div>
                        @endif
                    </div>
                    <div class="list" style="margin-top:12px">
                        <h3 style="margin:0 0 8px">Recent Equipment Added</h3>
                            @if(isset($recent) && $recent->count())
                                <div class="recent-list">
                                    <ul style="list-style:none;padding:0;margin:0;display:grid;gap:8px">
                                        @foreach($recent as $item)
                                            <li class="recent-item">
                                                <a onclick="openEquipmentModal(this)" data-equipment='{{json_encode(["id" => $item->id, "name" => $item->name, "category" => $item->category ?? "—", "location" => $item->location ?? "—", "serial" => $item->serial ?? "—", "quantity" => $item->quantity, "type" => $item->type ?? "—", "tag" => $item->tag ?? "—", "notes" => $item->notes ?? "No description provided", "image_path" => $item->image_path, "date_added" => $item->date_added ? $item->date_added->format('M d, Y') : $item->created_at->format('M d, Y'), "created_at" => $item->created_at->format('M d, Y'), "updated_at" => $item->updated_at->format('M d, Y')])}}' style="display:flex;justify-content:space-between;gap:12px">
                                                    <div class="meta">
                                                        <div class="title">{{ $item->name }}</div>
                                                        <div class="sub">{{ $item->category }} — {{ $item->location }}</div>
                                                    </div>
                                                    <div style="text-align:right;font-size:12px;color:var(--muted)">{{ $item->created_at->format('Y-m-d') }}</div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="placeholder">No recent equipment</div>
                            @endif
                    </div>
                </div>
                <aside>
                    <div class="card">
                        <h4 style="margin:0 0 8px">Stock Analytics</h4>
                        <div class="placeholder">Chart placeholder</div>
                    </div>
                </aside>
            </section>
        </main>
    </div>

    <!-- Equipment Modal -->
    <div class="modal-backdrop" id="equipmentBackdrop"></div>
    <div id="equipmentModal">
        <div class="modal-header">
            <h2 id="modalName">Equipment Details</h2>
            <button class="modal-close" id="modalCloseBtn">&times;</button>
        </div>
        <div class="modal-body">
            <img id="modalImage" src="" alt="Equipment" class="modal-image" style="display:none">
            <div id="noImage" style="width:100%;height:300px;background:#e5e7eb;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:16px">📷 No image available</div>
            
            <div class="modal-grid">
                <div class="modal-info">
                    <span class="modal-label">Category</span>
                    <div class="modal-value" id="modalCategory">—</div>
                </div>
                <div class="modal-info">
                    <span class="modal-label">Location</span>
                    <div class="modal-value" id="modalLocation">—</div>
                </div>
                <div class="modal-info">
                    <span class="modal-label">Quantity</span>
                    <div class="modal-value" id="modalQuantity">0</div>
                </div>
                <div class="modal-info">
                    <span class="modal-label">Type</span>
                    <div class="modal-value" id="modalType">—</div>
                </div>
            </div>

            <div class="modal-divider"></div>

            <div class="modal-section">
                <span class="modal-label">Serial Number</span>
                <div class="modal-value" id="modalSerial">—</div>
            </div>

            <div class="modal-section">
                <span class="modal-label">Tag / Identifier</span>
                <div class="modal-value" id="modalTag">—</div>
            </div>

            <div class="modal-section">
                <span class="modal-label">Description / Notes</span>
                <div class="modal-value" id="modalNotes" style="background:#f9fafb;padding:12px;border-radius:8px;border-left:4px solid var(--accent-2)">No description</div>
            </div>

            <div class="modal-divider"></div>

            <div class="modal-grid">
                <div class="modal-info">
                    <span class="modal-label">Date Added</span>
                    <div class="modal-value" id="modalDateAdded">—</div>
                </div>
                <div class="modal-info">
                    <span class="modal-label">Created</span>
                    <div class="modal-value" id="modalCreated">—</div>
                </div>
            </div>

            <div class="modal-section">
                <span class="modal-label">Last Updated</span>
                <div class="modal-value" id="modalUpdated">—</div>
            </div>
        </div>
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
    </script>
    <script>
        (function(){
            const dd = document.querySelector('.notif-dropdown');
            if(!dd) return;
            dd.addEventListener('click', function(e){
                if(e.target.closest('.actions')) return; // don't navigate when action buttons clicked
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
        const modal = document.getElementById('equipmentModal');
        const backdrop = document.getElementById('equipmentBackdrop');
        const closeBtn = document.getElementById('modalCloseBtn');

        function openEquipmentModal(element) {
            const data = JSON.parse(element.dataset.equipment);
            
            document.getElementById('modalName').textContent = data.name;
            document.getElementById('modalCategory').textContent = data.category;
            document.getElementById('modalLocation').textContent = data.location;
            document.getElementById('modalQuantity').textContent = data.quantity + ' unit(s)';
            document.getElementById('modalType').textContent = data.type;
            document.getElementById('modalSerial').textContent = data.serial;
            document.getElementById('modalTag').textContent = data.tag;
            document.getElementById('modalNotes').textContent = data.notes;
            document.getElementById('modalDateAdded').textContent = data.date_added;
            document.getElementById('modalCreated').textContent = data.created_at;
            document.getElementById('modalUpdated').textContent = data.updated_at;

            const imageEl = document.getElementById('modalImage');
            const noImageEl = document.getElementById('noImage');
            
            if (data.image_path) {
                imageEl.src = '/storage/' + data.image_path;
                imageEl.style.display = 'block';
                noImageEl.style.display = 'none';
            } else {
                imageEl.style.display = 'none';
                noImageEl.style.display = 'flex';
            }

            modal.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeEquipmentModal() {
            modal.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        closeBtn.addEventListener('click', closeEquipmentModal);
        backdrop.addEventListener('click', closeEquipmentModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeEquipmentModal();
            }
        });
    </script>
