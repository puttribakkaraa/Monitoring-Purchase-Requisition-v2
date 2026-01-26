<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RequestorController extends Controller
{
    /**
     * Show login page (Department Selection).
     */
    public function login()
    {
        // Get list of unique departments from PRs for the dropdown
        $departments = PurchaseRequisition::select('purchasing_group')
            ->whereNotNull('purchasing_group')
            ->distinct()
            ->orderBy('purchasing_group')
            ->pluck('purchasing_group');

        return view('requestor.login', compact('departments'));
    }

    /**
     * Authenticate (Store department in session).
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'department' => 'required|string',
        ]);

        session(['requestor_dept' => $request->department]);

        return redirect()->route('requestor.dashboard');
    }

    /**
     * User Dashboard.
     */
    public function dashboard()
    {
        $dept = session('requestor_dept');

        if (!$dept) {
            return redirect()->route('requestor.login');
        }

        // 1. Calculate Stats (Based on ALL data for this dept)
        // We use a base query builder to avoid repetition
        $baseQuery = PurchaseRequisition::where('purchasing_group', $dept);
        
        $total = (clone $baseQuery)->count();
        $processed = (clone $baseQuery)->whereNotNull('po_number')->count();
        $pending = (clone $baseQuery)->whereNull('po_number')->count();
        
        // Calculate overdue (Pending > 14 days)
        // Using collection filter on a constrained query or raw SQL is better than fetching all
        // For simplicity and performance on filtered set:
        $overdue = (clone $baseQuery)
            ->whereNull('po_number')
            ->whereNotNull('req_date')
            ->get() // Get only pending to check dates in PHP (safer if specific date logic needed)
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

        // 2. Get Data for Table (ONLY Pending PRs, as requested)
        $requisitions = PurchaseRequisition::where('purchasing_group', $dept)
            ->whereNull('po_number') // Filter: Only show PRs without PO
            ->with(['comments' => function($q) {
                $q->latest();
            }])
            ->latest('req_date')
            ->paginate(10);

        // 3. Prepare Chart Data (Department Specific)
        $chartData = $this->getDeptChartData($dept);

        return view('requestor.dashboard', compact('dept', 'requisitions', 'stats', 'chartData'));
    }

    private function getDeptChartData($dept)
    {
        // A. Monthly Volume (Last 6 Months)
        $months = collect([]);
        for ($i = 5; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        $labels = $months->map(fn($m) => $m->format('M Y'));
        $volumeData = [];

        foreach ($months as $month) {
            $count = PurchaseRequisition::where('purchasing_group', $dept)
                ->whereYear('req_date', $month->year)
                ->whereMonth('req_date', $month->month)
                ->count();
            $volumeData[] = $count;
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
            'volume' => $volumeData,
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
