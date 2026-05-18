<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Support\FolderCalendarBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request, FolderCalendarBuilder $folderCalendarBuilder): View
    {
        $year = $request->integer('year') ?: (int) now()->year;
        $month = $request->integer('month') ?: (int) now()->month;
        $month = max(1, min(12, $month));

        $reference = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        return view('admin.calendar.index', [
            'folderCalendar' => $folderCalendarBuilder->build($reference, upcomingOnly: false),
        ]);
    }

    public function folderDetails(Folder $folder): View
    {
        $folder->load([
            'agent',
            'company',
            'destination',
            'itineraries',
            'passengers',
            'packageCosts',
            'hotelDetails',
            'transportDetails',
            'visaDetails',
            'otherDetails',
            'payments.bank',
        ]);

        return view('partials.admin.folder-details-content', [
            'folder' => $folder,
        ]);
    }
}
