<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    //Ajustem el model perquè la clau primària sigui el camp dni ja sinos a la hora de fer la edició no funcionaba nomès marcant el dni com clau primària
    protected $primaryKey = 'dni'; 
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $fillable = ['name', 'surname', 'dni', 'email', 'telf'];

}
