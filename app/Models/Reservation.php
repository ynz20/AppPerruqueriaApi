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

    // Relació amb Worker
    public function user()
    {
        return $this->belongsTo(User::class, 'worker_dni', 'dni');
    }

    // Relació amb Client
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_dni', 'dni');
    }

    // Relació amb Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
