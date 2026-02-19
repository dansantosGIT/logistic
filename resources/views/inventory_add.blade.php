<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Add Equipment — San Juan CDRMMD</title>
    <link rel="icon" href="/images/favi.png" type="image/png">
    <meta name="theme-color" content="#0b1220">
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
        .field label{display:block;font-weight:600;color:var(--muted);margin-bottom:8px}
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
                        <a href="/dashboard" style="display:flex;align-items:center;gap:6px;font-weight:700;text-decoration:none;color:inherit">
                            <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                            <span>Add Equipment</span>
                        </a>
                    <div style="font-size:12px;color:var(--muted)">Inventory / Add</div>
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:13px;color:var(--muted-2)">Welcome</div>
                <div style="font-weight:700">{{ auth()->user()->name }}</div>
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
                <a href="#" class="nav-logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 17l5-5-5-5v3H3v4h7v3zM19 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z" fill="currentColor"/></svg><span class="label">Logout</span></a>
            </nav>
        </aside>

        <main class="main">
            <div class="panel">
                <form method="POST" action="/inventory/add" enctype="multipart/form-data">
                    @csrf
                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Name <span style="color:#ef4444;margin-left:6px;font-weight:700">*</span></label>
                            <input id="name" name="name" type="text" placeholder="Equipment Name" required>
                        </div>
                        <div class="field">
                            <label for="quantity">Quantity <span style="color:#ef4444;margin-left:6px;font-weight:700">*</span></label>
                            <input id="quantity" name="quantity" type="number" min="0" value="1" required>
                        </div>

                        <div class="field">
                            <label for="existing_category">Existing Category</label>
                            <select id="existing_category" name="existing_category">
                                <option value="">Choose existing category...</option>
                                @isset($categories)
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="field">
                            <label for="type">Type</label>
                            <select id="type" name="type"><option value="Non-consumable">Non-consumable</option><option value="Consumable">Consumable</option></select>
                        </div>

                        <div class="field">
                            <label for="location">Location</label>
                            <select id="location" name="location" class="custom-select">
                            <option value="" disabled selected>Select Location</option>
                            <option value="Logistics">Logistics</option>
                            <option value="Medical">Medical</option>
                            <option value="Office">Office</option>
                            <option value="Vehicle">Vehicle</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="date_added">Date Added</label>
                            <input id="date_added" name="date_added" type="date">
                        </div>

                        <div class="field full">
                            <label for="new_category">New Category</label>
                            <input id="new_category" name="new_category" type="text" placeholder="New category (optional)">
                        </div>

                        <div class="field full">
                            <label for="tag">Tag</label>
                            <input id="tag" name="tag" type="text" placeholder="Tag/Identifier">
                        </div>

                        <div class="field full">
                            <label for="image">Image</label>
                            <input id="image" name="image" type="file">
                        </div>

                        <div class="field full">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" placeholder="Notes or details about this equipment" maxlength="120"></textarea>
                            <small class="helper">Max 120 characters</small>
                        </div>

                        <div class="field full" style="display:flex;justify-content:flex-end;gap:12px">
                            <a href="/inventory" class="btn">Back</a>
                            <button type="submit" class="btn primary">Add Equipment</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>

    @if(session('success') || session('status'))
        <div id="success-toast" class="toast" role="status" aria-live="polite">
            <div class="message">{{ session('success') ?? session('status') }}</div>
            <div class="close" id="toast-close">✕</div>
        </div>
    @endif

        (function(){
            const incr = document.getElementById('qty-incr');
            const decr = document.getElementById('qty-decr');
            const qty = document.getElementById('quantity');
            incr && incr.addEventListener('click', ()=>{ qty.value = Math.max(0, parseInt(qty.value||0)+1) });
            decr && decr.addEventListener('click', ()=>{ qty.value = Math.max(0, parseInt(qty.value||0)-1) });

            // simple serial placeholder (timestamp-based)
            const serialEl = document.getElementById('serial-display');
            if(serialEl){
                const s = String(Date.now()).slice(-6);
                serialEl.textContent = s.padStart(6,'0');
            }

            // Category validation: allow submitting when either existing_category is chosen OR new_category is filled.
            const form = document.querySelector('form[action="/inventory/add"]');
            const existing = document.getElementById('existing_category');
            const newcat = document.getElementById('new_category');

            if(form && existing && newcat){
                // Provide immediate feedback when typing a new category
                newcat.addEventListener('input', ()=>{
                    if(newcat.value.trim()){
                        existing.required = false;
                    } else {
                        existing.required = false; // leave to submit-time check
                    }
                });

                form.addEventListener('submit', function(e){
                    const hasNew = newcat.value && newcat.value.trim().length > 0;
                    const hasExisting = existing.value && existing.value.trim().length > 0;
                    if(!hasNew && !hasExisting){
                        e.preventDefault();
                        alert('Please choose an existing category or enter a new category before submitting.');
                        existing.focus();
                        return false;
                    }
                    // if new category provided, ensure existing is ignored server-side; client-side we simply allow submit
                });
            }

            // Show success toast if present
            const toast = document.getElementById('success-toast');
            if(toast){
                // small delay to allow render
                setTimeout(()=> toast.classList.add('show'), 60);
                // auto-hide after 4s
                const hide = ()=> toast.classList.remove('show');
                const t = setTimeout(hide, 4000);
                const closer = document.getElementById('toast-close');
                closer && closer.addEventListener('click', ()=>{ clearTimeout(t); hide(); });
            }
        })();
    </script>

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
</body>
</html>
