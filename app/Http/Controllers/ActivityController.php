<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        return view('app.activity.index', ['activities' => ActivityLog::with('user')->latest()->paginate(20)]);
    }
}
