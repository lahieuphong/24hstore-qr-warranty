<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()
            ? redirect()->to(url('/admin').'/')
            : redirect()->to(url('/admin/login').'/?next=/admin/');
    }
}
