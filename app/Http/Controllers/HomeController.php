<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){

        $plans = Plan::where('is_active', true)->get();
        
        return view('home',compact('plans'));
    
    }
}
