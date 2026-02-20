<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequestItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_request_items';

    protected $fillable = [
        'inventory_request_id', 'equipment_id', 'quantity', 'notes', 'return_date', 'location', 'status', 'issued_quantity', 'handled_by', 'handled_at'
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function request()
    {
        return $this->belongsTo(InventoryRequest::class, 'inventory_request_id');
    }
}
