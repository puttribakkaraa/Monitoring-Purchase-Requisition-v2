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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        /* ===== SMART SUMMARY CARDS ===== */
        .smart-filter-bar {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 1.25rem;
            padding: 1rem 1.25rem;
            background: var(--bg-card);
            border-radius: 0.75rem;
            border: 1px solid var(--border);
        }
        .smart-filter-bar label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .smart-filter-bar select,
        .smart-filter-bar input[type="date"],
        .smart-filter-bar input[type="month"] {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.45rem 0.65rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.8rem;
            outline: none;
        }
        .smart-filter-bar select:focus,
        .smart-filter-bar input:focus {
            border-color: var(--accent-primary);
        }

        .smart-cards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .smart-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
            flex: 1;
            max-width: 300px;
        }
        .smart-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
        }
        .smart-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .smart-card.active {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
        }
        .smart-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .smart-card-title {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .smart-card-icon {
            font-size: 1.1rem;
            opacity: 0.7;
        }
        .smart-card-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.35rem;
        }
        .smart-card-pct {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* ===== SPLIT VIEW MODAL ===== */
        .split-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
            overflow-y: auto;
        }
        .split-overlay.show { display: flex; }
        .split-modal {
            background: var(--bg-card);
            border-radius: 1rem;
            width: 100%;
            max-width: 1100px;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            max-height: 85vh;
            display: flex;
            flex-direction: column;
        }
        .split-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .split-modal-header h2 {
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .split-modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.35rem;
            transition: all 0.2s;
        }
        .split-modal-close:hover { color: var(--danger); background: rgba(239,68,68,0.1); }
        .dept-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg-body);
        }
        .dept-tab {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 2rem;
            transition: all 0.25s;
            white-space: nowrap;
            font-family: inherit;
            letter-spacing: 0.5px;
            color: white;
            background: #64748b;
        }
        .dept-tab:hover { background: #10b981; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
        .dept-tab.active { background: #10b981; border-color: white; box-shadow: 0 0 0 3px rgba(16,185,129,0.3), 0 4px 12px rgba(0,0,0,0.2); transform: scale(1.05); }
        .split-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }

        /* Split body components */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .metric-box {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.75rem;
            text-align: center;
        }
        .metric-box .metric-val {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .metric-box .metric-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 0.15rem;
        }
        .section-title {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--text-primary);
        }

        /* Aging bars */
        .aging-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }
        .aging-label { min-width: 65px; color: var(--text-secondary); font-size: 0.75rem; }
        .aging-bar-wrap {
            flex: 1;
            height: 8px;
            background: var(--bg-body);
            border-radius: 4px;
            overflow: hidden;
        }
        .aging-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s;
        }
        .aging-count { min-width: 25px; text-align: right; font-weight: 600; font-size: 0.8rem; }

        /* Workload badges */
        .workload-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .wl-badge {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.6rem;
            text-align: center;
        }
        .wl-badge .wl-val { font-size: 1.1rem; font-weight: 700; }
        .wl-badge .wl-label { font-size: 0.65rem; color: var(--text-secondary); }

        /* Risk & SLA */
        .risk-sla-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .indicator-box {
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.75rem;
        }
        .indicator-box .ind-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.35rem;
        }
        .ind-header .ind-title { font-size: 0.75rem; color: var(--text-secondary); font-weight: 500; }
        .risk-badge {
            padding: 0.15rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .risk-low { background: rgba(16,185,129,0.15); color: #10b981; }
        .risk-medium { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .risk-high { background: rgba(239,68,68,0.15); color: #ef4444; }
        .sla-pct { font-size: 1.5rem; font-weight: 700; }
        .sla-bar {
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            margin-top: 0.35rem;
            overflow: hidden;
        }
        .sla-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s; }

        /* Insight box */
        .insight-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
        .insight-warning { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: #f59e0b; }
        .insight-info { background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); color: #3b82f6; }
        .insight-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; }

        /* Nominal table */
        .nominal-mini-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }
        .nominal-mini-table th {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-secondary);
            font-weight: 600;
            text-align: left;
            padding: 0.4rem 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        .nominal-mini-table td {
            padding: 0.35rem 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        /* PIC ranking */
        .pic-ranking {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .pic-rank-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.5rem;
            background: var(--bg-body);
            border-radius: 0.35rem;
            font-size: 0.8rem;
        }
        .rank-num {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 700;
            background: var(--accent-primary);
            color: white;
        }

        @media (max-width: 1024px) {
            .smart-cards-grid { grid-template-columns: repeat(3, 1fr); }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .smart-cards-grid { grid-template-columns: repeat(2, 1fr); }
            .risk-sla-row { grid-template-columns: 1fr; }
            .workload-grid { grid-template-columns: repeat(2, 1fr); }
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
                    <div class="notification-wrapper" onclick="toggleNotifications(event)">
                        <i class="ph ph-bell" style="font-size: 1.25rem; color: var(--text-secondary);"></i>
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <div class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</div>
                        @endif
                        
                        <!-- Dropdown -->
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <span>Notifications</span>
                                @if(isset($unreadCount) && $unreadCount > 0)
                                    <span style="font-size: 0.7rem; color: var(--accent-primary); cursor: pointer;" onclick="markAllRead(event)">Mark all read</span>
                                @endif
                            </div>
                            <div class="notification-list">
                                @if(isset($notifications) && count($notifications) > 0)
                                    @foreach($notifications as $notification)
                                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                                            @if(($notification->data['type'] ?? '') === 'feedback_responded' && isset($notification->data['pr_id']))
                                                style="cursor: pointer;"
                                                onclick="document.getElementById('notificationDropdown').style.display='none'; openMitigationModal({{ $notification->data['pr_id'] }});"
                                            @endif
                                        >
                                            <div class="notification-icon">
                                                @if(($notification->data['type'] ?? '') === 'feedback_responded')
                                                    <i class="ph ph-chat-dots" style="color: #3b82f6;"></i>
                                                @else
                                                    <i class="ph ph-check-circle"></i>
                                                @endif
                                            </div>
                                            <div class="notification-content">
                                                <div class="notification-message">{{ $notification->data['message'] ?? 'Notification' }}</div>
                                                <div class="notification-time">{{ $notification->created_at->setTimezone('Asia/Jakarta')->diffForHumans() }}</div>
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
                    
                    <!-- User Menu -->
                    <div class="user-menu-wrapper" style="position: relative;">
                        <div class="avatar" onclick="toggleUserMenu(event)" style="cursor: pointer;" title="{{ Auth::user()->name ?? 'User' }}">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="user-dropdown" id="userDropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 0.5rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 0.75rem; width: 200px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 999; overflow: hidden;">
                            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">
                                <div style="font-weight: 600; font-size: 0.85rem;">{{ Auth::user()->name ?? 'User' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">NPK: {{ Auth::user()->npk ?? '-' }}</div>
                            </div>
                            <a href="{{ route('users.index') }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; color: var(--text-primary); text-decoration: none; font-size: 0.85rem; transition: background 0.2s;" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='transparent'">
                                <i class="ph ph-users-three"></i> Kelola User
                            </a>
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; border: none; background: none; color: #ef4444; font-family: inherit; font-size: 0.85rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'">
                                    <i class="ph ph-sign-out"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ===== GLOBAL FILTER BAR ===== -->
        <div class="smart-filter-bar" id="smartFilterBar">
            <label><i class="ph ph-funnel"></i> Filter:</label>
            <div>
                <label style="font-size:0.7rem;">Bulan</label>
                <input type="month" id="filterMonth" onchange="loadSmartCards()">
            </div>
            <div>
                <label style="font-size:0.7rem;">Dari</label>
                <input type="date" id="filterDateFrom" onchange="loadSmartCards()">
            </div>
            <div>
                <label style="font-size:0.7rem;">Sampai</label>
                <input type="date" id="filterDateTo" onchange="loadSmartCards()">
            </div>
            <div>
                <label style="font-size:0.7rem;">Purchasing Group</label>
                <select id="filterDept" onchange="loadSmartCards()">
                    <option value="">Semua</option>
                    <option>DAA</option><option>DAB</option><option>DAC</option><option>DAD</option>
                    <option>DAE</option><option>DAF</option><option>DAG</option><option>DAH</option>
                </select>
            </div>
            <div>
                <label style="font-size:0.7rem;">PIC</label>
                <select id="filterPIC" onchange="loadSmartCards()">
                    <option value="">Semua</option>
                    <option>Bapak Imam</option><option>Ibu Ani</option><option>Ibu Yani</option><option>FINSA</option>
                </select>
            </div>
            <button class="btn btn-primary" style="padding:0.4rem 0.8rem; font-size:0.8rem;" onclick="resetFilters()">
                <i class="ph ph-arrow-counter-clockwise"></i> Reset
            </button>
        </div>

        <!-- ===== SMART SUMMARY CARDS ===== -->
        <div class="smart-cards-grid" id="smartCardsGrid" style="display: flex; justify-content: flex-start; gap: 1.5rem;">
            
            <!-- RELEASE CARD -->
            <div class="smart-card" data-status="released" onclick="openSplitView('released')" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 6px; padding: 1rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: center; gap: 0.75rem; transition: transform 0.2s, box-shadow 0.2s; position: relative; width: 180px; cursor: pointer; border-top: 3px solid #10b981;"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                 onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
                
                <div style="text-align: center; width: 100%;">
                    <div style="font-size: 1rem; font-weight: 700; color: #10b981; display:flex; align-items:center; justify-content:center; gap:0.4rem;">
                        <i class="ph ph-check-circle"></i> Release
                    </div>
                </div>

                <!-- Circle Chart -->
                <div id="sc_released_circle" style="position: relative; width: 85px; height: 85px; border-radius: 50%; background: conic-gradient(#10b981 0%, #e5e7eb 0); margin: 0.25rem 0;">
                    <div style="position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; background: var(--bg-card); border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1;">
                        <span id="sc_released_circle_pct" style="font-weight: 800; font-size: 1.05rem; color: #10b981;">0%</span>
                        <span style="font-size: 0.55rem; font-weight: 700; color: #10b981;">% PR</span>
                    </div>
                </div>

                <div style="font-size: 0.75rem; color: var(--text-secondary); text-align: center; width: 100%;">
                    Total PR: <strong id="sc_released_val" style="color: var(--text-primary); font-size: 1.1rem; display:block; margin-top:2px;">-</strong>
                </div>
                <div id="sc_released_pct" style="display:none;"></div>
            </div>

            <!-- PR OPEN CARD -->
            <div class="smart-card cursor-default" data-status="pr_open" style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 6px; padding: 1rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: center; gap: 0.75rem; transition: transform 0.2s, box-shadow 0.2s; position: relative; width: 180px; cursor: default; border-top: 3px solid #f59e0b;"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                 onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
                 
                <div style="text-align: center; width: 100%;">
                    <div style="font-size: 1rem; font-weight: 700; color: #f59e0b; display:flex; align-items:center; justify-content:center; gap:0.4rem;">
                        <i class="ph ph-folder-open"></i> PR Open
                    </div>
                </div>

                <!-- Circle Chart -->
                <div id="sc_pr_open_circle" style="position: relative; width: 85px; height: 85px; border-radius: 50%; background: conic-gradient(#f59e0b 0%, #e5e7eb 0); margin: 0.25rem 0;">
                    <div style="position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; background: var(--bg-card); border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1;">
                        <span id="sc_pr_open_circle_pct" style="font-weight: 800; font-size: 1.05rem; color: #f59e0b;">0%</span>
                        <span style="font-size: 0.55rem; font-weight: 700; color: #f59e0b;">% PR</span>
                    </div>
                </div>

                <div style="font-size: 0.75rem; color: var(--text-secondary); text-align: center; width: 100%;">
                    Total PR: <strong id="sc_pr_open_val" style="color: var(--text-primary); font-size: 1.1rem; display:block; margin-top:2px;">-</strong>
                </div>
                <div id="sc_pr_open_pct" style="display:none;"></div>
            </div>

            <!-- VISUAL PIPELINE (OPSI 2 - PROFESSIONAL MATCH) -->
            <div id="visualPipelineSection" style="flex: 1; display: none; background: transparent; padding: 0.5rem 0; position: relative; align-items: center; justify-content: space-between; gap: 0.5rem;">
                
                <!-- CARD 1: New Request -->
                <div style="flex: 1; background: #ffffff; border: 3px solid #0284c7; border-radius: 12px; padding: 1.25rem 0.5rem; display: flex; flex-direction: column; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; min-width: 140px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">New Request</div>
                    <div style="position: relative; margin-bottom: 0.5rem;">
                        <i class="ph ph-file-text" style="color: #0284c7; font-size: 2.5rem;"></i>
                        <i class="ph-fill ph-plus-circle" style="color: #0284c7; font-size: 1.2rem; position: absolute; bottom: -2px; right: -8px; background: white; border-radius: 50%;"></i>
                    </div>
                    <div id="vp_masuk_val" style="font-size: 2rem; font-weight: 800; color: #0284c7; line-height: 1.1; margin-top: 0.5rem;">-</div>
                    <div style="font-size: 0.65rem; font-weight: 600; color: #64748b; letter-spacing: 0.5px;">KPI</div>
                    <div style="position: absolute; bottom: -22px; width: 100%; text-align: center; font-size: 0.7rem; color: #475569;">Antrean Belum PO</div>
                </div>

                <!-- ARROW 1 -->
                <div style="display: flex; align-items: center; justify-content: center; width: 30px;">
                    <svg viewBox="0 0 24 24" width="30" height="30" stroke="#0284c7" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </div>

                <!-- CARD 2: In Process -->
                <div style="flex: 1; background: #ffffff; border: 3px solid #fbd38d; border-radius: 12px; padding: 1.25rem 0.5rem; display: flex; flex-direction: column; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; min-width: 140px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">In Process</div>
                    <div style="position: relative; margin-bottom: 0.5rem;">
                        <i class="ph ph-gear-six" style="color: #f59e0b; font-size: 2.5rem;"></i>
                    </div>
                    <div id="vp_proses_val" style="font-size: 2rem; font-weight: 800; color: #f59e0b; line-height: 1.1; margin-top: 0.5rem;">-</div>
                    <div style="font-size: 0.65rem; font-weight: 600; color: #64748b; letter-spacing: 0.5px;">KPI</div>
                    <div style="position: absolute; bottom: -22px; width: 100%; text-align: center; font-size: 0.7rem; color: #475569;">F/U & Feedback</div>
                </div>

                <!-- ARROW 2 -->
                <div style="display: flex; align-items: center; justify-content: center; width: 30px;">
                    <svg viewBox="0 0 24 24" width="30" height="30" stroke="#fbd38d" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </div>

                <!-- CARD 3: Completed -->
                <div style="flex: 1; background: #ffffff; border: 3px solid #22c55e; border-radius: 12px; padding: 1.25rem 0.5rem; display: flex; flex-direction: column; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: relative; min-width: 140px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Completed</div>
                    <div style="position: relative; margin-bottom: 0.5rem; background: #22c55e; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="ph ph-check" style="color: #ffffff; font-size: 1.8rem; stroke-width: 2px;"></i>
                    </div>
                    <div id="vp_released_val" style="font-size: 2rem; font-weight: 800; color: #22c55e; line-height: 1.1; margin-top: 0.5rem;">-</div>
                    <div style="font-size: 0.65rem; font-weight: 600; color: #64748b; letter-spacing: 0.5px;">KPI</div>
                    <div style="position: absolute; bottom: -22px; width: 100%; text-align: center; font-size: 0.7rem; color: #475569;">PO Released</div>
                </div>

            </div>

        </div>

        <!-- ===== SPLIT VIEW MODAL ===== -->
        <div class="split-overlay" id="splitOverlay" onclick="if(event.target===this) closeSplitView()">
            <div class="split-modal">
                <div class="split-modal-header">
                    <h2 id="splitTitle"><i class="ph ph-chart-bar"></i> Detail per Departemen</h2>
                    <button class="split-modal-close" onclick="closeSplitView()"><i class="ph ph-x"></i></button>
                </div>
                <div class="dept-tabs" id="deptTabs"></div>
                <div class="split-body" id="splitBody">
                    <div style="text-align:center; padding:2rem; color:var(--text-secondary);">
                        <i class="ph ph-spinner" style="font-size:2rem; animation: spin 1s linear infinite;"></i>
                        <p>Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Insights: Department Cards -->
        <div style="margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h3 style="font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="ph ph-chart-bar" style="color: var(--accent-primary);"></i> Performance Outstanding PR yang Belum PO
                </h3>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                @foreach($deptPerformance as $deptCode => $perf)
                @php
                    $val = $perf['percentage'];
                    $circleColor = $val <= 3 ? '#10b981' : ($val <= 10 ? '#f59e0b' : '#ef4444');
                    $total = max(1, $perf['total']);

                    $bars = [
                        ['label' => 'Belum PO', 'val' => $perf['qty'], 'color' => '#ef4444', 'key' => null],
                        ['label' => 'PR Release', 'val' => $perf['released'], 'color' => '#10b981', 'key' => 'released'],
                        ['label' => 'Sudah F/U', 'val' => $perf['sudah_fu'], 'color' => '#8b5cf6', 'key' => 'sudah_fu'],
                        ['label' => 'F/U Purchasing', 'val' => $perf['follow_up'], 'color' => '#f59e0b', 'key' => 'follow_up'],
                        ['label' => 'Need Feedback', 'val' => $perf['need_feedback'], 'color' => '#3b82f6', 'key' => 'feedback'],
                        ['label' => 'Sudah Feedback', 'val' => $perf['sudah_feedback'], 'color' => '#06b6d4', 'key' => 'sudah_feedback'],
                    ];
                @endphp
                <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 6px; padding: 1rem 0.6rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: center; gap: 0.75rem; transition: transform 0.2s, box-shadow 0.2s; position: relative;"
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                     onmouseout="this.style.transform=''; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)';">
                    
                    <!-- Header -->
                    <div style="text-align: center; width: 100%;">
                        <div style="font-size: 0.9rem; font-weight: 700; color: var(--accent-primary);">
                            {{ $deptCode }}
                        </div>
                        <div style="font-size: 0.65rem; color: var(--text-secondary); font-weight: 500; margin-bottom: 0.25rem;">
                            {{ isset($pgDescriptions[$deptCode]) ? $pgDescriptions[$deptCode] : '' }}
                        </div>
                        <div style="font-size: 0.65rem; color: var(--text-secondary);">
                            <span style="background: var(--bg-body); padding: 0.15rem 0.5rem; border-radius: 1rem; border: 1px solid var(--border);">{{ $perf['total'] }} PR</span>
                        </div>
                    </div>

                    <!-- PIC Edit -->
                    <div style="font-size: 0.65rem; color: var(--text-secondary); display: flex; align-items: center; justify-content: center; gap: 0.25rem; width: 100%; border-bottom: 1px dashed var(--border); padding-bottom: 0.5rem;">
                        <i class="ph ph-user"></i> <span id="pic-name-{{ $deptCode }}">{{ $perf['pic'] }}</span>
                        <button type="button" onclick="event.stopPropagation(); editPic('{{ $deptCode }}', '{{ $perf['pic'] }}')" style="background: none; border: none; cursor: pointer; color: #cbd5e1; transition: color 0.2s; display: flex; align-items: center;" onmouseover="this.style.color='var(--accent-primary)';" onmouseout="this.style.color='#cbd5e1';" title="Edit PIC">
                            <i class="ph ph-pencil-simple"></i>
                        </button>
                    </div>

                    <!-- Circle Chart -->
                    <div style="position: relative; width: 75px; height: 75px; border-radius: 50%; background: conic-gradient({{ $circleColor }} {{ $val }}%, #e5e7eb 0); margin: 0.25rem 0;">
                        <div style="position: absolute; top: 7px; left: 7px; right: 7px; bottom: 7px; background: var(--bg-card); border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1;">
                            <span style="font-weight: 800; font-size: 0.85rem; color: {{ $circleColor }};">{{ $val }}%</span>
                            <span style="font-size: 0.5rem; font-weight: 700; color: {{ $circleColor }};">% PR</span>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div style="font-size: 0.6rem; color: var(--text-secondary); text-align: center; width: 100%; margin-bottom: 0.25rem;">
                        Amount: <strong style="color: var(--warning);">{{ number_format($perf['amount'] / 1000000, 1, ',', '.') }} Juta (Rp)</strong>
                    </div>

                    <!-- Progress Bars -->
                    <div style="width: 100%; display: flex; flex-direction: column; gap: 0.4rem; padding: 0 0.2rem;">
                        @foreach($bars as $bar)
                            @php
                                $pct = ($bar['val'] / $total) * 100;
                            @endphp
                            <div @if($bar['key']) onclick="openSplitViewFromDept('{{ $bar['key'] }}', '{{ $deptCode }}')" style="cursor: pointer;" class="hover-bg-light" onmouseover="this.style.backgroundColor='var(--bg-card-hover)';" onmouseout="this.style.backgroundColor='transparent';" @endif style="padding: 0.15rem; border-radius: 4px; transition: background-color 0.2s;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.55rem; margin-bottom: 0.15rem;">
                                    <span style="color: var(--text-secondary);">{{ $bar['label'] }}</span>
                                    <span style="font-weight: 700; color: var(--text-primary);">{{ $bar['val'] }}</span>
                                </div>
                                <div style="width: 100%; height: 4px; background: #e5e7eb; border-radius: 2px; position: relative; overflow: hidden;">
                                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: {{ $pct }}%; background: {{ $bar['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                @endforeach
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>PR Volume (Last 12 Months)</h3>
                </div>
                <div style="height: 250px;">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Average Lead Time (PR -> PO)</h3>
                </div>
                <div id="trendChartContainer" style="height: 250px; position: relative; display: flex; flex-direction: column; justify-content: flex-end;">
                    <!-- Loading state -->
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-secondary);">
                        <i class="ph ph-spinner ph-spin" style="font-size: 1.5rem; margin-right: 0.5rem;"></i> Loading...
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requisitions (No PO) -->
        <div class="table-card" id="pendingRequisitionsCard">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    @if(request('search'))
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99,102,241,0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-primary);">
                            <i class="ph ph-magnifying-glass" style="font-size: 1.25rem;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 700;">Search Results</h3>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.15rem;">Menampilkan hasil pencarian untuk "{{ request('search') }}"</div>
                        </div>
                    @else
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245,158,11,0.1); display: flex; align-items: center; justify-content: center; color: var(--warning);">
                            <i class="ph ph-warning-circle" style="font-size: 1.25rem;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 700;">Pending Requisitions</h3>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.15rem;">Daftar PR yang belum memiliki nomor PO</div>
                        </div>
                    @endif
                </div>
                
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <form action="{{ route('dashboard') }}" method="GET" style="display: flex; align-items: center; background: var(--bg-body); border: 1px solid var(--border); border-radius: 2rem; padding: 0.25rem 0.25rem 0.25rem 1rem; transition: all 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);" id="searchForm" onfocusin="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.1)';" onfocusout="this.style.borderColor='var(--border)'; this.style.boxShadow='inset 0 2px 4px rgba(0,0,0,0.02)';">
                        <i class="ph ph-magnifying-glass" style="color: var(--text-secondary); font-size: 1.1rem;"></i>
                        <input type="text" name="search" placeholder="Cari PO, Dept, atau PR..." value="{{ request('search') }}" 
                               style="border: none; background: transparent; color: var(--text-primary); font-size: 0.85rem; width: 220px; outline: none; padding: 0.5rem 0.75rem;" autocomplete="off">
                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; border-radius: 1.5rem; display: flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.8rem;">
                            Cari
                        </button>
                    </form>
                    
                    @if(request('search'))
                        <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding: 0.6rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; text-decoration: none; color: var(--text-secondary); border-color: var(--border); transition: all 0.2s;" title="Clear Search" onmouseover="this.style.background='var(--danger)'; this.style.color='white'; this.style.borderColor='var(--danger)';" onmouseout="this.style.background='transparent'; this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border)';">
                            <i class="ph ph-x" style="font-size: 1rem;"></i>
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
                            <th>PIC</th>
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
                                <td>
                                    {{ $pr->purchasing_group ?? '-' }}
                                    <br>
                                    <span style="font-size: 0.75rem; color: var(--text-secondary);">({{ $pgDescriptions[$pr->purchasing_group] ?? $pr->department ?? '-' }})</span>
                                </td>
                                <td>{{ Str::limit($pr->short_text, 25) }}</td>
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
                                <td>
                                    @php
                                        $picName = $picMap[$pr->purchasing_group] ?? '-';
                                        if ($picName !== '-') {
                                            $parts = explode(' ', $picName);
                                            $initials = count($parts) > 1 
                                                ? strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1))
                                                : strtoupper(substr($picName, 0, 2));
                                        }
                                    @endphp
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        @if($picName !== '-')
                                            <div style="width: 26px; height: 26px; border-radius: 50%; background: var(--bg-body); border: 1px solid var(--border); color: var(--text-primary); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700;" title="{{ $picName }}">
                                                {{ $initials }}
                                            </div>
                                            <span style="font-size: 0.85rem; font-weight: 500;">{{ $picName }}</span>
                                        @else
                                            <span style="color: var(--text-secondary); font-style: italic;">Belum ada</span>
                                        @endif
                                    </div>
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

                <!-- Per-PR Timeline -->
                <div id="prTimeline" style="margin-bottom: 1rem;">
                    <div style="background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.75rem; padding: 0.75rem 1rem;">
                        <h4 style="font-size: 0.8rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem; color: var(--text-secondary);">
                            <i class="ph ph-flow-arrow" style="color: var(--accent-primary);"></i> Timeline PR
                        </h4>
                        <div id="prTimelineContent"></div>
                    </div>
                </div>

                <!-- Feedback Section (Tanya Departemen) -->
                <div id="feedbackSection" style="margin-bottom: 1rem; display: none;">
                    <div style="background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); border-radius: 0.5rem; padding: 0.75rem;">
                        <h4 style="font-size: 0.85rem; color: #3b82f6; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                            <i class="ph ph-question"></i> Feedback Departemen
                        </h4>
                        <div id="feedbackContent"></div>
                    </div>
                </div>

                <!-- Ask Feedback Form (when no pending feedback) -->
                <div id="askFeedbackForm" style="margin-bottom: 1rem; display: none;">
                    <div style="background: rgba(99,102,241,0.06); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.75rem;">
                        <label style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 0.35rem;">
                            <i class="ph ph-paper-plane-tilt"></i> Tanya Departemen:
                        </label>
                        <textarea id="feedbackQuestion" placeholder="Kenapa PR ini belum beres? Tulis pertanyaan untuk departemen..." rows="2"
                            style="width: 100%; background: var(--bg-body); border: 1px solid var(--border); border-radius: 0.35rem; padding: 0.5rem; color: var(--text-primary); font-family: inherit; font-size: 0.85rem; resize: vertical;"></textarea>
                        <button class="btn btn-primary" onclick="askFeedback()" style="margin-top: 0.5rem;">
                            <i class="ph ph-paper-plane-tilt"></i> Kirim ke Departemen
                        </button>
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

        // ===== NOTIFICATIONS =====
        function toggleNotifications(event) {
            if (event) event.stopPropagation();
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            // Close user menu if open
            document.getElementById('userDropdown').style.display = 'none';
        }

        // ===== USER MENU =====
        function toggleUserMenu(event) {
            if (event) event.stopPropagation();
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            // Close notification dropdown if open
            document.getElementById('notificationDropdown').style.display = 'none';
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const notifWrapper = document.querySelector('.notification-wrapper');
            const notifDropdown = document.getElementById('notificationDropdown');
            if (notifWrapper && notifDropdown && !notifWrapper.contains(e.target)) {
                notifDropdown.style.display = 'none';
            }
            const userWrapper = document.querySelector('.user-menu-wrapper');
            const userDropdown = document.getElementById('userDropdown');
            if (userWrapper && userDropdown && !userWrapper.contains(e.target)) {
                userDropdown.style.display = 'none';
            }
        });

        function markAllRead(event) {
            event.stopPropagation();
            
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Remove badge
                    const badge = document.querySelector('.notification-badge');
                    if (badge) badge.remove();

                    // Remove unread styling
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });

                    // Hide "Mark all read" button
                    event.target.style.display = 'none';
                }
            })
            .catch(err => console.error('Error:', err));
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

            // Create Gradient for Bars
            const barGradient = ctxVolume.createLinearGradient(0, 0, 0, 400);
            barGradient.addColorStop(0, '#3b82f6'); // Solid blue top
            barGradient.addColorStop(1, 'rgba(59, 130, 246, 0.1)'); // Faded blue bottom

            const hoverGradient = ctxVolume.createLinearGradient(0, 0, 0, 400);
            hoverGradient.addColorStop(0, '#2563eb'); // Darker blue hover top
            hoverGradient.addColorStop(1, 'rgba(37, 99, 235, 0.2)'); // Slightly darker faded hover bottom

            volumeChart = new Chart(ctxVolume, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: 'PR Created',
                            data: @json($chartData['pr']),
                            backgroundColor: barGradient,
                            hoverBackgroundColor: hoverGradient,
                            borderWidth: 0,
                            borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 },
                            barPercentage: 0.6,
                            categoryPercentage: 0.8
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

            // Status Chart removed in favor of HTML/CSS visualization
            // Timeline & Trend Charts
            fetch('{{ route("api.timeline") }}')
                .then(res => res.json())
                .then(apiData => {
                    const timelineData = apiData.timeline;
                    const trendData = apiData.trend;

                    // 1. Timeline Flow (HTML-based) - REMOVED
                    
                    // 2. Trend Chart (Average Lead Time) - HTML/CSS replacement
                    const trendContainer = document.getElementById('trendChartContainer');
                    const trendMaxVal = Math.max(...trendData.data, 1);
                    
                    let trendHtml = `
                        <!-- Grid lines -->
                        <div style="position: absolute; top: 10%; bottom: 30px; left: 0; right: 0; display: flex; flex-direction: column; justify-content: space-between; z-index: 0; pointer-events: none;">
                            <div style="border-top: 1px dashed var(--border); opacity: 0.4;"></div>
                            <div style="border-top: 1px dashed var(--border); opacity: 0.4;"></div>
                            <div style="border-top: 1px dashed var(--border); opacity: 0.4;"></div>
                        </div>
                        
                        <!-- Bars Container -->
                        <div style="display: flex; align-items: flex-end; justify-content: space-around; height: 100%; width: 100%; z-index: 1; position: relative;">
                    `;
                    
                    trendData.labels.forEach((label, i) => {
                        const val = trendData.data[i];
                        // Max height 85% to leave room for value label on top
                        const heightPct = val > 0 ? Math.max((val / trendMaxVal) * 80, 2) : 1; 
                        
                        trendHtml += `
                        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.35rem; transition: all 0.3s; opacity: ${val > 0 ? 1 : 0.4};">${val > 0 ? val + 'd' : '-'}</div>
                            
                            <div style="width: 100%; max-width: 45px; height: ${heightPct}%; background: linear-gradient(180deg, #10b981, rgba(16,185,129,0.1)); border-radius: 6px 6px 0 0; position: relative; overflow: hidden; box-shadow: 0 -2px 10px rgba(16,185,129,0.1);">
                                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: rgba(255,255,255,0.4);"></div>
                            </div>
                            
                            <div style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; margin-top: 0.75rem; margin-bottom: 0.5rem; text-align: right; white-space: nowrap; transform: rotate(-45deg); transform-origin: top; display: inline-block;">${label}</div>
                        </div>`;
                    });
                    
                    trendHtml += `</div>`;
                    trendContainer.innerHTML = trendHtml;
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
                    
                    // Status step tracker
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
                    
                    // Mitigation fields
                    document.getElementById('mitigationReason').value = data.mitigation_reason || '';
                    document.getElementById('mitigationStatus').value = data.mitigation_status || 'open';
                    
                    // Feedback section
                    renderFeedbackSection(data);
                    
                    // Per-PR timeline
                    renderPrTimeline(data);
                    
                    // Load comments
                    renderComments(data.comments);
                })
                .catch(err => {
                    console.error('Error loading PR details:', err);
                    alert('Error loading PR details');
                });
        }

        function renderFeedbackSection(data) {
            const feedbackSection = document.getElementById('feedbackSection');
            const feedbackContent = document.getElementById('feedbackContent');
            const askForm = document.getElementById('askFeedbackForm');

            if (data.feedback_status === 'waiting') {
                // Admin already asked, waiting for dept response
                feedbackSection.style.display = 'block';
                askForm.style.display = 'none';
                feedbackContent.innerHTML = `
                    <div style="margin-bottom: 0.5rem;">
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">Pertanyaan Anda (${data.feedback_asked_at}):</span>
                        <div style="background: var(--bg-card); padding: 0.5rem; border-radius: 0.35rem; margin-top: 0.25rem; font-size: 0.85rem;">${escapeHtml(data.feedback_question)}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem; color: #f59e0b; font-size: 0.8rem;">
                        <i class="ph ph-hourglass-medium" style="animation: spin 2s linear infinite;"></i>
                        <span>Menunggu respon dari departemen...</span>
                    </div>
                `;
            } else if (data.feedback_status === 'responded') {
                // Dept has responded
                feedbackSection.style.display = 'block';
                askForm.style.display = 'none';
                feedbackContent.innerHTML = `
                    <div style="margin-bottom: 0.5rem;">
                        <span style="font-size: 0.75rem; color: var(--text-secondary);">Pertanyaan Anda (${data.feedback_asked_at}):</span>
                        <div style="background: var(--bg-card); padding: 0.5rem; border-radius: 0.35rem; margin-top: 0.25rem; font-size: 0.85rem;">${escapeHtml(data.feedback_question)}</div>
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <span style="font-size: 0.75rem; color: var(--success);"><i class="ph ph-check-circle"></i> Respon Departemen (${data.feedback_responded_at}):</span>
                        <div style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); padding: 0.5rem; border-radius: 0.35rem; margin-top: 0.25rem; font-size: 0.85rem;">${escapeHtml(data.feedback_response)}</div>
                    </div>
                `;
            } else {
                // No feedback yet — show the ask form
                feedbackSection.style.display = 'none';
                askForm.style.display = 'block';
                document.getElementById('feedbackQuestion').value = '';
            }
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

        function askFeedback() {
            if (!currentPrId) return;
            const question = document.getElementById('feedbackQuestion').value.trim();
            if (!question) {
                alert('Tulis pertanyaan untuk departemen');
                document.getElementById('feedbackQuestion').focus();
                return;
            }

            fetch(`/pr/${currentPrId}/ask-feedback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ question: question })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Pertanyaan berhasil dikirim ke departemen!');
                    // Reload modal to show updated feedback state
                    openMitigationModal(currentPrId);
                } else {
                    alert('Error: ' + (data.message || 'Gagal mengirim pertanyaan'));
                }
            })
            .catch(err => {
                console.error('Error asking feedback:', err);
                alert('Error mengirim pertanyaan');
            });
        }

        function renderPrTimeline(data) {
            const container = document.getElementById('prTimelineContent');
            const stages = [
                { label: 'PR Created', date: data.req_date, icon: 'ph-file-text', color: '#6366f1' },
                { label: 'Feedback', date: data.feedback_asked_at, icon: 'ph-chat-dots', color: '#f59e0b' },
                { label: 'Response', date: data.feedback_responded_at, icon: 'ph-chat-text', color: '#10b981' },
                { label: 'Released', date: data.po_date || data.po_release_date, icon: 'ph-check-circle', color: '#0ea5e9' },
            ];

            // Parse date helper
            function parseDate(str) {
                if (!str || str === '-') return null;
                // Handle d.m.Y format
                if (str.includes('.')) {
                    const p = str.split('.');
                    return new Date(p[2], p[1]-1, p[0]);
                }
                return new Date(str);
            }
            function daysBetween(d1, d2) {
                if (!d1 || !d2) return null;
                const ms = Math.abs(d2 - d1);
                return Math.round(ms / 86400000);
            }

            let html = '';
            for (let i = 0; i < stages.length; i++) {
                const s = stages[i];
                const hasDate = s.date && s.date !== '-';
                const opacity = hasDate ? '1' : '0.35';

                // Stage row
                html += `<div style="display: flex; align-items: center; gap: 0.65rem; opacity: ${opacity};">`;
                html += `<div style="width: 32px; height: 32px; border-radius: 50%; background: ${s.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px ${s.color}40;">`;
                html += `<i class="ph ${s.icon}" style="font-size: 0.9rem; color: white;"></i></div>`;
                html += `<div style="flex: 1;">`;
                html += `<div style="font-size: 0.8rem; font-weight: 600; color: var(--text-primary);">${s.label}</div>`;
                html += `<div style="font-size: 0.7rem; color: var(--text-secondary);">${hasDate ? s.date : 'Belum'}</div>`;
                html += `</div>`;
                if (hasDate) {
                    html += `<i class="ph ph-check-circle" style="color: ${s.color}; font-size: 1rem;"></i>`;
                }
                html += `</div>`;

                // Connector with days
                if (i < stages.length - 1) {
                    const curDate = parseDate(s.date);
                    const nextDate = parseDate(stages[i+1].date);
                    const days = daysBetween(curDate, nextDate);

                    html += `<div style="display: flex; align-items: center; margin-left: 15px; padding: 0.15rem 0;">`;
                    html += `<div style="width: 2px; height: 20px; background: var(--border);"></div>`;
                    if (days !== null) {
                        html += `<span style="font-size: 0.65rem; font-weight: 700; color: var(--text-secondary); margin-left: 0.5rem; background: var(--bg-card); padding: 0.1rem 0.4rem; border-radius: 1rem; border: 1px solid var(--border);">${days} hari</span>`;
                    }
                    html += `</div>`;
                }
            }

            container.innerHTML = html;
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
        // startAutoPagination(); // Disabled: AJAX auto-pagination turned off

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

        // ===== SMART SUMMARY CARDS =====
        let smartCardData = null;
        let activeStatus = null;

        function getFilterParams() {
            const params = new URLSearchParams();
            const month = document.getElementById('filterMonth').value;
            const from = document.getElementById('filterDateFrom').value;
            const to = document.getElementById('filterDateTo').value;
            const dept = document.getElementById('filterDept').value;
            const pic = document.getElementById('filterPIC').value;
            if (month) params.set('month', month);
            if (from) params.set('date_from', from);
            if (to) params.set('date_to', to);
            if (dept) params.set('department', dept);
            if (pic) params.set('pic', pic);
            return params.toString();
        }

        function loadSmartCards() {
            const qs = getFilterParams();
            fetch(`/api/smart-cards?${qs}`)
                .then(r => r.json())
                .then(data => {
                    smartCardData = data;
                    // Update cards
                    ['released', 'pr_open'].forEach(k => {
                        const card = data.status_cards[k];
                        if (card) {
                            document.getElementById(`sc_${k}_val`).textContent = card.count.toLocaleString();
                            
                            const pctEl = document.getElementById(`sc_${k}_pct`);
                            if (pctEl) pctEl.textContent = `${card.percentage}% dari total (${data.total})`;

                            const circleEl = document.getElementById(`sc_${k}_circle`);
                            const circlePctEl = document.getElementById(`sc_${k}_circle_pct`);
                            if (circleEl && circlePctEl) {
                                circlePctEl.textContent = `${card.percentage}%`;
                                circleEl.style.background = `conic-gradient(${card.color} ${card.percentage}%, #e5e7eb 0)`;
                            }
                        }
                    });
                    // Render Visual Pipeline (Opsi 2)
                    if (data.global_breakdown) {
                        const vpSec = document.getElementById('visualPipelineSection');
                        if (vpSec) vpSec.style.display = 'flex';
                        
                        document.getElementById('vp_masuk_val').textContent = data.global_breakdown.belum_po.toLocaleString();
                        document.getElementById('vp_proses_val').textContent = (data.global_breakdown.follow_up + data.global_breakdown.need_feedback + data.global_breakdown.sudah_feedback).toLocaleString();
                        document.getElementById('vp_released_val').textContent = data.global_breakdown.released.toLocaleString();
                    }
                })
                .catch(err => console.error('Smart card error:', err));
        }

        function openSplitViewFromDept(status, dept) {
            openSplitView(status, dept);
        }

        function openSplitView(status, defaultDeptOverride = null) {
            if (!smartCardData) return;
            activeStatus = status;
            // Mark active card
            document.querySelectorAll('.smart-card').forEach(c => c.classList.remove('active'));
            document.querySelector(`.smart-card[data-status="${status}"]`)?.classList.add('active');

            const labels = { follow_up:'F/U Purchasing', feedback:'Need Feedback', released:'PR Release', overdue:'Overdue', no_status:'Tanpa Status', sudah_fu:'Sudah Follow-up', sudah_feedback:'Sudah Feedback' };
            document.getElementById('splitTitle').innerHTML = `<i class="ph ph-chart-bar"></i> ${labels[status]} — Detail per Departemen`;

            // Build dept tabs
            const tabsEl = document.getElementById('deptTabs');
            const depts = ['DAA','DAB','DAC','DAD','DAE','DAF','DAG','DAH'];
            
            // Auto-select requested dept, or first dept with data for this status
            let defaultDept = defaultDeptOverride;
            if (!defaultDept && smartCardData && smartCardData.departments) {
                defaultDept = depts[0];
                for (let d of depts) {
                    if (smartCardData.departments[d] && smartCardData.departments[d].status_breakdown[status] > 0) {
                        defaultDept = d;
                        break;
                    }
                }
            }
            
            tabsEl.innerHTML = depts.map((d) =>
                `<button class="dept-tab ${d === defaultDept ? 'active':''}" data-dept="${d}" onclick="switchDeptTab('${d}', this)">${d}</button>`
            ).join('');

            // Show default tab
            renderDeptTab(defaultDept);

            document.getElementById('splitOverlay').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function editPic(deptCode, currentPic) {
            // Get latest PIC name from DOM in case it was edited previously
            const activePic = document.getElementById('pic-name-' + deptCode).innerText;
            
            Swal.fire({
                title: 'Edit PIC ' + deptCode,
                html: '<div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.6rem;">Silakan masukkan nama Person In Charge baru:</div>',
                input: 'text',
                inputValue: activePic,
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-check-circle"></i> Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#10b981',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Nama PIC tidak boleh kosong!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newPic = result.value;
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    
                    fetch('{{ route('api.updatePic') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ dept_code: deptCode, pic_name: newPic })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI gracefully
                            document.getElementById('pic-name-' + deptCode).innerText = data.pic_name;
                            Swal.fire({
                                icon: 'success',
                                title: 'Tersimpan!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat menyimpan', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Gagal', 'Koneksi ke server bermasalah', 'error');
                    });
                }
            });
        }

        function closeSplitView() {
            document.getElementById('splitOverlay').classList.remove('show');
            document.body.style.overflow = '';
            document.querySelectorAll('.smart-card').forEach(c => c.classList.remove('active'));
        }

        function switchDeptTab(dept, btn) {
            document.querySelectorAll('.dept-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            renderDeptTab(dept);
        }

        function formatRupiah(val) {
            if (!val || val === 0) return 'Rp 0';
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        }

        function renderDeptTab(dept) {
            const d = smartCardData.departments[dept];
            if (!d) { document.getElementById('splitBody').innerHTML = '<p style="text-align:center;padding:2rem;color:var(--text-secondary);">Tidak ada data</p>'; return; }

            const agingTotal = d.aging_distribution.lte7 + d.aging_distribution['8to14'] + d.aging_distribution['15to30'] + d.aging_distribution.gt30;
            const agingPct = (v) => agingTotal > 0 ? Math.round((v/agingTotal)*100) : 0;

            const riskColors = { low: '#10b981', medium: '#f59e0b', high: '#ef4444' };
            const slaColor = d.sla.percentage >= 80 ? '#10b981' : d.sla.percentage >= 50 ? '#f59e0b' : '#ef4444';
            const colors = { follow_up:'#f59e0b', feedback:'#3b82f6', released:'#10b981', overdue:'#ef4444', no_status:'#64748b', sudah_fu:'#8b5cf6', sudah_feedback:'#06b6d4' };
            const labels = { follow_up:'F/U Purchasing', feedback:'Need Feedback', released:'PR Release', overdue:'Overdue', no_status:'Tanpa Status', sudah_fu:'Sudah F/U', sudah_feedback:'Sudah Feedback' };

            let html = `
            <!-- PIC Info -->
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;padding:0.75rem;background:var(--bg-body);border-radius:0.5rem;border:1px solid var(--border);">
                <div style="width:40px;height:40px;border-radius:50%;background:var(--accent-primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.85rem;">
                    ${d.pic.charAt(0)}${d.pic.split(' ').pop().charAt(0)}
                </div>
                <div>
                    <div style="font-weight:600;">${d.pic}</div>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">PIC ${dept} · Kontribusi: ${d.contribution}% dari total</div>
                </div>
                <div style="margin-left:auto;text-align:right;">
                    <div style="font-size:0.75rem;color:var(--text-secondary);">Total PR</div>
                    <div style="font-weight:700;">${Number(d.total).toLocaleString('id-ID')}</div>
                </div>
            </div>

            <!-- Main Metrics -->
            <div class="section-title"><i class="ph ph-chart-pie-slice"></i> Key Metrics</div>
            <div class="metrics-grid">
                <div class="metric-box"><div class="metric-val">${d.total}</div><div class="metric-label">Total PR</div></div>
                <div class="metric-box"><div class="metric-val" style="color:#10b981;">${d.with_po}</div><div class="metric-label">Sudah PO</div></div>
                <div class="metric-box"><div class="metric-val" style="color:#f59e0b;">${d.without_po}</div><div class="metric-label">Belum PO</div></div>
                <div class="metric-box"><div class="metric-val" style="color:#ef4444;">${d.overdue}</div><div class="metric-label">Overdue</div></div>
                <div class="metric-box"><div class="metric-val">${d.avg_aging}<small style="font-size:0.65rem;font-weight:400;"> hari</small></div><div class="metric-label">Aging Day</div></div>
                <div class="metric-box"><div class="metric-val">${d.avg_lead_time}<small style="font-size:0.65rem;font-weight:400;"> hari</small></div><div class="metric-label">Avg Lead Time</div></div>
                <div class="metric-box"><div class="metric-val" style="color:${d.overdue_rate > 30 ? '#ef4444' : d.overdue_rate > 15 ? '#f59e0b' : '#10b981'};">${d.overdue_rate}%</div><div class="metric-label">Overdue Rate</div></div>
                <div class="metric-box"><div class="metric-val">${d.contribution}%</div><div class="metric-label">Kontribusi</div></div>
            </div>

            <!-- Status Breakdown -->
            <div class="section-title"><i class="ph ph-squares-four"></i> Status Breakdown</div>
            <div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
                ${Object.entries(d.status_breakdown).map(([k,v]) => {
                    return `<span style="padding:0.3rem 0.6rem;border-radius:1rem;font-size:0.75rem;font-weight:600;background:${colors[k]}20;color:${colors[k]};border:1px solid ${colors[k]}30;">${labels[k]}: ${v}</span>`;
                }).join('')}
            </div>

            <!-- Aging Distribution -->
            <div class="section-title"><i class="ph ph-hourglass"></i> Aging Distribution</div>
            <div style="margin-bottom:1.25rem;">
                <div class="aging-row">
                    <span class="aging-label">≤7 hari</span>
                    <div class="aging-bar-wrap"><div class="aging-bar" style="width:${agingPct(d.aging_distribution.lte7)}%;background:#10b981;"></div></div>
                    <span class="aging-count" style="color:#10b981;">${d.aging_distribution.lte7}</span>
                </div>
                <div class="aging-row">
                    <span class="aging-label">8–14 hari</span>
                    <div class="aging-bar-wrap"><div class="aging-bar" style="width:${agingPct(d.aging_distribution['8to14'])}%;background:#f59e0b;"></div></div>
                    <span class="aging-count" style="color:#f59e0b;">${d.aging_distribution['8to14']}</span>
                </div>
                <div class="aging-row">
                    <span class="aging-label">15–30 hari</span>
                    <div class="aging-bar-wrap"><div class="aging-bar" style="width:${agingPct(d.aging_distribution['15to30'])}%;background:#ef4444;"></div></div>
                    <span class="aging-count" style="color:#ef4444;">${d.aging_distribution['15to30']}</span>
                </div>
                <div class="aging-row">
                    <span class="aging-label">>30 hari</span>
                    <div class="aging-bar-wrap"><div class="aging-bar" style="width:${agingPct(d.aging_distribution.gt30)}%;background:#dc2626;"></div></div>
                    <span class="aging-count" style="color:#dc2626;">${d.aging_distribution.gt30}</span>
                </div>
            </div>`;

            // Workload: show for released, follow_up, feedback
            if (['released','follow_up','feedback','sudah_fu','sudah_feedback'].includes(activeStatus)) {
                html += `
            <div class="section-title"><i class="ph ph-briefcase"></i> Workload</div>
            <div class="workload-grid" style="margin-bottom:1.25rem;">
                <div class="wl-badge"><div class="wl-val" style="color:#6366f1;">${d.workload.active}</div><div class="wl-label">PR Aktif</div></div>
                <div class="wl-badge"><div class="wl-val" style="color:#3b82f6;">${d.workload.incoming_this_month}</div><div class="wl-label">Masuk Bulan Ini</div></div>
                <div class="wl-badge"><div class="wl-val" style="color:#10b981;">${d.workload.done_this_month}</div><div class="wl-label">Selesai Bulan Ini</div></div>
            </div>`;
            }

            // SLA & Risk: show for released, overdue
            if (['released','overdue'].includes(activeStatus)) {
                html += `
            <div class="section-title"><i class="ph ph-shield-check"></i> SLA & Risk</div>
            <div class="risk-sla-row" style="margin-bottom:1.25rem;">
                <div class="indicator-box">
                    <div class="ind-header">
                        <span class="ind-title">SLA Compliance (≤7 hari)</span>
                    </div>
                    <div class="sla-pct" style="color:${slaColor};">${d.sla.percentage}%</div>
                    <div style="font-size:0.7rem;color:var(--text-secondary);">${d.sla.compliant}/${d.sla.total_completed} PR</div>
                    <div class="sla-bar"><div class="sla-bar-fill" style="width:${d.sla.percentage}%;background:${slaColor};"></div></div>
                </div>
                <div class="indicator-box">
                    <div class="ind-header">
                        <span class="ind-title">Risk Level</span>
                        <span class="risk-badge risk-${d.risk.level}">${d.risk.level}</span>
                    </div>
                    <div style="font-size:1.5rem;font-weight:700;color:${riskColors[d.risk.level]};">${d.risk.score}</div>
                    <div style="font-size:0.7rem;color:var(--text-secondary);">Risk Score (0-100)</div>
                </div>
            </div>`;
            }

            // Action Required PRs: show for follow_up, feedback, sudah_fu, sudah_feedback
            if (['follow_up','feedback','sudah_fu','sudah_feedback'].includes(activeStatus)) {
                // Show all pending PO PRs for this department
                const prList = d.action_req_prs || [];
                
                let filteredList = prList;
                if (activeStatus === 'follow_up') filteredList = prList.filter(p => p.feedback_status !== 'responded' && p.feedback_status !== 'waiting');
                if (activeStatus === 'feedback') filteredList = prList.filter(p => p.feedback_status === 'waiting');
                if (activeStatus === 'sudah_fu') filteredList = prList.filter(p => p.feedback_status === 'responded' || p.feedback_status === 'waiting');
                if (activeStatus === 'sudah_feedback') filteredList = prList.filter(p => p.feedback_status === 'responded');
                
                const iconColor = colors[activeStatus] || '#f59e0b';
                const iconClass = activeStatus === 'follow_up' ? 'ph-phone-outgoing' : (activeStatus === 'feedback' ? 'ph-chat-dots' : 'ph-check-circle');
                const titleText = labels[activeStatus] || 'PR Details';

                html += `<div class="section-title" style="margin-top:1.25rem;"><i class="ph ${iconClass}" style="color:${iconColor};"></i> ${titleText}</div>`;

                if (filteredList.length > 0) {
                    html += `
                    <div style="background:var(--bg-body); border:1px solid var(--border); border-radius:0.75rem; overflow:hidden; margin-bottom:1.25rem;">
                        <table style="width:100%; border-collapse:collapse; font-size:0.8rem; text-align:left;">
                            <thead>
                                <tr style="background:var(--bg-card); border-bottom:1px solid var(--border); color:var(--text-secondary);">
                                    <th style="padding:0.75rem 1rem; font-weight:600;">PR Number</th>
                                    <th style="padding:0.75rem 1rem; font-weight:600;">Deskripsi</th>
                                    <th style="padding:0.75rem 1rem; font-weight:600;">Usia</th>
                                    <th style="padding:0.75rem 1rem; font-weight:600; text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filteredList.map(pr => `
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:0.75rem 1rem; font-family:monospace; color:var(--accent-primary); font-weight:500;">
                                        ${pr.pr_number}
                                    </td>
                                    <td style="padding:0.75rem 1rem; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${pr.short_text}">
                                        ${pr.short_text || '-'}
                                    </td>
                                    <td style="padding:0.75rem 1rem; color:${pr.age > 14 ? '#ef4444' : (pr.age > 7 ? '#f59e0b' : 'inherit')}">
                                        ${pr.age} hari
                                    </td>
                                    <td style="padding:0.75rem 1rem; text-align:right;">
                                        <button type="button" onclick="closeSplitView(); openMitigationModal(${pr.id}, '${pr.pr_number}')" style="background:${iconColor}15; color:${iconColor}; border:1px solid ${iconColor}30; border-radius:0.35rem; padding:0.25rem 0.5rem; font-size:0.7rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem; transition:all 0.2s;">
                                            <i class="ph ph-arrow-square-out"></i> Buka PR
                                        </button>
                                    </td>
                                </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
                } else {
                    html += `
                    <div style="background:var(--bg-body); border:1px dashed var(--border); border-radius:0.75rem; padding:1.5rem; text-align:center; color:var(--text-secondary); margin-bottom:1.25rem;">
                        <i class="ph ph-check-circle" style="font-size:1.5rem; color:#10b981; margin-bottom:0.5rem; display:block;"></i>
                        <div style="font-weight:600; color:var(--text-primary); margin-bottom:0.25rem;">Tidak ada data</div>
                        Tidak ada PR pada kategori ${titleText} untuk departemen ini.
                    </div>`;
                }
            }

            // Nominal Monitoring: show for released, overdue
            if (['released','overdue'].includes(activeStatus)) {
                html += `
            <div class="section-title"><i class="ph ph-currency-circle-dollar"></i> Nominal Monitoring</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.5rem;margin-bottom:0.75rem;">
                <div class="metric-box"><div class="metric-val" style="font-size:1rem;">${formatRupiah(d.nominal.total)}</div><div class="metric-label">Total Nilai PR</div></div>
                <div class="metric-box"><div class="metric-val" style="font-size:1rem;color:#f59e0b;">${formatRupiah(d.nominal.belum_po)}</div><div class="metric-label">Belum PO</div></div>
                <div class="metric-box"><div class="metric-val" style="font-size:1rem;color:#ef4444;">${formatRupiah(d.nominal.overdue)}</div><div class="metric-label">Overdue</div></div>
            </div>`;

                // Daftar Seluruh PR Belum PO
                if (d.nominal.belum_po_list && d.nominal.belum_po_list.length > 0) {
                    html += `<div style="font-size:0.75rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.35rem;">Daftar PR Belum PO (Diurutkan dari Nominal Terbesar)</div>
                    <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--border); border-radius: 0.5rem; margin-bottom: 1.25rem;">
                        <table class="nominal-mini-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8rem;">
                            <thead style="position: sticky; top: 0; background: var(--bg-card); box-shadow: 0 1px 2px rgba(0,0,0,0.05); z-index: 1;">
                                <tr>
                                    <th style="padding: 0.5rem; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border);">PR Number</th>
                                    <th style="padding: 0.5rem; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border);">Deskripsi</th>
                                    <th style="padding: 0.5rem; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border);">Nilai</th>
                                    <th style="padding: 0.5rem; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border);">Aging</th>
                                    <th style="padding: 0.5rem; font-weight: 600; color: var(--text-secondary); border-bottom: 1px solid var(--border); text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>${d.nominal.belum_po_list.map(p => `
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem; font-family:monospace;color:var(--accent-primary);font-weight:500;">${p.pr_number}</td>
                                    <td style="padding: 0.5rem;">${(p.short_text||'-').substring(0,35)}</td>
                                    <td style="padding: 0.5rem; font-weight:600;">${formatRupiah(p.value)}</td>
                                    <td style="padding: 0.5rem; color:${p.age > 14 ? '#ef4444' : '#f59e0b'};font-weight:600;">${p.age} hari</td>
                                    <td style="padding: 0.5rem; text-align:right;">
                                        <button type="button" onclick="closeSplitView(); openMitigationModal(${p.id}, '${p.pr_number}')" style="background:#10b98115; color:#10b981; border:1px solid #10b98130; border-radius:0.35rem; padding:0.25rem 0.5rem; font-size:0.7rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:0.25rem; transition:all 0.2s;">
                                            <i class="ph ph-arrow-square-out"></i> Follow-up
                                        </button>
                                    </td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>`;
                }
            }

            // PIC Rankings: show only for released
            if (activeStatus === 'released') {
                if (smartCardData.pic_rankings && smartCardData.pic_rankings.length > 0) {
                    html += `<div class="section-title" style="margin-top:1.25rem;"><i class="ph ph-ranking"></i> PIC Ranking (PR Aktif)</div>
                    <div class="pic-ranking">
                        ${smartCardData.pic_rankings.map((p, i) => `<div class="pic-rank-item">
                            <span class="rank-num">${i+1}</span>
                            <span style="flex:1;font-weight:500;">${p.name}</span>
                            <span style="font-weight:700;color:var(--accent-primary);">${p.active}</span>
                            <span style="font-size:0.7rem;color:var(--text-secondary);">aktif / ${p.total} total</span>
                        </div>`).join('')}
                    </div>`;
                }
            }

            document.getElementById('splitBody').innerHTML = html;
        }

        function resetFilters() {
            document.getElementById('filterMonth').value = '';
            document.getElementById('filterDateFrom').value = '';
            document.getElementById('filterDateTo').value = '';
            document.getElementById('filterDept').value = '';
            document.getElementById('filterPIC').value = '';
            loadSmartCards();
        }

        // Load on page init
        document.addEventListener('DOMContentLoaded', function() {
            loadSmartCards();
        });
    </script>
</body>
</html>

