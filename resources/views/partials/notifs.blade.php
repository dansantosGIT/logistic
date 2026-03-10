<script>
(function(){
    const bell = document.getElementById('notif-bell');
    const toggle = document.getElementById('notif-toggle');
    const dropdown = document.getElementById('notif-dropdown');
    const countEl = document.getElementById('notif-count');
    if(!bell || !toggle || !dropdown) return;
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
            const cnt = data.count || 0;
            if(cnt){
                countEl.style.display = '';
                countEl.textContent = cnt;
            } else {
                countEl.style.display = 'none';
            }
            const isAdmin = ({{ auth()->user() ? 'true' : 'false' }} && '{{ auth()->user() ? strtolower(auth()->user()->role) : '' }}' === 'admin');
            renderItems(data.items || [], !!isAdmin);
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
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({ action })
            });
            if(res.ok){ await fetchNotifs(); }
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
