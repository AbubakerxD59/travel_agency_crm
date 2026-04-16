<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function create(): View
    {
        return view('admin.companies.create');
    }
}
