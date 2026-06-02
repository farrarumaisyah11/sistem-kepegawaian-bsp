<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildDashboardData;

class DashboardController extends Controller
{
    use BuildDashboardData;

    public function index()
    {
        return view('dashboard', $this->getDashboardData());
    }

    public function stats()
    {
        return response()->json($this->getDashboardData());
    }
}