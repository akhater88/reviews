<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display the TABsense landing page.
     */
    public function index()
    {
        return view('landing.index');
    }

    /**
     * Display the get started wizard page.
     */
    public function getStarted()
    {
        return view('landing.get-started');
    }
}
