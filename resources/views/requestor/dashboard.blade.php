<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requestor Dashboard - {{ $dept }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* [Copying styles from dashboard.blade.php for consistency] */
        :root {
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --bg-card-hover: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --accent-primary: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.2);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
            --chart-grid: #e2e8f0;
            --chart-text: #64748b;
        }

        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-primary: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.3);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border: #334155;
            --shadow: rgba(0, 0, 0, 0.3);
            --chart-grid: #334155;
            --chart-text: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            transition: background-color 0.3s, color 0.3s;
        }

        .main-content {
            flex: 1;
            padding: 1.5rem 2rem;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        .btn-theme-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-theme-toggle:hover {
            border-color: var(--accent-primary);
            color: var(--text-primary);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        /* KPI GRID */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .kpi-grid { grid-template-columns: 1fr; } }

        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        .feedback-badge {
            display: inline-flex; align-items: center; gap: 0.25rem;
            background: rgba(59,130,246,0.15); color: #3b82f6;
            font-size: 0.6rem; font-weight: 600; padding: 0.15rem 0.45rem;
            border-radius: 2rem; animation: pulse-badge 2s infinite;
        }
        .status-feedback { background: rgba(59,130,246,0.15); color: #3b82f6; }
        
        .kpi-card {
            background: var(--bg-card);
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px var(--shadow); }
        .kpi-title { font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-value { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
        .kpi-icon { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; opacity: 0.15; }
        .trend { display: flex; align-items: center; font-size: 0.7rem; margin-top: 0.35rem; gap: 0.2rem; }
        .trend.up { color: var(--success); }
        .trend.down { color: var(--danger); }

        /* CHARTS */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 1024px) {
            .charts-section { grid-template-columns: 1fr; }
        }
        .chart-card {
            background: var(--bg-card);
            padding: 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px var(--shadow);
        }
        .chart-header { margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
        .chart-header h3 { font-size: 0.95rem; font-weight: 600; }

        /* TABLE */
        .table-card {
            background: var(--bg-card);
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 2px 8px var(--shadow);
        }
        .table-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .table-header h3 { font-size: 0.95rem; font-weight: 600; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        th, td { text-align: left; padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); }
        th { color: var(--text-secondary); font-weight: 500; background: var(--bg-card-hover); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover { background-color: var(--bg-card-hover); }
        .status-badge { padding: 0.2rem 0.6rem; border-radius: 2rem; font-size: 0.65rem; font-weight: 600; display: inline-block; }
        .status-processed { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .status-overdue { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

        /* Step Tracker */
        .step-tracker {
            display: flex;
            align-items: center;
            gap: 0.15rem;
            font-size: 0.6rem;
        }
        .step-item {
            display: flex;
            align-items: center;
            gap: 0.15rem;
            padding: 0.1rem 0.3rem;
            border-radius: 0.2rem;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .step-item.done {
            background: rgba(16,185,129,0.12);
            color: #10b981;
            font-weight: 600;
        }
        .step-item.active {
            background: rgba(59,130,246,0.12);
            color: #3b82f6;
            font-weight: 600;
        }
        .step-item.pending {
            color: var(--text-secondary);
            opacity: 0.5;
        }
        .step-connector {
            color: var(--text-secondary);
            font-size: 0.55rem;
            opacity: 0.4;
        }
        
        .pagination { padding: 0.75rem 1rem; display: flex; justify-content: center; width: 100%; }
        .pagination nav { display: flex; gap: 0.35rem; width: 100%; justify-content: space-between; }
        .page-link { padding: 0.4rem 0.8rem; border-radius: 0.35rem; background: var(--bg-card-hover); color: var(--text-secondary); text-decoration: none; font-size: 0.8rem; }
        .page-item.active .page-link { background: var(--accent-primary); color: white; }

        /* BUTTONS */
        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; font-family: inherit; }
        .btn-primary { background: var(--accent-primary); color: white; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline { background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--accent-primary); }
        .btn-outline:hover { background: var(--accent-primary); color: white; }

        /* MITIGATE BUTTON */
        .btn-mitigate { background: var(--accent-primary); color: white; border: none; padding: 0.4rem 0.6rem; border-radius: 0.35rem; cursor: pointer; transition: all 0.2s; font-size: 0.9rem; }
        .btn-mitigate:hover { transform: scale(1.1); box-shadow: 0 2px 8px var(--accent-glow); }

        /* MODAL */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); z-index: 1000; display: flex; align-items: center; justify-content: center; }
        .modal-container { background: var(--bg-card); border-radius: 1rem; width: 90%; max-width: 550px; max-height: 85vh; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); display: flex; flex-direction: column; }
        .modal-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 1.25rem; overflow-y: auto; flex: 1; }
        .modal-close { background: none; border: none; color: var(--text-secondary); font-size: 1.25rem; cursor: pointer; }
        .modal-close:hover { color: var(--danger); }

        .pr-info { background: var(--bg-card-hover); padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .pr-info-row { display: flex; gap: 0.5rem; font-size: 0.85rem; margin-bottom: 0.35rem; }
        .pr-info-row .label { color: var(--text-secondary); min-width: 80px; }

        .mitigation-section { margin-bottom: 1rem; }
        .mitigation-section label { font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 0.35rem; }
        .mitigation-section textarea { width: 100%; background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.75rem; color: var(--text-primary); font-family: inherit; font-size: 0.85rem; resize: vertical; }
        .mitigation-section select { background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.35rem; padding: 0.5rem 0.75rem; color: var(--text-primary); font-family: inherit; font-size: 0.8rem; }

        .chat-section h4 { font-size: 0.9rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem; }
        .chat-messages { background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.75rem; max-height: 250px; overflow-y: auto; margin-bottom: 0.75rem; }
        .chat-message { margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border); }
        .chat-message:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .chat-author { font-size: 0.75rem; font-weight: 600; color: var(--accent-primary); }
        .chat-time { font-size: 0.65rem; color: var(--text-secondary); margin-left: 0.5rem; }
        .chat-text { font-size: 0.8rem; margin-top: 0.25rem; color: var(--text-primary); }
        .chat-empty { text-align: center; color: var(--text-secondary); font-size: 0.8rem; padding: 1rem; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="{{ asset('images/logomtmfix.png') }}" alt="MTM Logo" style="height: 50px; width: auto; border-radius: 8px;">
                    <div>
                        <h1 style="margin:0; font-size: 1.5rem;">Requestor Dashboard</h1>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin:0;">
                            Department: <span style="color: var(--primary); font-weight: 600;">{{ $dept }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="btn-theme-toggle" onclick="toggleTheme()" title="Switch Theme">
                    <i class="ph ph-sun" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>

                <form action="{{ route('requestor.logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="border-color: var(--danger); color: var(--danger);">
                        <i class="ph ph-sign-out"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- KPIs - 5 Columns -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Total Requisitions</div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-icon"><i class="ph ph-file-text"></i></div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-title">Processed (PO)</div>
                <div class="kpi-value" style="color: var(--success);">{{ number_format($stats['processed']) }}</div>
                <div class="kpi-icon" style="color: var(--success);"><i class="ph ph-check-circle"></i></div>
            </div>

            <div class="kpi-card">
                <div class="kpi-title">Overdue</div>
                <div class="kpi-value" style="color: var(--danger);">{{ number_format($stats['overdue']) }}</div>
                <div class="kpi-icon" style="color: var(--danger);"><i class="ph ph-warning-circle"></i></div>
            </div>

            <div class="kpi-card">
                <div class="kpi-title">Pending</div>
                <div class="kpi-value" style="color: var(--warning);">{{ number_format($stats['pending']) }}</div>
                <div class="kpi-icon" style="color: var(--warning);"><i class="ph ph-clock"></i></div>
            </div>

            <div class="kpi-card" style="border-color: {{ $stats['feedback_waiting'] > 0 ? '#3b82f6' : 'var(--border)' }}; {{ $stats['feedback_waiting'] > 0 ? 'box-shadow: 0 0 12px rgba(59,130,246,0.25);' : '' }}">
                <div class="kpi-title">Butuh Respon Anda</div>
                <div class="kpi-value" style="color: #3b82f6;">{{ number_format($stats['feedback_waiting']) }}</div>
                <div class="kpi-icon" style="color: #3b82f6;"><i class="ph ph-chat-dots"></i></div>
                @if($stats['feedback_waiting'] > 0)
                    <div class="trend" style="color: #3b82f6;">
                        <i class="ph ph-bell-ringing"></i> Ada pertanyaan dari admin
                    </div>
                @endif
            </div>
        </div>

        <!-- Charts Row 1: Volume & Status -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Your Requisition Volume</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Status Distribution</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: Timeline & Trend -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>PR Process Timeline (Global Average)</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Average Lead Time Trend (Global)</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pending Requisitions (No PO) -->
        <div class="table-card">
            <div class="table-header">
                <h3><i class="ph ph-warning-circle" style="color: var(--warning);"></i> Your Pending Requisitions</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>PR Number</th>
                            <th>Dept</th>
                            <th>Description</th>
                            <th>Req. Date</th>
                            <th>Aging</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $pr)
                            @php
                                $age = $pr->req_date ? floor($pr->req_date->diffInDays(now())) : 0;
                                $isOverdue = $age > 14;
                            @endphp
                            <tr>
                                <td style="font-family: monospace; color: var(--accent-primary); font-weight: 500;">{{ $pr->pr_number }}</td>
                                <td>{{ $pr->purchasing_group ?? '-' }} <span style="font-size: 0.7rem; color: var(--text-secondary);">({{ $pr->department ?? '-' }})</span></td>
                                <td>{{ Str::limit($pr->short_text, 35) }}</td>
                                <td>{{ $pr->req_date ? $pr->req_date->format('d.m.Y') : '-' }}</td>
                                <td>
                                    <span style="color: {{ $isOverdue ? 'var(--danger)' : 'var(--text-secondary)' }}; font-weight: {{ $isOverdue ? '600' : '400' }};">
                                        {{ $age }} hari
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $stepFollowUp = in_array($pr->feedback_status, ['waiting', 'responded']);
                                        $stepFeedback = $pr->feedback_status === 'responded';
                                        $stepReleased = !empty($pr->po_number);
                                    @endphp
                                    <div class="step-tracker">
                                        <span class="step-item {{ $stepFollowUp ? 'done' : ($isOverdue ? 'active' : 'pending') }}">
                                            @if($stepFollowUp)<i class="ph ph-check-circle"></i>@else<i class="ph ph-circle"></i>@endif
                                            Follow-up
                                        </span>
                                        <span class="step-connector">›</span>
                                        <span class="step-item {{ $stepFeedback ? 'done' : ($stepFollowUp ? 'active' : 'pending') }}">
                                            @if($stepFeedback)<i class="ph ph-check-circle"></i>@else<i class="ph ph-circle"></i>@endif
                                            Feedback
                                        </span>
                                        <span class="step-connector">›</span>
                                        <span class="step-item {{ $stepReleased ? 'done' : ($stepFeedback ? 'active' : 'pending') }}">
                                            @if($stepReleased)<i class="ph ph-check-circle"></i>@else<i class="ph ph-circle"></i>@endif
                                            Released
                                        </span>
                                    </div>
                                </td>
                                <td style="max-width: 150px;">
                                    <span style="font-size: 0.75rem; color: var(--text-secondary);">
                                        {{ Str::limit($pr->mitigation_reason, 30) ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if(!$pr->po_number)
                                        <button type="button" class="btn-mitigate" data-pr-id="{{ $pr->id }}" title="Lihat Detail"
                                            @if($pr->feedback_status === 'waiting') style="background: #3b82f6; animation: pulse-badge 2s infinite;" @endif>
                                            @if($pr->feedback_status === 'waiting')
                                                <i class="ph ph-chat-dots"></i>
                                            @else
                                                <i class="ph ph-eye"></i>
                                            @endif
                                        </button>
                                    @else
                                        <span style="color: var(--success); font-size: 0.8rem;"><i class="ph ph-check-circle"></i> Done</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    No requisitions found for {{ $dept }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Read-Only Modal -->
    <div id="mitigationModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="ph ph-info"></i> Detail PR: <span id="modalPrNumber"></span></h3>
                <button class="modal-close" onclick="closeMitigationModal()">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="pr-info">
                    <div class="pr-info-row">
                        <span class="label">Deskripsi:</span>
                        <span id="modalDesc"></span>
                    </div>
                    <div class="pr-info-row">
                        <span class="label">Status:</span>
                        <span id="modalStatus"></span>
                    </div>
                    <div class="pr-info-row">
                        <span class="label">Usia PR:</span>
                        <span id="modalDays"></span>
                    </div>
                </div>

                <!-- Feedback from Admin Section -->
                <div id="feedbackSection" style="margin-bottom: 1rem; display: none;">
                    <div style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); border-radius: 0.5rem; padding: 0.75rem;">
                        <h4 style="font-size: 0.85rem; color: #3b82f6; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            <i class="ph ph-question"></i> Pertanyaan dari Admin Purchasing
                        </h4>
                        <div id="feedbackQuestionDisplay" style="background: var(--bg-card); padding: 0.5rem; border-radius: 0.35rem; font-size: 0.85rem; margin-bottom: 0.5rem;"></div>
                        <div id="feedbackTimestamp" style="font-size: 0.7rem; color: var(--text-secondary); margin-bottom: 0.75rem;"></div>
                        
                        <!-- Response form (only shown when waiting) -->
                        <div id="feedbackResponseForm" style="display: none;">
                            <label style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 0.35rem;">
                                <i class="ph ph-pencil-line"></i> Tulis respon Anda:
                            </label>
                            <textarea id="feedbackResponse" placeholder="Jelaskan alasan atau status PR ini..." rows="3"
                                style="width: 100%; background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.35rem; padding: 0.5rem; color: var(--text-primary); font-family: inherit; font-size: 0.85rem; resize: vertical;"></textarea>
                            <button class="btn btn-primary" onclick="respondFeedback()" style="margin-top: 0.5rem;">
                                <i class="ph ph-paper-plane-tilt"></i> Kirim Respon ke Admin
                            </button>
                        </div>

                        <!-- Already responded display -->
                        <div id="feedbackRespondedDisplay" style="display: none;">
                            <div style="font-size: 0.75rem; color: var(--success); margin-bottom: 0.25rem;"><i class="ph ph-check-circle"></i> Respon Anda:</div>
                            <div id="feedbackResponseText" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); padding: 0.5rem; border-radius: 0.35rem; font-size: 0.85rem;"></div>
                            <div id="feedbackResponseTimestamp" style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.25rem;"></div>
                        </div>
                    </div>
                </div>

                <div class="mitigation-section">
                    <label>Alasan Keterlambatan / Masalah:</label>
                    <textarea id="mitigationReason" rows="3" disabled style="background-color: var(--bg-card); opacity: 0.7;"></textarea>
                    
                    <div style="margin-top: 0.5rem;">
                        <label>Status Mitigasi:</label>
                        <select id="mitigationStatus" disabled style="background-color: var(--bg-card); opacity: 0.7;">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                </div>

                <div class="chat-section">
                    <h4><i class="ph ph-chats"></i> Log Komentar</h4>
                    <div id="chatMessages" class="chat-messages" style="max-height: 250px;"></div>
                    
                    <div class="chat-input" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <input type="text" id="authorName" placeholder="Nama Anda" style="width: 120px; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.35rem; background: var(--bg-body); color: var(--text-primary);">
                        <input type="text" id="commentMessage" placeholder="Tulis komentar..." style="flex: 1; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.35rem; background: var(--bg-body); color: var(--text-primary);">
                        <button class="btn btn-primary" onclick="sendComment()">
                            <i class="ph ph-paper-plane-tilt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use PHP to pass chart data works, but we removed chart from HTML for this view.
        // If we want to keep chart, we need to add it back.
        // Controller didn't pass 'chartData' variable in index() method?
        // Let's check RequestorController.php content.
        // It passed 'dept', 'requisitions', 'stats'. NO 'chartData'.
        // So I REMOVED the chart HTML and Script logic in this file content to avoid errors.
        
        function getTheme() { return localStorage.getItem('theme') || 'dark'; }
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeUI(theme);
        }
        function toggleTheme() {
            const current = getTheme();
            setTheme(current === 'dark' ? 'light' : 'dark');
        }
        function updateThemeUI(theme) {
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (theme === 'dark') {
                icon.className = 'ph ph-sun';
                text.textContent = 'Light Mode';
            } else {
                icon.className = 'ph ph-moon';
                text.textContent = 'Dark Mode';
            }
        }

        document.documentElement.setAttribute('data-theme', getTheme());
        updateThemeUI(getTheme());

        // Modal Logic
        let currentPrId = null;
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-mitigate');
            if (btn) {
                const prId = btn.getAttribute('data-pr-id');
                if (prId) openMitigationModal(prId);
            }
        });

        function openMitigationModal(prId) {
            currentPrId = prId;
            document.getElementById('mitigationModal').style.display = 'flex';
            
            fetch(`/requestor/pr/${prId}/details`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalPrNumber').textContent = data.pr_number;
                    document.getElementById('modalDesc').textContent = data.short_text || '-';
                    document.getElementById('modalDays').textContent = data.days_overdue + ' hari';
                    
                    const stepFollowUp = data.feedback_status === 'waiting' || data.feedback_status === 'responded';
                    const stepFeedback = data.feedback_status === 'responded';
                    const stepReleased = !!data.po_number;
                    const isOverdue = data.days_overdue > 14;

                    function stepClass(done, activeCond) {
                        if (done) return 'done';
                        if (activeCond) return 'active';
                        return 'pending';
                    }
                    function stepIcon(done) {
                        return done ? '<i class="ph ph-check-circle"></i>' : '<i class="ph ph-circle"></i>';
                    }

                    let statusHtml = `<div class="step-tracker">
                        <span class="step-item ${stepClass(stepFollowUp, isOverdue)}">${stepIcon(stepFollowUp)} Follow-up</span>
                        <span class="step-connector">›</span>
                        <span class="step-item ${stepClass(stepFeedback, stepFollowUp)}">${stepIcon(stepFeedback)} Feedback</span>
                        <span class="step-connector">›</span>
                        <span class="step-item ${stepClass(stepReleased, stepFeedback)}">${stepIcon(stepReleased)} Released</span>
                    </div>`;
                    document.getElementById('modalStatus').innerHTML = statusHtml;
                    document.getElementById('mitigationReason').value = data.mitigation_reason || '';
                    document.getElementById('mitigationStatus').value = data.mitigation_status || 'open';
                    
                    // Handle feedback section
                    renderFeedbackSection(data);
                    
                    renderComments(data.comments);
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Error loading details');
                });
        }

        function renderFeedbackSection(data) {
            const section = document.getElementById('feedbackSection');
            const form = document.getElementById('feedbackResponseForm');
            const responded = document.getElementById('feedbackRespondedDisplay');

            if (data.feedback_status === 'waiting') {
                section.style.display = 'block';
                document.getElementById('feedbackQuestionDisplay').textContent = data.feedback_question;
                document.getElementById('feedbackTimestamp').textContent = 'Dikirim: ' + data.feedback_asked_at;
                form.style.display = 'block';
                responded.style.display = 'none';
                document.getElementById('feedbackResponse').value = '';
            } else if (data.feedback_status === 'responded') {
                section.style.display = 'block';
                document.getElementById('feedbackQuestionDisplay').textContent = data.feedback_question;
                document.getElementById('feedbackTimestamp').textContent = 'Dikirim: ' + data.feedback_asked_at;
                form.style.display = 'none';
                responded.style.display = 'block';
                document.getElementById('feedbackResponseText').textContent = data.feedback_response;
                document.getElementById('feedbackResponseTimestamp').textContent = 'Direspon: ' + data.feedback_responded_at;
            } else {
                section.style.display = 'none';
            }
        }

        function respondFeedback() {
            if (!currentPrId) return;
            const response = document.getElementById('feedbackResponse').value.trim();
            if (!response) {
                alert('Tulis respon Anda terlebih dahulu');
                document.getElementById('feedbackResponse').focus();
                return;
            }

            fetch(`/requestor/pr/${currentPrId}/respond-feedback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ response: response })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Respon berhasil dikirim ke admin purchasing!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || data.error || 'Gagal mengirim respon'));
                }
            })
            .catch(err => {
                console.error('Error responding:', err);
                alert('Error mengirim respon');
            });
        }

        function closeMitigationModal() {
            document.getElementById('mitigationModal').style.display = 'none';
        }

        document.getElementById('mitigationModal').addEventListener('click', function(e) {
            if (e.target === this) closeMitigationModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeMitigationModal();
        });

        function renderComments(comments) {
            const container = document.getElementById('chatMessages');
            if (!comments || comments.length === 0) {
                container.innerHTML = '<div class="chat-empty">Belum ada komentar</div>';
                return;
            }
            container.innerHTML = comments.map(c => `
                <div class="chat-message">
                    <span class="chat-author">${escapeHtml(c.author_name)}</span>
                    <span class="chat-time">${c.created_at}</span>
                    <div class="chat-text">${escapeHtml(c.message)}</div>
                </div>
            `).join('');
            container.scrollTop = container.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ===== CHARTS =====
        let volumeChart = null;
        let statusChart = null;
        let timelineChart = null;
        let trendChart = null;

        // Initialize theme and charts
        document.addEventListener('DOMContentLoaded', function() {
            createCharts();
        });

        function getChartColors(theme) {
            return {
                grid: theme === 'dark' ? '#334155' : '#e2e8f0',
                text: theme === 'dark' ? '#94a3b8' : '#64748b',
                legend: theme === 'dark' ? '#f8fafc' : '#1e293b'
            };
        }

        function createCharts() {
            const theme = getTheme();
            const colors = getChartColors(theme);

            // 1. Volume Chart
            const ctxVolume = document.getElementById('volumeChart').getContext('2d');
            volumeChart = new Chart(ctxVolume, {
                type: 'bar',
                data: {
                    labels: @json($chartData['months']),
                    datasets: [
                        {
                            label: 'PR Created',
                            data: @json($chartData['pr']),
                            backgroundColor: '#6366f1',
                            borderRadius: 4
                        },
                        {
                            label: 'PO Created',
                            data: @json($chartData['po']),
                            backgroundColor: '#10b981',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: colors.grid },
                            ticks: { color: colors.text }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: colors.text }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: colors.legend } }
                    }
                }
            });

            // 2. Status Distribution (Doughnut)
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Processed', 'Pending', 'Overdue'],
                    datasets: [{
                        data: @json($chartData['status']),
                        backgroundColor: [
                            '#10b981', // Green
                            '#f59e0b', // Yellow
                            '#ef4444'  // Red
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: colors.legend } }
                    }
                }
            });

            // 3 & 4. Timeline & Trend (API)
            fetch('{{ route("api.timeline") }}')
                .then(res => res.json())
                .then(apiData => {
                    const timelineData = apiData.timeline;
                    const trendData = apiData.trend;

                    // Timeline Chart
                    const ctxTimeline = document.getElementById('timelineChart').getContext('2d');
                    timelineChart = new Chart(ctxTimeline, {
                        type: 'bar',
                        data: {
                            labels: timelineData.labels,
                            datasets: [{
                                label: 'Average Days',
                                data: timelineData.data,
                                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                                borderColor: '#6366f1',
                                borderWidth: 1,
                                borderRadius: 4,
                                barPercentage: 0.6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: colors.grid },
                                    ticks: { color: colors.text }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: colors.text }
                                }
                            },
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });

                    // Trend Chart
                    const ctxTrend = document.getElementById('trendChart').getContext('2d');
                    trendChart = new Chart(ctxTrend, {
                        type: 'bar',
                        data: {
                            labels: trendData.labels,
                            datasets: [{
                                label: 'Avg Lead Time (Days)',
                                data: trendData.data,
                                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                borderRadius: 4,
                                barPercentage: 0.6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: colors.grid },
                                    ticks: { color: colors.text }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: colors.text }
                                }
                            },
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                });
        }

        function updateCharts(theme) {
            if (!volumeChart || !statusChart) return;
            
            const colors = getChartColors(theme);
            
            volumeChart.options.scales.y.grid.color = colors.grid;
            volumeChart.options.scales.y.ticks.color = colors.text;
            volumeChart.options.scales.x.ticks.color = colors.text;
            volumeChart.options.plugins.legend.labels.color = colors.legend;
            volumeChart.update();

            statusChart.options.plugins.legend.labels.color = colors.legend;
            statusChart.update();

            if (timelineChart) {
                timelineChart.options.scales.x.grid.color = colors.grid;
                timelineChart.options.scales.x.ticks.color = colors.text;
                timelineChart.options.scales.y.ticks.color = colors.text;
                timelineChart.update();
            }

            if (trendChart) {
                trendChart.options.scales.x.grid.color = colors.grid;
                trendChart.options.scales.x.ticks.color = colors.text;
                trendChart.options.scales.y.ticks.color = colors.text;
                trendChart.update();
            }
        }

        function sendComment() {
            if (!currentPrId) return;

            const authorName = document.getElementById('authorName').value;
            const message = document.getElementById('commentMessage').value;

            if (!authorName || !message) {
                alert('Please fill in both Name and Message');
                return;
            }

            fetch(`/requestor/pr/${currentPrId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    author_name: authorName,
                    message: message
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('commentMessage').value = '';
                    // Reload modal
                    openMitigationModal(currentPrId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred');
            });
        }
    </script>
</body>
</html>
