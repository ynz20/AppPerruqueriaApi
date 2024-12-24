<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    public function checkUserAuth() {
        if (Auth::check()){
            return response()->json([
                'status'=>true,
                'message'=> 'Usuari autenticat',
            ],200);
        }
    }
}
