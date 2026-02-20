@if(isset($equipment) && $equipment->count())
    @foreach($equipment as $item)
        <tr data-location="{{ strtolower($item->location ?? '') }}" class="equipment-row" onclick="openEquipmentModal(this)" data-equipment='{{json_encode(["id" => $item->id, "name" => $item->name, "category" => $item->category ?? "—", "location" => $item->location ?? "—", "serial" => $item->serial ?? "—", "quantity" => $item->quantity, "type" => $item->type ?? "—", "tag" => $item->tag ?? "—", "notes" => $item->notes ?? "No description provided", "image_path" => $item->image_path, "date_added" => $item->date_added ? $item->date_added->format('M d, Y') : $item->created_at->format('M d, Y'), "created_at" => $item->created_at->format('M d, Y H:i'), "updated_at" => $item->updated_at->format('M d, Y H:i')])}}'>
            <td>{{ $item->name }}</td>
            <td>{{ $item->category }}</td>
            <td>{{ $item->location }}</td>
            <td>{{ $item->serial }}</td>
            <td>{{ $item->quantity }}</td>
            <td>
                @if(strtolower(trim($item->type ?? '')) === 'consumable')
                    <span class="badge consumable">Consumable</span>
                @else
                    <span class="badge nonconsumable">Non&#8209;consumable</span>
                @endif
            </td>
            <td>
                @if($item->quantity >= 10)
                    <span class="badge instock">In stock</span>
                @elseif($item->quantity > 0)
                    <span class="badge low">Low stock</span>
                @else
                    <span class="badge out">Out of stock</span>
                @endif
            </td>
            <td>{{ $item->date_added ? $item->date_added->format('Y-m-d') : $item->created_at->format('Y-m-d') }}</td>
            <td style="display:flex;align-items:center;justify-content:flex-end;gap:8px" onclick="event.stopPropagation()">
                <a href="/inventory/{{ $item->id }}/request" class="btn request">Request</a>
                <a href="/inventory/{{ $item->id }}/edit" class="btn edit">Edit</a>
                <a href="/inventory/{{ $item->id }}/delete" class="btn delete" onclick="event.stopPropagation(); return confirm('Delete this item?');">Delete</a>
            </td>
        </tr>
    @endforeach
    <tr class="no-results" style="display:none"><td colspan="9"><div class="placeholder-msg" style="width:100%;padding:10px 8px;background:linear-gradient(180deg,#ffffff,#fbfdff);text-align:center;color:var(--muted)">No items in this section</div></td></tr>
@else
    <tr><td colspan="9" style="text-align:center;color:var(--muted)">No equipment yet</td></tr>
@endif
