<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Requisition Monitoring</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ===== THEME VARIABLES ===== */
        :root {
            /* Light Mode (Default) */
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

        /* ===== MAIN LAYOUT ===== */
        .main-content {
            flex: 1;
            padding: 1.5rem 2rem;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        /* Top Bar Style (for Logo/Nav if needed, or just Header) */
        
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
        .header h1 { font-size: 1.5rem; font-weight: 600; }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--accent-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
        }

        /* ===== KPI GRID - COMPACT 4 COLUMNS ===== */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 1200px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .kpi-grid { grid-template-columns: 1fr; }
        }

        .kpi-card {
            background: var(--bg-card);
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
            box-shadow: 0 2px 8px var(--shadow);
        }
        .kpi-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 20px var(--shadow);
        }
        .kpi-title { 
            font-size: 0.75rem; 
            color: var(--text-secondary); 
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-value { 
            font-size: 1.5rem; 
            font-weight: 700; 
            color: var(--text-primary); 
        }
        .kpi-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            opacity: 0.15;
        }
        .trend { 
            display: flex; 
            align-items: center; 
            font-size: 0.7rem; 
            margin-top: 0.35rem; 
            gap: 0.2rem; 
        }
        .trend.up { color: var(--success); }
        .trend.down { color: var(--danger); }

        /* ===== CHARTS SECTION ===== */
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
            transition: background-color 0.3s;
        }
        .chart-header {
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chart-header h3 { font-size: 0.95rem; font-weight: 600; }

        /* ===== TABLE SECTION ===== */
        .table-card {
            background: var(--bg-card);
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 2px 8px var(--shadow);
            transition: background-color 0.3s;
        }
        .table-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h3 { font-size: 0.95rem; font-weight: 600; }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }
        th, td {
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        th {
            color: var(--text-secondary);
            font-weight: 500;
            background: var(--bg-card-hover);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:hover { background-color: var(--bg-card-hover); }
        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-processed { background: rgba(16, 185, 129, 0.15); color: var(--success); }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
        .status-overdue { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
        
        .pagination {
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: center;
        }
        .pagination nav { display: flex; gap: 0.35rem; }
        .page-link {
            padding: 0.4rem 0.8rem;
            border-radius: 0.35rem;
            background: var(--bg-card-hover);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.8rem;
        }
        .page-item.active .page-link {
            background: var(--accent-primary);
            color: white;
        }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-family: inherit;
        }
        .btn-primary {
            background: var(--accent-primary);
            color: white;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-outline {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--accent-primary);
        }
        .btn-outline:hover {
            background: var(--accent-primary);
            color: white;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        /* ===== ANIMATIONS ===== */
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* ===== MITIGATE BUTTON ===== */
        .btn-mitigate {
            background: var(--accent-primary);
            color: white;
            border: none;
            padding: 0.4rem 0.6rem;
            border-radius: 0.35rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .btn-mitigate:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px var(--accent-glow);
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-container {
            background: var(--bg-card);
            border-radius: 1rem;
            width: 90%;
            max-width: 550px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .modal-close:hover { color: var(--danger); }
        .modal-body {
            padding: 1.25rem;
            overflow-y: auto;
            flex: 1;
        }

        /* PR Info */
        .pr-info {
            background: var(--bg-card-hover);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .pr-info-row {
            display: flex;
            gap: 0.5rem;
            font-size: 0.85rem;
            margin-bottom: 0.35rem;
        }
        .pr-info-row:last-child { margin-bottom: 0; }
        .pr-info-row .label {
            color: var(--text-secondary);
            min-width: 80px;
        }

        /* Mitigation Section */
        .mitigation-section {
            margin-bottom: 1rem;
        }
        .mitigation-section label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: block;
            margin-bottom: 0.35rem;
        }
        .mitigation-section textarea {
            width: 100%;
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.75rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.85rem;
            resize: vertical;
        }
        .mitigation-section textarea:focus {
            outline: none;
            border-color: var(--accent-primary);
        }
        .mitigation-section select {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.35rem;
            padding: 0.5rem 0.75rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.8rem;
        }

        /* Chat Section */
        .chat-section h4 {
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .chat-messages {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.75rem;
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 0.75rem;
        }
        .chat-message {
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }
        .chat-message:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .chat-author {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--accent-primary);
        }
        .chat-time {
            font-size: 0.65rem;
            color: var(--text-secondary);
            margin-left: 0.5rem;
        }
        .chat-text {
            font-size: 0.8rem;
            margin-top: 0.25rem;
            color: var(--text-primary);
        }
        .chat-input {
            display: flex;
            gap: 0.5rem;
        }
        .chat-input input {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.35rem;
            padding: 0.5rem 0.75rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.8rem;
        }
        .chat-input input:focus {
            outline: none;
            border-color: var(--accent-primary);
        }
        .chat-empty {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.8rem;
            padding: 1rem;
        }

        /* ===== NOTIFICATIONS ===== */
        .notification-wrapper {
            position: relative;
            cursor: pointer;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            width: 18px; height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid var(--bg-body);
        }
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 320px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px var(--shadow);
            z-index: 1000;
            display: none;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .notification-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .notification-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 0.75rem;
            transition: background-color 0.2s;
        }
        .notification-item:hover {
            background: var(--bg-card-hover);
        }
        .notification-item.unread {
            background: rgba(99, 102, 241, 0.05);
        }
        .notification-icon {
            color: var(--success);
            font-size: 1.25rem;
            margin-top: 0.1rem;
        }
        .notification-content {
            flex: 1;
        }
        .notification-message {
            font-size: 0.85rem;
            color: var(--text-primary);
            line-height: 1.4;
            margin-bottom: 0.25rem;
        }
        .notification-time {
            font-size: 0.7rem;
            color: var(--text-secondary);
        }
        .notification-empty {
            padding: 2rem;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="ph ph-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <i class="ph ph-warning-circle"></i>
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <i class="ph ph-warning-circle"></i>
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <div class="header">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="{{ asset('images/logomtmfix.png') }}" alt="MTM Logo" style="height: 30px; width: auto; border-radius: 8px;">
                    <div>
                        <h1 style="margin:0; font-size: 1.5rem;">Monitoring Dashboard</h1>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin:0;">Real-time PR to PO conversion tracking</p>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <!-- Theme Toggle -->
                <button class="btn-theme-toggle" onclick="toggleTheme()" title="Switch Theme">
                    <i class="ph ph-sun" id="themeIcon"></i>
                    <span id="themeText">Light Mode</span>
                </button>

                <!-- Import Action -->
                <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <input type="file" name="file" id="fileInput" accept=".xlsx, .xls, .csv, .html, .mhtml" style="display: none;" onchange="this.form.submit()">
                    <button type="button" onclick="document.getElementById('fileInput').click()" class="btn btn-outline" title="Sync SAP Data">
                        <i class="ph ph-file-arrow-up" style="color: var(--success);"></i>
                        <span style="display: none; @media(min-width: 1024px){ display: inline; }">Sync SAP</span>
                    </button>
                </form>

                <!-- Requestor View Link -->
                <a href="{{ route('requestor.login') }}" class="btn btn-outline" title="Requestor View">
                    <i class="ph ph-users"></i>
                    <span style="display: none; @media(min-width: 1024px){ display: inline; }">Requestor View</span>
                </a>

                <div class="user-profile">
                    <!-- Notification Bell -->
                    <div class="notification-wrapper" onclick="toggleNotifications()">
                        <i class="ph ph-bell" style="font-size: 1.25rem; color: var(--text-secondary);"></i>
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <div class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</div>
                        @endif
                        
                        <!-- Dropdown -->
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <span>Notifications</span>
                                @if(isset($unreadCount) && $unreadCount > 0)
                                    <span style="font-size: 0.7rem; color: var(--accent-primary); cursor: pointer;">Mark all read</span>
                                @endif
                            </div>
                            <div class="notification-list">
                                @if(isset($notifications) && count($notifications) > 0)
                                    @foreach($notifications as $notification)
                                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                                            <div class="notification-icon">
                                                <i class="ph ph-check-circle"></i>
                                            </div>
                                            <div class="notification-content">
                                                <div class="notification-message">{{ $notification->data['message'] ?? 'Notification' }}</div>
                                                <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="notification-empty">
                                        <i class="ph ph-bell-slash" style="font-size: 1.5rem; margin-bottom: 0.5rem; display: block;"></i>
                                        No new notifications
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="avatar">PM</div>
                </div>
            </div>
        </div>

        <!-- KPIs - 4 Columns -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Total Requisitions</div>
                <div class="kpi-value">{{ number_format($totalPR) }}</div>
                <div class="kpi-icon"><i class="ph ph-file-text"></i></div>
                <div class="trend up"><i class="ph ph-trend-up"></i> All PRs</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-title">Processed (PO)</div>
                <div class="kpi-value" style="color: var(--success);">{{ number_format($processedPO) }}</div>
                <div class="kpi-icon" style="color: var(--success);"><i class="ph ph-check-circle"></i></div>
                <div class="trend up"><i class="ph ph-check"></i> {{ $totalPR > 0 ? round(($processedPO / $totalPR) * 100) : 0 }}% Success Rate</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-title">Overdue</div>
                <div class="kpi-value" style="color: var(--danger);">{{ number_format($overduePR) }}</div>
                <div class="kpi-icon" style="color: var(--danger);"><i class="ph ph-warning-circle"></i></div>
                <div class="trend down"><i class="ph ph-warning"></i> {{ $totalPR > 0 ? round(($overduePR / $totalPR) * 100) : 0 }}% of Total</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-title">Avg. Lead Time</div>
                <div class="kpi-value">{{ $avgLeadTime }} <span style="font-size: 0.85rem; font-weight: 400;">days</span></div>
                <div class="kpi-icon"><i class="ph ph-timer"></i></div>
                <div class="trend {{ $avgLeadTime < 10 ? 'up' : 'down' }}">Target: < 7 days</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>PR vs PO Volume (Last 12 Months)</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Department PR Distribution</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Timeline & Trend Charts -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>PR Process Timeline (Average Days)</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Average Lead Time (PR -> PO)</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pending Requisitions (No PO) -->
        <div class="table-card" id="pendingRequisitionsCard">
            <div class="section-header" style="justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <h3>
                    @if(request('search'))
                        <i class="ph ph-magnifying-glass" style="color: var(--accent-primary);"></i> Search Results
                    @else
                        <i class="ph ph-warning-circle" style="color: var(--warning);"></i> Pending Requisitions (Belum ada PO)
                    @endif
                </h3>
                
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <form action="{{ route('dashboard') }}" method="GET" style="display: flex; gap: 0.5rem;">
                        <input type="text" name="search" placeholder="Search PO, Dept, or PR..." value="{{ request('search') }}" 
                               style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid var(--border); background: var(--bg-body); color: var(--text-primary); font-size: 0.85rem; width: 200px;">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 0.75rem;">
                            <i class="ph ph-magnifying-glass"></i>
                        </button>
                    </form>
                    
                    @if(request('search'))
                        <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding: 0.5rem 0.75rem; text-decoration: none;">
                            <i class="ph ph-x"></i> Clear
                        </a>
                    @endif
                </div>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisitions as $pr)
                            @php
                                $age = $pr->req_date ? floor($pr->req_date->diffInDays(now())) : 0;
                                $isOverdue = $age > 14;
                            @endphp
                            <tr>
                                <td style="font-family: monospace; color: var(--accent-primary); font-weight: 500;">{{ $pr->pr_number }}</td>
                                <td>{{ $pr->purchasing_group ?? '-' }}</td>
                                <td>{{ Str::limit($pr->short_text, 25) }}</td>
                                <td>{{ $pr->req_date ? $pr->req_date->format('d.m.Y') : '-' }}</td>
                                <td>
                                    <span style="color: {{ $isOverdue ? 'var(--danger)' : 'var(--text-secondary)' }}; font-weight: {{ $isOverdue ? '600' : '400' }};">
                                        {{ $age }} hari
                                    </span>
                                </td>
                                <td>
                                    @if($pr->po_number)
                                        <span class="status-badge status-processed">Processed</span>
                                    @elseif($isOverdue)
                                        <span class="status-badge status-overdue">Overdue</span>
                                    @else
                                        <span class="status-badge status-pending">Pending</span>
                                    @endif
                                </td>
                                <td style="max-width: 150px;">
                                    <span style="font-size: 0.75rem; color: var(--text-secondary);">
                                        {{ Str::limit($pr->mitigation_reason, 30) ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if(!$pr->po_number)
                                        <button type="button" class="btn-mitigate" data-pr-id="{{ $pr->id }}" title="Mitigasi">
                                            <i class="ph ph-chat-circle-text"></i>
                                        </button>
                                        <button type="button" class="btn-mitigate" onclick="openConvertModal({{ $pr->id }}, '{{ $pr->pr_number }}')" title="Convert to PO" style="background: var(--success); margin-left: 5px;">
                                            <i class="ph ph-check"></i>
                                        </button>
                                    @else
                                        <span style="color: var(--success); font-size: 0.8rem;"><i class="ph ph-check-circle"></i> Done</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                {{ $requisitions->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    <!-- Mitigation Modal -->
    <div id="mitigationModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="ph ph-chat-circle-text"></i> Mitigasi PR: <span id="modalPrNumber"></span></h3>
                <button class="modal-close" onclick="closeMitigationModal()">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <!-- PR Info -->
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

                <!-- Reason Section -->
                <div class="mitigation-section">
                    <label>Alasan Keterlambatan / Masalah:</label>
                    <textarea id="mitigationReason" placeholder="Tuliskan alasan..." rows="3"></textarea>
                    <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.5rem;">
                        <select id="mitigationStatus">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                        <button class="btn btn-primary" onclick="saveMitigation()">
                            <i class="ph ph-floppy-disk"></i> Simpan
                        </button>
                    </div>
                </div>

                <!-- Chat Section -->
                <div class="chat-section">
                    <h4><i class="ph ph-chats"></i> Diskusi Mitigasi</h4>
                    <div id="chatMessages" class="chat-messages">
                        <!-- Messages will be loaded here -->
                    </div>
                    
                    <div class="chat-input">
                        <input type="text" id="authorName" placeholder="Nama Anda" style="width: 120px;">
                        <input type="text" id="commentMessage" placeholder="Tulis komentar..." style="flex: 1;">
                        <button class="btn btn-primary" onclick="sendComment()">
                            <i class="ph ph-paper-plane-tilt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Convert PO Modal -->
    <div id="convertModal" class="modal-overlay" style="display: none;">
        <div class="modal-container" style="max-width: 400px;">
            <div class="modal-header">
                <h3><i class="ph ph-check-circle"></i> Convert to PO</h3>
                <button class="modal-close" onclick="closeConvertModal()">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    Enter PO Number for PR: <span id="convertPrNumber" style="font-weight: bold; color: var(--text-primary);"></span>
                </p>
                <input type="hidden" id="convertPrId">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; margin-bottom: 0.5rem;">PO Number</label>
                    <input type="text" id="poNumberInput" class="form-control" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.35rem; background: var(--bg-body); color: var(--text-primary);">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button class="btn btn-outline" onclick="closeConvertModal()">Cancel</button>
                    <button class="btn btn-primary" onclick="submitPoConversion()">Convert & Release</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script>
        // ===== THEME MANAGEMENT =====
        function getTheme() {
            return localStorage.getItem('theme') || 'dark';
        }

        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeUI(theme);
            updateCharts(theme);
        }

        function toggleTheme() {
            const current = getTheme();
            setTheme(current === 'dark' ? 'light' : 'dark');
        }

        // Convert PO Functions
        function openConvertModal(id, prNumber) {
            document.getElementById('convertPrId').value = id;
            document.getElementById('convertPrNumber').textContent = prNumber;
            document.getElementById('convertModal').style.display = 'flex';
        }

        function closeConvertModal() {
            document.getElementById('convertModal').style.display = 'none';
            document.getElementById('poNumberInput').value = '';
        }

        function submitPoConversion() {
            const id = document.getElementById('convertPrId').value;
            const poNumber = document.getElementById('poNumberInput').value;

            if (!poNumber) {
                alert('Please enter a PO Number');
                return;
            }

            fetch(`/pr/${id}/convert`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ po_number: poNumber })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Success! PR converted to PO.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred.');
            });
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

        // ===== CHARTS =====
        // ===== CHARTS =====
        let volumeChart = null;

        let statusChart = null;
        let timelineChart = null;
        let trendChart = null;

        // Initialize theme (before charts, but updateCharts will check if charts exist)
        document.documentElement.setAttribute('data-theme', getTheme());
        updateThemeUI(getTheme());

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

            // Volume Chart
            const ctxVolume = document.getElementById('volumeChart').getContext('2d');
            const rates = @json($chartData['rates']); // Get rates from backend

            // Custom Plugin for Percentage Labels
            const volumePercentagePlugin = {
                id: 'volumePercentagePlugin',
                afterDraw: (chart) => {
                    const ctx = chart.ctx;
                    const xAxis = chart.scales.x;
                    const ThemeColors = getChartColors(getTheme());
                    
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.font = 'italic 10px Outfit'; 
                    ctx.fillStyle = ThemeColors.text; 
                    
                    if (xAxis && xAxis.ticks) {
                        xAxis.ticks.forEach((tick, index) => {
                            if (rates[index] !== undefined) {
                                const x = xAxis.getPixelForTick(index);
                                const y = xAxis.bottom + 10; // Position below the label
                                ctx.fillText(`(${rates[index]}%)`, x, y);
                            }
                        });
                    }
                    ctx.restore();
                }
            };

            volumeChart = new Chart(ctxVolume, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
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
                    layout: {
                        padding: {
                            bottom: 20 // Make room for percentage text
                        }
                    },
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
                },
                plugins: [volumePercentagePlugin]
            });

            // Status Chart
            // Department Chart (Line)
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            statusChart = new Chart(ctxStatus, {
                type: 'line',
                data: {
                    labels: @json($deptChartData['labels']),
                    datasets: [{
                        label: 'Total PR',
                        data: @json($deptChartData['data']),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#f59e0b'
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
                            ticks: { color: colors.text, font: { size: 10 } }
                        }
                    },
                    plugins: {
                        legend: { display: true, labels: { color: colors.legend } }
                    }
                }
            });

            // Timeline & Trend Charts
            fetch('{{ route("api.timeline") }}')
                .then(res => res.json())
                .then(apiData => {
                    const timelineData = apiData.timeline;
                    const trendData = apiData.trend;

                    // 1. Timeline Chart
                    const ctxTimeline = document.getElementById('timelineChart').getContext('2d');
                    timelineChart = new Chart(ctxTimeline, {
                        type: 'bar',
                        data: {
                            labels: timelineData.labels,
                            datasets: [{
                                label: 'Average Days',
                                data: timelineData.data,
                                backgroundColor: [
                                    'rgba(99, 102, 241, 0.5)', // Indigo
                                    'rgba(245, 158, 11, 0.5)', // Amber
                                    'rgba(16, 185, 129, 0.5)'  // Emerald
                                ],
                                borderColor: [
                                    '#6366f1',
                                    '#f59e0b',
                                    '#10b981'
                                ],
                                borderWidth: 1,
                                borderRadius: 4,
                                barPercentage: 0.6,
                                minBarLength: 5 // Ensure small values are visible
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
                                legend: { display: false },
                                tooltip: {
                                    callbacks: { 
                                        label: (c) => {
                                            const val = c.parsed.y;
                                            if (val > 0 && val < 1) {
                                                const hours = Math.round(val * 24);
                                                const mins = Math.round(val * 24 * 60);
                                                if (hours < 1) return val + ' Days (~' + mins + ' Mins)';
                                                return val + ' Days (~' + hours + ' Hours)';
                                            }
                                            return val + ' Days'; 
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // 2. Trend Chart (Average Lead Time)
                    const ctxTrend = document.getElementById('trendChart').getContext('2d');
                    trendChart = new Chart(ctxTrend, {
                        type: 'bar',
                        data: {
                            labels: trendData.labels,
                            datasets: [{
                                label: 'Avg Lead Time (Days)',
                                data: trendData.data,
                                backgroundColor: 'rgba(16, 185, 129, 0.7)', // Solid Green
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
                                legend: { display: false }, // Hide legend as it's self explanatory
                                title: { display: false }
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

            statusChart.options.scales.y.grid.color = colors.grid;
            statusChart.options.scales.y.ticks.color = colors.text;
            statusChart.options.scales.x.ticks.color = colors.text;
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

            statusChart.options.plugins.legend.labels.color = colors.legend;
            statusChart.update();
        }

        // Initialize charts
        createCharts();

        // ===== IMPORT FORM LOADING =====
        document.getElementById('importForm').addEventListener('submit', function() {
            const btn = this.querySelector('button');
            btn.innerHTML = '<i class="ph ph-spinner" style="animation: spin 1s linear infinite;"></i> Importing...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        });

        // ===== MITIGATION MODAL =====
        let currentPrId = null;

        // Event delegation for mitigate buttons
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-mitigate');
            if (btn) {
                const prId = btn.getAttribute('data-pr-id');
                if (prId) {
                    openMitigationModal(prId);
                }
            }
        });

        function openMitigationModal(prId) {
            console.log('Opening modal for PR:', prId);
            currentPrId = prId;
            document.getElementById('mitigationModal').style.display = 'flex';
            
            // Load PR details
            fetch(`/pr/${prId}/details`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalPrNumber').textContent = data.pr_number;
                    document.getElementById('modalDesc').textContent = data.short_text || '-';
                    document.getElementById('modalDays').textContent = data.days_overdue + ' hari';
                    
                    // Status badge
                    let statusHtml = '';
                    if (data.status === 'processed') {
                        statusHtml = '<span class="status-badge status-processed">Processed</span>';
                    } else if (data.status === 'overdue') {
                        statusHtml = '<span class="status-badge status-overdue">Overdue</span>';
                    } else {
                        statusHtml = '<span class="status-badge status-pending">Pending</span>';
                    }
                    document.getElementById('modalStatus').innerHTML = statusHtml;
                    
                    // Mitigation fields
                    document.getElementById('mitigationReason').value = data.mitigation_reason || '';
                    document.getElementById('mitigationStatus').value = data.mitigation_status || 'open';
                    
                    // Load comments
                    renderComments(data.comments);
                })
                .catch(err => {
                    console.error('Error loading PR details:', err);
                    alert('Error loading PR details');
                });
        }

        function closeMitigationModal() {
            currentPrId = null;
            document.getElementById('mitigationModal').style.display = 'none';
        }

        // Close modal on outside click
        document.getElementById('mitigationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMitigationModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMitigationModal();
            }
        });

        function saveMitigation() {
            if (!currentPrId) return;
            
            const reason = document.getElementById('mitigationReason').value;
            const status = document.getElementById('mitigationStatus').value;
            
            fetch(`/pr/${currentPrId}/mitigation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    mitigation_reason: reason,
                    mitigation_status: status
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update the table row
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save'));
                }
            })
            .catch(err => {
                console.error('Error saving mitigation:', err);
                alert('Error saving mitigation');
            });
        }

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
            
            // Scroll to bottom
            container.scrollTop = container.scrollHeight;
        }

        function sendComment() {
            if (!currentPrId) return;
            
            const authorName = document.getElementById('authorName').value.trim();
            const message = document.getElementById('commentMessage').value.trim();
            
            if (!authorName) {
                alert('Masukkan nama Anda');
                document.getElementById('authorName').focus();
                return;
            }
            
            if (!message) {
                alert('Masukkan komentar');
                document.getElementById('commentMessage').focus();
                return;
            }
            
            fetch(`/pr/${currentPrId}/comment`, {
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
                    // Add new comment to chat
                    const container = document.getElementById('chatMessages');
                    
                    // Remove empty message if present
                    const emptyMsg = container.querySelector('.chat-empty');
                    if (emptyMsg) emptyMsg.remove();
                    
                    const commentHtml = `
                        <div class="chat-message">
                            <span class="chat-author">${escapeHtml(data.comment.author_name)}</span>
                            <span class="chat-time">${data.comment.created_at}</span>
                            <div class="chat-text">${escapeHtml(data.comment.message)}</div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', commentHtml);
                    container.scrollTop = container.scrollHeight;
                    
                    // Clear input
                    document.getElementById('commentMessage').value = '';
                    
                    // Save author name for next time
                    localStorage.setItem('mitigationAuthor', authorName);
                } else {
                    alert('Error: ' + (data.message || 'Failed to send'));
                }
            })
            .catch(err => {
                console.error('Error sending comment:', err);
                alert('Error sending comment');
            });
        }

        // Helper to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Restore saved author name
        const savedAuthor = localStorage.getItem('mitigationAuthor');
        if (savedAuthor) {
            document.getElementById('authorName').value = savedAuthor;
        }

        // Allow Enter to send comment
        document.getElementById('commentMessage').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendComment();
            }
        });

        // ===== AUTO PAGINATION for Pending Requisitions (AJAX) =====
        const AUTO_PAGE_INTERVAL = 10000; // 10 seconds
        
        function startAutoPagination() {
            setInterval(() => {
                // 1. Safety Check: Pause if any modal is visible
                const mitigationModal = document.getElementById('mitigationModal');
                const convertModal = document.getElementById('convertModal');
                
                if ((mitigationModal && mitigationModal.style.display !== 'none') || 
                    (convertModal && convertModal.style.display !== 'none')) {
                    console.log('Auto-pagination paused: User is interacting with a modal');
                    return;
                }

                // 2. Find Next Page Link
                const nextLink = document.querySelector('#pendingRequisitionsCard .pagination a[rel="next"]');
                let targetUrl = null;

                if (nextLink) {
                    targetUrl = nextLink.href;
                } else {
                    // 3. Loop back to Page 1 if on last page
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentPage = parseInt(urlParams.get('page')) || 1;
                    
                    if (currentPage > 1) {
                         const cleanUrl = new URL(window.location.href);
                         cleanUrl.searchParams.set('page', '1');
                         targetUrl = cleanUrl.toString();
                    }
                }

                // 4. Perform AJAX Update
                if (targetUrl) {
                    fetch(targetUrl)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newTableCard = doc.querySelector('#pendingRequisitionsCard');
                            const currentTableCard = document.querySelector('#pendingRequisitionsCard');

                            if (newTableCard && currentTableCard) {
                                currentTableCard.innerHTML = newTableCard.innerHTML;
                                
                                // Optional: Update URL without reload so refresh stays on current page
                                window.history.pushState({path: targetUrl}, '', targetUrl);
                            }
                        })
                        .catch(err => console.error('Auto-pagination error:', err));
                }

            }, AUTO_PAGE_INTERVAL);
        }

        // Initialize
        startAutoPagination();

        // ===== NOTIFICATIONS =====
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const wrapper = document.querySelector('.notification-wrapper');
            const dropdown = document.getElementById('notificationDropdown');
            
            if (wrapper && !wrapper.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>

