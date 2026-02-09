<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;

    protected $table = 'inventory_requests';

    protected $fillable = [
        'uuid', 'item_id', 'item_name', 'requester', 'requester_user_id', 'quantity', 'role', 'reason', 'return_date', 'status', 'handled_by'
    ];

    protected $casts = [
        'return_date' => 'date',
    ];
}
