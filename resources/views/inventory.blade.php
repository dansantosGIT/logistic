<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inventory — San Juan CDRMMD</title>
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

        /* Topbar */
        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.96);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.06);z-index:60}
          /* make the topbar full-width so the burger can sit flush left
              and the welcome/admin stays right, maximizing header space */
          .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}
        /* Notification bell */
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

        /* allow the panel to expand so the table can fit all columns */
        .panel{background:var(--panel);padding:12px 14px;border-radius:10px;box-shadow:0 6px 20px rgba(15,23,42,0.04);max-width:none;width:calc(100% - 48px);margin:12px auto}
        .panel h2, .panel h3{font-weight:700;margin:0}
        .row{display:flex;gap:12px;align-items:center}
        .tabs{display:flex;gap:6px;margin-bottom:12px}
        .tab{background:#f1f5f9;padding:8px 12px;border-radius:8px;font-size:13px;color:#0f172a}
        .tab.active{background:white;box-shadow:0 2px 8px rgba(2,6,23,0.04)}

        /* Table styles */
        .search-row{display:flex;gap:12px;margin-bottom:12px}
        input.search{flex:1;padding:10px;border:1px solid #e6e9ef;border-radius:8px}
        select.page-size{padding:8px 10px;border-radius:8px;border:1px solid #e6e9ef}

        .table-wrap{background:linear-gradient(180deg,rgba(255,255,255,0.72),rgba(250,250,255,0.56));padding:8px;border-radius:12px;backdrop-filter:blur(6px);box-shadow:0 8px 36px rgba(2,6,23,0.04);overflow:visible}
        /* use fixed layout and smaller paddings so columns fit in one view */
        table.inventory-table{width:100%;border-collapse:separate;border-spacing:0;background:transparent;table-layout:fixed;font-size:14px}
        thead th{background:linear-gradient(90deg,#eef8ff,#f6f0ff);padding:10px 8px;text-align:left;font-size:13px;color:var(--muted);border-bottom:1px solid rgba(14,21,40,0.04)}
        tbody td{background:linear-gradient(180deg,#ffffff,#fbfdff);padding:10px 8px;vertical-align:middle;border-bottom:1px solid rgba(14,21,40,0.03);transition:background .18s ease;font-size:14px;line-height:1.35;word-break:break-word}
        tbody tr{transition:transform .18s cubic-bezier(.2,.9,.2,1),box-shadow .18s ease;position:relative}
        tbody tr:hover{transform:translateY(-6px);box-shadow:0 14px 34px rgba(2,6,23,0.06);z-index:2}
        tbody tr td:first-child{border-top-left-radius:8px;border-bottom-left-radius:8px}
        tbody tr td:last-child{border-top-right-radius:8px;border-bottom-right-radius:8px}
        tbody tr:nth-child(odd) td{background:linear-gradient(180deg,#ffffff,#fcfeff)}
        /* Column min-widths to avoid cramped content */
        /* reduced min-widths so table fits without horizontal scroll */
        thead th:nth-child(1), tbody td:nth-child(1){min-width:160px}
        thead th:nth-child(2), tbody td:nth-child(2){min-width:120px}
        thead th:nth-child(3), tbody td:nth-child(3){min-width:90px}
        thead th:nth-child(4), tbody td:nth-child(4){min-width:110px}
        thead th:nth-child(5), tbody td:nth-child(5){min-width:60px;text-align:center}
        thead th:nth-child(6), tbody td:nth-child(6){min-width:90px}
        thead th:nth-child(7), tbody td:nth-child(7){min-width:90px;text-align:center}
        thead th:nth-child(8), tbody td:nth-child(8){min-width:90px}
        thead th:nth-child(9), tbody td:nth-child(9){min-width:120px;text-align:right}

        /* Ensure action buttons fit comfortably — make them smaller instead of forcing layout scroll */
        table.inventory-table tbody td:last-child{white-space:nowrap;padding-right:8px}
        /* Match status badge sizing: slightly smaller label text and fixed height so visually consistent */
        table.inventory-table tbody td:last-child{display:flex;align-items:center;justify-content:flex-end}
        table.inventory-table tbody td:last-child .btn{padding:6px 12px;font-size:11px;line-height:1;display:inline-flex;align-items:center;justify-content:center;margin-left:8px;min-width:0;border-radius:999px;text-decoration:none;height:32px;min-height:32px}
        table.inventory-table tbody td:last-child .btn.request{padding:6px 12px}
        table.inventory-table tbody td:last-child .btn.edit{padding:6px 10px}
        table.inventory-table tbody td:last-child .btn.delete{padding:6px 10px}
        table.inventory-table tbody td:last-child .btn:first-child{margin-left:0}

        .badge{display:inline-flex;align-items:center;justify-content:center;min-height:28px;padding:6px 12px;border-radius:999px;font-size:13px;color:white;white-space:nowrap}
        .badge.consumable{background:#10b981}
        .badge.nonconsumable{background:#6b7280}
        /* Stock status badges */
        .badge.instock{background:#10b981}
        .badge.low{background:#f59e0b}
        .badge.out{background:#ef4444}

        .actions{display:flex;gap:8px;justify-content:flex-end}
        /* Buttons: smaller by default; primary is larger */
        .btn{padding:6px 10px;font-size:13px;border-radius:999px;border:1px solid #e6e9ef;background:white;cursor:pointer;transition:transform .08s ease,box-shadow .08s ease,background .08s ease,color .08s ease;text-decoration:none}
        .btn:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(15,23,42,0.06);background:#f8fafc}
        .btn.primary{padding:8px 14px;font-size:14px;background:#2563eb;color:white;border:none}
        .btn.primary:hover{background:#1e4fd8}
        /* Action button colors and spacing */
        .btn.request{background:#2563eb;color:white;border:none;text-decoration:none}
        .btn.request:hover{background:#1e4fd8}
        .header-request{background:white;color:#0f172a;border:1px solid #e6e9ef;text-decoration:none}
        .header-request:hover{background:#f8fafc;color:#0f172a}
        /* Ensure only the header request button (panel/header) is white */
        .panel .btn.header-request, .main .btn.header-request{background:white;color:#0f172a;border:1px solid #e6e9ef;text-decoration:none}
        .panel .btn.header-request:hover, .main .btn.header-request:hover{background:#f8fafc;color:#0f172a}
        .btn.edit{background:white;color:#0f172a;border:1px solid #e6e9ef}
        .btn.edit:hover{background:#f8fafc;text-decoration:none}
        .btn.delete{background:#ef4444;color:white;border:none}
        .btn.delete:hover{background:#dc2626;text-decoration:none}
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
        .modal-image{display:block;width:100%;height:auto;max-height:60vh;object-fit:contain;border-radius:12px;margin-bottom:24px;background:#f3f4f6}
        .modal-section{margin-bottom:24px}
        .modal-section:last-child{margin-bottom:0}
        .modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px}
        .modal-info{padding:16px;background:#f9fafb;border-radius:10px;border-left:4px solid var(--accent)}
        .modal-label{font-weight:700;color:var(--accent);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;display:block}
        .modal-value{color:#0f172a;font-size:15px;line-height:1.6;font-weight:500}
        #modalNotes{white-space:pre-wrap;word-break:break-word}
        .modal-divider{height:1px;background:#e5e7eb;margin:24px 0}
        tbody tr{cursor:pointer}
        tbody tr:active{opacity:0.9}
        @media(max-width:768px){#equipmentModal{width:95%;max-height:85vh}.modal-grid{grid-template-columns:1fr}.modal-image{height:200px}}
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
                    <a href="/dashboard" style="display:flex;align-items:center;gap:6px;font-weight:700;text-decoration:none;color:inherit">
                        <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                        <span>San Juan CDRMMD Inventory</span>
                    </a>
                    <div style="font-size:12px;color:var(--muted)">All Categories</div>
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
            <a href="/dashboard" class="brand" style="text-decoration:none;color:inherit">
                <img src="/images/favi.png" alt="San Juan" class="logo-img" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                <div class="text" style="font-size:14px">San Juan CDRMMD</div>
            </a>
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
            <div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <div>
                        <h2 id="inventory-title" style="margin:0">All Inventory</h2>
                        <div style="color:var(--muted);font-size:13px;margin-top:6px">Type: <strong>Consumable</strong> / <strong>Non-consumable</strong></div>
                    </div>
                    <div class="row">
                        <button class="btn">Export CSV</button>
                        <button class="btn">Documents</button>
                        <button class="btn">Quick Request</button>
                        <button class="btn">Request Multiple</button>
                        <a href="/requests" class="btn request header-request" role="button" title="View requests">Request</a>
                        <a href="/inventory/add" class="btn primary">+ Add Equipment</a>
                    </div>
                </div>

                <div class="tabs">
                    <div class="tab active" data-location="all">All Inventory</div>
                    <div class="tab" data-location="logistics">Logistics</div>
                    <div class="tab" data-location="medical">Medical</div>
                    <div class="tab" data-location="office">Office</div>
                    <div class="tab" data-location="vehicle"> Vehicle</div>
                </div>

                <div class="search-row">
                    <input class="search" placeholder="Search name, category, location or tag">
                    <select class="page-size"><option>25</option><option>50</option><option>100</option></select>
                    <button class="btn primary">Search</button>
                </div>

                <div class="table-wrap" style="overflow:auto">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Room</th>
                            <th>Serial No.</th>
                            <th>Quantity</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th style="width:150px;text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($equipment) && $equipment->count())
                            @foreach($equipment as $item)
                                <tr data-location="{{ strtolower($item->location ?? '') }}" class="equipment-row" onclick="openEquipmentModal(this)" data-equipment='{{json_encode(["id" => $item->id, "name" => $item->name, "category" => $item->category ?? "—", "location" => $item->location ?? "—", "serial" => $item->serial ?? "—", "quantity" => $item->quantity, "type" => $item->type ?? "—", "tag" => $item->tag ?? "—", "notes" => $item->notes ?? "No description provided", "image_path" => $item->image_path, "date_added" => $item->date_added ? $item->date_added->format('M d, Y') : $item->created_at->format('M d, Y'), "created_at" => $item->created_at->format('M d, Y H:i'), "updated_at" => $item->updated_at->format('M d, Y H:i')])}}'>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->category }}</td>
                                    <td>{{ $item->location }}</td>
                                    <td>{{ $item->serial }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        @if(strtolower(trim($item->type ?? '')) === 'consumable')
                                            <span class="badge consumable">Consumable</span>
                                        @else
                                            <span class="badge nonconsumable">Non&#8209;consumable</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->quantity >= 10)
                                            <span class="badge instock">In stock</span>
                                        @elseif($item->quantity > 0)
                                            <span class="badge low">Low stock</span>
                                        @else
                                            <span class="badge out">Out of stock</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->date_added ? $item->date_added->format('Y-m-d') : $item->created_at->format('Y-m-d') }}</td>
                                    <td style="display:flex;align-items:center;justify-content:flex-end;gap:8px" onclick="event.stopPropagation()">
                                        <a href="/inventory/{{ $item->id }}/request" class="btn request">Request</a>
                                        <a href="/inventory/{{ $item->id }}/edit" class="btn edit">Edit</a>
                                        <a href="/inventory/{{ $item->id }}/delete" class="btn delete" onclick="event.stopPropagation(); return confirm('Delete this item?');">Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="no-results" style="display:none"><td colspan="9" style="text-align:center;color:var(--muted)">No items in this section</td></tr>
                        @else
                            <tr><td colspan="9" style="text-align:center;color:var(--muted)">No equipment yet</td></tr>
                        @endif
                    </tbody>
                </table>
                </div>

                <div class="pagination">
                    @if(isset($equipment))
                        {{ $equipment->links() }}
                    @endif
                </div>
            </div>
        </main>
    </div>
    @if(session('success') || session('status'))
        <div id="inv-success-toast" class="toast" role="status" aria-live="polite">
            <div class="message">{{ session('success') ?? session('status') }}</div>
            <div class="close" id="inv-toast-close">✕</div>
        </div>
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
                // Toggle overlay-style sidebar on both desktop and mobile
                const willOpen = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open');
                // if collapsed state existed, remove it to ensure overlay mode
                sidebar.classList.remove('collapsed');
                setOverlay(willOpen);
            });

            // Close when clicking outside the sidebar or on the overlay
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
                    // detect admin by presence of approve buttons server-side? we'll assume responses for admin contain pending items
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
        (function(){
            const toast = document.getElementById('inv-success-toast');
            if(toast){
                setTimeout(()=> toast.classList.add('show'), 60);
                const hide = ()=> toast.classList.remove('show');
                const t = setTimeout(hide, 4000);
                const closer = document.getElementById('inv-toast-close');
                closer && closer.addEventListener('click', ()=>{ clearTimeout(t); hide(); });
            }
        })();
    </script>
        <script>
            // Tabs: update inventory title and URL param
            (function(){
                const tabs = document.querySelectorAll('.tabs .tab');
                const titleEl = document.getElementById('inventory-title');
                if(!tabs.length || !titleEl) return;

                function filterRows(loc){
                    const rows = document.querySelectorAll('tbody tr');
                    let anyVisible = false;
                    rows.forEach(r=>{
                        if(r.classList && r.classList.contains('no-results')) return; // skip placeholder
                        const rowLoc = (r.dataset.location || '').toLowerCase();
                        if(loc === 'all' || (rowLoc && rowLoc.indexOf(loc) !== -1)){
                            r.style.display = '';
                            anyVisible = true;
                        } else {
                            r.style.display = 'none';
                        }
                    });
                    const placeholder = document.querySelector('tbody tr.no-results');
                    if(placeholder) placeholder.style.display = anyVisible ? 'none' : '';
                }

                function setActive(tab){
                    tabs.forEach(t=>t.classList.remove('active'));
                    tab.classList.add('active');
                    const txt = tab.textContent.trim();
                    if(txt.toLowerCase().includes('inventory')){
                        titleEl.textContent = txt;
                    } else {
                        titleEl.textContent = txt + ' Inventory';
                    }
                    const loc = tab.dataset.location || txt.toLowerCase().replace(/\s+/g,'-');
                    try{
                        const url = new URL(window.location);
                        url.searchParams.set('location', loc);
                        window.history.replaceState({}, '', url);
                    } catch(e){}
                    filterRows(loc);
                }

                tabs.forEach(tab=>{
                    tab.addEventListener('click', function(e){
                        e.preventDefault();
                        setActive(tab);
                    });
                });

                // initialize from URL or existing active
                (function init(){
                    try{
                        const param = (new URL(window.location)).searchParams.get('location');
                        if(param){
                            const match = Array.from(tabs).find(t=>t.dataset.location===param);
                            if(match) return setActive(match);
                        }
                    } catch(e){}
                    const active = document.querySelector('.tabs .tab.active');
                    if(active) setActive(active);
                })();
            })();
        </script>

        <!-- Equipment Details Modal -->
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
                <div style="height:12px"></div>
                <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:6px">
                    <a id="modalRequestBtn" class="btn request" href="#">Request</a>
                    <a id="modalEditBtn" class="btn edit" href="#">Edit</a>
                </div>
            </div>
        </div>

        <script>
            const modal = document.getElementById('equipmentModal');
            const backdrop = document.getElementById('equipmentBackdrop');
            const closeBtn = document.getElementById('modalCloseBtn');

            function openEquipmentModal(row) {
                const data = JSON.parse(row.dataset.equipment);
                
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

                    // set modal action links
                    const reqBtn = document.getElementById('modalRequestBtn');
                    const editBtn = document.getElementById('modalEditBtn');
                    if (reqBtn) reqBtn.href = '/inventory/' + data.id + '/request';
                    if (editBtn) editBtn.href = '/inventory/' + data.id + '/edit';

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
</body>
</html>
