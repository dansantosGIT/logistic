<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'plate_number',
        'image_path',
        'type',
        'brand',
        'year',
        'is_firetruck',
        'status',
        'notes',
    ];

    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }
}
