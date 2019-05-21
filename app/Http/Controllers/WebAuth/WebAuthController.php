<?php

namespace App\Http\Controllers\WebAuth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebAuthController extends Controller
{
    public function webLogin(){
        return view('web.webLogin');
    }
}
