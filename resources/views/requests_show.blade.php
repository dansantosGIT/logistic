<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Request Review — San Juan CDRMMD</title>
    <link rel="icon" href="/images/favi.png" type="image/png">
    <meta name="theme-color" content="#0b1220">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}
        /* Full-bleed background image (slightly transparent via overlay) */
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}
        .app{display:flex;min-height:100vh}

        /* Sidebar styles are moved to the shared partial to ensure consistent styling */

        /* Topbar */
        .topbar{position:fixed;left:0;right:0;top:0;height:72px;background:rgba(255,255,255,0.95);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.08);z-index:60}
          /* make the topbar full-width so the burger can sit flush left
              and the welcome/admin stays right, maximizing header space */
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

        /* Sidebar behavior and layout moved to the shared partial for consistency */
        /* Header becomes a white panel inside the main area to visually join with the sidebar */
        header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px;background:var(--panel);padding:12px 16px;border-radius:10px;box-shadow:0 6px 20px rgba(2,6,23,0.08)}
        header h1{color:#0f172a;margin:0}
        header .subtitle{color:var(--muted)}
        .header-left{display:flex;align-items:center;gap:16px}
        .burger{display:inline-flex;width:44px;height:44px;border-radius:8px;align-items:center;justify-content:center;background:transparent;border:1px solid transparent;cursor:pointer}
        .burger:hover{background:#eef2ff}
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-top:6px;margin-bottom:18px}
        .card{background:var(--panel);padding:18px;border-radius:12px;box-shadow:0 8px 30px rgba(15,23,42,0.06)}
        .card .title{font-size:13px;color:var(--muted)}
        .card .value{font-size:22px;font-weight:700;margin-top:6px}

        /* Request detail / form styles (restored) */
        .panel{max-width:1100px;margin:20px auto;padding:26px 28px;background:var(--panel);border-radius:14px;box-shadow:0 14px 40px rgba(15,23,42,0.08)}
        .panel .panel-header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:18px}
        .panel .panel-title{margin:0;font-size:20px;color:#0f172a}
        .panel .panel-sub{color:var(--muted);font-size:13px}
        .field-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px 24px;margin-top:6px}
        .field{background:linear-gradient(180deg,#fff,#fbfdff);padding:12px;border-radius:10px;border:1px solid rgba(14,21,40,0.04);display:flex;align-items:center;gap:12px}
        .label{width:140px;color:var(--muted);font-weight:700;font-size:13px}
        .value{flex:1;font-size:14px;color:#0f172a}
        .value a{color:#2563eb;text-decoration:none}
        .meta-block{display:flex;flex-direction:column;gap:6px}
        .request-row{display:flex;align-items:flex-start;gap:18px}
        .request-title{font-size:32px;font-weight:800;color:#0f172a;margin:8px 0}
        .request-card{max-width:980px;margin:6px auto 0;padding:18px;background:var(--panel);border-radius:12px;box-shadow:0 18px 40px rgba(2,6,23,0.06)}
        .meta-table{width:100%;border-radius:10px;overflow:hidden;border:1px solid rgba(14,21,40,0.04);background:linear-gradient(180deg,#fff,#fbfdff)}
        .meta-row{display:flex;justify-content:space-between;padding:14px 18px;border-bottom:1px solid rgba(14,21,40,0.04);align-items:center}
        .meta-row .k{color:#0f172a;font-weight:900}
        .meta-row .v{color:#0f172a}
        .request-card .card-header{background:#263544;color:#fff;padding:14px;border-radius:10px 10px 0 0;display:flex;justify-content:space-between;align-items:center}
        .request-card .card-header .request-title{font-size:20px;color:#fff;margin:0;font-weight:800}
        .request-card .card-header .back-btn{background:#6b7280;border:none;color:#fff;padding:8px 12px;border-radius:8px}
        .request-card .card-header .back-btn:hover{background:#8b93a0}
        .items-heading{margin:18px 0 8px 0;font-weight:700}
        .items-table{width:100%;border-collapse:separate;border-spacing:0 8px}
        .items-table thead th{background:#263544;color:#fff;padding:12px 14px;text-align:left;font-weight:700;border-top-left-radius:8px;border-top-right-radius:8px}
        .items-table tbody td{background:#fff;padding:12px 14px;border-bottom:1px solid rgba(14,21,40,0.04)}
        .items-table tbody td:last-child{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .items-table tr{box-shadow:0 6px 18px rgba(2,6,23,0.04);border-radius:8px}
        .items-table tbody tr td:first-child{border-top-left-radius:8px;border-bottom-left-radius:8px}
        .items-table tbody tr td:last-child{border-top-right-radius:8px;border-bottom-right-radius:8px}
        .request-actions{display:flex;gap:12px;margin-top:14px}
        .badge{display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;font-weight:700}
        .badge.pending{background:#ffebc2;color:#92400e}
        .badge.approved{background:#dcfce7;color:#065f46}
        .badge.done{background:#dcfce7;color:#065f46}
        .badge.rejected{background:#fee2e2;color:#991b1b}
        .actions{display:flex;gap:12px;justify-content:flex-end;margin-top:18px}
        .btn{padding:10px 14px;border-radius:10px;border:1px solid #e6e9ef;background:white;cursor:pointer;font-weight:600}
        .btn.ok{background:#10b981;color:#fff;border:none;box-shadow:0 8px 22px rgba(16,185,129,0.12)}
        .btn.rej{background:#ef4444;color:#fff;border:none;box-shadow:0 8px 22px rgba(239,68,68,0.12)}
        a.back{display:inline-flex;align-items:center;gap:8px;margin-bottom:8px;color:#2563eb;font-weight:600}
        a.back{display:inline-block;margin-bottom:12px;color:#2563eb}

        .center{display:grid;grid-template-columns:1fr 360px;gap:14px}
        .list{background:var(--panel);padding:14px;border-radius:10px;min-height:180px;box-shadow:0 6px 20px rgba(15,23,42,0.04)}
        .placeholder{height:200px;border-radius:8px;background:linear-gradient(90deg,#eef2ff,#f0fdf4);display:flex;align-items:center;justify-content:center;color:var(--muted)}

        /* Collapsed/responsive sidebar adjustments are handled in the shared partial */

        /* Approval Modal Styles */
        .approval-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;z-index:999;animation:fadeIn 0.2s ease-out}
        .approval-backdrop.show{display:block}
        .approval-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.95);background:white;border-radius:16px;box-shadow:0 25px 50px rgba(0,0,0,0.3);width:90%;max-width:540px;max-height:90vh;overflow-y:auto;display:none;z-index:1000;animation:slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1)}
        .approval-modal.show{display:block;transform:translate(-50%,-50%) scale(1)}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        @keyframes slideUp{from{transform:translate(-50%,-55%) scale(0.95);opacity:0}to{transform:translate(-50%,-50%) scale(1);opacity:1}}
        .approval-header{display:flex;justify-content:space-between;align-items:center;padding:24px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:linear-gradient(135deg,var(--accent),var(--accent-2));color:white}
        .approval-header h2{margin:0;font-size:18px;font-weight:700}
        .approval-close{background:rgba(255,255,255,0.2);border:none;cursor:pointer;font-size:28px;color:white;padding:0;width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;transition:background 0.2s;font-weight:300}
        .approval-close:hover{background:rgba(255,255,255,0.3)}
        .approval-body{padding:24px}
        .approval-item-header{background:linear-gradient(135deg,rgba(37,99,235,0.08),rgba(124,58,237,0.04));padding:20px;border-radius:12px;margin-bottom:24px;border:2px solid var(--accent);border-left:4px solid var(--accent)}
        .approval-item-title{font-size:18px;font-weight:800;color:var(--accent);margin-bottom:12px;padding:8px;background:white;border-radius:8px;border-left:4px solid var(--accent)}
        .approval-item-meta{display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px}
        .approval-item-meta .meta-item{color:#0f172a;font-weight:600}
        .approval-item-meta .meta-label{font-weight:700;color:var(--accent);margin-bottom:4px;font-size:11px;text-transform:uppercase}
        .approval-item-category{font-size:14px;font-weight:700;color:#7c3aed;background:rgba(124,58,237,0.1);padding:4px 8px;border-radius:6px;display:inline-block;margin-top:4px}
        .approval-stock-warning{background:#fef3c7;border:2px solid #f59e0b;border-radius:10px;padding:12px;margin-bottom:20px;color:#92400e}
        .approval-stock-warning strong{color:#d97706}
        .approval-stock-available{background:#d1fae5;border:1px solid #10b981;color:#065f46;padding:10px;border-radius:8px;margin-bottom:16px;font-weight:600;font-size:13px}
        .approval-form-group{margin-bottom:20px}
        .approval-form-group:last-child{margin-bottom:0}
        .approval-label{display:block;font-weight:700;color:#0f172a;margin-bottom:8px;font-size:14px}
        .approval-input,.approval-textarea{width:100%;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;font-family:inherit;font-size:14px;background:#f9fafb}
        .approval-input:focus,.approval-textarea:focus{outline:none;border-color:var(--accent);background:white;box-shadow:0 0 0 3px rgba(37,99,235,0.1)}
        .approval-textarea{resize:vertical;min-height:100px}
        .approval-actions{display:flex;gap:12px;margin-top:24px;padding-top:16px;border-top:1px solid #e5e7eb}
        .approval-btn{padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-weight:600;transition:all 0.2s;font-size:14px}
        .approval-btn-approve{background:#10b981;color:white;box-shadow:0 4px 12px rgba(16,185,129,0.2);flex:1}
        .approval-btn-approve:hover{background:#059669;box-shadow:0 6px 16px rgba(16,185,129,0.3)}
        .approval-btn-deny{background:#ef4444;color:white;box-shadow:0 4px 12px rgba(239,68,68,0.2);flex:1}
        .approval-btn-deny:hover{background:#dc2626;box-shadow:0 6px 16px rgba(239,68,68,0.3)}
        .approval-btn:disabled{opacity:0.6;cursor:not-allowed}
        @media(max-width:768px){.approval-modal{width:95%;max-width:none}}
        /* Toast styles (used for approve/reject feedback) */
        .toast{position:fixed;right:24px;bottom:24px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,0.12);transform:translateY(12px);opacity:0;transition:opacity .22s,transform .22s;z-index:220;display:flex;align-items:center;gap:10px}
        .toast.show{opacity:1;transform:translateY(0)}
        .toast .close{cursor:pointer;padding:6px;border-radius:6px;background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.9)}
        .toast.error{background:#ef4444}
        /* Batch approval table styles */
        .batch-table{width:100%;border-collapse:collapse;margin:6px 0;font-size:14px}
        .batch-table thead th{background:#f8fafc;padding:10px 12px;text-align:left;border-bottom:1px solid #eef2ff;font-weight:700}
        .batch-table tbody td{padding:10px 12px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
        .batch-table .small-muted{font-size:12px;color:var(--muted)}
        .batch-note{margin-top:6px}
        .batch-note-toggle{padding:6px 8px;border-radius:6px;border:1px solid #e6e9ef;background:#fff;font-size:12px;cursor:pointer}
        /* Mobile: convert items-table and batch-table rows into stacked cards for better readability */
        @media (max-width:900px) {
            .items-table thead, .batch-table thead { display: none; }
            .items-table, .items-table tbody, .items-table tr, .batch-table, .batch-table tbody, .batch-table tr { display: block; width: 100%; }
            .items-table tbody tr, .batch-table tbody tr { margin-bottom: 12px; background: #fff; padding: 12px; border-radius: 10px; box-shadow: 0 8px 20px rgba(2,6,23,0.04); border: 1px solid rgba(14,21,40,0.04); }
            .items-table tbody td, .batch-table tbody td { display: block; padding: 8px 0; border: none; }
            .items-table tbody td:first-child, .batch-table tbody td:first-child { font-weight:700; color:#0f172a; margin-bottom:6px }
            .items-table tbody td:nth-child(2)::before { content: 'Requested: '; font-weight:700; color:var(--muted); }
            .items-table tbody td:nth-child(3)::before { content: 'Issued: '; font-weight:700; color:var(--muted); }
            .items-table tbody td:nth-child(4)::before { content: 'Return: '; font-weight:700; color:var(--muted); }
            .items-table tbody td:nth-child(5)::before { content: 'Reason: '; font-weight:700; color:var(--muted); }
            .items-table tbody td:nth-child(6)::before { content: 'Status: '; font-weight:700; color:var(--muted); }
            .batch-table tbody td:nth-child(2)::before { content: 'Item: '; font-weight:700; color:var(--muted); }
            .batch-table tbody td:nth-child(3)::before { content: 'Details: '; font-weight:700; color:var(--muted); }
            .batch-table tbody td:nth-child(4)::before { content: 'Requested: '; font-weight:700; color:var(--muted); }
            .batch-table tbody td:nth-child(5)::before { content: 'Available: '; font-weight:700; color:var(--muted); }
            .batch-table tbody td:nth-child(6)::before { content: 'Issue Qty: '; font-weight:700; color:var(--muted); }
            .batch-table tbody td:nth-child(7)::before { content: 'Note: '; font-weight:700; color:var(--muted); }
            .items-table tbody td::before, .batch-table tbody td::before { display: inline-block; margin-right:6px; color:var(--muted); font-weight:700 }
            .items-table tbody td:last-child, .batch-table tbody td:last-child { display:flex;gap:8px;align-items:center;justify-content:flex-start }
        }
    </style>
    @include('partials._bg-preload')
    @include('partials._formatters')
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>
    <div class="topbar" role="banner">
        <div class="topbar-inner">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="burger-top" class="burger" aria-label="Toggle menu" title="Toggle menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </button>
                <div style="display:flex;flex-direction:column">
                    <a href="/dashboard" style="display:flex;align-items:center;gap:6px;font-weight:700;text-decoration:none;color:inherit">
                        <img src="/images/favi.png" alt="Logo" width="40" height="40" style="display:inline-block" />
                        <span>Request Review</span>
                    </a>
                    <div style="font-size:12px;color:var(--muted)">Review the submitted request</div>
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
        @include('partials.sidebar')

        <main class="main">
            <div>
                <div class="request-card">
                    <div class="card-header">
                        <div class="request-title">Request #{{ $r->id ?? $r->uuid }}</div>
                        <div><a href="/requests?tab=pending" class="back-btn" style="text-decoration:none">Back</a></div>
                    </div>
                    <div class="meta-table">
                        <div class="meta-row"><div class="k">Requested by</div><div class="v">{{ $r->requester }}</div></div>
                        <div class="meta-row"><div class="k">Role</div><div class="v">{{ $r->role ?? '—' }}</div></div>
                        <div class="meta-row"><div class="k">Department</div><div class="v">{{ $r->department ?? '—' }}</div></div>
                        <div class="meta-row"><div class="k">Status</div><div class="v"><span class="badge {{ strtolower($r->status) }}">{{ ucfirst(strtolower($r->status)) }}</span></div></div>
                        <div class="meta-row"><div class="k">Submitted</div><div class="v"><span class="local-datetime" data-datetime="{{ $r->created_at->toIso8601String() }}">{{ $r->created_at->format('F j, Y, g:i A') }}</span></div></div>
                    </div>

                    <div class="items-heading">Items</div>
                    <div style="overflow:auto">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Requested</th>
                                    <th>Issued</th>
                                    <th>Return</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $iterableItems = (isset($r->items) && is_countable($r->items) && $r->items->count() > 0) ? $r->items : [$r];
                                @endphp
                                @foreach($iterableItems as $it)
                                    @php
                                            $requestedQty = $it->quantity ?? ($it['quantity'] ?? $r->quantity ?? 1);
                                            // Prefer per-item issued quantity (set on approval), then legacy fields
                                            $issued = $it->issued_quantity ?? $it->issued ?? $r->issued ?? 0;
                                            $returnDate = $it->return_date ?? $r->return_date ?? null;
                                            $reasonText = $it->notes ?? $it->reason ?? $r->reason ?? '—';

                                            // find equipment model if available (prefer loaded relation, then equipment_id/item_id, then parent request, then page-level `$equipment`)
                                            $equipModel = null;
                                            try {
                                                // Prefer an eager-loaded relation when present
                                                if (is_object($it) && isset($it->equipment) && $it->equipment) {
                                                    $equipModel = $it->equipment;
                                                } else {
                                                    // Determine a candidate equipment id from the item or parent
                                                    $equipId = null;
                                                    if (is_object($it)) {
                                                        if (!empty($it->equipment_id)) $equipId = $it->equipment_id;
                                                        elseif (!empty($it->item_id)) $equipId = $it->item_id;
                                                    } elseif (is_array($it)) {
                                                        if (!empty($it['equipment_id'])) $equipId = $it['equipment_id'];
                                                        elseif (!empty($it['item_id'])) $equipId = $it['item_id'];
                                                    }

                                                    if ($equipId) {
                                                        $equipModel = \App\Models\Equipment::find($equipId);
                                                    } elseif (!empty($r->item_id)) {
                                                        $equipModel = \App\Models\Equipment::find($r->item_id);
                                                    }
                                                }
                                            } catch (\Throwable $_) { $equipModel = null; }

                                            // Final fallback: use the page-level `$equipment` (single-item view) when available
                                            if (empty($equipModel) && isset($equipment) && $equipment) {
                                                $equipModel = $equipment;
                                            }

                                                // Prefer actual equipment name when available (child items may inherit parent label "Multiple items")
                                            $itemName = $equipModel?->name ?? ($it->item_name ?? ($it['item_name'] ?? ($r->item_name ?? '—')));

                                            $equipData = [
                                                'id' => $equipModel?->id ?? ($it->equipment_id ?? $r->item_id ?? null),
                                                'request_item_id' => $it->id ?? ($it['id'] ?? null),
                                                'name' => $itemName,
                                                'category' => $equipModel?->category ?? null,
                                                'type' => $equipModel?->type ?? null,
                                                'serial' => $equipModel?->serial ?? null,
                                                'location' => $equipModel?->location ?? ($it->location ?? null),
                                                'quantity' => $equipModel?->quantity ?? 0,
                                                'requested_quantity' => $requestedQty,
                                            ];
                                        @endphp
                                    <tr>
                                        <td>
                                            @php
                                                $payload = [
                                                    'id' => $equipData['id'] ?? null,
                                                    'request_item_id' => $equipData['request_item_id'] ?? null,
                                                    'name' => $equipData['name'] ?? null,
                                                    'category' => $equipData['category'] ?? null,
                                                    'type' => $equipData['type'] ?? null,
                                                    'serial' => $equipData['serial'] ?? null,
                                                    'location' => $equipData['location'] ?? null,
                                                    'quantity' => $equipData['quantity'] ?? 0,
                                                    'requested_quantity' => $equipData['requested_quantity'] ?? $requestedQty,
                                                    'image_path' => $equipModel?->image_path ?? $equipModel?->photo ?? null,
                                                ];
                                            @endphp
                                            <a href="#" class="item-link" data-equipment='@json($payload)'>{{ $itemName }}</a>
                                        </td>
                                        <td>{{ $requestedQty }}</td>
                                        <td>{{ $issued }}</td>
                                        <td>{{ !empty($returnDate) ? ((($returnDate instanceof \DateTimeInterface) ? $returnDate->format('F j, Y') : \Carbon\Carbon::parse($returnDate)->format('F j, Y'))) : 'Consumable — N/A' }}</td>
                                        <td>{{ $reasonText }}</td>
                                        <td>
                                            <span class="badge {{ strtolower($it->status ?? $r->status) }}">{{ ucfirst(strtolower($it->status ?? $r->status)) }}</span>
                                            @php $hasChildren = isset($r->items) && is_countable($r->items) && $r->items->count() > 0; @endphp
                                            {{-- Per-item approve/deny buttons removed — use page-level controls below --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($isAdmin && $r->status === 'pending')
                        <div class="request-actions">
                            <button type="button" class="btn ok" id="btn-approve" style="padding:10px 18px;border-radius:8px">Approve</button>
                            <button type="button" class="btn rej" id="btn-reject" style="padding:10px 18px;border-radius:8px">Deny</button>
                        </div>
                    @endif

                    @php
                        $hasNonConsumable = false;
                        $returnableItems = [];
                        if(!empty($equipment) && strtolower(trim($equipment->type ?? '')) !== 'consumable') {
                            $hasNonConsumable = true;
                            $returnableItems[] = [
                                'name' => $equipment->name ?? ($r->item_name ?? 'Item'),
                                'request_item_id' => $r->id ?? null,
                                'equipment_id' => $equipment->id ?? null,
                            ];
                        } elseif(isset($r->items) && is_countable($r->items) && $r->items->count() > 0) {
                            foreach($r->items as $it) {
                                try {
                                    $type = null;
                                    if (is_object($it) && isset($it->equipment) && $it->equipment) {
                                        $type = $it->equipment->type ?? null;
                                    }
                                    if (empty($type) && is_object($it)) {
                                        $type = $it->type ?? null;
                                    }
                                    if (empty($type) && is_array($it)) {
                                        $type = $it['type'] ?? null;
                                    }
                                    if (!empty($type) && strtolower(trim($type)) !== 'consumable') {
                                        $hasNonConsumable = true;
                                        $returnableItems[] = [
                                            'name' => $it->item_name ?? ($it->equipment->name ?? 'Item'),
                                            'request_item_id' => $it->id ?? null,
                                            'equipment_id' => is_object($it) ? ($it->equipment_id ?? null) : ($it['equipment_id'] ?? null),
                                        ];
                                    }
                                } catch (\Throwable $_) {
                                    // ignore
                                }
                            }
                        }
                    @endphp

                    @if($isAdmin && in_array($r->status, ['approved','waiting']) && $hasNonConsumable)
                        <div class="request-actions">
                            <button type="button" class="btn" id="btn-mark-returned" style="background:#6b7280;color:#fff;border:none">Mark returned</button>
                        </div>
                    @endif

                    @if(!empty($returnableItems))
                        <script>window.returnableItems = {!! json_encode($returnableItems) !!};</script>
                    @else
                        <script>window.returnableItems = [];</script>
                    @endif
                    @php
                        // Show print button when any child item is approved (multi-item requests)
                        $hasApprovedItem = false;
                        if (isset($r->items) && is_countable($r->items) && $r->items->count() > 0) {
                            foreach ($r->items as $it) {
                                $status = strtolower($it->status ?? ($it['status'] ?? ''));
                                if ($status === 'approved') { $hasApprovedItem = true; break; }
                            }
                        } else {
                            $hasApprovedItem = (strtolower($r->status ?? '') === 'approved');
                        }
                    @endphp
                    @if($isAdmin && ($r->status === 'approved' || !empty($hasApprovedItem)))
                        <div class="request-actions">
                            <a href="/requests/{{ $r->uuid }}/print" target="_blank" class="btn" style="background:#2563eb;color:#fff;border:none">Print Form</a>
                        </div>
                    @endif
                </div>
            </div>
        </main>

        <!-- Approval/Denial Modal -->
        <div class="approval-backdrop" id="approvalBackdrop"></div>
        <div class="approval-modal" id="approvalModal">
            <div class="approval-header">
                <h2 id="approvalTitle">Approve Request</h2>
                <button class="approval-close" id="approvalCloseBtn">&times;</button>
            </div>
            <div class="approval-body">
                <div id="approvalItemsContainer" style="display:none;margin-bottom:12px"></div>
                <div class="approval-item-header">
                    <div class="approval-item-title" id="approvalItemName">Equipment Name</div>
                    <div class="approval-item-category" id="approvalItemCategoryHighlight">Category</div>
                    <div class="approval-item-meta" style="margin-top:12px">
                        <div>
                            <div class="meta-label">Type</div>
                            <div class="meta-item" id="approvalItemType">—</div>
                        </div>
                        <div>
                            <div class="meta-label">Serial</div>
                            <div class="meta-item" id="approvalItemSerial">—</div>
                        </div>
                        <div>
                            <div class="meta-label">Location</div>
                            <div class="meta-item" id="approvalItemLocation">—</div>
                        </div>
                        <div>
                            <div class="meta-label">Current Stock</div>
                            <div class="meta-item" id="approvalItemQuantity">0</div>
                        </div>
                    </div>
                </div>

                <div id="approvalStockAvailable" class="approval-stock-available" style="display:none"></div>
                <div id="approvalStockWarning" class="approval-stock-warning" style="display:none"></div>

                <form id="approvalForm">
                    <div class="approval-form-group">
                        <label class="approval-label">Admin Notes</label>
                        <textarea class="approval-textarea" id="approvalNotes" placeholder="Add any notes or comments about this approval/denial (optional)"></textarea>
                    </div>
                </form>

                <div class="approval-actions">
                    <button type="button" class="approval-btn approval-btn-approve" id="approvalConfirmBtn">Approve</button>
                    <button type="button" class="approval-btn approval-btn-deny" id="approvalDenyBtn">Deny</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark Returned Modal -->
    <div class="approval-backdrop" id="returnBackdrop" style="display:none"></div>
    <div class="approval-modal" id="returnModal" style="display:none;max-width:640px">
        <div class="approval-header" style="background:linear-gradient(135deg,#6b7280,#4b5563);">
            <h2 id="returnTitle">Mark Returned</h2>
            <button class="approval-close" id="returnClose">&times;</button>
        </div>
        <div class="approval-body">
            <p style="margin:0 0 12px">This will mark the following non-consumable item(s) as <strong>returned</strong> and restore inventory for the issued quantities. Are you sure you want to continue?</p>
            <div style="max-height:220px;overflow:auto;border:1px solid #eef2ff;padding:10px;border-radius:8px;margin-bottom:12px;background:#fbfdff">
                <ul id="return-item-list" style="margin:0;padding-left:16px"></ul>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end">
                <button id="returnCancel" class="btn" style="background:#f3f4f6;color:#111">Cancel</button>
                <button id="returnConfirm" class="btn ok" style="background:#6b7280;border:none">Confirm Return</button>
            </div>
        </div>
    </div>

    <form id="logout-form" method="POST" action="/logout" style="display:none">@csrf</form>

    <script>
        // Apply shared formatLocalISO to elements with data-datetime
        (function(){
            function applyLocalTimes(){
                const els = document.querySelectorAll('[data-datetime]');
                els.forEach(el=>{
                    const iso = el.getAttribute('data-datetime');
                    if(!iso) return;
                    try{ el.textContent = formatLocalISO(iso); }catch(e){ el.textContent = iso }
                });
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', applyLocalTimes);
            else applyLocalTimes();
        })();
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
                    let items = [];
                    if(Array.isArray(data)) items = data;
                    else if(data.items) items = data.items;
                    else if(data.pending) items = data.pending;

                    const cnt = (data.count !== undefined) ? data.count : items.length;
                    if(cnt){
                        countEl.style.display = '';
                        countEl.textContent = cnt;
                    } else {
                        countEl.style.display = 'none';
                    }

                    const isAdmin = ({{ auth()->user() ? 'true' : 'false' }} && '{{ auth()->user()->name }}'.toLowerCase() === 'admin');
                    renderItems(items, isAdmin);
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

            document.addEventListener('click', function(e){
                if(!bell.contains(e.target)){
                    visible = false; dropdown.classList.remove('show');
                }
            });

            fetchNotifs();
            setInterval(fetchNotifs, 8000);
        })();

        /* Sidebar toggle/overlay behaviour handled by the shared sidebar partial (avoid duplicate handlers) */

        // helper to show transient toasts (success/error)
        function showToast(message, type) {
            const id = (type === 'error') ? 'toast-error' : 'toast-success';
            let el = document.getElementById(id);
            if (!el) {
                el = document.createElement('div');
                el.id = id;
                el.className = 'toast' + (type === 'error' ? ' error' : '');
                document.body.appendChild(el);
            }
            el.textContent = message;
            el.style.display = '';
            setTimeout(function(){ el.classList.add('show'); }, 50);
            setTimeout(function(){ el.classList.remove('show'); }, 4200);
        }

        // Delete confirmation modal (reusable)
        (function(){
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div class="approval-backdrop" id="deleteBackdropReq" style="display:none"></div>
                <div class="approval-modal" id="deleteModalReq" style="display:none">
                    <div class="approval-header">
                        <h2 id="deleteTitleReq">Confirm Deletion</h2>
                        <button class="approval-close" id="deleteCloseReq">&times;</button>
                    </div>
                    <div class="approval-body">
                        <p id="deleteMessageReq">Are you sure you want to delete this item/request?</p>
                        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:18px">
                            <button id="deleteCancelReq" class="btn ghost">Cancel</button>
                            <button id="deleteConfirmReq" class="btn delete">Delete</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(wrapper);

            const deleteModal = document.getElementById('deleteModalReq');
            const deleteBackdrop = document.getElementById('deleteBackdropReq');
            const deleteClose = document.getElementById('deleteCloseReq');
            const deleteCancel = document.getElementById('deleteCancelReq');
            const deleteConfirm = document.getElementById('deleteConfirmReq');
            const deleteMessage = document.getElementById('deleteMessageReq');
            let pendingHref = null;

            function show(name, ref){
                deleteMessage.innerHTML = '<strong>' + (name || 'This item') + '</strong> will be permanently deleted. <br><small>Reference: ' + (ref || '') + '</small>';
                deleteBackdrop.style.display = 'block'; deleteModal.style.display = 'block';
                setTimeout(()=>{ deleteBackdrop.classList.add('show'); deleteModal.classList.add('show'); }, 10);
            }
            function hide(){ deleteModal.classList.remove('show'); deleteBackdrop.classList.remove('show'); setTimeout(()=>{ deleteBackdrop.style.display='none'; deleteModal.style.display='none'; },220); pendingHref=null; }

            document.addEventListener('click', function(e){
                const a = e.target.closest('a.btn.delete');
                if(!a) return;
                e.preventDefault();
                let name = a.dataset.name || a.getAttribute('data-name') || document.title || null;
                pendingHref = a.getAttribute('href');
                show(name, pendingHref);
            });

            if(deleteClose) deleteClose.addEventListener('click', hide);
            if(deleteCancel) deleteCancel.addEventListener('click', hide);
            if(deleteBackdrop) deleteBackdrop.addEventListener('click', hide);
            if(deleteConfirm) deleteConfirm.addEventListener('click', function(){ if(!pendingHref) return hide(); window.location.href = pendingHref; });
        })();

            function doDetailAction(id, action, btn) {
                btn.disabled = true;
                const notes = document.getElementById('approvalNotes').value;
                // prefer dataset, but fall back to the currentEquipmentData populated when modal opened
                const equipmentId = (btn && btn.dataset && btn.dataset.equipmentId) ? btn.dataset.equipmentId : (currentEquipmentData ? currentEquipmentData.id : null);
                const requestItemId = (btn && btn.dataset && btn.dataset.requestItemId) ? btn.dataset.requestItemId : (currentEquipmentData ? currentEquipmentData.request_item_id : null);
                // determine quantity: for single-item approvals use the request's requested_quantity (no modal input)
                const quantity = (action === 'approve') ? ((currentEquipmentData && currentEquipmentData.requested_quantity !== undefined) ? currentEquipmentData.requested_quantity : null) : null;

                const payload = { action: action, notes: notes, quantity: quantity, equipment_id: equipmentId, request_item_id: requestItemId };
            // safety: if modal was opened for a specific item but request_item_id is missing, abort to avoid parent-level fallback
            if (currentIsItem && !requestItemId) {
                console.error('Per-item action aborted: missing request_item_id', payload);
                alert('Request item identifier missing — action aborted');
                btn.disabled = false;
                return;
            }

            console.log('Sending approval payload', payload);

            fetch('/notifications/requests/'+encodeURIComponent(id)+'/action', {
                method: 'POST',
                headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            }).then(r=>r.json()).then(j=>{
                if (j.ok) {
                    // show colored toast, close modal and reload shortly after
                    if(action === 'approve') showToast('Request approved', 'success');
                    else showToast('Request rejected', 'error');
                    closeApprovalModal();
                    setTimeout(function(){ location.reload(); }, 700);
                } else {
                    alert('Failed'); btn.disabled=false;
                }
            }).catch(e=>{alert('Error');btn.disabled=false});
        }

        // Modal functions
        let currentAction = 'approve';
        let currentRequestId = '';
        let currentEquipmentData = null;
        let currentIsItem = false; // true when modal opened for a specific request item

        function openApprovalModal(action, requestId, equipmentData) {
            currentAction = action;
            currentRequestId = requestId;
            currentEquipmentData = equipmentData;
            currentIsItem = !!(equipmentData && equipmentData.request_item_id);
            const modal = document.getElementById('approvalModal');
            const backdrop = document.getElementById('approvalBackdrop');
            const title = document.getElementById('approvalTitle');
            const confirmBtn = document.getElementById('approvalConfirmBtn');
            const denyBtn = document.getElementById('approvalDenyBtn');
            const itemsContainer = document.getElementById('approvalItemsContainer');
            const stockWarning = document.getElementById('approvalStockWarning');
            const stockAvailable = document.getElementById('approvalStockAvailable');
            const quantityNote = document.getElementById('approvalQuantityNote');

            // If no equipmentData provided, prepare a placeholder
            if (!equipmentData) equipmentData = { name: 'Multiple Items', category: '—', type: '—', serial: '—', location: '—', quantity: 0, requested_quantity: 0, request_item_id: null, id: null };

            // detect multi-item: either provided by caller, or infer from the table rows on this page
            let isBatch = Array.isArray(equipmentData.items) && equipmentData.items.length > 0;
            if (!isBatch) {
                const rows = Array.from(document.querySelectorAll('.items-table tbody tr'));
                if (rows.length > 1) {
                    const items = [];
                    rows.forEach(row => {
                        const a = row.querySelector('.item-link');
                        if (!a) return;
                        try {
                            const d = a.dataset && a.dataset.equipment ? JSON.parse(a.dataset.equipment) : null;
                            if (!d) return;
                            const requested = parseInt(row.children[1].textContent.trim()) || (d.requested_quantity || 1);
                            d.requested_quantity = requested;
                            items.push(d);
                        } catch (err) { /* ignore */ }
                    });
                    if (items.length) { equipmentData.items = items; isBatch = true; }
                }
            }

            // If batch request, render items list and hide single-item header
            if (isBatch) {
                currentIsItem = false;
                currentEquipmentData = equipmentData; // keep items there
                document.querySelector('.approval-item-header').style.display = 'none';
                // build a compact table for batch approvals
                itemsContainer.innerHTML = '';
                let html = '<div style="overflow:auto"><table class="batch-table"><thead><tr><th style="width:36px"></th><th>Item</th><th>Details</th><th>Requested</th><th>Available</th><th style="width:120px">Issue Qty</th><th style="width:110px">Note</th></tr></thead><tbody>';
                equipmentData.items.forEach((it, idx) => {
                    const avail = parseInt(it.quantity) || 0;
                    const req = parseInt(it.requested_quantity) || 1;
                    const max = Math.max(0, avail);
                    const initial = Math.min(req, max);
                    html += `<tr data-idx="${idx}">` +
                        `<td><input type="checkbox" class="batch-include" data-idx="${idx}" checked></td>` +
                        `<td><div style="font-weight:800">${escapeHtml(it.name || '—')}</div></td>` +
                        `<td class="small-muted">${escapeHtml(it.type || '—')} · ${escapeHtml(it.serial || '—')} · ${escapeHtml(it.location || '—')}</td>` +
                        `<td><strong>${req}</strong></td>` +
                        `<td><strong>${avail}</strong></td>` +
                        `<td style="text-align:right"><input data-idx="${idx}" class="approval-input batch-qty" type="number" min="0" max="${max}" value="${initial}" style="width:90px"></td>` +
                        `<td style="text-align:right"><button type="button" class="batch-note-toggle" data-idx="${idx}">Note</button><div class="batch-note" id="batch-note-${idx}" style="display:none"><textarea class="approval-textarea batch-note-text" data-idx="${idx}" placeholder="Optional note" style="min-height:64px;margin-top:8px"></textarea></div></td>` +
                    `</tr>`;
                });
                html += '</tbody></table></div>';
                itemsContainer.innerHTML = html;
                itemsContainer.style.display = 'block';

                // show summary stock warnings for batch if any low/zero items
                stockWarning.style.display = 'none';
                stockAvailable.style.display = 'none';

                title.textContent = (action === 'approve') ? 'Approve Multiple Items' : 'Deny Multiple Items';
                confirmBtn.style.display = (action === 'approve') ? 'block' : 'none';
                denyBtn.style.display = (action === 'approve') ? 'none' : 'block';

            } else {
                // Single item flow (existing behaviour)
                document.querySelector('.approval-item-header').style.display = 'block';
                itemsContainer.style.display = 'none';

                // Populate equipment details with highlights
                document.getElementById('approvalItemName').textContent = equipmentData.name;
                document.getElementById('approvalItemCategoryHighlight').innerHTML = '<span style="color:#7c3aed;font-weight:800">' + (equipmentData.category || '—') + '</span>';
                document.getElementById('approvalItemType').textContent = equipmentData.type || '—';
                document.getElementById('approvalItemSerial').textContent = equipmentData.serial || '—';
                document.getElementById('approvalItemLocation').textContent = equipmentData.location || '—';
                document.getElementById('approvalItemQuantity').textContent = equipmentData.quantity + ' unit(s)';

                const availableQty = parseInt(equipmentData.quantity) || 0;
                const requestedQty = parseInt(equipmentData.requested_quantity) || 1;

                // Check stock availability
                stockWarning.style.display = 'none';
                stockAvailable.style.display = 'none';

                if (availableQty <= 0) {
                    stockWarning.innerHTML = '<strong>⚠️ Out of Stock!</strong> No units available for issue. You can deny this request or add a note about restocking.';
                    stockWarning.style.display = 'block';
                    // no per-modal quantity input; single-item approvals will use the request's requested quantity
                } else if (availableQty < requestedQty) {
                    stockWarning.innerHTML = '<strong>⚠️ Limited Stock!</strong> Only ' + availableQty + ' unit(s) available, but ' + requestedQty + ' were requested.';
                    stockWarning.style.display = 'block';
                    stockAvailable.innerHTML = '✓ You can issue up to <strong>' + availableQty + ' units</strong> from available inventory';
                    stockAvailable.style.display = 'block';
                    if (quantityNote) quantityNote.textContent = 'Max available: ' + availableQty + ' units';
                } else {
                    stockAvailable.innerHTML = '✓ Full stock available - All ' + requestedQty + ' unit(s) can be issued';
                    stockAvailable.style.display = 'block';
                    if (quantityNote) quantityNote.textContent = 'Max available: ' + availableQty + ' units';
                }

                if (action === 'approve') {
                    title.textContent = 'Approve Request';
                    confirmBtn.style.display = 'block';
                    denyBtn.style.display = 'none';
                } else {
                    title.textContent = 'Deny Request';
                    confirmBtn.style.display = 'none';
                    denyBtn.style.display = 'block';
                }

                // Set equipment ID on buttons
                confirmBtn.dataset.equipmentId = equipmentData.id;
                denyBtn.dataset.equipmentId = equipmentData.id;
                confirmBtn.dataset.requestItemId = equipmentData.request_item_id ?? '';
                denyBtn.dataset.requestItemId = equipmentData.request_item_id ?? '';
            }

            // Reset admin notes
            document.getElementById('approvalNotes').value = '';

            modal.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        // helper to escape HTML for insertion into templates
        function escapeHtml(str){
            if(!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Send batch approve/reject. If itemsPayload includes per-item `decision`, send per-item requests
        async function sendBatchAction(requestId, action, itemsPayload, btn){
            if (!itemsPayload || !itemsPayload.length) return alert('No items selected');
            btn.disabled = true;
            const notes = document.getElementById('approvalNotes').value;

            // If items include per-item decisions (mixed approve/reject), send each item as its own request
            const hasDecision = itemsPayload.some(i => i.decision);
            if (hasDecision) {
                try {
                    const results = await Promise.all(itemsPayload.map(async (it) => {
                        const itemAction = it.decision || action;
                        const payload = {
                            action: itemAction,
                            notes: notes,
                            quantity: (itemAction === 'approve') ? (it.issue_quantity || 0) : 0,
                            equipment_id: it.equipment_id,
                            request_item_id: it.request_item_id
                        };
                        try {
                            const res = await fetch('/notifications/requests/'+encodeURIComponent(requestId)+'/action', {
                                method: 'POST',
                                headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                                credentials: 'same-origin',
                                body: JSON.stringify(payload)
                            });
                            const j = await res.json().catch(()=>({ok:false}));
                            return j;
                        } catch(e) { return { ok: false }; }
                    }));

                    const anyOk = results.some(r => r && r.ok);
                    if (anyOk) {
                        showToast('Request updated', 'success');
                        closeApprovalModal();
                        setTimeout(()=> location.reload(), 700);
                        return;
                    } else {
                        alert('Failed to update items'); btn.disabled = false; return;
                    }
                } catch(e) {
                    alert('Error updating items'); btn.disabled = false; return;
                }
            }

            // Fallback: send a single batch payload (legacy)
            const payload = { action: action, notes: notes, items: itemsPayload };
            try{
                const res = await fetch('/notifications/requests/'+encodeURIComponent(requestId)+'/action', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const j = await res.json();
                if (j && j.ok) {
                    if(action === 'approve') showToast('Request approved', 'success'); else showToast('Request rejected', 'error');
                    closeApprovalModal();
                    setTimeout(()=> location.reload(), 700);
                } else {
                    alert('Failed'); btn.disabled = false;
                }
            } catch(e){ alert('Error'); btn.disabled = false; }
        }

        function closeApprovalModal() {
            const modal = document.getElementById('approvalModal');
            const backdrop = document.getElementById('approvalBackdrop');
            modal.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Modal event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtn = document.getElementById('approvalCloseBtn');
            const backdrop = document.getElementById('approvalBackdrop');
            const confirmBtn = document.getElementById('approvalConfirmBtn');
            const denyBtn = document.getElementById('approvalDenyBtn');

            if (!closeBtn || !backdrop) return;

            closeBtn.addEventListener('click', closeApprovalModal);
            backdrop.addEventListener('click', closeApprovalModal);

            // no modal quantity input to validate; batch inputs are handled on submit

            confirmBtn.addEventListener('click', function() {
                // if batch view is shown, collect per-item quantities and submit batch
                const itemsContainer = document.getElementById('approvalItemsContainer');
                if (itemsContainer && itemsContainer.style.display !== 'none') {
                    const baseItems = (currentEquipmentData && Array.isArray(currentEquipmentData.items)) ? currentEquipmentData.items : [];
                    const rows = Array.from(itemsContainer.querySelectorAll('tbody tr'));
                    const itemsPayload = [];
                    rows.forEach(row => {
                        const idx = parseInt(row.getAttribute('data-idx')) || 0;
                        const includeEl = row.querySelector('.batch-include');
                        const qtyEl = row.querySelector('.batch-qty');
                        const noteEl = row.querySelector('.batch-note-text');
                        const val = qtyEl ? (parseInt(qtyEl.value) || 0) : 0;
                        const note = noteEl ? noteEl.value.trim() : null;
                        const base = baseItems[idx];
                        if(!base) return;
                        const decided = (includeEl && includeEl.checked) ? 'approve' : 'reject';
                        itemsPayload.push({
                            request_item_id: base.request_item_id ?? null,
                            equipment_id: base.id ?? null,
                            issue_quantity: (decided === 'approve') ? val : 0,
                            requested_quantity: base.requested_quantity ?? 1,
                            note: note,
                            decision: decided
                        });
                    });
                    // ensure at least one item is being approved with a positive issue quantity
                    const totalApproved = itemsPayload.reduce((s,i)=>s+((i.decision === 'approve') ? (i.issue_quantity||0) : 0),0);
                    if (totalApproved <= 0) { alert('Please select and enter quantity for at least one item to approve'); return; }
                    confirmBtn.disabled = true;
                    sendBatchAction(currentRequestId, 'approve', itemsPayload, confirmBtn);
                    return;
                }
                // single-item flow: use the request's requested_quantity
                if (!currentEquipmentData) { alert('Equipment data not found'); return; }
                const qty = parseInt(currentEquipmentData.requested_quantity) || 0;
                if (qty <= 0) { alert('Requested quantity missing or zero'); return; }
                confirmBtn.disabled = true;
                // doDetailAction will read quantity from currentEquipmentData
                doDetailAction(currentRequestId, 'approve', confirmBtn);
            });

            denyBtn.addEventListener('click', function() {
                denyBtn.disabled = true;
                doDetailAction(currentRequestId, 'reject', denyBtn);
            });

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && document.getElementById('approvalModal').classList.contains('show')) {
                    closeApprovalModal();
                }
            });
        });

        // wire approve/reject on this page
        (function(){
            const id = '{{ $r->uuid }}';
            const approve = document.getElementById('btn-approve');
            const reject = document.getElementById('btn-reject');
            
            // Get equipment data from the request
            const equipmentData = {
                id: '{{ $equipment?->id ?? $r->item_id ?? 1 }}',
                name: '{{ $equipment?->name ?? $r->item_name }}',
                category: '{{ $equipment?->category ?? "—" }}',
                type: '{{ $equipment?->type ?? "—" }}',
                serial: '{{ $equipment?->serial ?? "—" }}',
                location: '{{ $equipment?->location ?? "—" }}',
                quantity: {{ $equipment?->quantity ?? $r->quantity ?? 0 }},
                requested_quantity: {{ $r->quantity ?? 1 }}
            };

            if(approve) {
                approve.addEventListener('click', function(e) {
                    e.preventDefault();
                    openApprovalModal('approve', id, equipmentData);
                });
            }
            
            if(reject) {
                reject.addEventListener('click', function(e) {
                    e.preventDefault();
                    openApprovalModal('deny', id, equipmentData);
                });
            }
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
        // Delegate per-item approve/deny buttons to open the approval modal with item data
        (function(){
            document.addEventListener('click', function(e){
                const approve = e.target.closest('.item-approve');
                const deny = e.target.closest('.item-deny');
                const btn = approve || deny;
                if(!btn) return;
                e.preventDefault();
                try{
                    const data = btn.dataset && btn.dataset.equipment ? JSON.parse(btn.dataset.equipment) : null;
                    if(!data) return alert('Item data missing');
                    const id = '{{ $r->uuid }}';
                    const action = approve ? 'approve' : 'reject';
                    openApprovalModal(action, id, data);
                }catch(err){ console.error('Failed to parse equipment payload', err); alert('Invalid item data'); }
            });
        })();
    </script>
    <script>
        // Mark returned modal flow
        (function(){
            const btn = document.getElementById('btn-mark-returned');
            const backdrop = document.getElementById('returnBackdrop');
            const modal = document.getElementById('returnModal');
            const closeBtn = document.getElementById('returnClose');
            const cancelBtn = document.getElementById('returnCancel');
            const confirmBtn = document.getElementById('returnConfirm');
            const listEl = document.getElementById('return-item-list');

            function openReturnModal(items){
                listEl.innerHTML = '';
                if(!items || !items.length){
                    listEl.innerHTML = '<li>No non-consumable items found.</li>';
                } else {
                    items.forEach(it=>{
                        const li = document.createElement('li');
                        li.textContent = it.name || 'Unnamed item';
                        listEl.appendChild(li);
                    });
                }
                backdrop.style.display = 'block'; modal.style.display = 'block';
                setTimeout(()=>{ backdrop.classList.add('show'); modal.classList.add('show'); }, 10);
                document.body.style.overflow = 'hidden';
            }

            function closeReturnModal(){
                modal.classList.remove('show'); backdrop.classList.remove('show');
                setTimeout(()=>{ backdrop.style.display='none'; modal.style.display='none'; },220);
                document.body.style.overflow = '';
            }

            if(closeBtn) closeBtn.addEventListener('click', closeReturnModal);
            if(cancelBtn) cancelBtn.addEventListener('click', closeReturnModal);

            if(btn){
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    openReturnModal(window.returnableItems || []);
                });
            }

            if(confirmBtn){
                confirmBtn.addEventListener('click', async function(){
                    confirmBtn.disabled = true;
                    try{
                        const res = await fetch('/requests/{{ $r->uuid }}/return', {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            credentials: 'same-origin',
                            body: JSON.stringify({})
                        });
                        if(res.ok){
                            showToast('Request marked returned', 'success');
                            closeReturnModal();
                            setTimeout(()=> location.reload(), 700);
                        } else {
                            showToast('Failed to mark returned', 'error');
                            confirmBtn.disabled = false;
                        }
                    }catch(err){ showToast('Error marking returned','error'); confirmBtn.disabled = false }
                });
            }
        })();
    </script>

    <!-- Equipment Details Modal (uses approval modal styles for consistency) -->
    <div class="approval-backdrop" id="equipmentBackdrop"></div>
    <div class="approval-modal" id="equipmentModal">
        <div class="approval-header">
            <h2 id="equipmentModalName">Equipment Details</h2>
            <button class="approval-close" id="equipmentModalClose">&times;</button>
        </div>
        <div class="approval-body">
            <img id="equipmentModalImage" src="" alt="Equipment" class="modal-image" style="display:none;display:block;width:100%;height:auto;max-height:60vh;object-fit:contain;border-radius:12px;margin-bottom:16px;background:#f3f4f6;padding:6px;box-sizing:border-box">
            <div id="equipmentModalNoImage" style="width:100%;height:auto;max-height:60vh;background:#e5e7eb;border-radius:12px;margin-bottom:16px;display:flex;align-items:center;justify-content:center;color:#6b7280;font-size:16px;padding:12px;box-sizing:border-box">📷 No image available</div>
            <div class="approval-item-meta">
                <div>
                    <div class="meta-label">Category</div>
                    <div class="meta-item" id="equipmentModalCategory">—</div>
                </div>
                <div>
                    <div class="meta-label">Type</div>
                    <div class="meta-item" id="equipmentModalType">—</div>
                </div>
                <div>
                    <div class="meta-label">Location</div>
                    <div class="meta-item" id="equipmentModalLocation">—</div>
                </div>
                <div>
                    <div class="meta-label">Quantity</div>
                    <div class="meta-item" id="equipmentModalQuantity">0</div>
                </div>
            </div>
            <div style="height:12px"></div>
            <div>
                <div class="meta-label">Serial</div>
                <div class="meta-item" id="equipmentModalSerial">—</div>
            </div>
            <div style="height:8px"></div>
            <div>
                <div class="meta-label">Notes</div>
                <div class="meta-item" id="equipmentModalNotes">—</div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            function openEquipmentModal(data){
                if(!data) return;
                const modal = document.getElementById('equipmentModal');
                const backdrop = document.getElementById('equipmentBackdrop');
                document.getElementById('equipmentModalName').textContent = data.name || 'Equipment Details';
                document.getElementById('equipmentModalCategory').textContent = data.category || '—';
                document.getElementById('equipmentModalType').textContent = data.type || '—';
                document.getElementById('equipmentModalLocation').textContent = data.location || '—';
                document.getElementById('equipmentModalQuantity').textContent = (data.quantity !== undefined) ? (data.quantity + ' unit(s)') : '0';
                document.getElementById('equipmentModalSerial').textContent = data.serial || '—';
                document.getElementById('equipmentModalNotes').textContent = data.notes || '—';

                const img = document.getElementById('equipmentModalImage');
                const noImg = document.getElementById('equipmentModalNoImage');
                if(data.image_path){
                    img.src = '/storage/' + data.image_path;
                    img.style.display = 'block';
                    noImg.style.display = 'none';
                } else {
                    img.style.display = 'none';
                    noImg.style.display = 'flex';
                }

                modal.classList.add('show');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeEquipmentModal(){
                const modal = document.getElementById('equipmentModal');
                const backdrop = document.getElementById('equipmentBackdrop');
                modal.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }

            // delegate clicks on equipment links
            document.addEventListener('click', function(e){
                const a = e.target.closest('.item-link');
                if(!a) return;
                e.preventDefault();
                try{
                    const data = a.dataset && a.dataset.equipment ? JSON.parse(a.dataset.equipment) : null;
                    openEquipmentModal(data);
                }catch(err){console.error('Invalid equipment payload', err)}
            });

            // close handlers
            const closeBtn = document.getElementById('equipmentModalClose');
            const backdrop = document.getElementById('equipmentBackdrop');
            if(closeBtn) closeBtn.addEventListener('click', closeEquipmentModal);
            if(backdrop) backdrop.addEventListener('click', closeEquipmentModal);
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ closeEquipmentModal(); } });
        })();
    </script>

    <script>
        /* Vehicle submenu toggle is handled in the shared sidebar partial to avoid duplicate handlers. */
    </script>
</body>
</html>
