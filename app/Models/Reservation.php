<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'hour',
        'worker_dni',
        'client_dni',
        'service_id',
        'shift_id',
        'status',
    ];
}
