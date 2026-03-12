@extends('layouts.app')

@section('head')
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}
        .bg{position:fixed;inset:0;background-image:url('/images/welcome-bg.jpg');background-size:cover;background-position:center;filter:brightness(0.6) saturate(0.95);z-index:-3}
        .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}
        .panel{background:var(--panel);padding:18px 20px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:min(1240px,calc(100% - 24px));margin:12px auto}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .btn.success{background:#10b981;border:none;color:#fff}
        .btn.warn{background:#f59e0b;border:none;color:#fff}
        .btn.danger{background:#ef4444;border:none;color:#fff}
        .actions{display:flex;gap:8px;flex-wrap:wrap}
        .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:8px}
        .page-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}
        .page-actions .btn{min-height:40px;font-size:14px;font-weight:600;display:inline-flex;align-items:center;justify-content:center}
        .section-title{margin:0;line-height:1.15}
        .section-subtitle{margin:0;color:var(--muted);font-size:13px;line-height:1.5}
        .table-wrap{overflow:auto;margin-top:8px}
        .delete-header{text-align:right}
        .delete-cell{text-align:right;white-space:nowrap}
        .status-badge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700}
        .status-badge.pending{background:#fef3c7;color:#92400e}
        .status-badge.needed{background:#dbeafe;color:#1d4ed8}
        .status-badge.denied{background:#fee2e2;color:#b91c1c}
        .review-cell{white-space:nowrap}
        .maintenance-row.is-highlighted{outline:2px solid #93c5fd;outline-offset:-2px;background:#f8fbff}
        .timeline-meta{font-size:12px;line-height:1.45}
        .row-text{display:block;max-width:100%;word-break:break-word}
        .row-text .row-full{display:none}
        .row-text.expanded .row-short{display:none}
        .row-text.expanded .row-full{display:inline}
        .view-more-btn{border:none;background:transparent;color:var(--accent);cursor:pointer;font-size:12px;font-weight:600;padding:0;margin-left:6px;text-decoration:underline}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:14px}
        th,td{padding:10px 8px;border-bottom:1px solid #edf2f7;text-align:left;vertical-align:top}
        th{font-size:12px;text-transform:uppercase;letter-spacing:.3px;color:var(--muted);font-weight:700}
        .muted{color:var(--muted);font-size:13px}
        .maintenance-row{cursor:pointer}
        .modal-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;z-index:210}
        .modal-backdrop.show{display:block}
        .modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:min(900px,94vw);max-height:90vh;background:#fff;border-radius:14px;box-shadow:0 30px 70px rgba(2,6,23,.3);display:none;z-index:220;overflow:auto}
        .modal.show{display:block}
        .modal-head{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #e5e7eb}
        .modal-close{border:none;background:#f3f4f6;color:#111827;width:32px;height:32px;border-radius:8px;cursor:pointer}
        .modal-body{padding:14px 16px}
        .modal-img{max-width:100%;max-height:72vh;display:block;margin:0 auto;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc}
        .confirm-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.45);display:none;z-index:230}
        .confirm-backdrop.show{display:block}
        .confirm-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:min(420px,92vw);background:#fff;border-radius:12px;box-shadow:0 24px 60px rgba(2,6,23,.28);display:none;z-index:240;padding:16px}
        .confirm-modal.show{display:block}
        .confirm-title{font-weight:700;font-size:16px;margin:0 0 8px 0}
        .confirm-text{color:var(--muted);font-size:14px;line-height:1.45;margin:0}
        .confirm-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:14px}
        .toast{position:fixed;right:20px;bottom:20px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,.2);z-index:200;display:none}
        .toast.show{display:block}
        .nav-overlay{position:fixed;left:0;right:0;top:var(--topbar-height);bottom:0;background:rgba(2,6,23,0.45);opacity:0;visibility:hidden;transition:opacity .18s ease;z-index:80}
        .nav-overlay.show{opacity:1;visibility:visible}
        @media(max-width:900px){.sidebar{position:fixed;left:0;top:0;bottom:0;z-index:80;transform:translateX(-110%);height:100vh}.sidebar.open{transform:translateX(0)}.main{padding:16px}}
        @media(max-width:900px){.page-actions{width:100%}.page-actions .btn{flex:1}}
        @media (max-width:900px) {
            table thead { display: none; }
            table, table tbody, table tr { display: block; width: 100%; }
            table tbody tr { margin-bottom: 12px; background: #fff; padding: 12px; border-radius: 10px; box-shadow: 0 8px 20px rgba(2,6,23,0.04); border: 1px solid rgba(14,21,40,0.04); }
            table tbody td { display: block; padding: 6px 0; border: none; }
            table tbody td:first-child { font-weight:700; margin-bottom:6px }
            table tbody td:nth-child(2)::before { content: 'Task: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(3)::before { content: 'Due: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(4)::before { content: 'Status: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(5)::before { content: 'Notes: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(6)::before { content: 'Timeline: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(7)::before { content: 'Review: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(8)::before { content: 'Delete: '; font-weight:700; color:var(--muted); }
            table tbody td::before { display:inline-block; margin-right:6px }
            .delete-header{text-align:left}
            .delete-cell{text-align:left}
        }
    </style>
@endsection

@section('content')
    <div class="panel">
        <div class="page-head">
            <div>
                <h2 class="section-title">Vehicle Maintenance</h2>
                <p class="section-subtitle">{{ $selectedVehicle ? 'Showing maintenance requests for ' . $selectedVehicle->name . '.' : 'All maintenance records are shown below.' }}</p>
            </div>
            <div class="page-actions">
                <a href="/vehicle/maintenance/add" class="btn primary">Add Maintenance</a>
                <a href="/vehicle" class="btn">Back to Vehicles</a>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3 class="section-title">All Maintenance List</h3>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Maintenance Task</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Timeline</th>
                    <th>Review</th>
                    <th class="delete-header">Delete</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $maintenance)
                    <tr class="maintenance-row {{ (($highlightedMaintenanceId ?? 0) === $maintenance->id) ? 'is-highlighted' : '' }}" onclick="openUploadedPhoto(this)" data-photo-url="{{ $maintenance->evidence_image_path ? asset('storage/' . $maintenance->evidence_image_path) : '' }}">
                        <td>
                            <div style="font-weight:700">{{ $maintenance->vehicle->name ?? '—' }}</div>
                            <div class="muted">{{ $maintenance->vehicle->plate_number ?? 'No plate' }}</div>
                        </td>
                        <td>
                            @php
                                $taskText = (string) ($maintenance->task ?? '—');
                                $taskIsLong = \Illuminate\Support\Str::length($taskText) > 70;
                            @endphp
                            <span class="row-text">
                                <span class="row-short">{{ $taskIsLong ? \Illuminate\Support\Str::limit($taskText, 70) : $taskText }}</span>
                                @if($taskIsLong)
                                    <span class="row-full">{{ $taskText }}</span>
                                    <button type="button" class="view-more-btn" onclick="toggleRowText(this, event)">View more</button>
                                @endif
                            </span>
                        </td>
                        <td>{{ $maintenance->due_date ? $maintenance->due_date->format('Y-m-d') : '—' }}</td>
                        <td onclick="event.stopPropagation()">
                            <span class="status-badge {{ $maintenance->status ?? 'needed' }}">{{ ucfirst($maintenance->status ?? 'needed') }}</span>
                        </td>
                        <td>
                            @php
                                $notesText = (string) ($maintenance->notes ?: '—');
                                $notesIsLong = \Illuminate\Support\Str::length($notesText) > 90;
                            @endphp
                            <span class="row-text">
                                <span class="row-short">{{ $notesIsLong ? \Illuminate\Support\Str::limit($notesText, 90) : $notesText }}</span>
                                @if($notesIsLong)
                                    <span class="row-full">{{ $notesText }}</span>
                                    <button type="button" class="view-more-btn" onclick="toggleRowText(this, event)">View more</button>
                                @endif
                            </span>
                        </td>
                        <td onclick="event.stopPropagation()">
                            <div class="muted timeline-meta">Reviewed: {{ $maintenance->reviewed_at ? $maintenance->reviewed_at->format('Y-m-d H:i') : '—' }}<br>Checked: {{ $maintenance->checked_at ? $maintenance->checked_at->format('Y-m-d H:i') : '—' }}<br>Updated: {{ $maintenance->updated_marker_at ? $maintenance->updated_marker_at->format('Y-m-d H:i') : '—' }}</div>
                        </td>
                        <td class="review-cell" onclick="event.stopPropagation()">
                            @if(($isAdmin ?? false) && ($maintenance->status ?? 'needed') === 'pending')
                                <div class="actions">
                                    <form method="POST" action="/vehicle/{{ $maintenance->vehicle_id }}/maintenance/{{ $maintenance->id }}/approve">@csrf<button class="btn primary" type="submit">Approve</button></form>
                                    <form method="POST" action="/vehicle/{{ $maintenance->vehicle_id }}/maintenance/{{ $maintenance->id }}/deny">@csrf<button class="btn warn" type="submit">Deny</button></form>
                                </div>
                            @elseif(($maintenance->status ?? 'needed') === 'needed')
                                <span class="muted">Approved</span>
                            @elseif(($maintenance->status ?? 'needed') === 'denied')
                                <span class="muted">Denied</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td class="delete-cell" onclick="event.stopPropagation()">
                            <form class="js-delete-maintenance-form" method="POST" action="/vehicle/{{ $maintenance->vehicle_id }}/maintenance/{{ $maintenance->id }}/delete">@csrf<button class="btn danger" type="submit">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">No maintenance entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div id="photo-modal-backdrop" class="modal-backdrop"></div>
    <div id="photo-modal" class="modal">
        <div class="modal-head">
            <div style="font-weight:700">Uploaded Photo</div>
            <button id="photo-modal-close" class="modal-close" type="button">✕</button>
        </div>
        <div class="modal-body">
            <img id="photo-modal-img" class="modal-img" alt="Uploaded photo" style="display:none">
            <div id="photo-modal-empty" class="muted" style="text-align:center;padding:10px 0">No uploaded photo for this maintenance item.</div>
        </div>
    </div>

    <div id="delete-confirm-backdrop" class="confirm-backdrop"></div>
    <div id="delete-confirm-modal" class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title">
        <h4 id="delete-confirm-title" class="confirm-title">Confirm Deletion</h4>
        <p class="confirm-text">Are you sure you want to delete this maintenance record? This action cannot be undone.</p>
        <div class="confirm-actions">
            <button id="delete-confirm-no" type="button" class="btn">No</button>
            <button id="delete-confirm-yes" type="button" class="btn danger">Yes, Delete</button>
        </div>
    </div>

    @if(session('success'))
        <div id="success-toast" class="toast">{{ session('success') }}</div>
    @endif
@endsection

@push('scripts')
    <script>
        (function(){ const toast = document.getElementById('success-toast'); if(!toast) return; toast.classList.add('show'); setTimeout(()=> toast.classList.remove('show'), 3500); })();

        (function(){
            const forms = document.querySelectorAll('.js-delete-maintenance-form');
            const backdrop = document.getElementById('delete-confirm-backdrop');
            const modal = document.getElementById('delete-confirm-modal');
            const yesBtn = document.getElementById('delete-confirm-yes');
            const noBtn = document.getElementById('delete-confirm-no');
            let targetForm = null;
            if(!forms.length || !backdrop || !modal || !yesBtn || !noBtn) return;

            function openConfirm(form){
                targetForm = form;
                backdrop.classList.add('show');
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeConfirm(){
                backdrop.classList.remove('show');
                modal.classList.remove('show');
                document.body.style.overflow = '';
                targetForm = null;
            }

            forms.forEach(function(form){
                form.addEventListener('submit', function(e){
                    if(form.dataset.confirmed === '1') return;
                    e.preventDefault();
                    openConfirm(form);
                });
            });

            yesBtn.addEventListener('click', function(){
                if(!targetForm) return closeConfirm();
                targetForm.dataset.confirmed = '1';
                targetForm.submit();
            });

            noBtn.addEventListener('click', closeConfirm);
            backdrop.addEventListener('click', closeConfirm);
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape' && modal.classList.contains('show')) closeConfirm();
            });
        })();

        function openUploadedPhoto(row){
            const url = row.dataset.photoUrl || '';
            const backdrop = document.getElementById('photo-modal-backdrop');
            const modal = document.getElementById('photo-modal');
            const img = document.getElementById('photo-modal-img');
            const empty = document.getElementById('photo-modal-empty');
            if(!backdrop || !modal || !img || !empty) return;
            if(url){
                img.src = url;
                img.style.display = 'block';
                empty.style.display = 'none';
            } else {
                img.style.display = 'none';
                empty.style.display = 'block';
            }
            backdrop.classList.add('show');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function toggleRowText(button, event){
            if(event) event.stopPropagation();
            const wrapper = button.closest('.row-text');
            if(!wrapper) return;
            const expanded = wrapper.classList.toggle('expanded');
            button.textContent = expanded ? 'View less' : 'View more';
        }

        (function(){
            const backdrop = document.getElementById('photo-modal-backdrop');
            const modal = document.getElementById('photo-modal');
            const closeBtn = document.getElementById('photo-modal-close');
            if(!backdrop || !modal || !closeBtn) return;
            function closeModal(){
                backdrop.classList.remove('show');
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
            closeBtn.addEventListener('click', closeModal);
            backdrop.addEventListener('click', closeModal);
            document.addEventListener('keydown', function(e){
                if(e.key === 'Escape' && modal.classList.contains('show')) closeModal();
            });
        })();

        (function(){
            const dd = document.querySelector('.notif-dropdown');
            if(!dd) return;
            dd.addEventListener('click', function(e){
                if(e.target.closest('.actions')) return;
                const item = e.target.closest('.item');
                if(!item) return;
                const url = item.dataset.url || item.getAttribute('data-url');
                if(url) {
                    window.location.href = url;
                    return;
                }
                const id = item.dataset.uuid || item.getAttribute('data-uuid') || item.getAttribute('data-id');
                if(id) window.location.href = '/requests/' + id;
            });
        })();
    </script>
@endpush
