<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Request Multiple — San Juan CDRMMD</title>
    <link rel="icon" href="/images/favi.png" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}
        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.96);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.06);z-index:60}
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
        .panel{background:var(--panel);padding:22px;border-radius:12px;box-shadow:0 10px 30px rgba(15,23,42,0.06);max-width:980px;margin:20px auto}
        .row{display:flex;gap:12px;align-items:center}
        .card{background:#f9fafb;padding:12px;border-radius:8px;border:1px solid #eef2f6;margin-bottom:12px}
        label{display:block;font-size:13px;color:#334155;margin-bottom:6px}
        input,select,textarea{width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px}
        .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
        .btn{padding:8px 12px;border-radius:8px;background:#2563eb;color:#fff;border:none;cursor:pointer}
        .btn.ghost{background:#fff;color:#0f172a;border:1px solid #e6e9ef}
        .small{padding:6px 8px;border-radius:6px}
        .notif-dropdown .item .actions .btn{padding:6px 8px}
        @media(max-width:900px){.sidebar{position:fixed;left:0;top:0;bottom:0;z-index:90;height:100vh}.main{padding:16px}}
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
                        <span>Request Equipment</span>
                    </a>
                    <div style="font-size:12px;color:var(--muted)">Request / New (Multiple)</div>
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
            <div class="panel" style="max-width:980px;padding:18px">
                <h2 style="margin:0 0 8px 0">Request Multiple Items</h2>
                <p style="margin:0 0 12px 0;color:var(--muted)">Submit multiple item requests under one requester.</p>

                @if($errors->any())
                    <div style="background:#fee2e2;color:#b91c1c;padding:10px;border-radius:8px;margin-bottom:12px">
                        <strong>There were errors with your submission</strong>
                        <ul style="margin:8px 0 0 16px">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $locations = $equipment->pluck('location')->filter()->unique()->values();
                @endphp

                <form id="requests-multiple-form" method="POST" action="/requests/multiple">
                    @csrf

                    <div class="card">
                        <h3 style="margin:0 0 8px 0">Requester</h3>
                        <div class="row" style="align-items:flex-start">
                            <div style="flex:1">
                                <label for="requester">Name</label>
                                <input id="requester" name="requester" value="{{ old('requester', auth()->user()->name ?? '') }}">
                            </div>
                            <div style="width:220px">
                                <label for="role">Role</label>
                                <select id="role" name="role">
                                    <option>Employee</option>
                                    <option>Volunteer</option>
                                    <option>Intern</option>
                                    <option>Operations</option>
                                    <option>Others</option>
                                </select>
                                <div id="role-other-field" style="margin-top:8px;display:none">
                                    <input id="role_other" name="role_other" placeholder="Specify role">
                                </div>
                            </div>
                            <div id="department-field" style="width:240px;display:none">
                                <label for="department">Department</label>
                                <select id="department" name="department"></select>
                            </div>
                        </div>
                    </div>

                    <div id="itemsContainer">
                        @for($i=0;$i<2;$i++)
                        <div class="card item-card" data-index="{{ $i }}">
                            <div style="display:flex;gap:12px;align-items:center">
                                <div style="flex:1">
                                    <label>Location</label>
                                    <select name="items[{{ $i }}][location]" class="location-select" style="margin-bottom:8px">
                                        <option value="">-- All locations --</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}">{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                    <label>Equipment</label>
                                    <div style="position:relative">
                                        <select name="items[{{ $i }}][equipment_id]" class="equipment-select">
                                            <option value="">-- Select equipment --</option>
                                            @foreach($equipment as $eq)
                                                <option value="{{ $eq->id }}" data-qty="{{ $eq->quantity }}" data-type="{{ strtolower($eq->type ?? '') }}" data-location="{{ $eq->location ?? '' }}">{{ $eq->name }} ({{ $eq->quantity }} available)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="item-location" style="font-size:12px;color:var(--muted);margin-top:6px;display:none">Location: <span class="loc-text"></span></div>
                                </div>
                                <div style="width:120px">
                                    <label>Quantity</label>
                                    <input type="number" name="items[{{ $i }}][quantity]" min="1" value="1" class="qty-input">
                                </div>
                                <div style="width:200px">
                                    <label>Notes (optional)</label>
                                    <input name="items[{{ $i }}][notes]" placeholder="Notes">
                                </div>
                                <div style="width:200px">
                                    <label>Return date (for non-consumable)</label>
                                    <input type="date" name="items[{{ $i }}][return_date]" class="return-date" style="display:none">
                                </div>
                                <div style="width:40px">
                                    <button type="button" class="btn ghost remove-item" title="Remove" style="margin-top:22px">✕</button>
                                </div>
                            </div>
                            <div class="item-error" style="color:#b91c1c;margin-top:6px;display:none"></div>
                        </div>
                        @endfor
                    </div>

                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
                        <button type="button" id="addItem" class="btn ghost small">+ Add item</button>
                        <div style="color:var(--muted);font-size:13px">You can add more items as needed.</div>
                    </div>

                    <div class="actions">
                        <a href="/inventory" class="btn ghost" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center">Cancel</a>
                        <button type="submit" class="btn">Submit requests</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <form id="logout-form" method="POST" action="/logout" style="display:none">@csrf</form>

                @if(session('success'))
                    <div class="toast" id="toast-success">{{ session('success') }}</div>
                @else
                    <div class="toast" id="toast-success" style="display:none"></div>
                @endif

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
                    const meta = `<div class="meta"><div class="title">${it.item_name} <span class="time">${it.created_at}</span></div><div class="sub">Requested by ${it.requester}</div></div>`;
                    const actions = isAdmin ? `<div class="actions"><button data-id="${it.id}" data-action="approve" class="btn" title="Approve">✓</button><button data-id="${it.id}" data-action="reject" class="btn delete" title="Reject">✕</button></div>` : '';
                    return `<div class="item" data-id="${it.id}"><div class="left"><div class="avatar">${avatar}</div></div>${meta}${actions}</div>`;
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

            dropdown.addEventListener('click', async function(e){
                const btn = e.target.closest('button[data-id]');
                if(!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                try{
                    btn.disabled = true;
                    const res = await fetch('/notifications/requests/'+encodeURIComponent(id)+'/action', {
                        method: 'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf()},
                        body: JSON.stringify({ action })
                    });
                    if(res.ok){
                        if(action === 'approve') showToast('Request approved', 'success');
                        else showToast('Request rejected', 'error');
                        await fetchNotifs();
                        setTimeout(()=>location.reload(), 700);
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
        // Dynamic department / other-role behavior (copied from inventory_request)
        (function(){
            const role = document.getElementById('role');
            const deptField = document.getElementById('department-field');
            const deptInput = document.getElementById('department');
            const roleOtherField = document.getElementById('role-other-field');
            const roleOtherInput = document.getElementById('role_other');
            if(!role) return;

            const opsOptions = ['<option value="">Select department</option>','<option value="Alpha">Alpha</option>','<option value="Bravo">Bravo</option>','<option value="Charlie">Charlie</option>'].join('');
            const staffOptions = ['<option value="">Select department</option>','<option value="Admin & Training">Admin & Training</option>','<option value="Planning & Research">Planning & Research</option>','<option value="CEDOC">CEDOC</option>'].join('');

            function showDept(optsHtml){
                if(!deptInput) return;
                deptInput.innerHTML = optsHtml;
                deptField.style.display = '';
                deptInput.setAttribute('required','required');
                if(roleOtherField){ roleOtherField.style.display = 'none'; roleOtherInput && (roleOtherInput.removeAttribute('required'), roleOtherInput.value = ''); }
            }

            function hideDept(){
                if(!deptField) return;
                deptField.style.display = 'none';
                deptInput && deptInput.removeAttribute('required');
                if(deptInput) deptInput.value = '';
            }

            function showOther(){
                if(roleOtherField){ roleOtherField.style.display = ''; roleOtherInput && roleOtherInput.setAttribute('required','required'); }
                hideDept();
            }

            function toggle(){
                const val = (role.value || '').trim();
                if(val === 'Operations'){
                    showDept(opsOptions);
                } else if (['Employee','Volunteer','Intern'].includes(val)){
                    showDept(staffOptions);
                } else if (val === 'Others'){
                    showOther();
                } else {
                    hideDept();
                    if(roleOtherField){ roleOtherField.style.display = 'none'; roleOtherInput && (roleOtherInput.removeAttribute('required'), roleOtherInput.value = ''); }
                }
            }

            role.addEventListener('change', toggle);
            // init on load in case of server-rendered value
            toggle();
        })();
    </script>

    <script>
        // Request Multiple: add/remove rows
        (function(){
            const container = document.getElementById('itemsContainer');
            const addBtn = document.getElementById('addItem');

            function makeIndex(){
                return container.querySelectorAll('.item-card').length;
            }

            function bindRemove(btn){
                btn.addEventListener('click', function(){
                    const card = btn.closest('.item-card');
                    if(!card) return;
                    card.remove();
                });
            }

            Array.from(document.querySelectorAll('.remove-item')).forEach(bindRemove);

                addBtn.addEventListener('click', function(){
                const idx = makeIndex();
                const tpl = document.createElement('div');
                tpl.className = 'card item-card';
                tpl.dataset.index = idx;
                tpl.innerHTML = `
                            <div style="display:flex;gap:12px;align-items:center">
                                <div style="flex:1">
                                    <label>Location</label>
                                    <select name="items[${idx}][location]" class="location-select" style="margin-bottom:8px">
                                        <option value="">-- All locations --</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc }}">{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                    <label>Equipment</label>
                                    <div style="position:relative">
                                        <select name="items[${idx}][equipment_id]" class="equipment-select">
                                            <option value="">-- Select equipment --</option>
                                            @foreach($equipment as $eq)
                                                <option value="{{ $eq->id }}" data-qty="{{ $eq->quantity }}" data-type="{{ strtolower($eq->type ?? '') }}" data-location="{{ $eq->location ?? '' }}">{{ addslashes($eq->name) }} ({{ $eq->quantity }} available)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="item-location" style="font-size:12px;color:var(--muted);margin-top:6px;display:none">Location: <span class="loc-text"></span></div>
                                </div>
                        <div style="width:120px">
                            <label>Quantity</label>
                            <input type="number" name="items[${idx}][quantity]" min="1" value="1" class="qty-input">
                        </div>
                        <div style="width:200px">
                            <label>Notes (optional)</label>
                            <input name="items[${idx}][notes]" placeholder="Notes">
                        </div>
                        <div style="width:200px">
                            <label>Return date (for non-consumable)</label>
                            <input type="date" name="items[${idx}][return_date]" class="return-date" style="display:none">
                        </div>
                        <div style="width:40px">
                            <button type="button" class="btn ghost remove-item" title="Remove" style="margin-top:22px">✕</button>
                        </div>
                    </div>`;
                // add inline error container for dynamic card
                const err = document.createElement('div');
                err.className = 'item-error';
                err.style.cssText = 'color:#b91c1c;margin-top:6px;display:none';
                tpl.appendChild(err);
                container.appendChild(tpl);
                bindRemove(tpl.querySelector('.remove-item'));
            });
        })();
    </script>

    <script>
        // per-item client-side stock validation
        (function(){
            const form = document.getElementById('requests-multiple-form');
            const container = document.getElementById('itemsContainer');
            if(!form || !container) return;

            function clearError(card){
                const el = card.querySelector('.item-error'); if(!el) return; el.style.display = 'none'; el.textContent = '';
                card.querySelectorAll('select, input').forEach(i=>i.classList.remove('input-error'));
            }

            function setError(card, msg){
                const el = card.querySelector('.item-error'); if(!el) return; el.textContent = msg; el.style.display = '';
            }

            container.addEventListener('change', function(e){
                if(e.target && e.target.matches('.equipment-select')){
                    const card = e.target.closest('.item-card');
                    const opt = e.target.selectedOptions && e.target.selectedOptions[0];
                    const avail = parseInt(opt ? (opt.dataset.qty||'0') : '0', 10) || 0;
                    const type = (opt && (opt.dataset.type || '')).toString().toLowerCase();
                    const loc = (opt && (opt.dataset.location || '')).toString();
                    const qty = card.querySelector('.qty-input');
                    const returnDate = card.querySelector('.return-date');
                    const locEl = card.querySelector('.item-location');

                    // set quantity max
                    if(qty) {
                        if(type !== 'consumable'){
                            // non-consumables usually single items; enforce max 1
                            qty.setAttribute('max', '1');
                            if(parseInt(qty.value||'0',10) > 1) qty.value = '1';
                        } else {
                            qty.setAttribute('max', String(Math.max(0, avail)));
                        }
                    }

                    // show/hide return date for non-consumables
                    if(returnDate){
                        if(type !== 'consumable'){
                            returnDate.style.display = '';
                            returnDate.setAttribute('required', 'required');
                        } else {
                            returnDate.style.display = 'none';
                            returnDate.removeAttribute('required');
                            returnDate.value = '';
                        }
                    }

                    // show location
                    if(locEl){
                        const txt = locEl.querySelector('.loc-text');
                        if(loc && txt){ txt.textContent = loc; locEl.style.display = ''; }
                        else { if(txt) txt.textContent = ''; locEl.style.display = 'none'; }
                    }

                    clearError(card);
                }
            });

            container.addEventListener('input', function(e){
                if(e.target && e.target.matches('.qty-input')){
                    const card = e.target.closest('.item-card');
                    clearError(card);
                }
            });

            form.addEventListener('submit', function(ev){
                let firstErr = null;
                const cards = Array.from(container.querySelectorAll('.item-card'));
                if(cards.length === 0){ alert('Please add at least one item to request.'); ev.preventDefault(); return; }
                for(const card of cards){
                    clearError(card);
                    const sel = card.querySelector('.equipment-select');
                    const qtyEl = card.querySelector('.qty-input');
                    const opt = sel && sel.selectedOptions && sel.selectedOptions[0];
                    const avail = parseInt(opt ? (opt.dataset.qty||'0') : '0', 10) || 0;
                    const type = (opt && (opt.dataset.type || '')).toString().toLowerCase();
                    const qty = qtyEl ? (parseInt(qtyEl.value||'0',10) || 0) : 0;

                    if(!sel || !sel.value){
                        setError(card, 'Please select equipment for this row.');
                        if(!firstErr) firstErr = card;
                        continue;
                    }
                    if(avail <= 0){
                        setError(card, 'Item is out of stock.');
                        if(!firstErr) firstErr = card;
                        continue;
                    }
                    if(qty < 1){
                        setError(card, 'Quantity must be at least 1.');
                        if(!firstErr) firstErr = card;
                        continue;
                    }
                    if(qty > avail){
                        setError(card, 'Requested quantity exceeds available ('+avail+').');
                        if(!firstErr) firstErr = card;
                        continue;
                    }
                    // require return_date for non-consumables
                    if(type !== 'consumable'){
                        const rd = card.querySelector('.return-date');
                        const rv = rd ? (rd.value || '').toString().trim() : '';
                        if(!rv){
                            setError(card, 'Return date required for non-consumable items.');
                            if(!firstErr) firstErr = card;
                            continue;
                        }
                    }
                }
                if(firstErr){
                    ev.preventDefault();
                    firstErr.scrollIntoView({behavior:'smooth', block:'center'});
                }
            });
        })();
    </script>
    <script>
        // initialize existing rows to show proper return-date/location/qty limits
        (function(){
            Array.from(document.querySelectorAll('.equipment-select')).forEach(function(s){
                try{ s.dispatchEvent(new Event('change')); }catch(e){}
            });
        })();
    </script>
    <script>
        // Lightweight equipment search: preload metadata and provide suggestion dropdown
        (function(){
            // Build equipment metadata from the hidden <select> options in the DOM
            // (avoid embedding complex PHP closures into the compiled view)
            const EQUIPMENT = (function(){
                const seen = new Set();
                const out = [];
                document.querySelectorAll('.equipment-select option').forEach(function(opt){
                    const id = opt.value;
                    if(!id || seen.has(id)) return;
                    seen.add(id);
                    const name = (opt.textContent || '').replace(/\s*\(\d+\s+available\)\s*$/i, '').trim();
                    out.push({
                        id: id,
                        name: name,
                        quantity: parseInt(opt.dataset.qty || '0', 10) || 0,
                        type: (opt.dataset.type || '').toLowerCase(),
                        location: opt.dataset.location || ''
                    });
                });
                return out;
            })();
            function createRowHandlers(root){
                const input = root.querySelector('.search-equipment');
                const select = root.querySelector('.equipment-select');
                const suggBox = root.querySelector('.suggestions');
                const locEl = root.querySelector('.item-location');
                const locationSelect = root.querySelector('.location-select');
                if(!select) return;

                // If legacy search inputs exist, remove them (we prefer visible selects)
                if(input && input.parentNode){ try{ input.parentNode.removeChild(input); }catch(e){} }
                if(suggBox && suggBox.parentNode){ try{ suggBox.parentNode.removeChild(suggBox); }catch(e){} }

                // When equipment selection changes, update location display and trigger change handlers
                select.addEventListener('change', function(){
                    const opt = select.selectedOptions && select.selectedOptions[0];
                    const pickedLoc = opt ? (opt.dataset.location || '') : '';
                    if(locEl){ const txt = locEl.querySelector('.loc-text'); if(txt) txt.textContent = pickedLoc || ''; locEl.style.display = pickedLoc ? '' : 'none'; }
                    // ensure return_date/qty rules applied by existing container change handler
                    try{ select.dispatchEvent(new Event('change')); }catch(e){}
                });

                // filter equipment options when location changes
                if(locationSelect){
                    const filterEquipmentOptions = function(){
                        const val = (locationSelect.value || '').toString();
                        Array.from(select.options).forEach(function(opt){
                            if(!opt.value) { opt.hidden = false; return; }
                            const oLoc = (opt.dataset.location || '').toString();
                            opt.hidden = val ? (oLoc !== val) : false;
                        });
                        // if current selection is hidden, clear it
                        try{
                            const cur = select.selectedOptions && select.selectedOptions[0];
                            if(cur && cur.hidden){ select.value = ''; select.dispatchEvent(new Event('change')); }
                        }catch(e){}
                    };
                    locationSelect.addEventListener('change', filterEquipmentOptions);
                    // initialize immediately
                    filterEquipmentOptions();
                }
            }

            function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

            // bind handlers for existing rows
            Array.from(document.querySelectorAll('.item-card')).forEach(function(card){ createRowHandlers(card); });

            // when new rows are added, bind handlers (mutation observer)
            const container = document.getElementById('itemsContainer');
            const mo = new MutationObserver(function(m){
                for(const rec of m){
                    for(const n of rec.addedNodes){ if(n.nodeType===1 && n.classList.contains('item-card')) createRowHandlers(n); }
                }
            });
            if(container) mo.observe(container, {childList:true});
        })();
    </script>
    <script>
        (function(){
            const msg = {!! json_encode(session('success')) !!};
            const toast = document.getElementById('toast-success');
            if(msg && toast){
                toast.textContent = msg;
                toast.style.display = '';
                setTimeout(function(){ toast.classList.add('show'); }, 50);
                setTimeout(function(){ toast.classList.remove('show'); }, 4200);
            }
        })();
    </script>
</body>
</html>
