<style>
    :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
    .app{display:flex;min-height:100vh}

    /* Sidebar - fixed below the topbar to avoid overlap */
    #sidebar{position:fixed;left:0;top:var(--topbar-height);bottom:0;width:240px;background:var(--panel);border-right:1px solid #e6e9ef;padding:20px;transition:width .22s ease,transform .22s ease;z-index:50;height:calc(100vh - var(--topbar-height));overflow:hidden}
    #sidebar.collapsed{width:64px}
    #sidebar .brand{font-weight:800;color:var(--accent);margin-bottom:18px;display:flex;align-items:center;gap:10px}
    #sidebar .brand .logo{width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:inline-flex;align-items:center;justify-content:center;color:white;font-weight:800}
    #sidebar .nav{display:flex;flex-direction:column;gap:6px;margin-top:6px}
    #sidebar .nav a, #sidebar .nav button.action{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#0f172a;text-decoration:none;background:transparent;border:none;cursor:pointer;font-size:14px;min-height:44px}
    /* Force a sane width inside the sidebar to override page-level styles that set full-width links */
    #sidebar .nav a, #sidebar .nav button.action{width:auto !important;box-sizing:border-box}
    #sidebar .nav a svg, #sidebar .nav button.action svg{display:block;width:18px;height:18px}
    #sidebar .nav a:hover, #sidebar .nav button.action:hover{background:#f1f5f9}
    /* Top-level active: full-width gradient (keeps Accounts prominent) */
    #sidebar .nav > a.active{
        background: linear-gradient(90deg,var(--accent),var(--accent-2)) !important;
        color: #fff !important;
    }

    /* Move label right to avoid indicator overlap for sub-links */
    #sidebar .nav a.sub-link.active .label{ padding-left:24px; }

    /* Hide indicator when sidebar is collapsed/closed/hidden */
    #sidebar.collapsed .nav a.active::before,
    #sidebar.hidden .nav a.active::before,
    #sidebar.closed .nav a.active::before{ display:none !important; }

    /* When sidebar is collapsed/closed we avoid showing the full highlight pill */
    #sidebar.collapsed .nav a.active,
    #sidebar.hidden .nav a.active,
    #sidebar.closed .nav a.active{background:transparent;color:inherit}

    /* Specifically target sub-link active state (Monitoring/Maintenance) to prevent long/full-width highlights */
    #sidebar .nav a.sub-link{ position:relative; }
    #sidebar .nav a.sub-link.active{
        background: none !important;
        background-image: none !important;
        box-shadow: none !important;
        color: inherit !important;
        position:relative !important;
        padding-left:26px !important; /* make room for indicator */
        padding-right:12px !important;
    }

    /* Short left indicator only for sub-links */
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

    /* ensure sub-link text and icon keep normal color */
    #sidebar .nav a.sub-link.active, #sidebar .nav a.sub-link.active .label, #sidebar .nav a.sub-link.active svg { color: inherit !important; }

    /* Ensure sub-links cannot overflow the sidebar or cast large shadows */
    #sidebar .nav a.sub-link,
    #sidebar .nav a.sub-link .label{
        max-width:100%;
        box-sizing:border-box;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }

    #sidebar .nav a.sub-link.active{
        display:inline-flex;
        width:100% !important;
        max-width:100% !important;
        box-shadow:none !important;
        background-clip:padding-box !important;
    }

    /* Last-resort clamps: ensure no pseudo-elements or page styles can make the highlight wider */
    #sidebar .nav a,
    #sidebar .nav a *,
    #sidebar .nav a::before,
    #sidebar .nav a::after,
    #sidebar .nav a.sub-link::before,
    #sidebar .nav a.sub-link::after {
        box-shadow: none !important;
        max-width: 100% !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
    }

    /* Keep the icon and label aligned while constraining width */
    #sidebar .nav a{
        display:flex !important;
        align-items:center !important;
        width:100% !important;
        box-sizing:border-box !important;
    }
    #sidebar .nav a.sub-link{margin-left:26px;min-height:36px;padding:8px 12px;font-size:13px;justify-content:flex-start;text-align:left}
    #sidebar .nav svg{flex-shrink:0}

    /* ensure active label/icon remain readable regardless of page-level overrides */
    #sidebar .nav a .label { color: inherit !important; }
    /* Let active links inherit color (we show a left indicator instead of full-width fill) */
    #sidebar .nav a.active, #sidebar .nav a.active .label, #sidebar .nav a.active svg { color: inherit !important; }
    /* Avoid conflicts with page-level `.label` rules (form labels) */
    #sidebar .label { font-weight: 500 !important; }

    /* Main area (push down for topbar). Sidebar is overlay by default - match dashboard behaviour */
    .main{flex:1;padding:24px;margin-top:var(--topbar-height);margin-left:0;transition:margin .22s ease;position:relative;z-index:75}
    #sidebar{transform:translateX(-110%);transition:transform .22s ease,width .22s ease}
    #sidebar.open{transform:translateX(0);z-index:90}
    #sidebar.collapsed{width:64px;transform:translateX(0)}
    /* Allow hiding the sidebar on desktop by toggling `.hidden` */
    #sidebar.hidden{transform:translateX(-110%)}
    .main.sidebar-hidden{margin-left:0}

    /* Sidebar inline badge to avoid conflict with global notif-count */
    .sidebar-badge{display:inline-block;background:#ef4444;color:#fff;font-size:12px;padding:3px 6px;border-radius:999px;margin-left:8px;vertical-align:middle}

    /* Collapsed state adjustments (copy from dashboard) */
    #sidebar.collapsed .brand .text,
    #sidebar.collapsed .nav a span.label,
    #sidebar.collapsed .nav button.action span.label{display:none}
    #sidebar.collapsed .nav a,
    #sidebar.collapsed .nav button.action{justify-content:center}
    /* ensure svg centers inside collapsed button */
    #sidebar.collapsed .nav a svg,
    #sidebar.collapsed .nav button.action svg{margin:0 auto}
    #sidebar.collapsed .brand{justify-content:center}

    /* Responsive */
    @media(max-width:900px){
        #sidebar{position:fixed;left:0;top:0;bottom:0;z-index:80;transform:translateX(-110%);height:100vh}
        #sidebar.open{transform:translateX(0)}
        #sidebar + .main{margin-left:0}
        .main{padding:16px}
    }
</style>

<aside id="sidebar" class="sidebar">
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
<script>
    (function(){
        const toggle = document.getElementById('vehicle-submenu-toggle');
        const submenu = document.getElementById('vehicle-submenu');
        if(!toggle || !submenu) return;
        if (window.__vehicleSubmenuInitialized) return; window.__vehicleSubmenuInitialized = true;

        const storageKey = 'sidebar.vehicleOpen';
        // server-side hint whether current page is vehicle-related
        const isVehiclePage = {{ request()->is('vehicle*') ? 'true' : 'false' }};

        function setOpen(open){
            submenu.style.display = open ? '' : 'none';
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.textContent = open ? '⌄' : '⌄'; // keep same glyph; rotation handled via CSS if desired
            try{ localStorage.setItem(storageKey, open ? '1' : '0'); }catch(e){}
        }

        // init from localStorage, or auto-open on vehicle pages
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
            const isOpen = submenu.style.display !== 'none' && submenu.style.display !== '' ? false : (submenu.style.display === '' || submenu.style.display === 'block');
            // toggle based on current computed state
            const currentlyOpen = submenu.style.display !== 'none' && submenu.style.display !== '' ? true : (getComputedStyle(submenu).display !== 'none');
            setOpen(!currentlyOpen);
        });
    })();
</script>
<script>
    // Sidebar burger/overlay behaviour - single guarded initializer
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

        burger.addEventListener('click', function(e){
            e.stopPropagation();
            const isMobile = window.matchMedia('(max-width:900px)').matches;
            if (isMobile) {
                const willOpen = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open');
                sidebar.classList.remove('collapsed');
                setOverlay(willOpen);
            } else {
                // on desktop, allow hiding the sidebar by toggling `hidden`
                const hidden = sidebar.classList.toggle('hidden');
                document.querySelector('.main')?.classList.toggle('sidebar-hidden', hidden);
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
    // Fallback callable from inline onclick if main initializer not attached (pages without layouts.app)
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