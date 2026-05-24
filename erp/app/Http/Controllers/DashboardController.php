<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('dashboard')],
            ],
        ]);
    }
}
