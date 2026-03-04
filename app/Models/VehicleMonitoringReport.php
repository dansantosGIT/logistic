<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMonitoringReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'report',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
