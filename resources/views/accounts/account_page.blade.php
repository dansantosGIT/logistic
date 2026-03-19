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
        /* Action button colors */
        .actions-row .btn.view-btn{background:linear-gradient(90deg,#3b82f6,#2563eb);color:#fff;border:1px solid rgba(37,99,235,0.12)}
        .actions-row .btn.edit{background:linear-gradient(90deg,#7c3aed,#a78bfa);color:#fff;border:1px solid rgba(124,58,237,0.08)}
        .actions-row .btn.delete-account{background:linear-gradient(90deg,#ef4444,#dc2626);color:#fff;border:1px solid rgba(239,68,68,0.12)}
        .actions-row .btn.toggle.inactive{background:linear-gradient(90deg,#10b981,#059669);color:#fff;border:1px solid rgba(6,150,136,0.12)}
        .actions-row .btn.toggle.active{background:linear-gradient(90deg,#f59e0b,#f97316);color:#fff;border:1px solid rgba(245,158,11,0.08)}

        /* Back button style */
        .back-btn{background:#eef2ff;color:var(--accent);padding:8px 12px;border-radius:999px;text-decoration:none;border:1px solid rgba(124,58,237,0.06);display:inline-flex;align-items:center;gap:8px}
        .back-btn:hover{background:#e6e2ff}

        /* Modal header and controls */
        #account-modal [role="dialog"]{transition:transform .18s ease,opacity .18s ease}
        #account-modal.show [role="dialog"]{transform:translateY(0);opacity:1}
        #account-modal-title{font-size:22px;font-weight:900;margin-bottom:6px;color:#0f172a}
        #account-modal .subtitle{color:var(--muted);font-size:13px;margin-bottom:12px}

        /* Emphasize account details */
        #account-modal .header-info{display:block;margin-bottom:10px}
        #am-name{font-size:16px;font-weight:800;color:#111;margin-bottom:4px}
        #am-email{color:var(--muted);font-size:14px;margin-left:6px;font-weight:600}
        #am-department, #am-role{font-size:14px;color:#374151;margin-bottom:6px;font-weight:600}
        #am-ops-role{font-size:14px;color:#374151;margin-bottom:6px;font-weight:600}
        #am-meta{font-size:13px;color:#6b7280;margin-top:8px}
        #account-modal .subtitle small{display:block;color:var(--muted)}
        /* Edit action (small) placed beside title */
        #account-modal .edit-small{padding:6px 10px;border-radius:8px;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:8px}
        #account-modal .edit-small.edit{background:linear-gradient(90deg,#7c3aed,#a78bfa);color:#fff;border:1px solid rgba(124,58,237,0.08)}

        /* Modal form styles */
        #account-modal .account-form label{display:block;font-weight:700;margin-bottom:6px;color:#374151}
        #account-modal .account-form input[type="text"],
        #account-modal .account-form input[type="email"],
        #account-modal .account-form textarea,
        #account-modal .account-form select{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;background:#fff;color:#0f172a}
        #account-modal .account-form textarea{min-height:90px}
        #account-modal .account-form .field-row{margin-bottom:10px}

        /* Modal action buttons: green for approve, red for deny */
        #account-modal .btn{padding:8px 14px;border-radius:10px;border:none;font-weight:700}
        #account-modal .btn.primary{background:#10b981;color:#fff} /* green */
        #account-modal .btn.delete{background:#ef4444;color:#fff} /* red */
        #account-modal .btn:focus{outline:3px solid rgba(16,185,129,0.18)}

        /* Close button */
        #account-modal-close{width:36px;height:36px;border-radius:10px;border:none;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
        #account-modal-close:hover{background:#e6eef6}

        /* Tabs: mirror inventory look */
        .tabs{display:flex;gap:8px;margin-bottom:12px}
        .tabs .tab{background:#f1f5f9;padding:10px 14px;border-radius:12px;font-size:14px;color:#0f172a;transition:background .12s ease,transform .12s ease,box-shadow .12s ease;cursor:pointer}
        .tabs .tab:not(.active):hover{transform:translateY(-1px);background:#fff}
        .tabs .tab.active{background:linear-gradient(90deg,#2563eb,#7c3aed);color:#fff;font-weight:700;box-shadow:0 8px 24px rgba(37,99,235,0.12);transform:translateY(-2px);position:relative}
        .tabs .tab.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:4px;border-radius:4px;background:linear-gradient(180deg,#2563eb,#7c3aed)}

        /* Header badge styling */
        .header-badge{padding:6px 12px;border-radius:999px;font-weight:700;color:#0f172a;background:#f1f5f9}
        .header-badge.blue{background:linear-gradient(90deg,#2563eb,#7c3aed);color:#fff;box-shadow:0 8px 24px rgba(37,99,235,0.08)}
        .header-badge.pending{background:#f1f5f9;color:#0f172a}
    </style>
@endsection

@section('content')
<div class="accounts-page-wrapper" style="display:flex;justify-content:center">
    <div class="panel accounts">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div>
                <h2 id="accounts-page-title" style="margin:0 0 6px;font-weight:800">{{ (isset($activeTab) && $activeTab === 'accounts') ? 'Accounts' : 'Pending Requests' }}</h2>
                <div id="accounts-page-subtitle" style="color:var(--muted);font-size:13px">{{ (isset($activeTab) && $activeTab === 'accounts') ? 'Manage active user accounts' : 'Manage account requests and approvals' }}</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                @php $tab = $activeTab ?? request()->query('tab','pending'); @endphp
                <div id="header-badge" class="header-badge {{ $tab === 'accounts' ? 'blue' : 'pending' }}">{{ $tab === 'accounts' ? 'Accounts' : 'Pending' }}</div>
                <a href="/dashboard" class="back-btn">Back</a>
            </div>
        </div>

        <div class="tabs" style="margin-bottom:12px">
            <div class="tab {{ (isset($activeTab) && $activeTab === 'accounts') ? 'active' : '' }}" data-tab="accounts">Accounts</div>
            <div class="tab {{ (isset($activeTab) && $activeTab === 'pending') ? 'active' : '' }}" data-tab="pending">Pending Accounts</div>
        </div>

        <!-- Accounts list (hidden by default unless $users provided) -->
        <div class="table-wrap accounts-wrap" style="{{ (isset($activeTab) && $activeTab === 'accounts') ? '' : 'display:none' }}">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" id="accounts-search" name="q" placeholder="Search name, email" style="padding:8px;border:1px solid #e6e9ef;border-radius:8px">
                    <select id="accounts-status" name="status" style="padding:8px;border:1px solid #e6e9ef;border-radius:8px">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div style="font-size:13px;color:var(--muted)">Total: <strong>{{ isset($users) ? $users->total() : 0 }}</strong></div>
            </div>

            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Joined</th>
                        <th>Name / Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Ops Role</th>
                        <th style="text-align:center">Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @if(isset($users) && $users->count())
                    @foreach($users as $u)
                    <tr>
                        <td data-created-at="{{ $u->created_at->toIso8601String() }}">
                            <div style="font-size:14px"><span class="created-at-date">{{ $u->created_at->setTimezone(config('app.timezone'))->format('F j, Y') }}</span><div style="color:var(--muted);font-size:13px"><span class="created-at-time">{{ $u->created_at->setTimezone(config('app.timezone'))->format('g:i A') }}</span></div></div>
                        </td>
                        <td>
                            <div style="font-weight:700">{{ $u->name }}</div>
                            <div style="color:var(--muted);font-size:13px">{{ $u->email }}</div>
                        </td>
                        <td>{{ $u->department }}</td>
                        <td>{{ $u->role ?? $u->requested_role ?? '' }}</td>
                        <td>{{ $u->ops_role ?? '' }}</td>
                        <td style="text-align:center">
                            @if(isset($u->active) && $u->active)
                                <span class="badge approved">Active</span>
                            @else
                                <span class="badge denied">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="actions-row">
                                <button type="button" data-id="{{ $u->id }}" class="btn view-btn">View</button>
                                <a href="/accounts/user/{{ $u->id }}/edit" class="btn edit">Edit</a>
                                @php
                                    $mainAdminEmail = strtolower(env('MAIL_ADMIN', 'sjcdrrmdlogistics@gmail.com'));
                                @endphp
                                @if(!($u->is_admin ?? false))
                                    <form method="POST" action="/accounts/user/{{ $u->id }}" class="delete-account-form" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn delete-account">Delete</button>
                                    </form>
                                @endif
                                <form method="POST" action="/accounts/{{ $u->id }}/toggle" style="display:inline">@csrf
                                    <input type="hidden" name="tab" value="accounts">
                                    <button class="btn toggle {{ (isset($u->active) && $u->active) ? 'active' : 'inactive' }}">{{ (isset($u->active) && $u->active) ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--muted)">No accounts found</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <div class="table-wrap pending-wrap" style="{{ (isset($activeTab) && $activeTab === 'accounts') ? 'display:none' : '' }}">
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
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                    <div style="min-width:0;flex:1">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                            <h3 id="account-modal-title" style="margin:0;font-weight:800;font-size:20px">Account Request</h3>
                        </div>
                        <div id="account-modal-subtitle" class="subtitle" style="color:var(--muted);font-size:13px;margin-top:6px">Details and actions for this account</div>
                    </div>
                </div>
                <div id="account-modal-body" style="color:var(--muted);font-size:14px">
                    <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px">
                        <div style="font-weight:700" id="am-name">Name</div>
                        <div id="am-email" style="color:var(--muted)"></div>
                    </div>
                    <div id="am-department" style="margin-bottom:6px"></div>
                    <div id="am-role" style="margin-bottom:6px"></div>
                    <div id="am-ops-role" style="margin-bottom:6px;display:none"></div>
                    <div id="am-message" style="margin-top:8px;white-space:pre-wrap;color:#111"></div>
                    <div id="am-user-controls" style="margin-top:10px"></div>
                    <div id="am-admin-note" style="margin-top:8px;white-space:pre-wrap;color:#374151;font-style:italic;display:none"></div>
                    <div id="am-admin-note-controls" style="margin-top:6px;display:none"></div>
                    <div id="am-meta" style="margin-top:12px;color:var(--muted);font-size:13px"></div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;align-items:center">
                    <a id="account-modal-edit" href="#" class="edit-small edit" aria-label="Edit account" style="margin-right:8px;">Edit</a>
                    <form id="am-approve-form" method="POST" style="display:inline">@csrf<input type="hidden" name="admin_note" id="am-admin-note-approve"><button id="am-approve-btn" class="btn primary">Approve</button></form>
                    <form id="am-deny-form" method="POST" style="display:inline">@csrf<input type="hidden" name="admin_note" id="am-admin-note-deny"><button id="am-deny-btn" class="btn delete">Deny</button></form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Deleted old typed-confirm modal in favor of inline confirm prompt -->

<!-- Typed-confirm delete modal (lightweight, accessible) -->
<div id="delete-confirm-modal" class="modal-overlay" style="display:none;position:fixed;inset:0;z-index:350;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(5,10,20,0.45);"></div>
    <div role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title" style="background:white;max-width:560px;width:92%;border-radius:12px;box-shadow:0 18px 46px rgba(2,6,23,0.24);padding:18px;position:relative;z-index:360">
        <button id="delete-confirm-close" style="position:absolute;right:12px;top:12px;background:transparent;border:none;font-size:18px">✕</button>
        <h3 id="delete-confirm-title" style="margin:0 0 8px">Delete Account</h3>
        <div id="delete-confirm-body" style="color:var(--muted);font-size:14px">
            <p id="delete-confirm-desc">Are you sure you want to delete this account? This action is permanent.</p>
            <p style="font-weight:700" id="delete-confirm-which2"></p>
            <label style="display:block;margin-top:12px;font-weight:700">Type <span style="background:#111;color:#fff;padding:2px 6px;border-radius:4px">CONFIRM</span> to enable delete</label>
            <input id="delete-confirm-input2" placeholder="Type CONFIRM to delete" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px;margin-top:8px">
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
            <button id="delete-confirm-cancel2" class="btn">Cancel</button>
            <button id="delete-confirm-do2" class="btn delete" disabled>Delete</button>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .sj-toast{position:fixed;right:18px;bottom:18px;background:white;border-radius:8px;box-shadow:0 8px 30px rgba(2,6,23,.12);padding:12px 14px;display:none;z-index:200}
    .sj-toast.show{display:block}
    .sj-toast .actions{margin-left:10px}
</style>
<script>
    // Global helper: parse various server timestamp formats and render in viewer's local timezone
    window.formatIsoLocal = function(iso){
        try{
            if(!iso) return '';
            var s = String(iso).trim();
            // Normalize common "YYYY-MM-DD HH:MM:SS" -> "YYYY-MM-DDTHH:MM:SS"
            s = s.replace(/^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2}(?:\.\d+)?)/, '$1T$2');
            var d = new Date(s);
            if(isNaN(d.getTime())){
                // Fallback: try forcing Z (UTC) if browser can't parse local form
                var s2 = s.replace(' ', 'T') + 'Z';
                d = new Date(s2);
                if(isNaN(d.getTime())) return String(iso);
            }
            var dateStr = d.toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' });
            var timeStr = d.toLocaleTimeString(undefined, { hour:'numeric', minute:'2-digit' });
            return dateStr + ' ' + timeStr;
        }catch(e){ return String(iso); }
    };
</script>
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
            const btns = qsa('.pending-wrap .view-btn');
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
                            // show admin note if present and add edit control for admins
                            const amAdminNote = qs('#am-admin-note');
                            const amAdminControls = qs('#am-admin-note-controls');
                            if(amAdminNote){
                                if(json.admin_note){ amAdminNote.style.display = 'block'; amAdminNote.textContent = 'Admin note: ' + json.admin_note; }
                                else { amAdminNote.style.display = 'none'; amAdminNote.textContent = ''; }
                            }
                                if(amAdminControls){
                                // show edit/add control when request is not pending (or always for admins)
                                const canEdit = (json.status || '').toLowerCase() !== 'pending';
                                // Use a hidden form submit for deletion (more robust than fetch here)
                                // add hidden form to document if not present
                                let hiddenDeleteForm = document.getElementById('account-delete-form');
                                if(!hiddenDeleteForm){
                                    hiddenDeleteForm = document.createElement('form');
                                    hiddenDeleteForm.id = 'account-delete-form';
                                    hiddenDeleteForm.method = 'POST';
                                    hiddenDeleteForm.style.display = 'none';
                                    // create CSRF and _method inputs via DOM to avoid embedding raw HTML
                                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                                    const tokenValue = tokenMeta ? tokenMeta.getAttribute('content') : (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '');
                                    const tokenInput = document.createElement('input');
                                    tokenInput.type = 'hidden'; tokenInput.name = '_token'; tokenInput.value = tokenValue;
                                    const methodInput = document.createElement('input');
                                    methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'DELETE';
                                    hiddenDeleteForm.appendChild(tokenInput);
                                    hiddenDeleteForm.appendChild(methodInput);
                                    document.body.appendChild(hiddenDeleteForm);
                                }

                                // note: removed stray handler that referenced undefined variables (doBtn/currentId)
                            }
                            const focusable = modal.querySelector('button, [href], input, textarea, select');
                            if(focusable) focusable.focus();
                            // update title subtitle if available
                            const title = qs('#account-modal-title');
                            if(title) title.textContent = 'Account Request — ' + (json.name || '');
                            const subtitle = qs('#account-modal-body .subtitle');
                            if(!subtitle){
                                const s = document.createElement('div'); s.className = 'subtitle'; s.textContent = json.email || ''; title.insertAdjacentElement('afterend', s);
                            }
                            // helper to format ISO timestamps to local date + time
                            function formatIsoLocal(iso){
                                try{
                                    if(!iso) return '';
                                    const d = new Date(iso);
                                    if(isNaN(d.getTime())) return iso;
                                    const dateStr = d.toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' });
                                    const timeStr = d.toLocaleTimeString(undefined, { hour:'numeric', minute:'2-digit' });
                                    return dateStr + ' ' + timeStr;
                                }catch(e){ return iso || ''; }
                            }
                                // update title and subtitle (cleanup any stray subtitle nodes first)
                                qsa('#account-modal .subtitle').forEach(n => n.remove());
                                if(title){
                                    const s = document.createElement('div');
                                    s.className = 'subtitle';
                                    s.textContent = json.email || '';
                                    title.insertAdjacentElement('afterend', s);
                                }
                                        // show/hide action buttons depending on status
                                        try{
                                            const status = (json.status || '').toLowerCase();
                                            if(status !== 'pending'){
                                                if(approveForm) approveForm.style.display = 'none';
                                                if(denyForm) denyForm.style.display = 'none';
                                                // show a small status label inside modal body
                                                let statusEl = qs('#am-status');
                                                if(!statusEl){ statusEl = document.createElement('div'); statusEl.id = 'am-status'; statusEl.style.marginTop = '10px'; statusEl.style.fontWeight = '700'; statusEl.style.color = '#6b7280'; qs('#account-modal-body').appendChild(statusEl); }
                                                const map = { 'rejected': 'Denied', 'denied': 'Denied', 'approved': 'Approved', 'pending': 'Pending' };
                                                statusEl.textContent = 'Status: ' + (map[status] || status || 'Unknown');
                                            } else {
                                                if(approveForm) approveForm.style.display = '';
                                                if(denyForm) denyForm.style.display = '';
                                                const existingStatus = qs('#am-status'); if(existingStatus) existingStatus.remove();
                                            }
                                        }catch(e){/* ignore */}
                                // update title and subtitle (remove duplicates then insert)
                                qsa('#account-modal .subtitle').forEach(n => n.remove());
                                if(title){ const s = document.createElement('div'); s.className = 'subtitle'; s.textContent = json.email || ''; title.insertAdjacentElement('afterend', s); }
                                // show/hide actions based on status
                                try{
                                    const status = (json.status || '').toLowerCase();
                                    if(status !== 'pending'){
                                        if(approveForm) approveForm.style.display = 'none';
                                        if(denyForm) denyForm.style.display = 'none';
                                        let statusEl = qs('#am-status');
                                        if(!statusEl){ statusEl = document.createElement('div'); statusEl.id = 'am-status'; statusEl.style.marginTop = '10px'; statusEl.style.fontWeight = '700'; statusEl.style.color = '#6b7280'; qs('#account-modal-body').appendChild(statusEl); }
                                        const map = { 'rejected': 'Denied', 'denied': 'Denied', 'approved': 'Approved', 'pending': 'Pending' };
                                        statusEl.textContent = 'Status: ' + (map[status] || status || 'Unknown');
                                    } else {
                                        if(approveForm) approveForm.style.display = '';
                                        if(denyForm) denyForm.style.display = '';
                                        const existingStatus = qs('#am-status'); if(existingStatus) existingStatus.remove();
                                    }
                                }catch(e){/* ignore */}
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
                                // show admin note if present
                                const amAdminNote = qs('#am-admin-note');
                                if(amAdminNote){
                                    if(json.admin_note){ amAdminNote.style.display = 'block'; amAdminNote.textContent = 'Admin note: ' + json.admin_note; }
                                    else { amAdminNote.style.display = 'none'; amAdminNote.textContent = ''; }
                                }
                                // Format requested time to local timezone
                                try{
                                    const createdIso = json.created_at_display || json.created_at_iso || json.created_at;
                                    amMeta.textContent = 'Requested: ' + (json.created_at_display ? json.created_at_display : (createdIso ? (function(d){ try{ const dt=new Date(d); if(isNaN(dt.getTime())) return d; return dt.toLocaleDateString(undefined,{year:'numeric',month:'long',day:'numeric'}) + ' ' + dt.toLocaleTimeString(undefined,{hour:'numeric',minute:'2-digit'}); }catch(e){return d;} })(createdIso) : ''));
                                }catch(e){ amMeta.textContent = 'Requested: ' + (json.created_at_display || json.created_at_iso || json.created_at || ''); }
                                if(approveForm) approveForm.setAttribute('action', '/accounts/' + id + '/approve');
                                if(denyForm) denyForm.setAttribute('action', '/accounts/' + id + '/deny');
                                modal.style.display = 'flex'; modal.classList.add('show');
                                // focus the appropriate action button
                                if(action === 'approve'){ var _b = qs('#am-approve-btn'); if(_b) _b.focus(); }
                                if(action === 'deny'){ var _b2 = qs('#am-deny-btn'); if(_b2) _b2.focus(); }
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
        // Simple global helper so inline onclick can always open modal
        window.openDeleteModal = function(form){
            try{
                window.__pendingDeleteForm = form;
                const modal = document.getElementById('delete-confirm-modal');
                const which = document.getElementById('delete-confirm-which2');
                const input = document.getElementById('delete-confirm-input2');
                if(which){
                    var name = '';
                    var email = '';
                    try{
                        var _n = form && form.querySelector('td:nth-child(2) > div');
                        if(_n && _n.textContent) name = _n.textContent.trim();
                        var _e = form && form.querySelector('td:nth-child(2) > div + div');
                        if(_e && _e.textContent) email = _e.textContent.trim();
                    }catch(err){}
                    which.textContent = (name ? name + ' ' : '') + (email ? ('(' + email + ')') : '');
                }
                if(input) input.value = '';
                const doBtn = document.getElementById('delete-confirm-do2'); if(doBtn) doBtn.disabled = true;
                if(modal){ modal.style.display = 'flex'; modal.classList.add('show'); }
                if(input) input.focus();
            }catch(e){ console.error('openDeleteModal error', e); }
            return false;
        };

        // Modal-driven delete confirmation
        document.addEventListener('DOMContentLoaded', function(){
            function getRowNameEmail(form){
                try{
                    const tr = form.closest('tr');
                    if(!tr) return {name:'', email:''};
                    const nameEl = tr.querySelector('td:nth-child(2) > div');
                    const emailEl = tr.querySelector('td:nth-child(2) > div + div');
                    const name = nameEl ? nameEl.textContent.trim() : '';
                    const email = emailEl ? emailEl.textContent.trim() : '';
                    return {name, email};
                }catch(e){ return {name:'', email:''}; }
            }

                    // Modal-driven delete confirmation (simplified)
                    (function(){
                        const modal = document.getElementById('delete-confirm-modal');
                        const which = document.getElementById('delete-confirm-which2');
                        const input = document.getElementById('delete-confirm-input2');
                        const doBtn = document.getElementById('delete-confirm-do2');
                        const cancel = document.getElementById('delete-confirm-cancel2');
                        const closeBtn = document.getElementById('delete-confirm-close');
                        let pendingForm = null;

                        function openModal(form){
                            const info = getRowNameEmail(form);
                            if(which) which.textContent = (info.name ? info.name + ' ' : '') + (info.email ? ('(' + info.email + ')') : '');
                            if(input) input.value = '';
                            if(doBtn) doBtn.disabled = true;
                            pendingForm = form;
                            if(modal){ modal.style.display = 'flex'; modal.classList.add('show'); }
                            if(input) input.focus();
                        }

                        function closeModal(){ if(modal){ modal.style.display = 'none'; modal.classList.remove('show'); } pendingForm = null; try{ window.__pendingDeleteForm = null; }catch(e){} }

                        document.querySelectorAll('form.delete-account-form').forEach(function(form){
                            form.addEventListener('submit', function(e){
                                e.preventDefault();
                                openModal(form);
                            });
                        });

                        if(input) input.addEventListener('input', function(){ if(doBtn) doBtn.disabled = (this.value || '').trim() !== 'CONFIRM'; });

                        if(doBtn){
                            doBtn.addEventListener('click', function(e){
                                e.preventDefault();
                                const formToSubmit = pendingForm || window.__pendingDeleteForm;
                                if(!formToSubmit) return closeModal();
                                try{ formToSubmit.submit(); }catch(err){ console.error('submit failed', err); }
                                closeModal();
                            });
                        }

                        if(cancel) cancel.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });
                        if(closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });
                        window.addEventListener('keyup', function(e){ if(e.key === 'Escape') closeModal(); });
                    })();
                });
        </script>

    <script>
        // global error logger to surface JS errors in the console and toast
        window.addEventListener('error', function(ev){
            try{ console.error('Global JS error:', ev.message, ev.filename, ev.lineno, ev.colno); }catch(e){}
            const t = document.getElementById('sj-toast');
            const tm = document.getElementById('sj-toast-msg');
            if(t && tm){ tm.textContent = 'JS error: ' + (ev.message || 'unknown'); t.classList.add('show'); setTimeout(()=> t.classList.remove('show'),5000); }
        });
    </script>
    <script>
        (function(){
            // Handler for viewing user accounts from the Accounts tab (inline modal + inline edit)
            function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
            function qsa(sel, ctx){ return Array.from((ctx||document).querySelectorAll(sel)); }

            const accountViewBtns = qsa('.accounts-wrap .view-btn');
            const modal = qs('#account-modal');
            const amOps = qs('#am-ops-role');
            const amUserControls = qs('#am-user-controls');
            const amName = qs('#am-name');
            const amEmail = qs('#am-email');
            const amDept = qs('#am-department');
            const amRole = qs('#am-role');
            const amMessage = qs('#am-message');
            const amMeta = qs('#am-meta');

            function getCsrf(){ const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : ''); }

                    accountViewBtns.forEach(b => b.addEventListener('click', function(e){
                e.preventDefault();
                const id = this.getAttribute('data-id') || (this.getAttribute('href') ? this.getAttribute('href').split('/').pop() : null);
                if(!id) return;
                fetch('/accounts/user/' + id + '/json', {credentials: 'same-origin'})
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(json => {
                        // populate modal fields
                        if(amName) amName.textContent = json.name || '';
                        if(amEmail) amEmail.textContent = json.email ? (' — ' + json.email) : '';
                        if(amDept) amDept.textContent = json.department ? ('Department: ' + json.department) : '';
                        if(amRole) amRole.textContent = json.role ? ('Role: ' + json.role) : '';
                        if(amMessage) amMessage.textContent = '';
                        if(amMeta) {
                            const createdIso = json.created_at_iso || json.created_at;
                            amMeta.textContent = 'Joined: ' + (createdIso ? formatIsoLocal(createdIso) : (json.created_at_display ? json.created_at_display : ''));
                        }

                        if(json.active !== undefined){
                            // it's a user account — only show Ops Role when department is Operations
                            if(amOps){
                                if((String(json.department || '').toLowerCase()) === 'operations'){
                                    amOps.style.display = 'block';
                                    amOps.textContent = 'Ops Role: ' + (json.ops_role || '(none)');
                                } else {
                                    amOps.style.display = 'none';
                                    amOps.textContent = '';
                                }
                            }
                            if(amUserControls){
                                // Keep Edit button singular. The main Edit control is `#account-modal-edit` (moved to the action row).
                                // Set its href below instead of injecting another button into the body.
                            }
                            // also set header Edit link target if present
                            const headerEdit = qs('#account-modal-edit'); if(headerEdit) headerEdit.href = '/accounts/user/' + json.id + '/edit';
                        }
                        // hide approve/deny forms (not applicable to user accounts)
                        const approveForm = qs('#am-approve-form'); if(approveForm) approveForm.style.display = 'none';
                        const denyForm = qs('#am-deny-form'); if(denyForm) denyForm.style.display = 'none';

                        // show modal
                        // populate Joined/Requested meta using local timezone
                        try{
                            const metaEl = qs('#am-meta');
                            const createdIso = json.created_at_iso || json.created_at || json.created_at_display;
                            if(metaEl){
                                metaEl.textContent = 'Joined: ' + (createdIso ? formatIsoLocal(createdIso) : (json.created_at_display ? json.created_at_display : ''));
                            }
                        }catch(e){}
                        if(modal){ modal.style.display = 'flex'; modal.classList.add('show'); const focusable = modal.querySelector('button, [href], input, textarea, select'); if(focusable) focusable.focus(); }
                    }).catch(()=> alert('Failed to load account'));
            }));
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
                    prompt.innerHTML = '' +
                        '<label style="display:block;font-weight:600;margin-bottom:6px">Admin note (optional)</label>' +
                        '<textarea id="am-confirm-note" rows="3" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px"></textarea>' +
                        '<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px">' +
                            '<button id="am-cancel-btn" class="btn">Cancel</button>' +
                            '<button id="am-confirm-btn" class="btn">Confirm ' + action + '</button>' +
                        '</div>';
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
    <script>
        // Admin note editor + AJAX save
        (function(){
            function qs(sel, ctx){ return (ctx||document).querySelector(sel); }
            function qsa(sel, ctx){ return Array.from((ctx||document).querySelectorAll(sel)); }

            function getCsrf(){
                const meta = document.querySelector('meta[name="csrf-token"]');
                if(meta) return meta.getAttribute('content');
                const input = document.querySelector('input[name="_token"]');
                if(input) return input.value;
                return '';
            }

            function renderEditButton(id, note){
                const controls = qs('#am-admin-note-controls');
                if(!controls) return;
                controls.innerHTML = '<button id="am-edit-note" class="btn" style="padding:6px 10px;border-radius:8px;border:1px solid #e6e9ef;background:#f8fafc">' + (note ? 'Edit note' : 'Add note') + '</button>';
                const btn = qs('#am-edit-note');
                if(btn) btn.onclick = function(){ openNoteEditor(id); };
            }

            function openNoteEditor(id){
                const controls = qs('#am-admin-note-controls');
                const display = qs('#am-admin-note');
                const current = display ? display.textContent.replace(/^Admin note:\s*/,'') : '';
                if(!controls) return;
                controls.innerHTML = '' +
                    '<textarea id="am-note-edit" rows="4" style="width:100%;padding:8px;border:1px solid #e6e9ef;border-radius:8px">' + current + '</textarea>' +
                    '<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px">' +
                        '<button id="am-note-cancel" class="btn">Cancel</button>' +
                        '<button id="am-note-save" class="btn primary">Save</button>' +
                    '</div>';
                qs('#am-note-cancel').onclick = function(){ renderEditButton(id, current); };
                qs('#am-note-save').onclick = function(){
                    const val = qs('#am-note-edit').value || '';
                    const token = getCsrf();
                    fetch('/accounts/' + id + '/note', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN': token},
                        body: new URLSearchParams({ admin_note: val }).toString()
                    }).then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(json => {
                        if(json && json.admin_note !== undefined){
                            const display = qs('#am-admin-note');
                            if(display){ display.style.display = 'block'; display.textContent = 'Admin note: ' + (json.admin_note || ''); }
                            renderEditButton(id, json.admin_note);
                            // show toast
                            const toastMsg = qs('#sj-toast-msg'); if(toastMsg) toastMsg.textContent = 'Admin note saved'; const t = qs('#sj-toast'); if(t) t.classList.add('show'); setTimeout(()=>{ if(t) t.classList.remove('show'); },3000);
                        }
                    }).catch(()=>{ alert('Failed to save note'); renderEditButton(id, current); });
                };
            }

            // expose small helpers globally so modal openers can call them as needed
            window.openNoteEditor = openNoteEditor;
            window.renderEditButton = renderEditButton;
        })();
    </script>
    <script>
        (function(){
            // Simple tab switching for Accounts / Pending Accounts
            function qs(sel){ return document.querySelector(sel); }
            function qsa(sel){ return Array.from(document.querySelectorAll(sel)); }
            const tabs = qsa('.tabs .tab');
            const accountsWrap = qs('.accounts-wrap');
            const pendingWrap = qs('.pending-wrap');
            const titleEl = qs('#accounts-page-title');
            const subtitleEl = qs('#accounts-page-subtitle');
            const badgeEl = qs('#header-badge');

            function updateHeaderForAccounts(){
                if(titleEl) titleEl.textContent = 'Accounts';
                if(subtitleEl) subtitleEl.textContent = 'Manage active user accounts';
                // try to read total from accounts-wrap
                let count = 0;
                try {
                    const strong = accountsWrap ? accountsWrap.querySelector('strong') : null;
                    if(strong) count = strong.textContent.trim();
                } catch(e){}
                if(badgeEl){
                    badgeEl.textContent = count ? ('Accounts (' + count + ')') : 'Accounts';
                    badgeEl.classList.remove('pending');
                    badgeEl.classList.add('blue');
                }
            }

            function updateHeaderForPending(){
                if(titleEl) titleEl.textContent = 'Pending Requests';
                if(subtitleEl) subtitleEl.textContent = 'Manage account requests and approvals';
                if(badgeEl){
                    badgeEl.textContent = 'Pending';
                    badgeEl.classList.remove('blue');
                    badgeEl.classList.add('pending');
                }
            }

            tabs.forEach(t => t.addEventListener('click', function(){
                const target = this.getAttribute('data-tab');
                tabs.forEach(x => x.classList.remove('active'));
                this.classList.add('active');
                if(target === 'accounts'){
                    if(accountsWrap) accountsWrap.style.display = '';
                    if(pendingWrap) pendingWrap.style.display = 'none';
                    updateHeaderForAccounts();
                } else {
                    if(accountsWrap) accountsWrap.style.display = 'none';
                    if(pendingWrap) pendingWrap.style.display = '';
                    updateHeaderForPending();
                }
                try{
                    const params = new URLSearchParams(window.location.search);
                    params.set('tab', target);
                    history.replaceState(null, '', window.location.pathname + '?' + params.toString());
                }catch(e){}
            }));

            // Wire the status <select> controls to reload with the selected status and tab
            try{
                const accountsStatus = qs('#accounts-status') || qs('.accounts-wrap select[name="status"]');
                const pendingStatus = qs('.pending-wrap select[name="status"]');
                function setStatusAndReload(value, tab){
                    const params = new URLSearchParams(window.location.search);
                    if(value && value.length) params.set('status', value); else params.delete('status');
                    if(tab) params.set('tab', tab);
                    window.location.search = params.toString();
                }
                if(accountsStatus) accountsStatus.addEventListener('change', function(){ setStatusAndReload(this.value, 'accounts'); });
                if(pendingStatus) pendingStatus.addEventListener('change', function(){
                    try{
                        // If pending table is visible, filter rows client-side to avoid reload
                        const pw = document.querySelector('.pending-wrap');
                        if(pw && pw.style.display !== 'none'){
                            const val = (this.value||'').toLowerCase();
                            const rows = Array.from(pw.querySelectorAll('tbody tr'));
                            let visibleCount = 0;
                            rows.forEach(tr => {
                                const statusTd = tr.querySelector('td:nth-child(5)');
                                const statusText = statusTd ? (statusTd.textContent||'').toLowerCase().trim() : '';
                                if(!val || val === '' ){
                                    tr.style.display = '';
                                    visibleCount++;
                                } else if((val === 'pending' && statusText.indexOf('pending') !== -1) ||
                                          (val === 'approved' && statusText.indexOf('approved') !== -1) ||
                                          (val === 'inactive' && statusText.indexOf('inactive') !== -1) ||
                                          (val === 'denied' && statusText.indexOf('denied') !== -1)){
                                    tr.style.display = '';
                                    visibleCount++;
                                } else {
                                    tr.style.display = 'none';
                                }
                            });
                            // update total display if present
                            try{
                                const totalEl = document.querySelector('.pending-wrap').parentElement.querySelector('div[style*="Total:"] strong');
                                if(totalEl) totalEl.textContent = visibleCount;
                            }catch(e){}
                            return;
                        }
                    }catch(e){}
                    // fallback: reload with status & pending tab
                    setStatusAndReload(this.value, 'pending');
                });
            }catch(e){}
        })();
    </script>
@endpush
