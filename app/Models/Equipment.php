<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name','serial','category','type','quantity','location','tag','date_added','image_path','notes','created_by'
    ];

    protected $casts = [
        'date_added' => 'datetime',
    ];
}
