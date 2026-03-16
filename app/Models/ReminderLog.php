<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    protected $table = 'reminder_logs';
    protected $fillable = [
        'inventory_request_item_id',
        'days',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryRequestItem::class, 'inventory_request_item_id');
    }
}
