<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Models\InventoryRequest;
use Carbon\Carbon;

class AnalyticsController extends BaseController
{
    // Return department request counts.
    // If ?months=N (N>1) provided, return monthly trend for last N months.
    public function departmentRequests(Request $request)
    {
        $months = (int) $request->query('months', 1);
        if ($months <= 1) {
            // current month counts
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            $rows = InventoryRequest::selectRaw("COALESCE(NULLIF(department,''),'Unassigned') as department, COUNT(*) as cnt")
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('department')
                ->orderByDesc('cnt')
                ->get();

            return response()->json([
                'labels' => $rows->pluck('department'),
                'data' => $rows->pluck('cnt'),
                'month' => $start->format('Y-m')
            ]);
        }

        // monthly trend for last N months
        $n = max(2, $months);
        $end = Carbon::now()->endOfMonth();
        $start = Carbon::now()->subMonths($n - 1)->startOfMonth();

        $rows = InventoryRequest::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COALESCE(NULLIF(department,''),'Unassigned') as department, COUNT(*) as cnt")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym', 'department')
            ->orderBy('ym')
            ->get();

        $monthsLabels = [];
        $cur = $start->copy();
        while ($cur->lte($end)){
            $monthsLabels[] = $cur->format('Y-m');
            $cur->addMonth();
        }

        // pivot into series per department
        $departments = $rows->pluck('department')->unique()->values()->all();
        $series = [];
        foreach ($departments as $d) {
            $map = array_fill_keys($monthsLabels, 0);
            foreach ($rows->where('department', $d) as $r) {
                $map[$r->ym] = (int) $r->cnt;
            }
            $series[] = ['department' => $d, 'data' => array_values($map)];
        }

        return response()->json([
            'months' => $monthsLabels,
            'series' => $series,
        ]);
    }
}
