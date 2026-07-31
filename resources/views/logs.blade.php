@extends('layouts.app')

@section('content')
<style>
    /* ================= ROOT VARIABLES ================= */
    :root {
        --bg-logs: #ffffff;
        --bg-card-logs: #ffffff;
        --bg-table-header-logs: #fafbfc;
        --bg-hover-row-logs: #f8fafc;
        --bg-stats-logs: #ffffff;
        --text-primary-logs: #0f172a;
        --text-secondary-logs: #475569;
        --text-muted-logs: #94a3b8;
        --border-color-logs: #eef2f6;
        --border-table-logs: #f1f5f9;
        --shadow-card-logs: 0 4px 20px rgba(0, 0, 0, 0.08);
        --shadow-hover-logs: 0 8px 30px rgba(0, 0, 0, 0.12);
        --radius-logs: 16px;
        
        --badge-up-bg: #ecfdf5;
        --badge-up-text: #065f46;
        --badge-warning-bg: #fffbeb;
        --badge-warning-text: #92400e;
        --badge-down-bg: #fef2f2;
        --badge-down-text: #991b1b;
        --badge-unknown-bg: #f1f5f9;
        --badge-unknown-text: #64748b;
        --badge-change-bg: #dbeafe;
        --badge-change-text: #1e40af;
        --badge-all-bg: #e0e7ff;
        --badge-all-text: #3730a3;
        
        --btn-back-bg: #f1f5f9;
        --btn-back-text: #475569;
        --btn-back-border: #e2e8f0;
        --input-bg: #ffffff;
        --input-border: #e2e8f0;
        --input-text: #1e293b;
        --btn-filter-bg: #6366f1;
        --btn-filter-text: #ffffff;
        --btn-reset-bg: #f1f5f9;
        --btn-reset-text: #475569;
    }

    [data-theme="dark"] {
        --bg-logs: #0f172a;
        --bg-card-logs: #1e293b;
        --bg-table-header-logs: #1e293b;
        --bg-hover-row-logs: #2d3a4f;
        --bg-stats-logs: #1e293b;
        --text-primary-logs: #e2e8f0;
        --text-secondary-logs: #94a3b8;
        --text-muted-logs: #64748b;
        --border-color-logs: #334155;
        --border-table-logs: #334155;
        --shadow-card-logs: 0 4px 20px rgba(0, 0, 0, 0.2);
        --shadow-hover-logs: 0 8px 30px rgba(0, 0, 0, 0.3);
        
        --badge-up-bg: #064e3b;
        --badge-up-text: #6ee7b7;
        --badge-warning-bg: #78350f;
        --badge-warning-text: #fcd34d;
        --badge-down-bg: #7f1d1d;
        --badge-down-text: #fca5a5;
        --badge-unknown-bg: #1e293b;
        --badge-unknown-text: #94a3b8;
        --badge-change-bg: #1a2332;
        --badge-change-text: #93c5fd;
        --badge-all-bg: #1a2332;
        --badge-all-text: #93c5fd;
        
        --btn-back-bg: #1e293b;
        --btn-back-text: #94a3b8;
        --btn-back-border: #334155;
        --input-bg: #1e293b;
        --input-border: #334155;
        --input-text: #e2e8f0;
        --btn-filter-bg: #6366f1;
        --btn-filter-text: #ffffff;
        --btn-reset-bg: #1e293b;
        --btn-reset-text: #94a3b8;
    }

    .logs-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        background: var(--bg-logs);
        min-height: 100vh;
        transition: all 0.3s ease;
        color: var(--text-primary-logs);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Header */
    .logs-header {
        background: var(--bg-card-logs);
        padding: 20px 24px;
        border-radius: var(--radius-logs);
        margin-bottom: 20px;
        box-shadow: var(--shadow-card-logs);
        border: 1px solid var(--border-color-logs);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .logs-header .header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .logs-header .header-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        flex-shrink: 0;
    }

    .logs-header h1 {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary-logs);
        margin: 0;
    }

    .logs-header .header-subtitle {
        color: var(--text-secondary-logs);
        font-size: 13px;
    }

    .btn-back {
        background: var(--btn-back-bg);
        color: var(--btn-back-text);
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--btn-back-border);
        transition: all 0.2s ease;
    }

    .btn-back:hover {
        background: var(--border-color-logs);
        transform: translateY(-1px);
    }

    /* Stats Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-item {
        background: var(--bg-stats-logs);
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid var(--border-color-logs);
        box-shadow: var(--shadow-card-logs);
        text-align: center;
        transition: all 0.2s ease;
    }

    .stat-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover-logs);
    }

    .stat-item .stat-number {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary-logs);
        display: block;
    }

    .stat-item .stat-label {
        font-size: 11px;
        color: var(--text-muted-logs);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    .stat-item .stat-number.green { color: #10b981; }
    .stat-item .stat-number.yellow { color: #f59e0b; }
    .stat-item .stat-number.red { color: #ef4444; }
    .stat-item .stat-number.blue { color: #3b82f6; }

    /* Filter Section */
    .filter-section {
        background: var(--bg-card-logs);
        padding: 14px 18px;
        border-radius: var(--radius-logs);
        border: 1px solid var(--border-color-logs);
        margin-bottom: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-end;
        transition: all 0.3s ease;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex: 1;
        min-width: 120px;
    }

    .filter-group label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary-logs);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group select,
    .filter-group input {
        padding: 6px 10px;
        border: 1px solid var(--input-border);
        border-radius: 6px;
        background: var(--input-bg);
        color: var(--input-text);
        font-size: 13px;
        font-family: inherit;
        transition: all 0.2s ease;
        outline: none;
        width: 100%;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-filter {
        background: var(--btn-filter-bg);
        color: var(--btn-filter-text);
        padding: 6px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .btn-filter:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-reset {
        background: var(--btn-reset-bg);
        color: var(--btn-reset-text);
        padding: 6px 14px;
        border: 1px solid var(--border-color-logs);
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
        text-decoration: none;
    }

    .btn-reset:hover {
        background: var(--border-color-logs);
    }

    /* Badges */
    .badge-change {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        background: var(--badge-change-bg);
        color: var(--badge-change-text);
        margin-left: 4px;
    }

    .badge-all {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        background: var(--badge-all-bg);
        color: var(--badge-all-text);
        margin-left: 4px;
    }

    .badge-code {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        background: var(--badge-all-bg);
        color: var(--badge-all-text);
        margin-left: 4px;
    }

    /* Table */
    .table-container {
        background: var(--bg-card-logs);
        border-radius: var(--radius-logs);
        box-shadow: var(--shadow-card-logs);
        border: 1px solid var(--border-color-logs);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .table-header {
        padding: 12px 18px;
        border-bottom: 1px solid var(--border-table-logs);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        background: var(--bg-table-header-logs);
    }

    .table-header h2 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary-logs);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-header .table-info {
        font-size: 13px;
        color: var(--text-muted-logs);
    }

    .table-header .table-info strong {
        color: var(--text-primary-logs);
    }

    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-secondary-logs);
    }

    .perpage-selector select {
        padding: 4px 8px;
        border: 1px solid var(--border-color-logs);
        border-radius: 4px;
        background: var(--bg-card-logs);
        font-size: 13px;
        color: var(--text-primary-logs);
        cursor: pointer;
        outline: none;
    }

    .perpage-selector select:focus {
        border-color: #6366f1;
    }

    .table-scroll {
        overflow-x: auto;
        padding: 0 16px 16px;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container thead th {
        text-align: left;
        padding: 10px 12px;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted-logs);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-table-logs);
        background: var(--bg-table-header-logs);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-container tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--border-table-logs);
        color: var(--text-primary-logs);
        font-size: 13px;
        vertical-align: middle;
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .table-container tbody tr:hover {
        background: var(--bg-hover-row-logs);
    }

    tr.status-change { border-left: 3px solid #6366f1; }
    tr.code-change { border-left: 3px solid #f59e0b; }
    tr.no-change { opacity: 0.7; }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-badge.up { background: var(--badge-up-bg); color: var(--badge-up-text); }
    .status-badge.up::before { background: #10b981; }

    .status-badge.warning { background: var(--badge-warning-bg); color: var(--badge-warning-text); }
    .status-badge.warning::before { background: #f59e0b; }

    .status-badge.down { background: var(--badge-down-bg); color: var(--badge-down-text); }
    .status-badge.down::before { background: #ef4444; }

    .status-badge.unknown { background: var(--badge-unknown-bg); color: var(--badge-unknown-text); }
    .status-badge.unknown::before { background: #94a3b8; }

    .service-name {
        font-weight: 600;
        color: var(--text-primary-logs);
        font-size: 13px;
    }

    .response-time {
        font-weight: 600;
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }
    .response-time .unit { color: var(--text-muted-logs); font-weight: 400; font-size: 11px; }
    .response-time.slow { color: #ef4444; }
    .response-time.fast { color: #10b981; }

    .message-cell {
        max-width: 300px;
        word-wrap: break-word;
        font-size: 13px;
        color: var(--text-secondary-logs);
        line-height: 1.4;
    }

    .time-cell {
        font-size: 12px;
        color: var(--text-secondary-logs);
        font-family: 'Courier New', monospace;
        white-space: nowrap;
    }

    .code-cell {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        font-weight: 600;
    }
    .code-cell.success { color: #10b981; }
    .code-cell.error { color: #ef4444; }
    .code-cell.warning { color: #f59e0b; }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-muted-logs);
    }
    .empty-state .empty-icon { font-size: 40px; display: block; margin-bottom: 10px; }
    .empty-state h3 { color: var(--text-primary-logs); font-size: 17px; margin: 0 0 6px; }

    /* Pagination */
    .pagination-wrapper {
        padding: 12px 18px 16px;
        border-top: 1px solid var(--border-table-logs);
        background: var(--bg-table-header-logs);
        border-radius: 0 0 var(--radius-logs) var(--radius-logs);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-secondary-logs);
    }
    .pagination-info strong { color: var(--text-primary-logs); }

    .pagination-links {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
    }

    .pagination-links .page-link {
        padding: 5px 10px;
        background: var(--bg-card-logs);
        border: 1px solid var(--border-color-logs);
        border-radius: 4px;
        font-size: 13px;
        color: var(--text-secondary-logs);
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 32px;
        text-align: center;
    }

    .pagination-links .page-link:hover:not(.active) {
        background: var(--bg-hover-row-logs);
        border-color: var(--text-muted-logs);
    }

    .pagination-links .page-link.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    .pagination-links .page-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    .pagination-links .page-dots {
        padding: 5px 4px;
        color: var(--text-muted-logs);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .logs-container { padding: 12px; }
        .logs-header { flex-direction: column; align-items: stretch; padding: 16px; }
        .logs-header h1 { font-size: 18px; }
        .stats-bar { grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .stat-item .stat-number { font-size: 18px; }
        .filter-section { flex-direction: column; gap: 8px; }
        .filter-group { min-width: 100%; }
        .filter-actions { width: 100%; }
        .btn-filter, .btn-reset { flex: 1; justify-content: center; }
        .table-scroll { padding: 0 8px 12px; }
        .table-container thead th, .table-container tbody td { padding: 6px 6px; font-size: 11px; }
        .time-cell { font-size: 10px; }
        .message-cell { max-width: 120px; font-size: 11px; }
        .pagination-wrapper { flex-direction: column; align-items: stretch; }
        .pagination-links { justify-content: center; }
        .pagination-links .page-link { padding: 4px 8px; font-size: 11px; min-width: 28px; }
    }

    @media (max-width: 480px) {
        .stats-bar { grid-template-columns: 1fr 1fr; gap: 6px; }
        .stat-item .stat-number { font-size: 15px; }
        .stat-item { padding: 8px 10px; }
        .table-container thead th, .table-container tbody td { padding: 4px 4px; font-size: 10px; }
        .time-cell { font-size: 9px; }
        .message-cell { max-width: 80px; font-size: 10px; }
        .status-badge { font-size: 9px; padding: 2px 6px; }
        .logs-header h1 { font-size: 16px; }
        .btn-back { font-size: 11px; padding: 6px 12px; }
    }
</style>

<div class="logs-container">
    <!-- Header -->
    <div class="logs-header">
        <div class="header-left">
            <div class="header-icon">📋</div>
            <div>
                <h1>Log Monitoring</h1>
                <div class="header-subtitle">Riwayat pengecekan service</div>
            </div>
        </div>
        <a href="{{ route('services') }}" class="btn-back">← Kembali</a>
    </div>

    <!-- Stats -->
    @php
        $totalLogs = $stats['total'] ?? $logs->total() ?? 0;
        $upCount = $stats['up'] ?? 0;
        $warningCount = $stats['warning'] ?? 0;
        $downCount = $stats['down'] ?? 0;
    @endphp

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number blue">{{ $totalLogs }}</span>
            <span class="stat-label">Total</span>
        </div>
        <div class="stat-item">
            <span class="stat-number green">{{ $upCount }}</span>
            <span class="stat-label">UP</span>
        </div>
        <div class="stat-item">
            <span class="stat-number yellow">{{ $warningCount }}</span>
            <span class="stat-label">Warning</span>
        </div>
        <div class="stat-item">
            <span class="stat-number red">{{ $downCount }}</span>
            <span class="stat-label">DOWN</span>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('logs') }}" class="filter-section">
        <div class="filter-group">
            <label for="service_id">Service</label>
            <select name="service_id" id="service_id">
                <option value="">Semua</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="">Semua</option>
                <option value="UP" {{ request('status') == 'UP' ? 'selected' : '' }}>✅ UP</option>
                <option value="WARNING" {{ request('status') == 'WARNING' ? 'selected' : '' }}>⚠️ WARNING</option>
                <option value="DOWN" {{ request('status') == 'DOWN' ? 'selected' : '' }}>❌ DOWN</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="log_type">Tipe Log</label>
            <select name="log_type" id="log_type">
                <option value="changes" {{ !request('all_logs') ? 'selected' : '' }}>
                    🔄 Setiap Perubahan (Status + Code)
                </option>
                <option value="all" {{ request('all_logs') ? 'selected' : '' }}>
                    📋 Semua Log
                </option>
            </select>
        </div>

        <div class="filter-group">
            <label for="date_from">Dari</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
        </div>

        <div class="filter-group">
            <label for="date_to">Sampai</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-filter">🔍 Filter</button>
            <a href="{{ route('logs') }}" class="btn-reset">↺ Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>
                📋 Daftar Log
                @if(request('all_logs'))
                    <span class="badge-all">📋 Semua Log</span>
                @else
                    <span class="badge-change">🔄 Setiap Perubahan</span>
                @endif
            </h2>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div class="perpage-selector">
                    <label for="perPage">Tampilkan:</label>
                    <select id="perPage" onchange="changePerPage(this.value)">
                        <option value="10" {{ request('perPage', $perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('perPage', $perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('perPage', $perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage', $perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <span class="table-info">Total <strong>{{ $logs->total() ?? 0 }}</strong></span>
            </div>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="min-width: 150px;">Waktu</th>
                        <th>Service</th>
                        <th style="width: 90px;">Status</th>
                        <th style="width: 60px;">Code</th>
                        <th style="width: 90px;">Response</th>
                        <th style="min-width: 180px;">Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $statusLabel = $log->status ?? 'UNKNOWN';
                            $responseTime = $log->response_time ?? 0;
                            $timeClass = $responseTime < 1 ? 'fast' : ($responseTime < 3 ? '' : 'slow');
                            $codeClass = $log->response_code < 400 ? 'success' : ($log->response_code < 500 ? 'warning' : 'error');
                            $isStatusChange = $log->is_status_change ?? false;
                            $previousStatus = $log->previous_status ?? null;
                            
                            // Deteksi perubahan code
                            $oldCode = $log->service->last_code ?? null;
                            $isCodeChange = ($log->response_code && $oldCode && $log->response_code != $oldCode);
                            
                            $rowClass = $isStatusChange ? 'status-change' : ($isCodeChange ? 'code-change' : 'no-change');
                            
                            // Message
                            $message = $log->message ?? '-';
                            if ($statusLabel == 'UP' && $log->response_code == 200) {
                                $message = '✅ Normal';
                            } elseif ($statusLabel == 'UP' && $log->response_code == 403) {
                                $message = '⚠️ Forbidden';
                            } elseif ($statusLabel == 'UP' && $log->response_code == 404) {
                                $message = '⚠️ Not Found';
                            } elseif ($statusLabel == 'UP' && $log->response_code >= 300 && $log->response_code < 400) {
                                $message = '↪️ Redirect';
                            } elseif ($statusLabel == 'WARNING' && $log->response_code == 200) {
                                $message = '⚠️ Lambat (' . number_format($responseTime, 2) . 's)';
                            } elseif ($statusLabel == 'DOWN') {
                                $message = '❌ Tidak bisa diakses';
                            } elseif ($log->response_code == 'TIMEOUT') {
                                $message = '⏰ Timeout';
                            }
                            
                            // Change info
                            $changeInfo = '';
                            if ($isStatusChange && $previousStatus) {
                                $changeInfo = ' <span class="badge-change">' . $previousStatus . ' → ' . $statusLabel . '</span>';
                            } elseif ($isCodeChange && $oldCode) {
                                $changeInfo = ' <span class="badge-code">' . $oldCode . ' → ' . $log->response_code . '</span>';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>
                                <span class="time-cell">
                                    {{ $log->checked_at ? $log->checked_at->format('d/m/Y H:i:s') : $log->created_at->format('d/m/Y H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <div class="service-name">
                                    {{ $log->service->name ?? 'Unknown' }}
                                    {!! $changeInfo !!}
                                </div>
                            </td>
                            <td>
                                @if($statusLabel == 'UP')
                                    <span class="status-badge up">UP</span>
                                @elseif($statusLabel == 'WARNING')
                                    <span class="status-badge warning">WARNING</span>
                                @elseif($statusLabel == 'DOWN')
                                    <span class="status-badge down">DOWN</span>
                                @else
                                    <span class="status-badge unknown">UNKNOWN</span>
                                @endif
                            </td>
                            <td>
                                <span class="code-cell {{ $codeClass }}">
                                    {{ $log->response_code ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="response-time {{ $timeClass }}">
                                    {{ number_format($responseTime, 2) }}
                                    <span class="unit">s</span>
                                </span>
                            </td>
                            <td>
                                <div class="message-cell">{{ $message }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-icon">📭</span>
                                    <h3>Belum Ada Log</h3>
                                    <p>Belum ada data monitoring yang tercatat</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }}
            </div>
            <div class="pagination-links">
                @if($logs->onFirstPage())
                    <span class="page-link disabled">‹</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="page-link">‹</a>
                @endif

                @php
                    $start = max(1, $logs->currentPage() - 2);
                    $end = min($logs->lastPage(), $logs->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $logs->url(1) }}" class="page-link">1</a>
                    @if($start > 2) <span class="page-dots">…</span> @endif
                @endif

                @foreach(range($start, $end) as $page)
                    @if($page == $logs->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $logs->url($page) }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($end < $logs->lastPage())
                    @if($end < $logs->lastPage() - 1) <span class="page-dots">…</span> @endif
                    <a href="{{ $logs->url($logs->lastPage()) }}" class="page-link">{{ $logs->lastPage() }}</a>
                @endif

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="page-link">›</a>
                @else
                    <span class="page-link disabled">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function changePerPage(value) {
        let url = new URL(window.location.href);
        url.searchParams.set('perPage', value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // Auto submit saat log_type berubah
    document.getElementById('log_type')?.addEventListener('change', function() {
        let url = new URL(window.location.href);
        let value = this.value;
        
        // Hapus parameter lama
        url.searchParams.delete('all_logs');
        
        // Set parameter baru
        if (value === 'all') {
            url.searchParams.set('all_logs', '1');
        }
        // default (changes): tidak ada parameter
        
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    });
</script>
@endsection