<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RequestorController extends Controller
{
    public function login()
    {
        
        $departments = PurchaseRequisition::select('purchasing_group')
            ->whereNotNull('purchasing_group')
            ->distinct()
            ->orderBy('purchasing_group')
            ->pluck('purchasing_group');

        return view('requestor.login', compact('departments'));
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'department' => 'required|string',
        ]);

        session(['requestor_dept' => $request->department]);

        return redirect()->route('requestor.dashboard');
    }

    public function dashboard()
    {
        $dept = session('requestor_dept');

        if (!$dept) {
            return redirect()->route('requestor.login');
        }

        $baseQuery = PurchaseRequisition::where('purchasing_group', $dept);
        $total = (clone $baseQuery)->count();
        $processed = (clone $baseQuery)->whereNotNull('po_number')->count();
        $pending = (clone $baseQuery)->whereNull('po_number')->count();
        
        $overdue = (clone $baseQuery)
            ->whereNull('po_number')
            ->whereNotNull('req_date')
            ->get() 
            ->filter(function ($pr) {
                return $pr->req_date->diffInDays(now()) > 14;
            })
            ->count();

        $stats = [
            'total' => $total,
            'processed' => $processed,
            'pending' => $pending,
            'overdue' => $overdue,
        ];

        $requisitions = PurchaseRequisition::where('purchasing_group', $dept)
            ->whereNull('po_number') 
            ->with(['comments' => function($q) {
                $q->latest();
            }])
            ->latest('req_date')
            ->paginate(10);

        $chartData = $this->getDeptChartData($dept);

        return view('requestor.dashboard', compact('dept', 'requisitions', 'stats', 'chartData'));
    }

    private function getDeptChartData($dept)
    {
        // A. Monthly Volume (Last 12 Months)
        $months = collect([]);
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        $labels = $months->map(fn($m) => $m->format('M Y'));
        $prData = [];
        $poData = [];

        foreach ($months as $month) {
            $prCount = PurchaseRequisition::where('purchasing_group', $dept)
                ->whereYear('req_date', $month->year)
                ->whereMonth('req_date', $month->month)
                ->count();
            
            $poCount = PurchaseRequisition::where('purchasing_group', $dept)
                ->whereNotNull('po_date')
                ->whereYear('po_date', $month->year)
                ->whereMonth('po_date', $month->month)
                ->count();

            $prData[] = $prCount;
            $poData[] = $poCount;
        }

        // B. Status Distribution
        // processed = has po_number
        // overdue = no po + > 14 days
        // pending = no po + <= 14 days
        $processed = PurchaseRequisition::where('purchasing_group', $dept)->whereNotNull('po_number')->count();
        $basePending = PurchaseRequisition::where('purchasing_group', $dept)->whereNull('po_number')->get();
        
        $overdue = $basePending->filter(function ($pr) {
            return $pr->req_date && $pr->req_date->diffInDays(now()) > 14;
        })->count();
        
        $pending = $basePending->count() - $overdue;

        return [
            'months' => $labels,
            'pr' => $prData,
            'po' => $poData,
            'status' => [$processed, $pending, $overdue] // Processed, Pending, Overdue
        ];
    }

    /**
     * 'Logout' (Clear session).
     */
    public function logout()
    {
        session()->forget('requestor_dept');
        return redirect()->route('requestor.login');
    }
}
