@extends('layouts.app')

@section('content')
<style>
    .logs-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #ffffff;
        min-height: 100vh;
    }

    /* Header Section */
    .logs-header {
        background: white;
        padding: 24px 28px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #eef2f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
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
    }

    .logs-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .logs-header .header-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 400;
        margin-top: 2px;
    }

    .logs-header .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Stats Bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-item {
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #eef2f6;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: all 0.2s ease;
    }

    .stat-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .stat-item .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        display: block;
    }

    .stat-item .stat-label {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    .stat-item .stat-number.green { color: #10b981; }
    .stat-item .stat-number.yellow { color: #f59e0b; }
    .stat-item .stat-number.red { color: #ef4444; }
    .stat-item .stat-number.purple { color: #6366f1; }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #eef2f6;
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: #fafbfc;
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
    }

    .table-header .table-info {
        font-size: 13px;
        color: #94a3b8;
    }

    .table-header .table-info strong {
        color: #0f172a;
    }

    /* PerPage Selector */
    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
    }

    .perpage-selector select {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        font-size: 13px;
        color: #0f172a;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
    }

    .perpage-selector select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
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
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 2px solid #f1f5f9;
        background: #fafbfc;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-container tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        font-size: 14px;
        vertical-align: middle;
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .table-container tbody tr {
        transition: background 0.15s ease;
    }

    .table-container tbody tr:hover {
        background: #f8fafc;
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
    }

    .status-badge::before {
        content: '';
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .status-badge.up {
        background: #ecfdf5;
        color: #065f46;
    }

    .status-badge.up::before {
        background: #10b981;
        animation: pulse 2s infinite;
    }

    .status-badge.warning {
        background: #fffbeb;
        color: #92400e;
    }

    .status-badge.warning::before {
        background: #f59e0b;
        animation: pulse 1.5s infinite;
    }

    .status-badge.down {
        background: #fef2f2;
        color: #991b1b;
    }

    .status-badge.down::before {
        background: #ef4444;
        animation: pulse 1s infinite;
    }

    .status-badge.unknown {
        background: #f1f5f9;
        color: #64748b;
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
        color: #0f172a;
    }

    .service-name .service-id {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 400;
        display: block;
        margin-top: 1px;
    }

    /* Response Time */
    .response-time {
        font-weight: 600;
        color: #0f172a;
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }

    .response-time .unit {
        color: #94a3b8;
        font-weight: 400;
        font-size: 11px;
        margin-left: 1px;
    }

    .response-time .slow {
        color: #ef4444;
    }

    .response-time .fast {
        color: #10b981;
    }

    /* Message */
    .message-cell {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        color: #475569;
    }

    .message-cell .tooltip {
        position: relative;
        cursor: help;
    }

    .message-cell .tooltip:hover::after {
        content: attr(data-full);
        position: absolute;
        background: #0f172a;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        white-space: normal;
        max-width: 300px;
        z-index: 100;
        bottom: 100%;
        left: 0;
        margin-bottom: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Time */
    .time-cell {
        font-size: 13px;
        color: #64748b;
        font-family: 'Courier New', monospace;
        white-space: nowrap;
    }

    /* Code */
    .code-cell {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
    }

    .code-cell.success { color: #10b981; }
    .code-cell.error { color: #ef4444; }
    .code-cell.warning { color: #f59e0b; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }

    .empty-state .empty-icon {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.6;
    }

    .empty-state h3 {
        color: #0f172a;
        font-size: 18px;
        margin: 0 0 8px;
        font-weight: 600;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 16px 24px 20px;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
        border-radius: 0 0 16px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        font-size: 13px;
        color: #64748b;
    }

    .pagination-info strong {
        color: #0f172a;
    }

    .pagination-links {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
    }

    .pagination-links .page-link {
        padding: 6px 12px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 36px;
        text-align: center;
    }

    .pagination-links .page-link:hover:not(.active) {
        background: #f1f5f9;
        border-color: #94a3b8;
        transform: translateY(-1px);
    }

    .pagination-links .page-link.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }

    .pagination-links .page-link.disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        pointer-events: none;
    }

    .pagination-links .page-dots {
        padding: 6px 4px;
        color: #94a3b8;
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
            max-width: 100px;
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
            max-width: 80px;
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
            <!-- Back Button -->
            <a href="{{ route('services') }}" class="btn-primary" style="
                background: #f1f5f9;
                color: #475569;
                padding: 8px 16px;
                border-radius: 8px;
                text-decoration: none;
                font-size: 13px;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s ease;
                border: 1px solid #e2e8f0;
            ">
                ← Kembali ke Service
            </a>
        </div>
    </div>

    <!-- Stats Bar -->
    @php
        $totalLogs = $logs->total();
        $upCount = $logs->where('status', 'UP')->count();
        $warningCount = $logs->where('status', 'WARNING')->count();
        $downCount = $logs->where('status', 'DOWN')->count();
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
                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('perPage') == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data</span>
                </div>
                <span class="table-info">
                    Total <strong>{{ $logs->total() }}</strong> logs
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
                        <th>Message</th>
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
                        @endphp
                        <tr>
                            <td>
                                <span class="time-cell">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <div class="service-name">
                                    {{ $log->service->name ?? 'Unknown Service' }}
                                    <span class="service-id">ID: {{ $log->service_id ?? '-' }}</span>
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
                                <div class="message-cell tooltip" data-full="{{ $log->message ?? '-' }}">
                                    {{ $log->message ?? '-' }}
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
        // Get current URL
        let url = new URL(window.location.href);
        // Set or update perPage parameter
        url.searchParams.set('perPage', value);
        // Redirect to new URL
        window.location.href = url.toString();
    }

    // Tooltip for message cell
    document.addEventListener('DOMContentLoaded', function() {
        const tooltips = document.querySelectorAll('.tooltip');
        tooltips.forEach(el => {
            el.addEventListener('mouseenter', function(e) {
                const fullText = this.getAttribute('data-full');
                if (fullText && fullText.length > 50) {
                    // Show tooltip
                    this.title = fullText;
                }
            });
        });
    });
</script>
@endsection