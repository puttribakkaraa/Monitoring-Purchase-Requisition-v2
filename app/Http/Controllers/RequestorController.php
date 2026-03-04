<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Notifications\FeedbackRespondedNotification;
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

        // Count PRs waiting for department feedback
        $feedbackWaiting = PurchaseRequisition::where('purchasing_group', $dept)
            ->where('feedback_status', 'waiting')
            ->whereNull('po_number')
            ->count();

        $stats = [
            'total' => $total,
            'processed' => $processed,
            'pending' => $pending,
            'overdue' => $overdue,
            'feedback_waiting' => $feedbackWaiting,
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

    /**
     * Get PR details for requestor modal (AJAX)
     */
    public function getPrDetails($id)
    {
        $dept = session('requestor_dept');
        if (!$dept) {
            return response()->json(['error' => 'Not authenticated'], 403);
        }

        $pr = PurchaseRequisition::with(['comments' => function($q) {
            $q->orderBy('created_at', 'asc');
        }])->findOrFail($id);

        // Ensure requestor can only view PRs from their department
        if ($pr->purchasing_group !== $dept) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $age = $pr->req_date ? (int) $pr->req_date->diffInDays(now()) : 0;

        return response()->json([
            'id' => $pr->id,
            'pr_number' => $pr->pr_number,
            'short_text' => $pr->short_text,
            'req_date' => $pr->req_date ? $pr->req_date->format('d.m.Y') : '-',
            'po_number' => $pr->po_number,
            'po_date' => $pr->po_date ? $pr->po_date->setTimezone('Asia/Jakarta')->format('d M Y') : null,
            'po_release_date' => $pr->po_release_date ? (is_string($pr->po_release_date) ? $pr->po_release_date : $pr->po_release_date->setTimezone('Asia/Jakarta')->format('d M Y')) : null,
            'days_overdue' => $age,
            'status' => $pr->po_number ? 'processed' : ($age > 14 ? 'overdue' : 'pending'),
            'mitigation_reason' => $pr->mitigation_reason,
            'mitigation_status' => $pr->mitigation_status,
            'feedback_status' => $pr->feedback_status,
            'feedback_question' => $pr->feedback_question,
            'feedback_asked_at' => $pr->feedback_asked_at ? $pr->feedback_asked_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') : null,
            'feedback_response' => $pr->feedback_response,
            'feedback_responded_at' => $pr->feedback_responded_at ? $pr->feedback_responded_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') : null,
            'comments' => $pr->comments->map(function($c) {
                return [
                    'id' => $c->id,
                    'author_name' => $c->author_name,
                    'message' => $c->message,
                    'created_at' => $c->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i'),
                ];
            }),
        ]);
    }

    /**
     * Department responds to admin's feedback question
     */
    public function respondFeedback(Request $request, $id)
    {
        $dept = session('requestor_dept');
        if (!$dept) {
            return response()->json(['error' => 'Not authenticated'], 403);
        }

        $request->validate([
            'response' => 'required|string|max:2000',
        ]);

        $pr = PurchaseRequisition::findOrFail($id);

        // Ensure requestor can only respond for their department
        if ($pr->purchasing_group !== $dept) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($pr->feedback_status !== 'waiting') {
            return response()->json(['error' => 'Tidak ada pertanyaan yang menunggu respon.'], 400);
        }

        $pr->update([
            'feedback_status'       => 'responded',
            'feedback_response'     => $request->response,
            'feedback_responded_at' => now(),
            'mitigation_status'     => 'in_progress',
        ]);

        // Also add as a comment for the chat log
        $pr->comments()->create([
            'author_name' => 'Dept. ' . $dept,
            'message'     => '[Respon Feedback] ' . $request->response,
        ]);

        // Notify all admin users
        $responsePreview = Str::limit($request->response, 50);
        $admins = User::all();
        foreach ($admins as $admin) {
            $admin->notify(new FeedbackRespondedNotification([
                'pr_id'           => $pr->id,
                'pr_number'       => $pr->pr_number,
                'department'      => $dept,
                'response'        => $request->response,
                'response_preview' => $responsePreview,
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Respon berhasil dikirim ke admin purchasing.',
        ]);
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
            'status' => [$processed, $pending, $overdue]
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
