<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        $leads = [
            [
                'initials' => 'MS',
                'name' => 'Maria Santos',
                'email' => 'maria.santos@email.com',
                'phone' => '+1 555 012 3344',
                'city' => 'Lisbon, Portugal',
                'source' => 'META',
                'source_class' => 'meta',
                'agent' => 'James Cole',
                'status' => 'SALE DONE',
                'status_class' => 'sale-done',
            ],
            [
                'initials' => 'JD',
                'name' => 'John Davidson',
                'email' => 'jd.travel@email.com',
                'phone' => '+44 20 7946 0958',
                'city' => 'London, UK',
                'source' => 'GOOGLE',
                'source_class' => 'google',
                'agent' => 'Sarah Lin',
                'status' => 'FOLLOW-UP',
                'status_class' => 'follow-up',
            ],
            [
                'initials' => 'AK',
                'name' => 'Aisha Khan',
                'email' => 'aisha.k@email.com',
                'phone' => '+971 4 555 0199',
                'city' => 'Dubai, UAE',
                'source' => 'REFERRAL',
                'source_class' => 'referral',
                'agent' => 'James Cole',
                'status' => 'NEW',
                'status_class' => 'new',
            ],
            [
                'initials' => 'PL',
                'name' => 'Pierre Laurent',
                'email' => 'p.laurent@email.com',
                'phone' => '+33 1 42 86 82 00',
                'city' => 'Paris, France',
                'source' => 'META',
                'source_class' => 'meta',
                'agent' => 'Unassigned',
                'status' => 'CONTACTED',
                'status_class' => 'contacted',
            ],
            [
                'initials' => 'EW',
                'name' => 'Emma Wilson',
                'email' => 'emma.w@email.com',
                'phone' => '+61 2 9374 4000',
                'city' => 'Sydney, Australia',
                'source' => 'GOOGLE',
                'source_class' => 'google',
                'agent' => 'Sarah Lin',
                'status' => 'NOT CONVERTED',
                'status_class' => 'not-converted',
            ],
        ];

        return view('admin.leads.index', [
            'leads' => $leads,
            'total' => 124,
        ]);
    }
}
