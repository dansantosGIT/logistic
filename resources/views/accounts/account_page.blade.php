@extends('layouts.app')

@section('head')
    <style>
        /* Inventory-like table styles copied for accounts view */
        .table-wrap{background:linear-gradient(180deg,rgba(255,255,255,0.72),rgba(250,250,255,0.56));padding:16px;border-radius:12px;backdrop-filter:blur(6px);box-shadow:0 8px 36px rgba(2,6,23,0.04)}
        table.inventory-table{width:100%;border-collapse:separate;border-spacing:0;background:transparent;table-layout:auto}
        table.inventory-table thead th{background:linear-gradient(90deg,#eef8ff,#f6f0ff);padding:16px;text-align:left;font-size:14px;color:var(--muted);border-bottom:1px solid rgba(14,21,40,0.04)}
        table.inventory-table tbody td{background:linear-gradient(180deg,#ffffff,#fbfdff);padding:16px;vertical-align:middle;border-bottom:1px solid rgba(14,21,40,0.03);transition:transform .18s cubic-bezier(.2,.9,.2,1),box-shadow .18s ease,background .18s ease;font-size:15px}
        table.inventory-table tbody tr:hover td{transform:translateY(-4px);box-shadow:0 14px 34px rgba(2,6,23,0.06)}
        table.inventory-table tbody tr td:first-child{border-top-left-radius:8px;border-bottom-left-radius:8px}
        table.inventory-table tbody tr td:last-child{border-top-right-radius:8px;border-bottom-right-radius:8px}
        table.inventory-table tbody tr:nth-child(odd) td{background:linear-gradient(180deg,#ffffff,#fcfeff)}
        .badge{display:inline-flex;align-items:center;justify-content:center;min-height:28px;padding:6px 12px;border-radius:999px;font-size:13px;color:white}
        .badge.pending{background:#fbbf24;color:#92400e}
        .badge.approved{background:#d1fae5;color:#065f46}
        .badge.denied{background:#fee2e2;color:#991b1b}
        .actions-row{display:flex;gap:8px;justify-content:flex-end}
        .panel.accounts{max-width:1100px;margin:64px 0;padding:28px;border-radius:16px;width:100%;max-width:1100px;box-sizing:border-box;box-shadow:0 18px 50px rgba(2,6,23,0.08)}

        /* Inline action buttons: colored pills for approve/deny */
        .actions-row .btn{background:transparent;border:none;padding:6px 10px;border-radius:10px;margin:0 6px;color:var(--muted-2);cursor:pointer;font-weight:600}
        .actions-row .btn.primary{background:#10b981;color:#fff;border:1px solid rgba(16,185,129,0.12)}
        .actions-row .btn.delete{background:#ef4444;color:#fff;border:1px solid rgba(239,68,68,0.12)}
        .actions-row .btn:hover{transform:translateY(-1px)}

        /* Back button style */
        .back-btn{background:#eef2ff;color:var(--accent);padding:8px 12px;border-radius:999px;text-decoration:none;border:1px solid rgba(124,58,237,0.06);display:inline-flex;align-items:center;gap:8px}
        .back-btn:hover{background:#e6e2ff}

        /* Modal header and controls */
        #account-modal [role="dialog"]{transition:transform .18s ease,opacity .18s ease}
        #account-modal.show [role="dialog"]{transform:translateY(0);opacity:1}
        #account-modal-title{font-size:20px;font-weight:800;margin-bottom:6px}
        #account-modal .subtitle{color:var(--muted);font-size:13px;margin-bottom:10px}

        /* Modal action buttons: green for approve, red for deny */
        #account-modal .btn{padding:8px 14px;border-radius:10px;border:none;font-weight:700}
        #account-modal .btn.primary{background:#10b981;color:#fff} /* green */
        #account-modal .btn.delete{background:#ef4444;color:#fff} /* red */
        #account-modal .btn:focus{outline:3px solid rgba(16,185,129,0.18)}

        /* Close button */
        #account-modal-close{width:36px;height:36px;border-radius:10px;border:none;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
        #account-modal-close:hover{background:#e6eef6}
    </style>
@endsection

@section('content')
<div class="accounts-page-wrapper" style="display:flex;justify-content:center">
    <div class="panel accounts">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div>
                <h2 style="margin:0 0 6px;font-weight:800">Pending Requests</h2>
                <div style="color:var(--muted);font-size:13px">Manage account requests and approvals</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <div style="background:#f1f5f9;padding:6px 10px;border-radius:999px">Pending</div>
                <a href="/dashboard" class="back-btn">Back</a>
            </div>
        </div>

        <div class="table-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="q" placeholder="Search name, email" style="padding:8px;border:1px solid #e6e9ef;border-radius:8px">
                    <select name="status" style="padding:8px;border:1px solid #e6e9ef;border-radius:8px">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div style="font-size:13px;color:var(--muted)">Total: <strong>{{ $requests->total() }}</strong> — Pending: <strong>{{ $requests->where('status','pending')->count() }}</strong></div>
            </div>

            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Requested</th>
                        <th>Name / Email</th>
                        <th>Department</th>
                        <th>Requested Role</th>
                        <th style="text-align:center">Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($requests as $r)
                    <tr>
                        <td data-created-at="{{ $r->created_at->toIso8601String() }}">
                            <div style="font-size:14px"><span class="created-at-date">{{ $r->created_at->setTimezone(config('app.timezone'))->format('F j, Y') }}</span><div style="color:var(--muted);font-size:13px"><span class="created-at-time">{{ $r->created_at->setTimezone(config('app.timezone'))->format('g:i A') }}</span></div></div>
                        </td>
                        <td>
                            <div style="font-weight:700">{{ $r->name }}</div>
                            <div style="color:var(--muted);font-size:13px">{{ $r->email }}</div>
                        </td>
                        <td>{{ $r->department }}</td>
                        <td>{{ $r->requested_role }}</td>
                        <td style="text-align:center">
                            @if($r->status === 'pending')
                                <span class="badge pending">Pending</span>
                            @elseif($r->status === 'approved')
                                <span class="badge approved">Approved</span>
                            @else
                                <span class="badge denied">Denied</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="actions-row">
                                <button type="button" class="btn view-btn" data-id="{{ $r->id }}">View</button>
                                        @if($r->status === 'pending')
                                            <button type="button" class="btn primary row-open" data-id="{{ $r->id }}" data-action="approve">Approve</button>
                                            <button type="button" class="btn delete row-open" data-id="{{ $r->id }}" data-action="deny">Deny</button>
                                        @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $requests->links() }}</div>

        <!-- Centered modal for viewing account request details -->
        <div id="account-modal" class="modal-overlay" style="display:none;position:fixed;inset:0;z-index:300;align-items:center;justify-content:center;">
            <div style="position:absolute;inset:0;background:rgba(5,10,20,0.45);"></div>
            <div role="dialog" aria-modal="true" aria-labelledby="account-modal-title" style="background:white;max-width:720px;width:92%;border-radius:12px;box-shadow:0 18px 46px rgba(2,6,23,0.24);padding:18px;position:relative;z-index:310">
                <button id="account-modal-close" style="position:absolute;right:12px;top:12px;background:transparent;border:none;font-size:18px">✕</button>
                <h3 id="account-modal-title" style="margin:0 0 8px">Account Request</h3>
                <div id="account-modal-body" style="color:var(--muted);font-size:14px">
                    <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px">
                        <div style="font-weight:700" id="am-name">Name</div>
                        <div id="am-email" style="color:var(--muted)"></div>
                    </div>
                    <div id="am-department" style="margin-bottom:6px"></div>
                    <div id="am-role" style="margin-bottom:6px"></div>
                    <div id="am-message" style="margin-top:8px;white-space:pre-wrap;color:#111"></div>
                    <div id="am-meta" style="margin-top:12px;color:var(--muted);font-size:13px"></div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
                    <form id="am-approve-form" method="POST" style="display:inline">@csrf<input type="hidden" name="admin_note" id="am-admin-note-approve"><button id="am-approve-btn" class="btn primary">Approve</button></form>
                    <form id="am-deny-form" method="POST" style="display:inline">@csrf<input type="hidden" name="admin_note" id="am-admin-note-deny"><button id="am-deny-btn" class="btn delete">Deny</button></form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
    .sj-toast{position:fixed;right:18px;bottom:18px;background:white;border-radius:8px;box-shadow:0 8px 30px rgba(2,6,23,.12);padding:12px 14px;display:none;z-index:200}
    .sj-toast.show{display:block}
    .sj-toast .actions{margin-left:10px}
</style>
<div id="sj-toast" class="sj-toast" role="status" aria-live="polite">
    <span id="sj-toast-msg" class="text-sm text-gray-800"></span>
    <button id="sj-toast-undo" class="ml-3 px-3 py-1 text-sm rounded bg-gray-100">Undo</button>
    <button id="sj-toast-close" class="ml-2 px-2 py-1 text-sm text-gray-500">✕</button>
</div>

<script>
    (function(){
        const selectAll = document.getElementById('select-all');
        if(selectAll){
            selectAll.addEventListener('change', function(){
                const checked = !!this.checked;
                document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = checked);
            });
        }

        // Keep native confirm only for non-modal forms (none expected)
        document.querySelectorAll('form').forEach(form => {
            const id = form.id || '';
            if(id === 'am-approve-form' || id === 'am-deny-form') return;
            // only target legacy approve/deny forms by action URL
            const action = (form.getAttribute('action')||'');
            if(action.endsWith('/approve') || action.endsWith('/deny')){
                form.addEventListener('submit', function(e){
                    const isApprove = action.endsWith('/approve');
                    const verb = isApprove ? 'Approve' : 'Deny';
                    const ok = confirm(verb + ' this account request?');
                    if(!ok){ e.preventDefault(); }
                    else { showToast(verb + ' request sent'); }
                });
            }
        });

        // Simple toast
        const toast = document.getElementById('sj-toast');
        const toastMsg = document.getElementById('sj-toast-msg');
        const toastUndo = document.getElementById('sj-toast-undo');
        const toastClose = document.getElementById('sj-toast-close');
        let toastTimer = null;

        function showToast(msg, timeout = 6000){
            if(!toast) return;
            toastMsg.textContent = msg;
            toast.classList.add('show');
            if(toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(hideToast, timeout);
        }
        function hideToast(){
            if(!toast) return;
            toast.classList.remove('show');
            if(toastTimer) { clearTimeout(toastTimer); toastTimer = null; }
        }

        if(toastClose) toastClose.addEventListener('click', hideToast);
        if(toastUndo) toastUndo.addEventListener('click', function(){
            // Undo is optimistic UI only. To implement server-side undo, wire this to an endpoint.
            hideToast();
            alert('Undo requested (not implemented server-side)');
        });
    })();
</script>
    <script>
        (function(){
            function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
            function qsa(sel, ctx){ return Array.from((ctx||document).querySelectorAll(sel)); }

            const modal = qs('#account-modal');
        const btns = qsa('.view-btn');
        const rowOpeners = qsa('.row-open');
            const close = qs('#account-modal-close');
            const body = qs('#account-modal-body');
            const amName = qs('#am-name');
            const amEmail = qs('#am-email');
            const amDept = qs('#am-department');
            const amRole = qs('#am-role');
            const amMessage = qs('#am-message');
            const amMeta = qs('#am-meta');
            const approveForm = qs('#am-approve-form');
            const denyForm = qs('#am-deny-form');

            if(btns.length){
                btns.forEach(b => b.addEventListener('click', function(){
                    const id = this.getAttribute('data-id');
                    if(!id) return;
                    fetch('/accounts/' + id + '/json', {credentials: 'same-origin'})
                        .then(r => r.ok ? r.json() : Promise.reject(r))
                        .then(json => {
                            amName.textContent = json.name || '';
                            amEmail.textContent = json.email ? (' — ' + json.email) : '';
                            amDept.textContent = json.department ? ('Department: ' + json.department) : '';
                            amRole.textContent = json.requested_role ? ('Role: ' + json.requested_role) : '';
                            amMessage.textContent = json.message || json.justification || '';
                            // use server-provided ISO in app timezone when available
                            amMeta.textContent = 'Requested: ' + (json.created_at_display ?? (json.created_at_iso ? new Date(json.created_at_iso).toLocaleString() : ''));

                            // Set form actions
                            if(approveForm) approveForm.setAttribute('action', '/accounts/' + id + '/approve');
                            if(denyForm) denyForm.setAttribute('action', '/accounts/' + id + '/deny');

                            // show modal
                            modal.style.display = 'flex';
                            modal.classList.add('show');
                            // focus first button inside modal
                            const focusable = modal.querySelector('button, [href], input, textarea, select');
                            if(focusable) focusable.focus();
                            // update title subtitle if available
                            const title = qs('#account-modal-title');
                            if(title) title.textContent = 'Account Request — ' + (json.name || '');
                            const subtitle = qs('#account-modal-body .subtitle');
                            if(!subtitle){
                                const s = document.createElement('div'); s.className = 'subtitle'; s.textContent = json.email || ''; title.insertAdjacentElement('afterend', s);
                            }
                        })
                        .catch(() => alert('Failed to load details'));
                }));
            }

                // open modal when clicking Approve/Deny in row (delegates to same modal)
                if(rowOpeners.length){
                    rowOpeners.forEach(b => b.addEventListener('click', function(){
                        const id = this.getAttribute('data-id');
                        if(!id) return;
                        const action = this.getAttribute('data-action');
                        fetch('/accounts/' + id + '/json', {credentials: 'same-origin'})
                            .then(r => r.ok ? r.json() : Promise.reject(r))
                            .then(json => {
                                amName.textContent = json.name || '';
                                amEmail.textContent = json.email ? (' — ' + json.email) : '';
                                amDept.textContent = json.department ? ('Department: ' + json.department) : '';
                                amRole.textContent = json.requested_role ? ('Role: ' + json.requested_role) : '';
                                amMessage.textContent = json.message || json.justification || '';
                                amMeta.textContent = 'Requested: ' + (json.created_at_display ?? (json.created_at_iso ? new Date(json.created_at_iso).toLocaleString() : ''));
                                if(approveForm) approveForm.setAttribute('action', '/accounts/' + id + '/approve');
                                if(denyForm) denyForm.setAttribute('action', '/accounts/' + id + '/deny');
                                modal.style.display = 'flex'; modal.classList.add('show');
                                // focus the appropriate action button
                                if(action === 'approve') qs('#am-approve-btn')?.focus();
                                if(action === 'deny') qs('#am-deny-btn')?.focus();
                            })
                            .catch(() => alert('Failed to load details'));
                    }));
                }

            function hideModal(){ modal.style.display = 'none'; }
            if(close) close.addEventListener('click', hideModal);
            window.addEventListener('keyup', function(e){ if(e.key === 'Escape') hideModal(); });
            // click outside to close
            modal.addEventListener('click', function(e){ if(e.target === modal) hideModal(); });
        })();
    </script>
    <script>
        // Convert server ISO timestamps to viewer's local timezone for display
        (function(){
            document.addEventListener('DOMContentLoaded', function(){
                document.querySelectorAll('[data-created-at]').forEach(td => {
                    try{
                        const iso = td.getAttribute('data-created-at');
                        if(!iso) return;
                        const dt = new Date(iso);
                        if(isNaN(dt.getTime())) return;
                        const dateStr = dt.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
                        const timeStr = dt.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
                        const dateEl = td.querySelector('.created-at-date');
                        const timeEl = td.querySelector('.created-at-time');
                        if(dateEl) dateEl.textContent = dateStr;
                        if(timeEl) timeEl.textContent = timeStr;
                    }catch(e){/* ignore */}
                });
            });
        })();
    </script>
    <script>
        (function(){
            // Approve/Deny confirmation with optional admin note
            const approveBtn = document.getElementById('am-approve-btn');
            const denyBtn = document.getElementById('am-deny-btn');
            const approveForm = document.getElementById('am-approve-form');
            const denyForm = document.getElementById('am-deny-form');

            function showConfirm(action, callback){
                // Inline simple prompt modal inside account modal
                let prompt = document.getElementById('am-confirm-prompt');
                if(!prompt){
                    prompt = document.createElement('div');
                    prompt.id = 'am-confirm-prompt';
                    prompt.style.marginTop = '12px';
                    prompt.innerHTML = `
                        <label style="display:block;font-weight:600;margin-bottom:6px">Admin note (optional)</label>
                        <textarea id="am-confirm-note" rows="3" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px"></textarea>
                        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px">
                            <button id="am-cancel-btn" class="btn">Cancel</button>
                            <button id="am-confirm-btn" class="btn">Confirm ${action}</button>
                        </div>
                    `;
                    const body = document.getElementById('account-modal-body');
                    body.appendChild(prompt);
                }

                const cancel = document.getElementById('am-cancel-btn');
                const confirm = document.getElementById('am-confirm-btn');
                const note = document.getElementById('am-confirm-note');

                // hide original action buttons while confirming
                if(approveBtn) approveBtn.style.display = 'none';
                if(denyBtn) denyBtn.style.display = 'none';

                // style confirm button according to action
                if(confirm){
                    confirm.classList.remove('primary', 'delete');
                    if(String(action).toLowerCase() === 'deny' || String(action).toLowerCase() === 'confirm deny'){
                        confirm.classList.add('delete');
                    } else {
                        confirm.classList.add('primary');
                    }
                    confirm.textContent = 'Confirm ' + action;
                }

                // attach single-use handlers
                if(cancel){
                    cancel.onclick = function(){
                        prompt.remove();
                        if(approveBtn) approveBtn.style.display = '';
                        if(denyBtn) denyBtn.style.display = '';
                    };
                }

                if(confirm){
                    confirm.onclick = function(){
                        const val = note.value || '';
                        callback(val);
                        prompt.remove();
                        if(approveBtn) approveBtn.style.display = '';
                        if(denyBtn) denyBtn.style.display = '';
                    };
                }
            }

            if(approveBtn && approveForm){
                approveBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    showConfirm('Approve', function(note){
                        const hidden = document.getElementById('am-admin-note-approve');
                        if(hidden) hidden.value = note;
                        approveForm.submit();
                    });
                });
            }

            if(denyBtn && denyForm){
                denyBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    showConfirm('Deny', function(note){
                        const hidden = document.getElementById('am-admin-note-deny');
                        if(hidden) hidden.value = note;
                        denyForm.submit();
                    });
                });
            }
        })();
    </script>
@endpush
