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
        function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
        dropdown.innerHTML = items.map(it=>{
            const avatar = (it.item_name||'R').trim().charAt(0).toUpperCase();
            const rawTitle = it.item_name || '';
            const displayTitle = rawTitle.length > 48 ? rawTitle.slice(0,45) + '…' : rawTitle;
            const subtitle = it.subtitle || `Requested by ${it.requester || 'System'}`;
            const targetUrl = it.url || (it.id ? `/requests/${encodeURIComponent(it.id)}` : '');
            const meta = `<div class="meta"><div class="title" title="${escapeHtml(rawTitle)}">${escapeHtml(displayTitle)} <span class="time">${formatLocalISO(it.created_at)}</span></div><div class="sub">${escapeHtml(subtitle)}</div></div>`;
            const overduePill = (it.status === 'overdue') ? '<span style="display:inline-block;margin-left:8px;padding:4px 8px;border-radius:999px;background:#ef4444;color:#fff;font-size:11px;font-weight:700">Overdue</span>' : '';
            return `<div class="item" data-id="${it.id || ''}" data-url="${targetUrl}"><div class="left"><div class="avatar">${avatar}</div></div>${meta}${overduePill}</div>`;
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
