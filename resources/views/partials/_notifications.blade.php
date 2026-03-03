<div class="notif-bell" id="notif-bell">
    <button id="notif-toggle" aria-haspopup="true" aria-expanded="false" title="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1h6z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div class="notif-count" id="notif-count" style="display:none">0</div>
    <div class="notif-dropdown" id="notif-dropdown" aria-hidden="true"></div>
</div>

<script>
(function(){
    const bell = document.getElementById('notif-bell');
    const toggle = document.getElementById('notif-toggle');
    const dropdown = document.getElementById('notif-dropdown');
    const countEl = document.getElementById('notif-count');
    if(!bell || !toggle || !dropdown || !countEl) return;
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
            const meta = `<div class="meta"><div class="title">${it.item_name} <span class="time">${formatLocalISO(it.created_at)}</span></div><div class="sub">Requested by ${it.requester}</div></div>`;
            const actions = isAdmin ? `<div class="actions"><button data-id="${it.id}" data-action="approve" class="btn" title="Approve">✓</button><button data-id="${it.id}" data-action="reject" class="btn delete" title="Reject">✕</button></div>` : '';
            return `<div class="item" data-id="${it.id}"><div class="left"><div class="avatar">${avatar}</div></div>${meta}${actions}</div>`;
        }).join('');
    }

    async function fetchNotifs(){
        try{
            const res = await fetch('/notifications/requests', {credentials:'same-origin'});
            if(!res.ok) return;
            const data = await res.json();
            const cnt = data.count || (data.items ? data.items.length : 0);
            if(cnt){ countEl.style.display = ''; countEl.textContent = cnt; } else { countEl.style.display = 'none'; }
            const isAdmin = ({{ auth()->user() ? 'true' : 'false' }} && '{{ auth()->user()->name }}'.toLowerCase() === 'admin');
            renderItems(data.items || [], isAdmin);
        }catch(e){ console.error('fetchNotifs', e); }
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
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                credentials: 'same-origin',
                body: JSON.stringify({ action })
            });
            if(res.ok) await fetchNotifs();
            else alert('Action failed');
        }catch(err){ console.error(err); alert('Action error'); }
        finally{ btn.disabled = false; }
    });

    toggle.addEventListener('click', function(e){
        visible = !visible;
        dropdown.classList.toggle('show', visible);
        toggle.setAttribute('aria-expanded', visible ? 'true' : 'false');
    });

    document.addEventListener('click', function(e){ if(!bell.contains(e.target)){ visible = false; dropdown.classList.remove('show'); } });

    fetchNotifs();
    setInterval(fetchNotifs, 8000);
})();
</script>
