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

        // Relación con Worker
        public function user()
        {
            return $this->belongsTo(User::class, 'worker_dni', 'dni');
        }
    
        // Relación con Client
        public function client()
        {
            return $this->belongsTo(Client::class, 'client_dni', 'dni');
        }
}
