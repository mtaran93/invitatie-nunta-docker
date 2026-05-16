<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class WrittenGuestController extends Controller
{
    public function index(): View
    {
        return view('invitation');
    }
}
