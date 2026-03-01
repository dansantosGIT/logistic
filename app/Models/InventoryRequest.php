<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;

    protected $table = 'inventory_requests';

    protected $fillable = [
        'uuid', 'item_id', 'item_name', 'requester', 'requester_user_id', 'quantity', 'role', 'department', 'reason', 'return_date', 'status', 'handled_by',
        // print/sign metadata
        'printed_pdf_path', 'printed_by', 'printed_at', 'signed_scan_path', 'signed_by', 'signed_at'
    ];


    protected $casts = [
        'return_date' => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'item_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\InventoryRequestItem::class, 'inventory_request_id');
    }
}
