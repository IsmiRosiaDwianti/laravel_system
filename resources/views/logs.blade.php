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
        --text-light-logs: #64748b;
        --border-color-logs: #eef2f6;
        --border-table-logs: #f1f5f9;
        --shadow-card-logs: 0 4px 20px rgba(0, 0, 0, 0.08);
        --shadow-hover-logs: 0 8px 30px rgba(0, 0, 0, 0.12);
        --radius-logs: 16px;
        --transition-logs: all 0.2s ease;
        
        /* Status badge colors - light mode */
        --badge-up-bg: #ecfdf5;
        --badge-up-text: #065f46;
        --badge-warning-bg: #fffbeb;
        --badge-warning-text: #92400e;
        --badge-down-bg: #fef2f2;
        --badge-down-text: #991b1b;
        --badge-unknown-bg: #f1f5f9;
        --badge-unknown-text: #64748b;
        
        --btn-back-bg: #f1f5f9;
        --btn-back-text: #475569;
        --btn-back-border: #e2e8f0;
    }

    /* Dark mode override dari layout utama */
    [data-theme="dark"] {
        --bg-logs: #0f172a;
        --bg-card-logs: #1e293b;
        --bg-table-header-logs: #1e293b;
        --bg-hover-row-logs: #2d3a4f;
        --bg-stats-logs: #1e293b;
        --text-primary-logs: #e2e8f0;
        --text-secondary-logs: #94a3b8;
        --text-muted-logs: #64748b;
        --text-light-logs: #94a3b8;
        --border-color-logs: #334155;
        --border-table-logs: #334155;
        --shadow-card-logs: 0 4px 20px rgba(0, 0, 0, 0.2);
        --shadow-hover-logs: 0 8px 30px rgba(0, 0, 0, 0.3);
        
        /* Status badge colors - dark mode */
        --badge-up-bg: #064e3b;
        --badge-up-text: #6ee7b7;
        --badge-warning-bg: #78350f;
        --badge-warning-text: #fcd34d;
        --badge-down-bg: #7f1d1d;
        --badge-down-text: #fca5a5;
        --badge-unknown-bg: #1e293b;
        --badge-unknown-text: #94a3b8;
        
        --btn-back-bg: #1e293b;
        --btn-back-text: #94a3b8;
        --btn-back-border: #334155;
    }

    .logs-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-logs);
        min-height: 100vh;
        transition: background 0.3s ease, color 0.3s ease;
        color: var(--text-primary-logs);
    }

    /* Header Section */
    .logs-header {
        background: var(--bg-card-logs);
        padding: 24px 28px;
        border-radius: var(--radius-logs);
        margin-bottom: 24px;
        box-shadow: var(--shadow-card-logs);
        border: 1px solid var(--border-color-logs);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        transition: all 0.3s ease;
    }

    .logs-header .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .logs-header .header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        flex-shrink: 0;
    }

    .logs-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary-logs);
        margin: 0;
        letter-spacing: -0.5px;
        transition: color 0.3s ease;
    }

    .logs-header .header-subtitle {
        color: var(--text-secondary-logs);
        font-size: 13px;
        font-weight: 400;
        margin-top: 2px;
        transition: color 0.3s ease;
    }

    .logs-header .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
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
        transition: all 0.2s ease;
        border: 1px solid var(--btn-back-border);
        font-family: inherit;
        cursor: pointer;
    }

    .btn-back:hover {
        background: var(--border-color-logs);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    /* Stats Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-item {
        background: var(--bg-stats-logs);
        padding: 16px 20px;
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
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary-logs);
        display: block;
        transition: color 0.3s ease;
    }

    .stat-item .stat-label {
        font-size: 12px;
        color: var(--text-muted-logs);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
        transition: color 0.3s ease;
    }

    .stat-item .stat-number.green { color: #10b981; }
    .stat-item .stat-number.yellow { color: #f59e0b; }
    .stat-item .stat-number.red { color: #ef4444; }
    .stat-item .stat-number.purple { color: #6366f1; }

    /* Table Container */
    .table-container {
        background: var(--bg-card-logs);
        border-radius: var(--radius-logs);
        box-shadow: var(--shadow-card-logs);
        border: 1px solid var(--border-color-logs);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-table-logs);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: var(--bg-table-header-logs);
        transition: all 0.3s ease;
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary-logs);
        margin: 0;
        transition: color 0.3s ease;
    }

    .table-header .table-info {
        font-size: 13px;
        color: var(--text-muted-logs);
        transition: color 0.3s ease;
    }

    .table-header .table-info strong {
        color: var(--text-primary-logs);
        transition: color 0.3s ease;
    }

    /* PerPage Selector */
    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-secondary-logs);
        transition: color 0.3s ease;
    }

    .perpage-selector select {
        padding: 6px 12px;
        border: 1px solid var(--border-color-logs);
        border-radius: 6px;
        background: var(--bg-card-logs);
        font-size: 13px;
        color: var(--text-primary-logs);
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
    }

    .perpage-selector select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .perpage-selector select option {
        background: var(--bg-card-logs);
        color: var(--text-primary-logs);
    }

    .table-scroll {
        overflow-x: auto;
        padding: 0 24px 24px;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container thead th {
        text-align: left;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted-logs);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 2px solid var(--border-table-logs);
        background: var(--bg-table-header-logs);
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .table-container tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-table-logs);
        color: var(--text-primary-logs);
        font-size: 14px;
        vertical-align: middle;
        transition: all 0.3s ease;
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .table-container tbody tr {
        transition: background 0.15s ease;
    }

    .table-container tbody tr:hover {
        background: var(--bg-hover-row-logs);
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .status-badge::before {
        content: '';
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .status-badge.up {
        background: var(--badge-up-bg);
        color: var(--badge-up-text);
    }

    .status-badge.up::before {
        background: #10b981;
        animation: pulse 2s infinite;
    }

    .status-badge.warning {
        background: var(--badge-warning-bg);
        color: var(--badge-warning-text);
    }

    .status-badge.warning::before {
        background: #f59e0b;
        animation: pulse 1.5s infinite;
    }

    .status-badge.down {
        background: var(--badge-down-bg);
        color: var(--badge-down-text);
    }

    .status-badge.down::before {
        background: #ef4444;
        animation: pulse 1s infinite;
    }

    .status-badge.unknown {
        background: var(--badge-unknown-bg);
        color: var(--badge-unknown-text);
    }

    .status-badge.unknown::before {
        background: #94a3b8;
    }

    @keyframes pulse {
        0%, 100% { 
            opacity: 1;
            transform: scale(1);
        }
        50% { 
            opacity: 0.4;
            transform: scale(0.8);
        }
    }

    /* Service Name */
    .service-name {
        font-weight: 600;
        color: var(--text-primary-logs);
        transition: color 0.3s ease;
    }

    /* Response Time */
    .response-time {
        font-weight: 600;
        color: var(--text-primary-logs);
        font-family: 'Courier New', monospace;
        font-size: 13px;
        transition: color 0.3s ease;
    }

    .response-time .unit {
        color: var(--text-muted-logs);
        font-weight: 400;
        font-size: 11px;
        margin-left: 1px;
        transition: color 0.3s ease;
    }

    .response-time .slow {
        color: #ef4444;
    }

    .response-time .fast {
        color: #10b981;
    }

    /* Message Cell */
    .message-cell {
        max-width: 400px;
        word-wrap: break-word;
        white-space: normal;
        font-size: 13px;
        color: var(--text-secondary-logs);
        line-height: 1.5;
        transition: color 0.3s ease;
    }

    .message-cell .tooltip {
        position: relative;
        cursor: help;
    }

    /* Time */
    .time-cell {
        font-size: 13px;
        color: var(--text-secondary-logs);
        font-family: 'Courier New', monospace;
        white-space: nowrap;
        transition: color 0.3s ease;
    }

    /* Code */
    .code-cell {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary-logs);
        transition: color 0.3s ease;
    }

    .code-cell.success { color: #10b981; }
    .code-cell.error { color: #ef4444; }
    .code-cell.warning { color: #f59e0b; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted-logs);
    }

    .empty-state .empty-icon {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.6;
    }

    .empty-state h3 {
        color: var(--text-primary-logs);
        font-size: 18px;
        margin: 0 0 8px;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
        color: var(--text-secondary-logs);
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 16px 24px 20px;
        border-top: 1px solid var(--border-table-logs);
        background: var(--bg-table-header-logs);
        border-radius: 0 0 var(--radius-logs) var(--radius-logs);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-secondary-logs);
        transition: color 0.3s ease;
    }

    .pagination-info strong {
        color: var(--text-primary-logs);
        transition: color 0.3s ease;
    }

    .pagination-links {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
    }

    .pagination-links .page-link {
        padding: 6px 12px;
        background: var(--bg-card-logs);
        border: 1px solid var(--border-color-logs);
        border-radius: 6px;
        font-size: 13px;
        color: var(--text-secondary-logs);
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 36px;
        text-align: center;
    }

    .pagination-links .page-link:hover:not(.active) {
        background: var(--bg-hover-row-logs);
        border-color: var(--text-muted-logs);
        transform: translateY(-1px);
    }

    .pagination-links .page-link.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    .pagination-links .page-link.disabled {
        background: var(--bg-hover-row-logs);
        color: var(--text-muted-logs);
        cursor: not-allowed;
        pointer-events: none;
        border-color: var(--border-color-logs);
    }

    .pagination-links .page-dots {
        padding: 6px 4px;
        color: var(--text-muted-logs);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .stats-bar {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .logs-container {
            padding: 16px;
        }

        .logs-header {
            padding: 16px 20px;
            flex-direction: column;
            align-items: stretch;
        }

        .logs-header h1 {
            font-size: 20px;
        }

        .logs-header .header-icon {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        .stats-bar {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .stat-item .stat-number {
            font-size: 22px;
        }

        .table-scroll {
            padding: 0 12px 12px;
        }

        .table-container thead th,
        .table-container tbody td {
            padding: 10px 10px;
            font-size: 12px;
        }

        .status-badge {
            font-size: 10px;
            padding: 3px 10px;
        }

        .pagination-wrapper {
            flex-direction: column;
            align-items: stretch;
            padding: 12px 16px;
        }

        .pagination-links {
            justify-content: center;
        }

        .message-cell {
            max-width: 200px;
        }

        .perpage-selector {
            font-size: 12px;
        }

        .perpage-selector select {
            padding: 4px 8px;
            font-size: 12px;
        }

        .table-header {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }

        .btn-back {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .stats-bar {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-item {
            padding: 12px 14px;
        }

        .stat-item .stat-number {
            font-size: 18px;
        }

        .stat-item .stat-label {
            font-size: 10px;
        }

        .table-container thead th,
        .table-container tbody td {
            padding: 8px 6px;
            font-size: 11px;
        }

        .time-cell {
            font-size: 11px;
        }

        .message-cell {
            max-width: 150px;
            font-size: 11px;
        }

        .response-time {
            font-size: 11px;
        }

        .code-cell {
            font-size: 11px;
        }

        .pagination-links .page-link {
            padding: 4px 8px;
            font-size: 11px;
            min-width: 30px;
        }

        .perpage-selector {
            font-size: 11px;
        }

        .perpage-selector select {
            padding: 3px 6px;
            font-size: 11px;
        }

        .logs-header h1 {
            font-size: 17px;
        }

        .btn-back {
            font-size: 12px;
            padding: 6px 12px;
        }
    }
</style>

<div class="logs-container">
    <!-- Header -->
    <div class="logs-header">
        <div class="header-left">
            <div class="header-icon">📋</div>
            <div>
                <h1>Monitoring Logs</h1>
                <div class="header-subtitle">Lihat semua log monitoring service</div>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('services') }}" class="btn-back">
                ← Kembali ke Service
            </a>
        </div>
    </div>

    <!-- Stats Bar -->
    @php
        $totalLogs = $stats['total'] ?? $logs->count();
        $upCount = $stats['up'] ?? $logs->where('status', 'UP')->count();
        $warningCount = $stats['warning'] ?? $logs->where('status', 'WARNING')->count();
        $downCount = $stats['down'] ?? $logs->where('status', 'DOWN')->count();
    @endphp

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number purple">{{ $totalLogs }}</span>
            <span class="stat-label">Total Logs</span>
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

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>📋 Daftar Log Monitoring</h2>
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <div class="perpage-selector">
                    <label for="perPage">Tampilkan:</label>
                    <select id="perPage" onchange="changePerPage(this.value)">
                        <option value="10" {{ request('perPage', $perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('perPage', $perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('perPage', $perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage', $perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data</span>
                </div>
                <span class="table-info">
                    Total <strong>{{ $stats['total'] ?? $logs->count() }}</strong> logs
                </span>
            </div>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 160px;">Waktu</th>
                        <th>Service</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 80px;">Code</th>
                        <th style="width: 100px;">Response</th>
                        <th style="min-width: 250px;">Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $statusClass = strtolower($log->status ?? 'unknown');
                            $statusLabel = $log->status ?? 'UNKNOWN';
                            $responseTime = $log->response_time ?? 0;
                            $timeClass = $responseTime < 1 ? 'fast' : ($responseTime < 3 ? '' : 'slow');
                            $codeClass = $log->response_code < 400 ? 'success' : ($log->response_code < 500 ? 'warning' : 'error');
                            
                            $message = $log->message ?? '-';
                            if ($statusLabel == 'UP' && $log->response_code == 200) {
                                $message = '✅ Server berjalan dengan normal. Service dalam kondisi baik dan merespon dengan cepat.';
                            } elseif ($statusLabel == 'UP' && $log->response_code == 403) {
                                $message = '⚠️ Server merespon namun akses ditolak. Periksa kembali hak akses atau autentikasi pada endpoint yang dituju.';
                            } elseif ($statusLabel == 'UP' && $log->response_code == 404) {
                                $message = '⚠️ Halaman atau endpoint tidak ditemukan. Periksa kembali URL yang dituju.';
                            } elseif ($statusLabel == 'WARNING' && $log->response_code == 200) {
                                $message = '⚠️ Service berjalan namun response time lambat. Perlu dilakukan optimasi performa.';
                            } elseif ($statusLabel == 'DOWN') {
                                $message = '❌ Service tidak dapat diakses. Periksa koneksi jaringan atau status server.';
                            }
                        @endphp
                        <tr>
                            <td>
                                <span class="time-cell">
                                    {{ $log->checked_at ? $log->checked_at->format('d/m/Y H:i:s') : $log->created_at->format('d/m/Y H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <div class="service-name">
                                    {{ $log->service->name ?? 'Unknown Service' }}
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
                                <div class="message-cell">
                                    {{ $message }}
                                </div>
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

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}</strong> - <strong>{{ $logs->lastItem() ?? 0 }}</strong> dari <strong>{{ $logs->total() }}</strong> data
            </div>
            <div class="pagination-links">
                {{-- Previous Page --}}
                @if($logs->onFirstPage())
                    <span class="page-link disabled">‹</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="page-link">‹</a>
                @endif

                {{-- Pagination Elements --}}
                @php
                    $start = max(1, $logs->currentPage() - 2);
                    $end = min($logs->lastPage(), $logs->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $logs->url(1) }}" class="page-link">1</a>
                    @if($start > 2)
                        <span class="page-dots">…</span>
                    @endif
                @endif

                @foreach(range($start, $end) as $page)
                    @if($page == $logs->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $logs->url($page) }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($end < $logs->lastPage())
                    @if($end < $logs->lastPage() - 1)
                        <span class="page-dots">…</span>
                    @endif
                    <a href="{{ $logs->url($logs->lastPage()) }}" class="page-link">{{ $logs->lastPage() }}</a>
                @endif

                {{-- Next Page --}}
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
        window.location.href = url.toString();
    }
</script>
@endsection