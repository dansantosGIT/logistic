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
        .tab{background:#f1f5f9;padding:8px 12px;border-radius:8px;font-size:13px;color:#0f172a;transition:background .12s ease,transform .12s ease,box-shadow .12s ease}
        /* Active tab: stronger visual anchor with gradient, left accent bar and subtle elevation */
        .tab.active{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff;font-weight:700;box-shadow:0 8px 24px rgba(37,99,235,0.12);transform:translateY(-2px);position:relative}
        .tab.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:4px;border-radius:4px;background:linear-gradient(180deg,var(--accent),var(--accent-2));}

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

        /* Row status tint + left accent for quick scanning */
        tbody tr.low td{background:linear-gradient(180deg,#fffaf0,#fffdf8)}
        tbody tr.low td:first-child{box-shadow:inset 6px 0 0 rgba(249,115,22,0.14)}

        tbody tr.out td{background:linear-gradient(180deg,#FFE3E4,#FFAEA1)}
        tbody tr.out td:first-child{box-shadow:inset 6px 0 0 rgba(255,105,112,0.28)}

        /* instock: keep default background for minimal noise */

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
        .badge.notworking{background:#6b7280}
        .badge.out{background:#FF1F27}

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
        .btn.delete{background:#FF1F27;color:white;border:none}
        .btn.delete:hover{background:#e01a21;text-decoration:none}
        /* make table row action buttons consistent and non-overlapping */
        tbody td .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:6px 10px;font-size:13px;border-radius:8px;margin-left:6px;white-space:nowrap;vertical-align:middle}
        tbody td .btn.edit{margin-left:0} /* keep edit closer to item */

        /* Tabs hover to match button feedback (don't override active tab) */
        .tabs .tab{transition:transform .08s ease,box-shadow .08s ease,background .08s ease}
        .tabs .tab:not(.active):hover{cursor:pointer;transform:translateY(-1px);background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.04)}

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

        /* Modal backdrop and styling - Professional upgrade */
        .modal-backdrop{position:fixed;inset:0;background:rgba(2,6,23,0.6);display:none;z-index:999;animation:fadeIn 0.2s ease-out;backdrop-filter:blur(2px)}
        .modal-backdrop.show{display:block}
        #equipmentModal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.95);background:linear-gradient(180deg,#ffffff,#f8fafc);border-radius:20px;box-shadow:0 40px 80px rgba(0,0,0,0.25);width:95%;max-width:1400px;max-height:88vh;overflow-y:auto;display:none;z-index:1000;animation:slideUp 0.35s cubic-bezier(0.16,1,0.3,1)}
        #equipmentModal.show{display:block;transform:translate(-50%,-50%) scale(1)}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        @keyframes slideUp{from{transform:translate(-50%,-60%) scale(0.92);opacity:0}to{transform:translate(-50%,-50%) scale(1);opacity:1}}
        .modal-header{display:flex;justify-content:space-between;align-items:flex-start;padding:28px 32px;background:linear-gradient(135deg,var(--accent),var(--accent-2));color:white;border-radius:20px 20px 0 0;gap:16px;position:sticky;top:0;z-index:10}
        .modal-header h2{margin:0;font-size:26px;font-weight:700;letter-spacing:-0.5px;flex:1}
        .modal-close{background:rgba(255,255,255,0.15);border:none;cursor:pointer;font-size:32px;color:white;padding:0;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;transition:background 0.25s,transform 0.2s;font-weight:300;flex-shrink:0}
        .modal-close:hover{background:rgba(255,255,255,0.25);transform:scale(1.05)}
        .modal-body{padding:28px 32px;display:grid;grid-template-columns:280px 1fr;gap:28px;align-items:start}
        .modal-image-section{display:flex;flex-direction:column;gap:16px}
        .modal-image{display:block;width:100%;height:auto;max-height:400px;object-fit:cover;border-radius:16px;background:#e2e8f0;border:1px solid rgba(14,21,40,0.08)}
        .modal-image-container{width:100%}
        #noImage{width:100%;height:300px;background:linear-gradient(135deg,#f0f4ff,#e2e8f0);border-radius:16px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#64748b;font-size:16px;border:2px dashed rgba(37,99,235,0.2);gap:8px}
        .modal-content-section{display:flex;flex-direction:column;gap:24px}
        .modal-section{margin-bottom:0}
        .modal-section:last-child{margin-bottom:0}
        .modal-grid{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:0}
        .modal-grid-2col{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .modal-grid-3col{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
        .modal-info{padding:14px 16px;background:linear-gradient(135deg,#f6f9ff,#f0f4ff);border-radius:14px;border:1px solid rgba(37,99,235,0.12);transition:all 0.2s ease}
        .modal-info:hover{box-shadow:0 8px 24px rgba(37,99,235,0.08);transform:translateY(-2px)}
        .modal-label{font-weight:700;color:#2563eb;font-size:10px;text-transform:uppercase;letter-spacing:0.7px;margin-bottom:6px;display:block}
        .modal-value{color:#0f172a;font-size:14px;line-height:1.5;font-weight:600}
        .modal-section-title{font-weight:700;color:#0f172a;font-size:13px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;padding-bottom:12px;border-bottom:2px solid #e2e8f0}
        #modalNotes{white-space:pre-wrap;word-break:break-word;background:linear-gradient(135deg,#f8fafc,#f1f5f9);padding:14px;border-radius:12px;border-left:5px solid var(--accent-2);font-size:14px;line-height:1.6;color:#1e293b}
        .modal-divider{height:1px;background:linear-gradient(90deg,transparent,#e5e7eb,transparent);margin:20px 0}
        .modal-actions{display:flex;gap:12px;justify-content:flex-end;padding-top:16px;border-top:1px solid #e5e7eb;margin-top:16px}
        .modal-actions .btn{padding:11px 18px;font-size:13px;font-weight:600;border-radius:10px;transition:all 0.2s ease;display:inline-flex;align-items:center;justify-content:center;gap:6px}
        .modal-actions .btn.request{background:linear-gradient(135deg,var(--accent),var(--accent-2));color:white;border:none;flex:1}
        .modal-actions .btn.request:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(37,99,235,0.3)}
        .modal-actions .btn.edit{background:white;color:#0f172a;border:1.5px solid #e5e7eb}
        .modal-actions .btn.edit:hover{background:#f8fafc;border-color:#2563eb;color:#2563eb}
        tbody tr{cursor:pointer}
        tbody tr:active{opacity:0.9}
        @media(max-width:1200px){#equipmentModal{max-width:96%;width:96%}.modal-body{grid-template-columns:1fr;gap:20px}.modal-grid{grid-template-columns:1fr 1fr}}
        @media(max-width:768px){#equipmentModal{width:96%;max-width:100%;max-height:95vh;border-radius:20px;overflow-y:auto}.modal-header{padding:20px 24px;font-size:22px}.modal-body{padding:24px;grid-template-columns:1fr;gap:16px}.modal-image-section{gap:12px}.modal-grid{grid-template-columns:1fr}}
        @media(max-width:900px){
            .sidebar{position:fixed;left:0;top:0;bottom:0;z-index:90;height:100vh}
            .sidebar.open{transform:translateX(0)}
            .main{padding:16px}
        }
        /* Approval / Delete modal styles (shared) */
        .approval-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;z-index:999;animation:fadeIn 0.2s ease-out}
        .approval-backdrop.show{display:block}
        .approval-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.95);background:white;border-radius:16px;box-shadow:0 25px 50px rgba(0,0,0,0.3);width:90%;max-width:540px;max-height:90vh;overflow-y:auto;display:none;z-index:1000;animation:slideUp 0.3s cubic-bezier(0.16,1,0.3,1)}
        .approval-modal.show{display:block;transform:translate(-50%,-50%) scale(1)}
        .approval-header{display:flex;justify-content:space-between;align-items:center;padding:18px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:linear-gradient(135deg,var(--accent),var(--accent-2));color:white;border-radius:16px 16px 0 0}
        .approval-header h2{margin:0;font-size:16px;font-weight:700}
        .approval-close{background:rgba(255,255,255,0.18);border:none;cursor:pointer;font-size:24px;color:white;padding:0;width:40px;height:40px;border-radius:8px}
        .approval-body{padding:18px}
    </style>
    @include('partials._bg-preload')
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
                <a href="/vehicle"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13l1.5-4.5A2 2 0 016.4 7h11.2a2 2 0 011.9 1.5L21 13v5a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H6v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM6 14h12M7.5 10.5h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="label">Vehicle</span></a>
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
                        <a href="/requests/multiple" class="btn header-request">Request Multiple</a>
                        <a href="/requests" class="btn request header-request" role="button" title="View requests">Request</a>
                        <a href="/inventory/add" class="btn primary">+ Add Equipment</a>
                    </div>
                </div>

                <div class="tabs">
                    <div class="tab active" data-location="all">All Inventory</div>
                    <div class="tab" data-location="logistics">Logistics</div>
                    <div class="tab" data-location="medical">Medical</div>
                    <div class="tab" data-location="office">Office</div>
                    <div class="tab" data-category="power-tools">Power Tools</div>
                    <div class="tab" data-category="electronics">Electronics</div>
                </div>

                <div class="search-row">
                    <input id="inventory-search" class="search" placeholder="Search name, category, location or tag">
                    <select class="page-size"><option>25</option><option>50</option><option>100</option></select>
                    <button class="btn primary" id="inventory-search-clear" type="button" style="display:none">Clear</button>
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
                                @php
                                    $categorySlug = strtolower(str_replace(' ', '-', trim($item->category ?? '')));
                                    $isPowerTools = in_array($categorySlug, ['power-tool', 'power-tools'], true);
                                    $isElectronics = ($categorySlug === 'electronics');
                                    $isSpecialCategory = $isPowerTools || $isElectronics;
                                    $savedStatus = strtolower(trim((string) ($item->status ?? '')));
                                    if ($savedStatus === 'not working') {
                                        $savedStatus = 'not_working';
                                    }

                                    $statusClass = ($item->quantity <= 0) ? 'out' : (($item->quantity < 10) ? 'low' : 'instock');
                                    $statusLabel = ($item->quantity <= 0) ? 'Out of stock' : (($item->quantity < 10) ? 'Low stock' : 'In stock');

                                    if ($isSpecialCategory && $savedStatus === 'missing') {
                                        $statusClass = 'out';
                                        $statusLabel = 'Missing';
                                    } elseif ($isSpecialCategory && $savedStatus === 'not_working') {
                                        $statusClass = 'notworking';
                                        $statusLabel = 'Not working';
                                    } elseif ($isSpecialCategory && $savedStatus === 'available') {
                                        $statusClass = 'instock';
                                        $statusLabel = 'Available';
                                    } elseif ($item->quantity > 0 && $item->quantity < 10 && $isSpecialCategory) {
                                        $statusClass = 'instock';
                                        $statusLabel = 'Available';
                                    }

                                    $rowStatus = $statusClass;
                                    if (($isPowerTools || $isElectronics) && $rowStatus === 'low') {
                                        $rowStatus = 'instock';
                                    }
                                @endphp
                                <tr data-location="{{ strtolower($item->location ?? '') }}" data-category="{{ $categorySlug }}" class="equipment-row {{ $rowStatus }}" onclick="openEquipmentModal(this)" data-equipment='{{json_encode(["id" => $item->id, "name" => $item->name, "category" => $item->category ?? "—", "location" => $item->location ?? "—", "serial" => $item->serial ?? "—", "quantity" => $item->quantity, "type" => $item->type ?? "—", "status" => $item->status ?? null, "tag" => $item->tag ?? "—", "notes" => $item->notes ?? "No description provided", "image_path" => $item->image_path, "date_added" => $item->date_added ? $item->date_added->format('M d, Y') : $item->created_at->format('M d, Y'), "created_at" => $item->created_at->format('M d, Y H:i'), "updated_at" => $item->updated_at->format('M d, Y H:i')])}}'>
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
                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>{{ $item->date_added ? $item->date_added->format('Y-m-d') : $item->created_at->format('Y-m-d') }}</td>
                                    <td style="display:flex;align-items:center;justify-content:flex-end;gap:8px" onclick="event.stopPropagation()">
                                        <a href="/inventory/{{ $item->id }}/request" class="btn request">Request</a>
                                        <a href="/inventory/{{ $item->id }}/edit" class="btn edit">Edit</a>
                                        <a href="/inventory/{{ $item->id }}/delete" class="btn delete">Delete</a>
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
        // Live AJAX search for inventory
        (function(){
            const input = document.getElementById('inventory-search');
            const clearBtn = document.getElementById('inventory-search-clear');
            const tbody = document.querySelector('table.inventory-table tbody');
            const pagination = document.querySelector('.pagination');
            if(!input || !tbody) return;

            // keep a snapshot of the original table so we can restore without reloading
            const _originalTbody = tbody.innerHTML;
            const _originalPaginationDisplay = pagination ? (pagination.style.display || '') : '';

            let timer = null;
            let controller = null;
            // smaller debounce for snappier UX
            const DEBOUNCE = 150;
            const MIN_CHARS = 2;

            // simple in-memory cache (query -> rendered HTML) with small LRU behavior
            const _cache = new Map();
            const _cacheOrder = [];
            const _CACHE_MAX = 20;

            // removed setLoading helper; placeholder handling uses setPlaceholder()

            function setPlaceholder(message){
                // show the existing placeholder row if present, otherwise fallback to replacing tbody
                const placeholder = tbody.querySelector('tr.no-results');
                const html = '<div class="placeholder-msg" style="width:100%;padding:10px 8px;background:linear-gradient(180deg,#ffffff,#fbfdff);text-align:center;color:var(--muted)">' + message + '</div>';
                if(placeholder){
                    // hide all data rows
                    Array.from(tbody.querySelectorAll('tr')).forEach(r=> r.style.display = 'none');
                    placeholder.style.display = '';
                    const td = placeholder.querySelector('td');
                    if(td) td.innerHTML = html;
                } else {
                    tbody.dataset.prev = tbody.innerHTML;
                    tbody.innerHTML = '<tr class="no-results"><td colspan="9">' + html + '</td></tr>';
                }
                if(pagination) pagination.style.display = 'none';
            }

            function doSearch(q){
                // immediate cache hit: render cached HTML and skip network
                if(_cache.has(q)){
                    tbody.innerHTML = _cache.get(q);
                    if(pagination) pagination.style.display = 'none';
                    return Promise.resolve();
                }
                if(controller){
                    try{ controller.abort(); }catch(e){}
                }
                controller = new AbortController();
                // show temporary placeholder while searching (previously used setLoading())
                setPlaceholder('Searching...');
                return fetch('/inventory/search?q=' + encodeURIComponent(q), {credentials:'same-origin', signal: controller.signal})
                    .then(r => {
                        if(!r.ok) throw new Error('Network');
                        return r.text();
                    })
                    .then(html => {
                        tbody.innerHTML = html;
                        try{
                            // store in cache
                            _cache.set(q, html);
                            _cacheOrder.push(q);
                            if(_cacheOrder.length > _CACHE_MAX){
                                const old = _cacheOrder.shift();
                                _cache.delete(old);
                            }
                        }catch(e){}
                    })
                    .catch(err => {
                        if(err.name === 'AbortError') return;
                        console.error(err);
                        setPlaceholder('Search failed');
                    })
                    .finally(()=>{
                        controller = null;
                    });
            }

            input.addEventListener('input', function(e){
                const q = input.value.trim();
                clearTimeout(timer);
                if(q.length >= MIN_CHARS){
                    clearBtn.style.display = '';
                    timer = setTimeout(()=> doSearch(q), DEBOUNCE);
                } else if(q.length === 0) {
                    // restore original page state without reloading
                    clearBtn.style.display = 'none';
                    if(pagination) pagination.style.display = _originalPaginationDisplay || '';
                    tbody.innerHTML = _originalTbody;
                } else {
                    // short query: show hint
                    setPlaceholder('Type at least ' + MIN_CHARS + ' characters to search');
                }
            });

            clearBtn.addEventListener('click', function(){
                input.value = '';
                clearBtn.style.display = 'none';
                if(pagination) pagination.style.display = _originalPaginationDisplay || '';
                tbody.innerHTML = _originalTbody;
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

                function filterRows(loc, type, category){
                    const rows = document.querySelectorAll('tbody tr');
                    let anyVisible = false;
                    rows.forEach(r=>{
                        if(r.classList && r.classList.contains('no-results')) return; // skip placeholder
                        const rowLoc = (r.dataset.location || '').toLowerCase();
                        const rowType = (r.dataset.type || '').toLowerCase();
                        const rowCategory = (r.dataset.category || '').toLowerCase();
                        
                        let isVisible = false;
                        if(category){
                            // category-based tabs (e.g., power-tools)
                            isVisible = rowCategory === (category || '').toLowerCase();
                        } else if(type){
                            // preserve any future type-based tabs
                            isVisible = (rowType === (type || '').toLowerCase());
                        } else if(loc){
                            if(loc === 'all'){
                                // show everything when 'all' is selected
                                isVisible = true;
                            } else {
                                // show rows matching the requested location
                                isVisible = (rowLoc && rowLoc.indexOf(loc) !== -1);
                            }
                        } else {
                            // default: show all
                            isVisible = true;
                        }
                        
                        if(isVisible){
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
                    
                    // Determine title
                    if(txt.toLowerCase().includes('inventory')){
                        titleEl.textContent = txt;
                    } else if(txt.toLowerCase().includes('heavy') || txt.toLowerCase().includes('power')){
                        titleEl.textContent = txt;
                    } else {
                        titleEl.textContent = txt + ' Inventory';
                    }
                    
                    // Get filter type, location or category value
                    const type = tab.dataset.type || null;
                    const loc = tab.dataset.location || null;
                    const category = tab.dataset.category || null;
                    
                    try{
                        const url = new URL(window.location);
                        if(category){
                            url.searchParams.set('category', category);
                            url.searchParams.delete('type');
                            url.searchParams.delete('location');
                        } else if(type) {
                            url.searchParams.set('type', type);
                            url.searchParams.delete('location');
                            url.searchParams.delete('category');
                        } else if(loc) {
                            url.searchParams.set('location', loc);
                            url.searchParams.delete('type');
                            url.searchParams.delete('category');
                        }
                        window.history.replaceState({}, '', url);
                    } catch(e){}
                    
                    filterRows(loc, type, category);
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
                        const urlParams = new URL(window.location).searchParams;
                        const category = urlParams.get('category');
                        const type = urlParams.get('type');
                        const loc = urlParams.get('location');

                        if(category){
                            const match = Array.from(tabs).find(t=>t.dataset.category===category);
                            if(match) return setActive(match);
                        }
                        if(type){
                            const match = Array.from(tabs).find(t=>t.dataset.type===type);
                            if(match) return setActive(match);
                        }
                        if(loc){
                            const match = Array.from(tabs).find(t=>t.dataset.location===loc);
                            if(match) return setActive(match);
                        }
                    } catch(e){}
                    const active = document.querySelector('.tabs .tab.active');
                    if(active) setActive(active);
                })();
            })();
        </script>
    <script>
        // Lightweight client-side column sorting for tables with class `inventory-table`.
        // Adds a small arrow indicator and tooltip via JS only (no CSS changes).
        (function(){
            const tables = document.querySelectorAll('table.inventory-table');
            if(!tables || !tables.length) return;

            tables.forEach(table => {
                const thead = table.tHead || table.querySelector('thead');
                const tbody = table.tBodies[0];
                if(!thead || !tbody) return;
                const headers = Array.from(thead.querySelectorAll('th'));

                // store original titles to preserve any existing tooltip
                headers.forEach(h=>{ if(!h.dataset.origTitle) h.dataset.origTitle = h.getAttribute('title') || ''; });

                headers.forEach((th, colIdx) => {
                    th.style.cursor = th.style.cursor || 'pointer';
                    th.addEventListener('click', function(){
                        const rows = Array.from(tbody.querySelectorAll('tr')).filter(r=> r.style.display !== 'none');
                        if(!rows.length) return;

                        const getCell = (row) => {
                            const cell = row.children[colIdx];
                            return cell ? cell.innerText.trim() : '';
                        };

                        // detect simple column type from first non-empty cell
                        let sample = '';
                        for(const r of rows){ sample = getCell(r); if(sample) break; }
                        const isDate = sample && !isNaN(Date.parse(sample));
                        const numTest = sample && sample.replace(/[^0-9.\-]/g,'');
                        const isNum = numTest && /^-?[0-9,.]+$/.test(numTest.replace(/,/g,''));

                        const current = th.getAttribute('data-sort-order') || 'none';
                        const asc = current !== 'asc';
                        // reset other headers: remove indicator, aria-sort and data attribute
                        headers.forEach(h=>{
                            if(h !== th){
                                h.removeAttribute('data-sort-order');
                                h.removeAttribute('aria-sort');
                                // remove indicator span if present
                                const old = h.querySelector('[data-sort-indicator]');
                                if(old) old.remove();
                                // restore original title (if any)
                                if(h.dataset.origTitle) h.setAttribute('title', h.dataset.origTitle);
                            }
                        });

                        th.setAttribute('data-sort-order', asc ? 'asc' : 'desc');
                        th.setAttribute('aria-sort', asc ? 'ascending' : 'descending');
                        // update tooltip/title
                        th.setAttribute('title', (asc ? 'Sort: Ascending' : 'Sort: Descending') + ' — click to toggle');

                        // remove any existing indicator then append new one
                        const existing = th.querySelector('[data-sort-indicator]');
                        if(existing) existing.remove();
                        const ind = document.createElement('span');
                        ind.setAttribute('data-sort-indicator', '1');
                        ind.setAttribute('aria-hidden', 'true');
                        ind.style.marginLeft = '6px';
                        ind.textContent = asc ? '▲' : '▼';
                        th.appendChild(ind);

                        const collator = new Intl.Collator(undefined, {numeric:true, sensitivity:'base'});

                        rows.sort((a,b)=>{
                            const va = getCell(a);
                            const vb = getCell(b);
                            if(isNum){
                                const na = parseFloat(va.replace(/[^0-9.-]+/g,'')) || 0;
                                const nb = parseFloat(vb.replace(/[^0-9.-]+/g,'')) || 0;
                                return asc ? na - nb : nb - na;
                            }
                            if(isDate){
                                const da = Date.parse(va) || 0;
                                const db = Date.parse(vb) || 0;
                                return asc ? da - db : db - da;
                            }
                            return asc ? collator.compare(va, vb) : collator.compare(vb, va);
                        });

                        // re-append rows in sorted order (preserves event listeners on row elements)
                        rows.forEach(r => tbody.appendChild(r));
                    });
                });
            });
        })();
    </script>

        <!-- Equipment Details Modal - Professional Design -->
        <div class="modal-backdrop" id="equipmentBackdrop"></div>
        <div id="equipmentModal">
            <div class="modal-header">
                <h2 id="modalName">Equipment Details</h2>
                <button class="modal-close" id="modalCloseBtn">✕</button>
            </div>
            <div class="modal-body">
                <!-- Image Section (Left) -->
                <div class="modal-image-section">
                    <div class="modal-image-container">
                        <img id="modalImage" src="" alt="Equipment" class="modal-image" style="display:none">
                        <div id="noImage" style="display:flex">
                            <span style="font-size:48px;margin-right:8px" style="display:none"></span>
                            <div style="text-align:center;flex:1;width:100%;display:flex;flex-direction:column;align-items:center;justify-content:center">
                                <p style="margin:0;font-weight:600">No image available</p>
                                <p style="margin:4px 0 0 0;font-size:12px;color:#94a3b8">Add an image to showcase equipment</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Section (Right) -->
                <div class="modal-content-section">
                    <!-- Key Information -->
                    <div class="modal-section">
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
                    </div>

                    <!-- Identification -->
                    <div class="modal-section">
                        <div class="modal-section-title">Identification</div>
                        <div class="modal-grid-2col">
                            <div class="modal-info">
                                <span class="modal-label">Serial Number</span>
                                <div class="modal-value" id="modalSerial">—</div>
                            </div>
                            <div class="modal-info">
                                <span class="modal-label">Tag / Identifier</span>
                                <div class="modal-value" id="modalTag">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="modal-section">
                        <div class="modal-section-title">Timeline</div>
                        <div class="modal-grid-3col">
                            <div class="modal-info">
                                <span class="modal-label">Date Added</span>
                                <div class="modal-value" id="modalDateAdded">—</div>
                            </div>
                            <div class="modal-info">
                                <span class="modal-label">Created On</span>
                                <div class="modal-value" id="modalCreated">—</div>
                            </div>
                            <div class="modal-info">
                                <span class="modal-label">Last Updated</span>
                                <div class="modal-value" id="modalUpdated">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="modal-section">
                        <div class="modal-section-title">Description / Notes</div>
                        <div class="modal-value" id="modalNotes" style="margin-top:8px">No description provided</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="modal-actions">
                        <a id="modalEditBtn" class="btn edit" href="#">Edit Equipment</a>
                        <a id="modalRequestBtn" class="btn request" href="#">Request Equipment</a>
                    </div>
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
        <!-- Delete confirmation modal -->
        <div class="approval-backdrop" id="deleteBackdrop" style="display:none"></div>
        <div class="approval-modal" id="deleteModal" style="display:none">
            <div class="approval-header">
                <h2 id="deleteTitle">Confirm Deletion</h2>
                <button class="approval-close" id="deleteClose">&times;</button>
            </div>
            <div class="approval-body">
                <p id="deleteItemName" style="margin:0 0 8px 0;font-weight:800;font-size:16px;color:#0f172a">This equipment</p>
                <p id="deleteMessage" style="margin:0 0 12px 0;color:#6b7280">This action will permanently remove the equipment from inventory.</p>
                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:18px">
                    <button id="deleteCancel" class="btn ghost">Cancel</button>
                    <button id="deleteConfirm" class="btn delete">Delete</button>
                </div>
            </div>
        </div>

            <!-- Delete success modal (reuses approval modal styles) -->
            <div class="approval-backdrop" id="deleteSuccessBackdrop" style="display:none"></div>
            <div class="approval-modal" id="deleteSuccessModal" style="display:none">
                <div class="approval-header">
                    <h2 id="deleteSuccessTitle">Deleted</h2>
                    <button class="approval-close" id="deleteSuccessClose">&times;</button>
                </div>
                <div class="approval-body">
                    <p id="deleteSuccessItemName" style="margin:0 0 8px 0;font-weight:800;font-size:16px;color:#0f172a">Item</p>
                    <p id="deleteSuccessBody" style="margin:0 0 12px 0;color:#6b7280">The equipment has been deleted successfully.</p>
                </div>
            </div>

            <script>
            (function(){
                    const deleteModal = document.getElementById('deleteModal');
                    const deleteBackdrop = document.getElementById('deleteBackdrop');
                    const deleteClose = document.getElementById('deleteClose');
                    const deleteCancel = document.getElementById('deleteCancel');
                    const deleteConfirm = document.getElementById('deleteConfirm');
                    const deleteMessage = document.getElementById('deleteMessage');
                    let pendingHref = null;
                    let pendingItemName = null;
                    // delete success modal elements
                    const deleteSuccessModal = document.getElementById('deleteSuccessModal');
                    const deleteSuccessBackdrop = document.getElementById('deleteSuccessBackdrop');
                    const deleteSuccessClose = document.getElementById('deleteSuccessClose');
                    const deleteSuccessItemName = document.getElementById('deleteSuccessItemName');
                    const deleteSuccessBody = document.getElementById('deleteSuccessBody');
                    let _deleteSuccessTimeout = null;

                function showDeleteModal(name, ref){
                    const displayName = name || 'This equipment';
                    const nameEl = document.getElementById('deleteItemName');
                    if(nameEl) nameEl.textContent = displayName;
                    // store pending item name for success notification
                    pendingItemName = displayName;
                    deleteMessage.innerHTML = 'This <strong>equipment</strong> will be <strong style="color:#e11d48">permanently deleted</strong>.';
                    deleteBackdrop.style.display = 'block';
                    deleteModal.style.display = 'block';
                    setTimeout(()=>{ deleteBackdrop.classList.add('show'); deleteModal.classList.add('show'); }, 10);
                }

                function hideDeleteModal(){
                    deleteModal.classList.remove('show');
                    deleteBackdrop.classList.remove('show');
                    setTimeout(()=>{ deleteBackdrop.style.display = 'none'; deleteModal.style.display = 'none'; }, 220);
                    pendingHref = null;
                }

                function showDeleteSuccess(name){
                    if(deleteSuccessItemName) deleteSuccessItemName.textContent = name || 'Item';
                    if(deleteSuccessBody) deleteSuccessBody.textContent = 'The equipment has been deleted successfully.';
                    deleteSuccessBackdrop.style.display = 'block';
                    deleteSuccessModal.style.display = 'block';
                    setTimeout(()=>{ deleteSuccessBackdrop.classList.add('show'); deleteSuccessModal.classList.add('show'); }, 10);
                    // auto-dismiss and reload after 3s
                    clearTimeout(_deleteSuccessTimeout);
                    _deleteSuccessTimeout = setTimeout(()=>{ hideDeleteSuccess(true); }, 3000);
                }

                function hideDeleteSuccess(shouldReload){
                    deleteSuccessModal.classList.remove('show');
                    deleteSuccessBackdrop.classList.remove('show');
                    clearTimeout(_deleteSuccessTimeout);
                    setTimeout(()=>{ deleteSuccessBackdrop.style.display = 'none'; deleteSuccessModal.style.display = 'none'; if(shouldReload) window.location.reload(); }, 220);
                }

                // delegate clicks on delete links (use capture so td.stopPropagation() doesn't block it)
                document.addEventListener('click', function(e){
                    // find nearest element node from event target and then closest delete anchor
                    let node = e.target;
                    while(node && node.nodeType !== 1) node = node.parentElement;
                    if(!node) return;
                    const a = node.closest('a.btn.delete');
                    if(!a) return;
                    e.preventDefault();
                    // try to extract name/id from closest row dataset.equipment
                    const row = a.closest('tr');
                    let name = null;
                    let ref = a.getAttribute('href');
                    if(row && row.dataset && row.dataset.equipment){
                        try{ const data = JSON.parse(row.dataset.equipment); name = data.name || data.category || ('ID ' + (data.id||'')); }catch(err){}
                    }
                    pendingHref = ref;
                    showDeleteModal(name, ref);
                }, true);

                // wire up success modal close actions
                if(deleteSuccessClose) deleteSuccessClose.addEventListener('click', function(){ hideDeleteSuccess(true); });
                if(deleteSuccessBackdrop) deleteSuccessBackdrop.addEventListener('click', function(){ hideDeleteSuccess(true); });

                if(deleteClose) deleteClose.addEventListener('click', hideDeleteModal);
                if(deleteCancel) deleteCancel.addEventListener('click', hideDeleteModal);
                if(deleteBackdrop) deleteBackdrop.addEventListener('click', hideDeleteModal);
                if(deleteConfirm) deleteConfirm.addEventListener('click', async function(){
                    if(!pendingHref) return hideDeleteModal();
                    // attempt CSRF-protected POST (method spoofing to DELETE) so server handles deletion safely
                    deleteConfirm.disabled = true;
                    const origText = deleteConfirm.textContent;
                    deleteConfirm.textContent = 'Deleting...';
                    const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
                    try{
                        const res = await fetch(pendingHref, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': token
                            },
                            body: '_method=DELETE'
                        });

                        if(res.ok){
                            // remove the row from the DOM if present, then reload to ensure pagination/state
                            const link = document.querySelector('a.btn.delete[href="' + pendingHref + '"]');
                                    const row = link ? link.closest('tr') : null;
                                    if(row) row.remove();
                                    hideDeleteModal();
                                    // show delete-success modal (auto-dismiss then reload)
                                    try{ showDeleteSuccess(pendingItemName); }catch(e){ window.location.reload(); }
                        } else {
                            // fallback: submit a POST form (method-spoof DELETE) to ensure server receives POST
                            const tokenInput = '<input type="hidden" name="_token" value="' + token + '">';
                            const methodInput = '<input type="hidden" name="_method" value="DELETE">';
                            const f = document.createElement('form');
                            f.method = 'POST';
                            f.action = pendingHref;
                            f.style.display = 'none';
                            f.innerHTML = tokenInput + methodInput;
                            document.body.appendChild(f);
                            f.submit();
                        }
                    }catch(err){
                        console.error(err);
                        // last-resort fallback: submit POST form to avoid GET route
                        try{
                            const tokenInput = '<input type="hidden" name="_token" value="' + token + '">';
                            const methodInput = '<input type="hidden" name="_method" value="DELETE">';
                            const f = document.createElement('form');
                            f.method = 'POST';
                            f.action = pendingHref;
                            f.style.display = 'none';
                            f.innerHTML = tokenInput + methodInput;
                            document.body.appendChild(f);
                            f.submit();
                        }catch(e){
                            // if even that fails, navigate as absolute fallback
                            window.location.href = pendingHref;
                        }
                    }finally{
                        deleteConfirm.disabled = false;
                        deleteConfirm.textContent = origText;
                    }
                });
            })();
        </script>
</body>
</html>
