@extends('layouts.app')

@section('head')
    <style>
        :root{--bg:#f6f8fb;--panel:#ffffff;--accent:#2563eb;--accent-2:#7c3aed;--muted:#6b7280;--muted-2:#94a3b8;--topbar-height:72px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,Arial,Helvetica;background:var(--bg);color:#0f172a}
        .panel{background:var(--panel);padding:14px;border-radius:12px;box-shadow:0 6px 20px rgba(15,23,42,0.04);width:min(1240px,calc(100% - 24px));margin:10px auto}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .field{display:flex;flex-direction:column;gap:6px}
        .field.full{grid-column:1/-1}
        input,select,textarea{width:100%;padding:10px;border:1px solid #e6e9ef;border-radius:8px;font:inherit}
        textarea{min-height:120px;resize:vertical}
        .btn{padding:8px 12px;border-radius:8px;border:1px solid #e6e9ef;background:#fff;cursor:pointer;text-decoration:none;color:#0f172a}
        .btn.primary{background:#2563eb;border:none;color:#fff}
        .btn.warn{background:#f59e0b;border:none;color:#fff}
        .btn.danger{background:#ef4444;border:none;color:#fff}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:14px}
        th,td{padding:10px 8px;border-bottom:1px solid #edf2f7;text-align:left;vertical-align:top}
        th{font-size:12px;text-transform:uppercase;letter-spacing:.3px;color:var(--muted);font-weight:700}
        .muted{color:var(--muted);font-size:13px}
        .actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .page-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
        .page-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .page-actions .btn{min-height:38px;font-size:14px;font-weight:600}
        .row-text{display:block;max-width:100%;word-break:break-word}
        .row-text .row-full{display:none}
        .row-text.expanded .row-short{display:none}
        .row-text.expanded .row-full{display:inline}
        .view-more-btn{border:none;background:transparent;color:var(--accent);cursor:pointer;font-size:12px;font-weight:600;padding:0;margin-left:6px;text-decoration:underline}
        .inline-edit{margin-top:8px;display:none}
        .inline-edit.show{display:block}
        .counter{font-size:12px;color:var(--muted);text-align:right;margin-top:6px}
        .report-floating{position:fixed;right:20px;bottom:20px;z-index:190;display:inline-flex;align-items:center;padding:10px 16px;border-radius:999px;background:#eef2ff;color:#1e3a8a;font-size:14px;font-weight:700;box-shadow:0 8px 20px rgba(2,6,23,0.16)}
        .edited{font-size:11px;color:#7c3aed;margin-top:4px}
        .confirm-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.45);display:none;z-index:230}
        .confirm-backdrop.show{display:block}
        .confirm-modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:min(420px,92vw);background:#fff;border-radius:12px;box-shadow:0 24px 60px rgba(2,6,23,.28);display:none;z-index:240;padding:16px}
        .confirm-modal.show{display:block}
        .confirm-title{font-weight:700;font-size:16px;margin:0 0 8px 0}
        .confirm-text{color:var(--muted);font-size:14px;line-height:1.45;margin:0}
        .confirm-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:14px}
        .process{margin:0;padding-left:18px;color:#334155}
        .process li{margin-bottom:4px}
        .toast{position:fixed;right:20px;bottom:90px;background:#10b981;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 10px 30px rgba(2,6,23,.2);z-index:200;display:none}
        .toast.show{display:block}
        .nav-overlay{position:fixed;left:0;right:0;top:var(--topbar-height);bottom:0;background:rgba(2,6,23,0.45);opacity:0;visibility:hidden;transition:opacity .18s ease;z-index:80}
        .nav-overlay.show{opacity:1;visibility:visible}
        @media(max-width:980px){.form-grid{grid-template-columns:1fr}}
        @media(max-width:900px){
            .sidebar{position:fixed;left:0;top:0;bottom:0;z-index:80;transform:translateX(-110%);height:100vh}
            .sidebar.open{transform:translateX(0)}
            .main{padding:16px}
            .page-actions{width:100%}
            .page-actions .btn{flex:1}
        }
        @media (max-width:900px) {
            table thead { display: none; }
            table, table tbody, table tr { display: block; width: 100%; }
            table tbody tr { margin-bottom: 12px; background: #fff; padding: 12px; border-radius: 10px; box-shadow: 0 8px 20px rgba(2,6,23,0.04); border: 1px solid rgba(14,21,40,0.04); }
            table tbody td { display: block; padding: 6px 0; border: none; }
            table tbody td:first-child { font-weight:700; margin-bottom:6px }
            table tbody td:nth-child(3)::before { content: 'Report: '; font-weight:700; color:var(--muted); }
            table tbody td:nth-child(4)::before { content: 'Actions: '; font-weight:700; color:var(--muted); }
            table tbody td::before { display:inline-block; margin-right:6px }
        }
    </style>
@endsection

@section('content')
    <div class="panel">
        <div class="page-head">
            <h2 style="margin:0">Vehicle Monitoring</h2>
            <div class="page-actions">
                <a href="/vehicle/monitoring/add{{ $selectedVehicle ? '?vehicle=' . $selectedVehicle->id : '' }}" class="btn primary">Add Monitoring Report</a>
                <a href="/vehicle" class="btn">Back to Vehicles</a>
            </div>
        </div>
        <div class="muted" style="margin-top:6px">Track all completed activities/reports for each specific vehicle with exact date and time.</div>
        <div class="field" style="margin-top:10px;max-width:420px">
            <label for="vehicle-filter">Vehicle</label>
            <select id="vehicle-filter">
                @forelse($vehicles as $v)
                    <option value="{{ $v->id }}" {{ ($selectedVehicle && $selectedVehicle->id === $v->id) ? 'selected' : '' }}>{{ $v->name }} ({{ $v->plate_number ?: 'No plate' }})</option>
                @empty
                    <option value="">No available vehicle</option>
                @endforelse
            </select>
            <small class="muted">Switch vehicle to view its monitoring history.</small>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin-top:0">Monitoring History {{ $selectedVehicle ? '— ' . $selectedVehicle->name : '' }}</h3>
        <div style="overflow:auto">
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Vehicle</th>
                    <th>Report</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->created_at ? $report->created_at->format('Y-m-d H:i') : '—' }}</td>
                        <td>
                            <div style="font-weight:700">{{ $selectedVehicle->name ?? '—' }}</div>
                            <div class="muted">{{ $selectedVehicle->plate_number ?? 'No plate' }}</div>
                        </td>
                        <td>
                            @php
                                $reportText = (string) ($report->report ?? '—');
                                $reportIsLong = \Illuminate\Support\Str::length($reportText) > 120;
                            @endphp
                            <div id="report-text-{{ $report->id }}" class="row-text">
                                <span class="row-short">{{ $reportIsLong ? \Illuminate\Support\Str::limit($reportText, 120) : $reportText }}</span>
                                @if($reportIsLong)
                                    <span class="row-full">{{ $reportText }}</span>
                                    <button type="button" class="view-more-btn" onclick="toggleReportText(this)">View more</button>
                                @endif
                            </div>
                            @if($report->updated_at && $report->created_at && $report->updated_at->gt($report->created_at))
                                <div class="edited">Edited: {{ $report->updated_at->format('Y-m-d H:i') }}</div>
                            @endif
                            <form id="edit-form-{{ $report->id }}" class="inline-edit" method="POST" action="/vehicle/monitoring/{{ $report->id }}/update">
                                @csrf
                                <textarea name="report" maxlength="2000" required>{{ $report->report }}</textarea>
                                <div class="actions" style="margin-top:6px">
                                    <button class="btn primary" type="submit">Save</button>
                                    <button class="btn" type="button" onclick="toggleEdit({{ $report->id }}, false)">Cancel</button>
                                </div>
                            </form>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn warn" type="button" onclick="toggleEdit({{ $report->id }}, true)">Edit</button>
                                <form class="js-delete-monitoring-form" method="POST" action="/vehicle/monitoring/{{ $report->id }}/delete">
                                    @csrf
                                    <button class="btn danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">No monitoring reports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if(session('success'))
        <div id="success-toast" class="toast">{{ session('success') }}</div>
    @endif

    <div id="delete-monitoring-confirm-backdrop" class="confirm-backdrop"></div>
    <div id="delete-monitoring-confirm-modal" class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-monitoring-confirm-title">
        <h4 id="delete-monitoring-confirm-title" class="confirm-title">Confirm Deletion</h4>
        <p class="confirm-text">Are you sure you want to delete this monitoring report? This action cannot be undone.</p>
        <div class="confirm-actions">
            <button id="delete-monitoring-confirm-no" type="button" class="btn">No</button>
            <button id="delete-monitoring-confirm-yes" type="button" class="btn danger">Yes, Delete</button>
        </div>
    </div>

    <div class="report-floating">Reports: {{ $reports->count() }}</div>
@endsection

@push('scripts')
    <script>
        (function(){ const toast = document.getElementById('success-toast'); if(!toast) return; toast.classList.add('show'); setTimeout(()=> toast.classList.remove('show'), 3500); })();

        (function(){
            const select = document.getElementById('vehicle-filter');
            if(!select) return;
            select.addEventListener('change', function(){
                if(!this.value) return;
                window.location.href = '/vehicle/monitoring?vehicle=' + encodeURIComponent(this.value);
            });
        })();

        function toggleEdit(id, show){
            const form = document.getElementById('edit-form-' + id);
            if(!form) return;
            form.classList.toggle('show', !!show);
        }

        function toggleReportText(button){
            const wrapper = button.closest('.row-text');
            if(!wrapper) return;
            const expanded = wrapper.classList.toggle('expanded');
            button.textContent = expanded ? 'View less' : 'View more';
        }

        (function(){
            const forms = document.querySelectorAll('.js-delete-monitoring-form');
            const backdrop = document.getElementById('delete-monitoring-confirm-backdrop');
            const modal = document.getElementById('delete-monitoring-confirm-modal');
            const yesBtn = document.getElementById('delete-monitoring-confirm-yes');
            const noBtn = document.getElementById('delete-monitoring-confirm-no');
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
@endpush
