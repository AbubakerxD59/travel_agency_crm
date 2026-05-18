<?php

namespace App\Http\Controllers\Agent;

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
        $agentId = (int) $request->user()->id;
        $year = $request->integer('year') ?: (int) now()->year;
        $month = $request->integer('month') ?: (int) now()->month;
        $month = max(1, min(12, $month));

        $reference = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        return view('agent.calendar.index', [
            'folderCalendar' => $folderCalendarBuilder->build($reference, upcomingOnly: false, agentId: $agentId),
            'calendarRoutes' => self::calendarRoutes(),
        ]);
    }

    public function folderDetails(Request $request, Folder $folder): View
    {
        if ((int) $folder->agent_id !== (int) $request->user()->id) {
            abort(404);
        }

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

    /**
     * @return array{index: string, upcoming: string, folders_index: string, folder_show: string, folder_details_base: string, folder_show_base: string}
     */
    public static function calendarRoutes(): array
    {
        return [
            'index' => 'agent.calendar.index',
            'upcoming' => 'agent.folders.upcoming',
            'folders_index' => 'agent.folders.index',
            'folder_show' => 'agent.folders.show',
            'folder_details_base' => url('/agent/calendar/folders'),
            'folder_show_base' => url('/agent/folders'),
        ];
    }
}
