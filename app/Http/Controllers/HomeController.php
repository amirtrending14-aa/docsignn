<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;

class HomeController extends Controller
{
    public function index()
    {
        // Передаём статистику в шаблон
        $stats = [
            'users' => User::count(),
            'documents' => Document::count(),
        ];

        return view('welcome', compact('stats'));
        // или return view('home', compact('stats')); — зависит от твоего шаблона
    }
}