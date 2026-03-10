@php $bgUrl = asset('images/welcome-bg.jpg'); $logoUrl = asset('images/favi.png'); @endphp
<link rel="preload" as="image" href="{{ $bgUrl }}">
<link rel="preload" as="image" href="{{ $logoUrl }}">
<style>
    /* Placeholder and class-based swap for the full-bleed background */
    .bg{position:fixed;inset:0;background-image:linear-gradient(180deg,#e9f0fb,#f6fbf9);background-size:cover;background-position:center center;filter:brightness(0.6) saturate(0.95);z-index:-3;transition:opacity .28s ease}
    .overlay{position:fixed;inset:0;background:linear-gradient(180deg,rgba(2,6,23,0.28),rgba(2,6,23,0.4));z-index:-2}
    .bg.has-image{background-image:url('{{ $bgUrl }}');background-size:cover;background-position:center center}
    /* Shared Topbar styles applied site-wide for consistent header layout */
    :root{--topbar-height:72px}
    .topbar{position:fixed;left:0;right:0;top:0;height:var(--topbar-height,72px);background:rgba(255,255,255,0.95);backdrop-filter:saturate(1.05) blur(4px);box-shadow:0 6px 24px rgba(2,6,23,0.08);z-index:60}
    .topbar-inner{max-width:none;width:100%;margin:0;padding:12px 12px 12px 0;display:flex;justify-content:space-between;align-items:center}
    .topbar .left-area{display:flex;align-items:center;gap:12px}
    .topbar .branding{display:flex;flex-direction:column}
    .topbar .brand-title{display:flex;align-items:center;gap:6px;font-weight:700}
    .topbar .brand-subtitle{font-size:12px;color:var(--muted, #6b7280)}
    .notif-bell{position:relative;display:inline-flex;align-items:center;gap:8px;margin-right:12px}
    .notif-bell button{background:transparent;border:none;cursor:pointer;padding:8px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center}
    .notif-count{position:absolute;top:-6px;right:-6px;z-index:70;background:#ef4444;color:#fff;font-size:12px;padding:3px 6px;border-radius:999px;min-width:20px;text-align:center;box-shadow:0 6px 18px rgba(2,6,23,0.12)}
    .notif-dropdown{position:absolute;right:0;top:44px;width:360px;max-height:420px;background:linear-gradient(180deg,#ffffff,#fbfdff);border-radius:12px;box-shadow:0 18px 50px rgba(2,6,23,0.16);overflow:auto;display:none;z-index:120;padding:8px}
    .notif-dropdown.show{display:block}

    /* Overlay shown when sidebar opens on small screens */
    .nav-overlay{position:fixed;left:0;right:0;top:var(--topbar-height);bottom:0;background:rgba(2,6,23,0.45);opacity:0;visibility:hidden;transition:opacity .18s ease;z-index:80}
    .nav-overlay.show{opacity:1;visibility:visible}
    .notif-dropdown .item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;transition:background .12s ease,transform .12s ease;cursor:pointer}
    .notif-dropdown .item:hover{background:linear-gradient(90deg,rgba(37,99,235,0.04),rgba(124,58,237,0.02));transform:translateY(-2px)}
    .notif-dropdown .left{flex:0 0 44px;display:flex;align-items:center;justify-content:center}
    .notif-dropdown .avatar{width:44px;height:44px;border-radius:50%;display:inline-grid;place-items:center;background:linear-gradient(135deg,var(--accent, #2563eb),var(--accent-2, #7c3aed));color:#fff;font-weight:700;box-shadow:0 8px 22px rgba(15,23,42,0.06)}
    .notif-dropdown .meta{flex:1;min-width:0}
    .notif-dropdown .meta .title{font-weight:700;color:#0f172a;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:8px}
    .notif-dropdown .meta .sub{font-size:12px;color:var(--muted, #6b7280);margin-top:4px}
    .notif-dropdown .time{font-size:11px;color:var(--muted, #6b7280);margin-left:6px}
    .notif-dropdown .actions{display:flex;gap:6px;flex-shrink:0}
    .notif-dropdown .empty{padding:12px;color:var(--muted, #6b7280);text-align:center}

    /* Mobile: pin notification dropdown under topbar and limit height so it's fully visible */
    @media (max-width:900px) {
        .notif-dropdown{position:fixed;left:12px;right:12px;top:calc(var(--topbar-height,72px) + 8px);width:auto;max-height:calc(100vh - var(--topbar-height,72px) - 24px);overflow:auto;z-index:9999;box-shadow:0 24px 60px rgba(2,6,23,0.24)}
        .notif-dropdown.show{display:block}
        .cards{grid-template-columns:1fr !important}
        .center{grid-template-columns:1fr !important;gap:12px}
    }

    /* Conservative, site-wide table/card responsive defaults.
       These are minimal fallbacks so pages without per-view rules
       still behave on small screens. Keep selectors broad but non-invasive. */
    .table-wrap{overflow-x:auto}
    .table-wrap table{width:100%;border-collapse:separate}
    .badge{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

    @media (max-width:900px) {
        .table-wrap table thead{display:none}
        .table-wrap table, .table-wrap table tbody, .table-wrap table tr{display:block;width:100%}
        .table-wrap table tbody tr{margin-bottom:12px;padding:12px;border-radius:10px;background:#fff;border:1px solid rgba(14,21,40,0.04)}
        .table-wrap table tbody td{display:flex;justify-content:space-between;gap:8px;padding:8px 6px;border-bottom:none;align-items:flex-start}
        /* allow templates to provide readable labels by adding data-label attributes to TDs */
        .table-wrap table tbody td::before{content:attr(data-label);color:var(--muted);font-weight:600;margin-right:8px;flex:0 0 auto}
        .table-wrap td .badge{max-width:140px}
        .table-wrap .actions{display:flex;gap:8px;flex-wrap:wrap}
    }
</style>
<script>
    (function(){
        function applyBgAndLogo(){
            try{
                // Background
                var bg = document.querySelector('.bg');
                if(bg){
                    var img = new Image();
                    img.src = '{{ $bgUrl }}';
                    if(img.complete) bg.classList.add('has-image');
                    else img.onload = function(){ bg.classList.add('has-image'); };
                }

                // Topbar / brand logo: prefer showing immediately
                var logoSelectors = ['img.logo-img', '.topbar img', '.brand img'];
                var logos = [];
                logoSelectors.forEach(function(s){ document.querySelectorAll(s).forEach(function(i){ logos.push(i); }); });
                logos.forEach(function(i){
                    try{
                        // set attributes that help immediate decode/display
                        i.loading = 'eager';
                        i.decoding = 'async';
                        // ensure src is set to canonical logo (covers cases using data-src)
                        if(!i.getAttribute('src') || i.getAttribute('src').indexOf('data:')===0){
                            i.src = '{{ $logoUrl }}';
                        }
                        // set width/height to avoid layout shift if not present
                        if(!i.getAttribute('width')) i.setAttribute('width', 40);
                        if(!i.getAttribute('height')) i.setAttribute('height', 40);
                    }catch(e){}
                });
            }catch(e){console && console.error && console.error('bg-preload', e)}
        }

        if(document.readyState === 'loading'){
            document.addEventListener('DOMContentLoaded', applyBgAndLogo);
        } else {
            applyBgAndLogo();
        }
    })();
</script>
<script>
    // Improve mobile card view accessibility: copy table headers into td[data-label]
    (function(){
        function applyDataLabels(){
            try{
                var tables = document.querySelectorAll('.table-wrap table, table.inventory-table, table');
                tables.forEach(function(table){
                    var thead = table.querySelector('thead');
                    if(!thead) return;
                    var headers = Array.prototype.slice.call(thead.querySelectorAll('th')).map(function(th){
                        return th.textContent.trim();
                    });

                    var bodies = table.querySelectorAll('tbody');
                    bodies.forEach(function(tbody){
                        Array.prototype.slice.call(tbody.querySelectorAll('tr')).forEach(function(row){
                            var cells = Array.prototype.slice.call(row.children).filter(function(n){ return n.tagName.toLowerCase() === 'td' || n.tagName.toLowerCase() === 'th'; });
                            cells.forEach(function(td, i){
                                // don't overwrite existing explicit labels
                                if(td.hasAttribute('data-label')) return;
                                var label = headers[i] || '';
                                if(label) td.setAttribute('data-label', label + ':');
                            });
                        });
                    });
                });
            }catch(e){console && console.error && console.error('applyDataLabels', e)}
        }

        if(document.readyState === 'loading'){
            document.addEventListener('DOMContentLoaded', function(){ applyDataLabels(); setTimeout(applyDataLabels, 600); });
        } else {
            applyDataLabels(); setTimeout(applyDataLabels, 600);
        }
    })();
</script>
