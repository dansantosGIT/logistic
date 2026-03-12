<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'task',
        'due_date',
        'status',
        'completed_at',
        'reviewed_at',
        'checked_at',
        'updated_marker_at',
        'evidence_image_path',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'checked_at' => 'datetime',
        'updated_marker_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
