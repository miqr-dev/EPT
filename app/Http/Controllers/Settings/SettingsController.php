<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/SettingsPage');
    }

    public function questions()
    {
        return Inertia::render('settings/QuestionsPage');
    }
}
