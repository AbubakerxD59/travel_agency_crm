<?php

namespace App\Support;

use App\Models\Folder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FolderCalendarBuilder
{
    /**
     * @return array{
     *     month_label: string,
     *     today_iso: string,
     *     year: int,
     *     month: int,
     *     is_current_month: bool,
     *     upcoming_only: bool,
     *     prev: array{year: int, month: int},
     *     next: array{year: int, month: int},
     *     weeks: list<list<array{
     *         iso: string,
     *         day: int,
     *         in_month: bool,
     *         is_today: bool,
     *         is_past: bool,
     *         folder_count: int,
     *         folders: Collection<int, Folder>
     *     }>>,
     *     upcoming_folders: Collection<int, Folder>
     * }
     */
    public function build(?Carbon $reference = null, bool $upcomingOnly = true, ?int $agentId = null): array
    {
        $monthAnchor = ($reference ?? now())->copy()->startOfMonth();
        $today = now()->startOfDay();
        $monthStart = $monthAnchor->copy();
        $monthEnd = $monthAnchor->copy()->endOfMonth();

        $foldersQuery = Folder::query()
            ->with([
                'agent:id,name',
                'company:id,name',
                'destination:id,name',
            ])
            ->whereNotNull('travel_date')
            ->whereBetween('travel_date', [$monthStart, $monthEnd]);

        if ($agentId !== null) {
            $foldersQuery->where('agent_id', $agentId);
        }

        if ($upcomingOnly) {
            $foldersQuery->whereDate('travel_date', '>', $today);
        }

        $monthFolders = $foldersQuery
            ->orderBy('travel_date')
            ->orderBy('customer_name')
            ->get();

        $foldersByDate = $monthFolders->groupBy(
            fn (Folder $folder): string => $folder->travel_date->format('Y-m-d')
        );

        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::MONDAY);

        $weeks = [];
        $cursor = $gridStart->copy();

        while ($cursor->lte($gridEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->copy();
                $iso = $date->format('Y-m-d');
                $week[] = [
                    'iso' => $iso,
                    'day' => (int) $date->day,
                    'in_month' => $date->month === $monthAnchor->month && $date->year === $monthAnchor->year,
                    'is_today' => $date->isSameDay($today),
                    'is_past' => $date->lt($today),
                    'folder_count' => $foldersByDate->get($iso)?->count() ?? 0,
                    'folders' => $foldersByDate->get($iso) ?? collect(),
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        $prev = $monthAnchor->copy()->subMonth();
        $next = $monthAnchor->copy()->addMonth();

        return [
            'month_label' => $monthAnchor->format('F Y'),
            'today_iso' => $today->format('Y-m-d'),
            'year' => (int) $monthAnchor->year,
            'month' => (int) $monthAnchor->month,
            'is_current_month' => $monthAnchor->isSameMonth($today),
            'upcoming_only' => $upcomingOnly,
            'prev' => [
                'year' => (int) $prev->year,
                'month' => (int) $prev->month,
            ],
            'next' => [
                'year' => (int) $next->year,
                'month' => (int) $next->month,
            ],
            'weeks' => $weeks,
            'upcoming_folders' => $monthFolders,
        ];
    }
}
