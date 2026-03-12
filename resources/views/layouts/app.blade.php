<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>San Juan CDRMMD</title>
    <link rel="icon" href="/images/favi.png" type="image/png">
    <link rel="apple-touch-icon" href="/images/favi.png">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}

        /* Full-bleed background image */
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}

        /* Topbar */
        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.96);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.06);z-index:60}
        .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}
        .notif-bell{position:relative;display:inline-flex;align-items:center;gap:8px;margin-right:12px}
        .notif-bell button{background:transparent;border:none;cursor:pointer;padding:8px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center}
        .notif-count{position:absolute;top:-6px;right:-6px;z-index:70;background:#ef4444;color:#fff;font-size:12px;padding:3px 6px;border-radius:999px;min-width:20px;text-align:center;box-shadow:0 6px 18px rgba(2,6,23,0.12)}
        .notif-dropdown{position:absolute;right:0;top:44px;width:360px;max-height:420px;background:linear-gradient(180deg,#ffffff,#fbfdff);border-radius:12px;box-shadow:0 18px 50px rgba(2,6,23,0.16);overflow:auto;display:none;z-index:120;padding:8px}
        .notif-dropdown.show{display:block}

        .app{display:flex;min-height:100vh}

        /* Sidebar */
        .sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transition:width .22s ease,transform .22s ease;z-index:50;height:calc(100vh - var(--topbar-height))}
        .sidebar.collapsed{width:64px}
        .sidebar.collapsed{padding:8px 6px}
        .sidebar.collapsed .label{display:none}
        .sidebar.collapsed .text{display:none}
        .sidebar.collapsed .logo-img{width:28px;height:28px}
        .sidebar.collapsed .nav a{justify-content:center;padding:8px}
        .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
        .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
        .nav a, .nav button.action{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px;width:100%;box-sizing:border-box}
        .nav a svg{display:block;width:18px;height:18px;min-width:18px}
        .nav a:hover, .nav button.action:hover{background:#f1f5f9}
        /* Top-level links: full-width gradient (keeps Accounts prominent) */
        .nav > a.active{
            background:linear-gradient(90deg,var(--accent),var(--accent-2));
            color:white;
        }

        /* Sub-link adjustments (Monitoring/Maintenance): compact left indicator */
        .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 12px;font-size:13px;justify-content:flex-start;text-align:left;position:relative}
        .nav a.sub-link.active{
            background:none !important;
            background-image:none !important;
            box-shadow:none !important;
            color:inherit !important;
            padding-left:26px !important;
            padding-right:12px !important;
        }
        .nav a.sub-link.active::before{
            content:'' !important;
            position:absolute !important;
            left:12px !important;
            top:50% !important;
            transform:translateY(-50%) !important;
            height:20px !important;
            width:6px !important;
            border-radius:4px !important;
            background: linear-gradient(180deg,var(--accent),var(--accent-2)) !important;
            box-shadow:none !important;
        }

        /* Main area */
        .main{flex:1;padding:24px;margin-top:var(--topbar-height);margin-left:0;transition:margin .22s ease}
        /* Desktop: sidebar visible by default. Mobile behavior overrides in media query. */
        .sidebar{transform:translateX(0);transition:transform .22s ease,width .22s ease}
        .sidebar.closed{transform:translateX(-110%);}
        .sidebar.open{transform:translateX(0);z-index:90}
        .sidebar.collapsed{width:64px;transform:translateX(0)}

        .panel{background:var(--panel);padding:12px 14px;border-radius:10px;box-shadow:0 6px 20px rgba(15,23,42,0.04);max-width:none;width:calc(100% - 48px);margin:12px auto}

        .nav-overlay{position:fixed;left:0;right:0;top:var(--topbar-height);bottom:0;background:rgba(2,6,23,0.45);opacity:0;visibility:hidden;transition:opacity .18s ease;z-index:80}
        .nav-overlay.show{opacity:1;visibility:visible}

        @media(max-width:900px){
            .sidebar{position:fixed;left:0;top:0;bottom:0;z-index:90;height:100vh}
            .sidebar.open{transform:translateX(0)}
            .main{padding:16px}
        }
    </style>
    @include('partials._bg-preload')
    @include('partials._formatters')
    @yield('head')
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
                        <span>San Juan CDRMMD Accounts</span>
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
        <aside class="sidebar {{ (request()->is('vehicle/maintenance*') || request()->is('vehicle/monitoring*')) ? 'closed' : '' }}" id="sidebar">
            <a href="/dashboard" class="brand" style="text-decoration:none;color:inherit">
                <img src="/images/favi.png" alt="San Juan" class="logo-img" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
                <div class="text" style="font-size:14px">San Juan CDRMMD</div>
            </a>
            <nav class="nav">
                <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3v6h8V3h-8zM3 21h8v-6H3v6z" fill="currentColor"/></svg><span class="label">Home</span></a>
                <a href="/inventory" class="{{ request()->is('inventory*') ? 'active' : '' }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 7a5 5 0 100 10 5 5 0 000-10zM2 12a10 10 0 1120 0A10 10 0 012 12z" fill="currentColor"/></svg><span class="label">Inventory</span></a>
                <div style="display:flex;align-items:center;gap:6px">
                    <a href="/vehicle" style="flex:1"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 13l1.5-4.5A2 2 0 016.4 7h11.2a2 2 0 011.9 1.5L21 13v5a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H6v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM6 14h12M7.5 10.5h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="label">Vehicle</span></a>
                    <button id="vehicle-submenu-toggle" type="button" aria-label="Toggle Vehicle menu" title="Toggle Vehicle menu" style="width:28px;height:28px;border:none;background:transparent;color:#475569;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;line-height:1;padding:0">⌄</button>
                </div>
                <div id="vehicle-submenu" style="display:none">
                    <a href="/vehicle/monitoring" class="sub-link {{ request()->is('vehicle/monitoring*') ? 'active' : '' }}"><span class="label">Monitoring</span></a>
                    <a href="/vehicle/maintenance" class="sub-link {{ request()->is('vehicle/maintenance*') ? 'active' : '' }}"><span class="label">Maintenance</span></a>
                </div>
                <a href="/requests" class="{{ request()->is('requests*') ? 'active' : '' }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 3H5a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Request</span></a>
                <a href="/accounts" class="{{ request()->is('accounts*') ? 'active' : '' }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 20a8 8 0 0116 0v1H4v-1z" fill="currentColor"/></svg><span class="label">Accounts</span></a>
                <a href="#" class="{{ request()->is('settings*') ? 'active' : '' }}"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 8a4 4 0 100 8 4 4 0 000-8zM3 13h3l1-3 2 2 3-4 2 4 3-2 1 3h3" stroke="currentColor" stroke-width="1" fill="none"/></svg><span class="label">Settings</span></a>
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg>
                    <span class="label">Logout</span>
                </a>
            </nav>
        </aside>

        <main class="main">
            @yield('content')
        </main>
    </div>

    <form id="logout-form" method="POST" action="/logout" style="display:none">@csrf</form>

    @if(request()->is('vehicle/maintenance*') || request()->is('vehicle/monitoring*'))
        <script>
            try{ localStorage.setItem('sidebar.closed','1'); localStorage.setItem('sidebar.collapsed','0'); }catch(e){}
        </script>
    @endif

    <script>
        (function(){
            if (window.__vehicleSubmenuInitialized) return; window.__vehicleSubmenuInitialized = true;
            const toggle = document.getElementById('vehicle-submenu-toggle');
            const submenu = document.getElementById('vehicle-submenu');
            if(!toggle || !submenu) return;

            const storageKey = 'sidebar.vehicleOpen';
            const isVehiclePage = {{ request()->is('vehicle*') ? 'true' : 'false' }};

            function setOpen(open){
                submenu.style.display = open ? '' : 'none';
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.textContent = open ? '⌄' : '⌄';
                try{ localStorage.setItem(storageKey, open ? '1' : '0'); }catch(e){}
            }

            try{
                const saved = localStorage.getItem(storageKey);
                if(saved === null){
                    setOpen(!!isVehiclePage);
                } else {
                    setOpen(saved === '1');
                }
            }catch(e){ setOpen(!!isVehiclePage); }

            toggle.addEventListener('click', function(e){
                e.preventDefault(); e.stopPropagation();
                const currentlyOpen = submenu.style.display !== 'none' && submenu.style.display !== '' ? true : (getComputedStyle(submenu).display !== 'none');
                setOpen(!currentlyOpen);
            });
        })();
    </script>
    <script>
        (function(){
            const sidebar = document.getElementById('sidebar');
            const burger = document.getElementById('burger-top');
            const topbar = document.querySelector('.topbar');
            if (!sidebar || !burger) return;
            if (window.__sidebarInitialized) return; window.__sidebarInitialized = true;

            let navOverlay = document.getElementById('nav-overlay');
            if (!navOverlay) {
                navOverlay = document.createElement('div');
                navOverlay.id = 'nav-overlay';
                navOverlay.className = 'nav-overlay';
                document.body.appendChild(navOverlay);
            }

            function setOverlay(show){
                navOverlay.classList.toggle('show', !!show);
                document.body.style.overflow = show ? 'hidden' : '';
            }

            // Apply persisted closed/collapsed state on desktop so pages (like Accounts) don't force-open
            try{
                const savedClosed = localStorage.getItem('sidebar.closed');
                const savedCollapsed = localStorage.getItem('sidebar.collapsed');
                const isMobileDefault = window.matchMedia('(max-width:900px)').matches;
                const isAccounts = {{ request()->is('accounts*') ? 'true' : 'false' }};
                if (!isMobileDefault) {
                    if (isAccounts) {
                        sidebar.classList.add('closed');
                        sidebar.classList.remove('collapsed');
                        try{ localStorage.setItem('sidebar.closed','1'); }catch(e){}
                        try{ localStorage.setItem('sidebar.collapsed','0'); }catch(e){}
                        document.querySelector('.main')?.classList.remove('sidebar-hidden');
                    } else if (savedClosed === '1') {
                        sidebar.classList.add('closed');
                        sidebar.classList.remove('collapsed');
                        document.querySelector('.main')?.classList.remove('sidebar-hidden');
                    } else if (savedCollapsed === '1') {
                        sidebar.classList.add('collapsed');
                        sidebar.classList.remove('closed');
                        document.querySelector('.main')?.classList.add('sidebar-hidden');
                    } else {
                        sidebar.classList.remove('collapsed');
                        sidebar.classList.remove('closed');
                        document.querySelector('.main')?.classList.remove('sidebar-hidden');
                    }
                    // ensure mobile-open classes are not set
                    sidebar.classList.remove('open');
                    navOverlay.classList.remove('show');
                    try{ document.body.style.overflow = ''; }catch(e){}
                } else {
                    // mobile: start closed
                    sidebar.classList.remove('open');
                    navOverlay.classList.remove('show');
                }
            }catch(e){}

            console.log('sidebar init attaching listener');
            burger.addEventListener('click', function(e){
                console.log('sidebar listener click');
                e.stopPropagation();
                const isMobile = window.matchMedia('(max-width:900px)').matches;
                if (isMobile) {
                    const willOpen = !sidebar.classList.contains('open');
                    sidebar.classList.toggle('open');
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.remove('closed');
                    setOverlay(willOpen);
                } else {
                    // On desktop toggle between fully hidden (closed) and visible. Clear collapsed when closed.
                    const closed = sidebar.classList.toggle('closed');
                    if (closed) {
                        sidebar.classList.remove('collapsed');
                        try{ localStorage.setItem('sidebar.closed', '1'); }catch(e){}
                        try{ localStorage.setItem('sidebar.collapsed', '0'); }catch(e){}
                        document.querySelector('.main')?.classList.remove('sidebar-hidden');
                    } else {
                        try{ localStorage.setItem('sidebar.closed', '0'); }catch(e){}
                    }
                    // ensure overlay is hidden when collapsing/expanding on desktop
                    setOverlay(false);
                    try{ document.body.style.overflow = ''; }catch(e){}
                }
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

        // Fallback callable from inline onclick if listener not attached. No-op when main initializer is present.
        window.__sidebarFallback = function(e){
            if (window.__sidebarInitialized) { try{ console.log('__sidebarFallback suppressed (main listener active)'); }catch(e){}; return; }
            try{ console.log('__sidebarFallback invoked'); }catch(e){}
            e && e.stopPropagation();
            const sidebar = document.getElementById('sidebar');
            if(!sidebar) return;
            let navOverlay = document.getElementById('nav-overlay');
            if(!navOverlay){
                navOverlay = document.createElement('div');
                navOverlay.id = 'nav-overlay';
                navOverlay.className = 'nav-overlay';
                document.body.appendChild(navOverlay);
                navOverlay.addEventListener('click', function(){ sidebar.classList.remove('open'); navOverlay.classList.remove('show'); document.body.style.overflow = ''; });
            }
            const isMobile = window.matchMedia('(max-width:900px)').matches;
            if(isMobile){
                const willOpen = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open');
                sidebar.classList.remove('collapsed');
                navOverlay.classList.toggle('show', willOpen);
                document.body.style.overflow = willOpen ? 'hidden' : '';
            } else {
                const collapsed = sidebar.classList.toggle('collapsed');
                document.querySelector('.main')?.classList.toggle('sidebar-hidden', collapsed);
            }
        };
    </script>

    <style>
        /* High-specificity overrides:
           - Top-level active link uses full-width gradient
           - Sub-links use compact left indicator */
        #sidebar .nav > a.active{
            background: linear-gradient(90deg,var(--accent),var(--accent-2)) !important;
            color: #fff !important;
        }
        #sidebar .nav a.sub-link.active{
            background:none !important;
            color:inherit !important;
            position:relative !important;
        }
        #sidebar .nav a.sub-link.active::before{
            content:'' !important;
            position:absolute !important;
            left:12px !important;
            top:50% !important;
            transform:translateY(-50%) !important;
            height:20px !important;
            width:6px !important;
            border-radius:4px !important;
            background: linear-gradient(180deg,var(--accent),var(--accent-2)) !important;
            box-shadow:none !important;
        }
    </style>
    @stack('scripts')
    @include('partials.notifs')
</body>
</html>
