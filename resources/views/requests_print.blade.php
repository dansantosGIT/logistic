<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Printable Request — San Juan CDRMMD</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    /* A4 print layout */
    @page { size: A4; margin: 20mm; }
    html,body{height:100%;margin:0;padding:0;font-family:Inter,system-ui,Arial,Helvetica;color:#0f172a}
    body{background:white}
    .sheet{width:210mm;min-height:297mm;padding:0;margin:0 auto;box-sizing:border-box}

    .print-header{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;margin-bottom:8px}
    .logos-row{display:flex;gap:10px;align-items:center;justify-content:center}
    .logo{width:72px;height:72px;display:flex;align-items:center;justify-content:center;background:transparent;border:none}
    .logo.center{width:96px;height:96px}
    .logo img{max-width:100%;max-height:100%;display:block;background:transparent;object-fit:contain}
    .header-center{text-align:center;flex:1}
    .org-name{font-size:18px;font-weight:800;margin-bottom:4px}
    .form-title{font-size:14px;font-weight:700;color:#2563eb}

    .meta{display:flex;justify-content:space-between;gap:12px;margin-bottom:12px}
    .meta .col{flex:1;padding:8px;border:1px solid #e6e9ef;border-radius:6px}
    .meta .col h4{margin:0 0 6px 0;font-size:12px;color:#6b7280}
    .meta .col .val{font-weight:700;font-size:13px}

    .items-table{width:100%;border-collapse:collapse;margin-bottom:12px}
    .items-table thead th{border:1px solid #d1d5db;padding:8px;background:#f8fafc;font-weight:700;font-size:12px}
    .items-table tbody td{border:1px solid #e6e9ef;padding:8px;font-size:12px}

    .remarks{border:1px solid #e6e9ef;padding:10px;min-height:60px;border-radius:6px;font-size:12px}

    .signatures{display:flex;gap:18px;margin-top:20px}
    .sig-block{flex:1;border:1px solid #e6e9ef;padding:12px;border-radius:6px}
    .sig-label{font-size:12px;color:#6b7280;margin-bottom:6px}
    .sig-printed{font-weight:700;margin-bottom:12px}
    .sig-line{border-bottom:1px solid #111;height:28px;margin-bottom:8px}
    .sig-small{font-size:11px;color:#6b7280}

    .footer{margin-top:18px;font-size:11px;color:#6b7280}

    .print-action{position:fixed;top:18px;right:18px;z-index:1200}
    .print-action button{background:#2563eb;border:0;color:#fff;padding:8px 12px;border-radius:6px;cursor:pointer;font-weight:700}

    /* Print helpers */
    @media print{
      .no-print{display:none}
    }
  </style>
</head>
<body>
  <div class="sheet" id="request-print">
    <div class="print-action no-print">
      <button type="button" onclick="doPrint()">Print Form</button>
    </div>
    <div class="print-header">
      <div class="header-center">
        <div class="logos-row">
          <div class="logo"><img src="/images/msj.png" alt="Makabagong San Juan"></div>
          <div class="logo center"><img src="/images/sj.png" alt="San Juan Seal"></div>
          <div class="logo"><img src="/images/favi.png" alt="San Juan Favicon"></div>
        </div>
        <div class="org-name">San Juan CDRMMD</div>
        <div class="form-title">Equipment Request Form</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Request ID: {{ $r->uuid ?? $r->id }} — Submitted: {{ $r->created_at->format('F j, Y') }}</div>
      </div>
    </div>

    <div class="meta">
      <div class="col">
        <h4>Requestor</h4>
        <div class="val">{{ $r->requester }}</div>
        <div style="margin-top:6px">Role: {{ $r->role ?? '—' }}</div>
        <div>Department: {{ $r->department ?? '—' }}</div>
      </div>
      <div class="col">
        <h4>Approver / Office</h4>
        <div class="val">{{ auth()->user()->name ?? '—' }}</div>
        <div style="margin-top:6px">Position: ________________________</div>
        <div>Approval date: ___________________</div>
      </div>
    </div>

    <table class="items-table" aria-label="Requested items">
      <thead>
        <tr>
          <th style="width:6%">#</th>
          <th style="width:34%">Equipment</th>
          <th style="width:12%">Category / Type</th>
          <th style="width:12%">Serial</th>
          <th style="width:10%">Qty Requested</th>
          <th style="width:12%">Qty Issued</th>
          <th style="width:14%">Return Date</th>
        </tr>
      </thead>
      <tbody>
        @php
          $iterableItems = (isset($r->items) && is_countable($r->items) && $r->items->count() > 0) ? $r->items : [$r];
          $i = 1;
        @endphp
        @foreach($iterableItems as $it)
          @php
            $requestedQty = $it->quantity ?? ($it['quantity'] ?? $r->quantity ?? 1);
            $returnDate = $it->return_date ?? $r->return_date ?? null;

            // Resolve item name for multi-item requests: prefer explicit item_name,
            // then related equipment name (if eager-loaded), then lookup by equipment_id,
            // finally fall back to parent request item_name.
            $itemName = null;
            if (!empty($it->item_name)) {
              $itemName = $it->item_name;
            } elseif (!empty($it->equipment) && !empty($it->equipment->name)) {
              $itemName = $it->equipment->name;
            } elseif (!empty($it->equipment_id)) {
              try {
                $equip = \App\Models\Equipment::find($it->equipment_id);
                $itemName = $equip ? $equip->name : null;
              } catch (Throwable $__e) {
                $itemName = null;
              }
            }
            $itemName = $itemName ?? ($r->item_name ?? '—');

            // Type and serial: prefer item fields, then equipment relation, then parent's values
            $type = $it->type ?? ($it->equipment->type ?? ($r->type ?? '—'));
            $serial = $it->serial ?? ($it->equipment->serial ?? ($r->serial ?? '—'));

            // Issued quantity: prefer per-item issued_quantity (set on approval), then legacy fields
            $issuedQty = null;
            if (isset($it->issued_quantity)) {
              $issuedQty = $it->issued_quantity;
            } elseif (isset($it->issued)) {
              $issuedQty = $it->issued;
            } elseif (isset($r->issued)) {
              $issuedQty = $r->issued;
            }
          @endphp
          <tr>
            <td>{{ $i }}</td>
            <td>{{ $itemName }}</td>
            <td>{{ $type }}</td>
            <td>{{ $serial }}</td>
            <td style="text-align:center">{{ $requestedQty }}</td>
            <td style="text-align:center">{{ ($issuedQty !== null && $issuedQty !== '') ? $issuedQty : '—' }}</td>
            <td style="text-align:center">{{ !empty($returnDate) ? ((($returnDate instanceof \DateTimeInterface) ? $returnDate->format('F j, Y') : \Carbon\Carbon::parse($returnDate)->format('F j, Y'))) : '—' }}</td>
          </tr>
          @php $i++; @endphp
        @endforeach
        @for($j = $i; $j <= 8; $j++)
          <tr>
            <td>{{ $j }}</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        @endfor
      </tbody>
    </table>

    <div>
      <div style="font-weight:700;margin-bottom:6px">Reason / Remarks</div>
      <div class="remarks">{{ $r->reason ?? '—' }}</div>
    </div>

    <div class="signatures">
      <div class="sig-block">
        <div class="sig-label">Requestor (Printed Name)</div>
        <div class="sig-printed">{{ $r->requester }}</div>
        <div class="sig-label">Signature</div>
        <div class="sig-line"></div>
        <div style="display:flex;justify-content:space-between;margin-top:8px">
          <div style="width:48%"><div class="sig-small">Date</div><div class="sig-line" style="height:18px"></div></div>
          <div style="width:48%"><div class="sig-small">ID / Employee No.</div><div class="sig-line" style="height:18px"></div></div>
        </div>
      </div>

      <div class="sig-block">
        <div class="sig-label">Approver (Printed Name)</div>
        <div class="sig-printed">{{ auth()->user()->name ?? '—' }}</div>
        <div class="sig-label">Signature</div>
        <div class="sig-line"></div>
        <div style="display:flex;justify-content:space-between;margin-top:8px;align-items:center">
          <div style="width:48%"><div class="sig-small">Date</div><div class="sig-line" style="height:18px"></div></div>
          <div style="width:48%"><div class="sig-small">Official stamp</div><div style="width:90%;height:48px;border:1px dashed #e6e9ef;display:block"></div></div>
        </div>
      </div>
    </div>

    <div class="footer">
      Printed by: {{ auth()->user()->name ?? '—' }} — Printed on: {{ \Carbon\Carbon::now()->format('F j, Y, g:i A') }}
      <div style="margin-top:6px">Instructions: Signatures must be handwritten. Keep a scanned copy for records and upload via the request page if required.</div>
    </div>

  </div>

  <script>
    // Provide quick print button when opened in browser
    // Write the printable sheet into a hidden iframe and print from there to avoid navigating the main tab.
    function doPrint(){
      try{
        const sheet = document.getElementById('request-print');
        if (!sheet) { window.print(); return; }

        // Collect inline styles
        let styles = '';
        document.querySelectorAll('head style, head link[rel="stylesheet"]').forEach(function(node){
          styles += node.outerHTML;
        });

        const printHtml = '<!doctype html><html><head><meta charset="utf-8"><title></title>' + styles + '</head><body>' + sheet.outerHTML + '</body></html>';

        // Create hidden iframe
        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        iframe.style.overflow = 'hidden';
        iframe.setAttribute('aria-hidden', 'true');
        document.body.appendChild(iframe);

        const idoc = iframe.contentDocument || iframe.contentWindow.document;
        idoc.open();
        idoc.write(printHtml);
        idoc.close();

        // Ensure title is empty to avoid printing a title string.
        try { idoc.title = ''; } catch(e) {}

        // Wait for images to load inside iframe before printing
        const imgs = idoc.images || [];
        let loaded = 0;
        const total = imgs.length;
        function triggerPrint(){
          try{
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
          }catch(e){ window.print(); }
          // remove iframe after a short delay
          setTimeout(function(){ try{ document.body.removeChild(iframe); }catch(e){} }, 500);
        }

        if (total === 0) { setTimeout(triggerPrint, 50); }
        else {
          for (let i=0;i<imgs.length;i++){
            imgs[i].addEventListener('load', function(){ loaded++; if (loaded>=total) triggerPrint(); });
            imgs[i].addEventListener('error', function(){ loaded++; if (loaded>=total) triggerPrint(); });
          }
          // fallback timeout
          setTimeout(triggerPrint, 1500);
        }

      }catch(e){ window.print(); }
    }

    (function(){ if(window.location.search.indexOf('autoPrint') !== -1){ setTimeout(doPrint, 300); } })();
  </script>
</body>
</html>