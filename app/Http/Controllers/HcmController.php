<?php

namespace App\Http\Controllers;

class HcmController extends Controller
{
    public function index()
    {
        return app(DashboardController::class)->index();
    }

    public function stats()
    {
        return app(DashboardController::class)->stats();
    }
}