<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'existing_category' => 'nullable|string|max:255',
            'new_category' => 'nullable|string|max:255',
            'type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'location' => 'nullable|string',
            'tag' => 'nullable|string|max:255',
            'date_added' => 'nullable|date',
            'image' => 'nullable|file|image|max:5120',
            'notes' => 'nullable|string|max:120',
        ]);

        // Determine category (existing or new)
        $category = $data['existing_category'] ?? null;
        if (empty($category) && !empty($data['new_category'])) {
            $category = $data['new_category'];
        }

        // Generate a unique serial
        do {
            $serial = 'SN' . substr(uniqid(), -8);
        } while (Equipment::where('serial', $serial)->exists());

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('equipment', 'public');
        }

        $equipment = Equipment::create([
            'name' => $data['name'],
            'serial' => $serial,
            'category' => $category,
            'type' => $data['type'] ?? null,
            'quantity' => $data['quantity'] ?? 1,
            'location' => $data['location'] ?? null,
            'tag' => $data['tag'] ?? null,
            'date_added' => $data['date_added'] ?? now(),
            'image_path' => $imagePath,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect('/inventory/' . $equipment->id . '/edit')->with('success', 'Equipment added');
    }
}
