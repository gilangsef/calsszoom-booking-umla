<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    // landing
    public function index() {

        return view('landing');
        
    }
}
