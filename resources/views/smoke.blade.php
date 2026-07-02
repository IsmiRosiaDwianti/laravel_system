@extends('layouts.app')

@section('content')
<style>
    .smoke-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f0f2f5;
        min-height: 100vh;
    }

    /* ================= HEADER ================= */
    .smoke-header {
        background: linear-gradient(135deg, #0d3b66 0%, #1a4d7a 50%, #2563eb 100%);
        padding: 24px 32px;
        border-radius: 16px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(13, 59, 102, 0.25);
    }

    .smoke-header .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .smoke-header .header-icon {
        width: 52px;
        height: 52px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .smoke-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .smoke-header .header-subtitle {
        color: rgba(255, 255, 255, 0.75);
        font-size: 13px;
        font-weight: 400;
        margin-top: 2px;
    }

    .smoke-header .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .btn-download-csv {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        padding: 8px 18px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        cursor: pointer;
    }

    .btn-download-csv:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .status-esp {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.15);
        padding: 6px 16px;
        border-radius: 20px;
        color: white;
        font-size: 13px;
        font-weight: 500;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .status-esp .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-esp .dot.online {
        background: #10b981;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
        animation: pulse 2s infinite;
    }

    .status-esp .dot.offline {
        background: #ef4444;
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.8); }
    }

    /* ================= AUTO REFRESH TIMER ================= */
    .auto-refresh-timer {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(10, 46, 92, 0.85);
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        z-index: 99999;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        user-select: none;
        cursor: default;
    }

    .auto-refresh-timer .icon { font-size: 14px; }
    .auto-refresh-timer .label { opacity: 0.7; font-size: 10px; }
    .auto-refresh-timer .countdown {
        font-weight: 700;
        font-size: 14px;
        min-width: 40px;
        text-align: center;
        color: #6ee7b7;
    }
    .auto-refresh-timer .countdown.warning { color: #fcd34d; }
    .auto-refresh-timer .countdown.danger {
        color: #fca5a5;
        animation: blink 0.5s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* ================= SMOKE STATUS CARD ================= */
    .smoke-status-card {
        background: white;
        border-radius: 16px;
        padding: 28px 36px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        transition: all 0.3s ease;
    }

    .smoke-status-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .smoke-status-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .smoke-status-left .smoke-icon {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        flex-shrink: 0;
    }

    .smoke-status-left .smoke-icon.normal {
        background: #d1fae5;
        color: #059669;
    }

    .smoke-status-left .smoke-icon.warning {
        background: #fef3c7;
        color: #d97706;
    }

    .smoke-status-left .smoke-icon.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .smoke-status-left .smoke-info h3 {
        margin: 0 0 2px 0;
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
    }

    .smoke-status-left .smoke-info p {
        margin: 0;
        font-size: 13px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .smoke-status-left .smoke-info .status-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .smoke-status-left .smoke-info .status-label.normal {
        background: #d1fae5;
        color: #065f46;
    }

    .smoke-status-left .smoke-info .status-label.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .smoke-status-left .smoke-info .status-label.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .smoke-status-right {
        display: flex;
        align-items: center;
        gap: 28px;
        flex: 1;
        max-width: 500px;
        min-width: 200px;
    }

    .smoke-status-right .smoke-value {
        font-size: 2.8rem;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        min-width: 100px;
    }

    .smoke-status-right .smoke-value small {
        font-size: 1.2rem;
        font-weight: 400;
        color: #94a3b8;
    }

    .smoke-status-right .smoke-value.normal { color: #059669; }
    .smoke-status-right .smoke-value.warning { color: #d97706; }
    .smoke-status-right .smoke-value.danger { color: #dc2626; }

    .smoke-status-right .smoke-bar-container {
        flex: 1;
        min-width: 100px;
    }

    .smoke-status-right .bar-track {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .smoke-status-right .bar-fill {
        height: 100%;
        border-radius: 20px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .smoke-status-right .bar-fill.normal {
        background: linear-gradient(90deg, #10b981, #059669);
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }
    .smoke-status-right .bar-fill.warning {
        background: linear-gradient(90deg, #f59e0b, #d97706);
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
    }
    .smoke-status-right .bar-fill.danger {
        background: linear-gradient(90deg, #ef4444, #dc2626);
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
    }

    .smoke-status-right .bar-label {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
        font-weight: 500;
    }

    /* ================= TABLE LOGS ================= */
    .table-container {
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
        overflow: hidden;
    }

    .table-header {
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: #fafbfc;
    }

    .table-header h2 {
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-header .table-info {
        font-size: 13px;
        color: #94a3b8;
    }

    .table-header .table-info strong {
        color: #0f172a;
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
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #f1f5f9;
        background: #fafbfc;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-container tbody td {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        font-size: 13px;
        vertical-align: middle;
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .table-container tbody tr:hover {
        background: #f8fafc;
    }

    /* ================= STATUS BADGE ================= */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .status-badge::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-badge.danger {
        background: #fee2e2;
        color: #991b1b;
    }
    .status-badge.danger::before { background: #ef4444; animation: pulse 1s infinite; }

    .status-badge.warning {
        background: #fef3c7;
        color: #92400e;
    }
    .status-badge.warning::before { background: #f59e0b; animation: pulse 1.5s infinite; }

    .status-badge.normal {
        background: #d1fae5;
        color: #065f46;
    }
    .status-badge.normal::before { background: #10b981; animation: pulse 2s infinite; }

    .value-cell {
        font-weight: 700;
        font-family: 'Courier New', monospace;
        font-size: 14px;
    }

    .value-cell.danger { color: #ef4444; }
    .value-cell.warning { color: #f59e0b; }
    .value-cell.normal { color: #10b981; }

    .time-cell {
        font-size: 12px;
        color: #64748b;
        font-family: 'Courier New', monospace;
        white-space: nowrap;
    }

    .message-cell {
        font-size: 12px;
        color: #475569;
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .device-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 13px;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .empty-state .empty-icon {
        font-size: 40px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.6;
    }

    .empty-state h3 {
        color: #0f172a;
        font-size: 17px;
        margin: 0 0 4px;
        font-weight: 600;
    }

    .empty-state p {
        margin: 0;
        font-size: 13px;
    }

    /* ================= PAGINATION ================= */
    .pagination-wrapper {
        padding: 12px 24px 16px;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
        border-radius: 0 0 14px 14px;
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
        padding: 5px 10px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 34px;
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
        padding: 5px 4px;
        color: #94a3b8;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 768px) {
        .smoke-container { padding: 16px; }
        .smoke-header {
            padding: 18px 20px;
            flex-direction: column;
            align-items: stretch;
            border-radius: 14px;
        }
        .smoke-header h1 { font-size: 20px; }
        .smoke-header .header-icon { width: 40px; height: 40px; font-size: 20px; }
        .smoke-status-card {
            flex-direction: column;
            align-items: stretch;
            padding: 18px 20px;
        }
        .smoke-status-left .smoke-icon { width: 48px; height: 48px; font-size: 24px; }
        .smoke-status-left .smoke-info h3 { font-size: 15px; }
        .smoke-status-right {
            max-width: 100%;
            flex-wrap: wrap;
            gap: 16px;
        }
        .smoke-status-right .smoke-value { font-size: 2rem; min-width: 70px; }
        .table-scroll { padding: 0 12px 12px; }
        .table-container thead th,
        .table-container tbody td { padding: 8px 10px; font-size: 12px; }
        .status-badge { font-size: 10px; padding: 2px 10px; }
        .value-cell { font-size: 12px; }
        .time-cell { font-size: 11px; }
        .message-cell { max-width: 100px; }
        .pagination-wrapper { flex-direction: column; align-items: stretch; padding: 12px 16px; }
        .pagination-links { justify-content: center; }
        .auto-refresh-timer {
            bottom: 10px;
            right: 10px;
            padding: 6px 12px;
            font-size: 10px;
        }
        .auto-refresh-timer .countdown { font-size: 12px; min-width: 30px; }
        .btn-download-csv {
            padding: 6px 14px;
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .smoke-header h1 { font-size: 17px; }
        .smoke-status-card { padding: 14px 16px; }
        .smoke-status-left .smoke-icon { width: 40px; height: 40px; font-size: 20px; }
        .smoke-status-right .smoke-value { font-size: 1.6rem; min-width: 50px; }
        .smoke-status-right .smoke-value small { font-size: 0.9rem; }
        .table-container thead th,
        .table-container tbody td { padding: 6px 6px; font-size: 11px; }
        .status-badge { font-size: 9px; padding: 2px 8px; gap: 4px; }
        .status-badge::before { width: 5px; height: 5px; }
        .device-name { font-size: 12px; }
        .value-cell { font-size: 11px; }
        .time-cell { font-size: 10px; }
        .message-cell { max-width: 60px; font-size: 10px; }
        .pagination-links .page-link { padding: 4px 8px; font-size: 11px; min-width: 30px; }
        .btn-download-csv {
            padding: 5px 10px;
            font-size: 11px;
        }
        .btn-download-csv span { display: none; }
    }
</style>

<div class="smoke-container">
    <!-- ================= HEADER ================= -->
    <div class="smoke-header">
        <div class="header-left">
            <div class="header-icon">🔥</div>
            <div>
                <h1>Smoke Detector Monitoring</h1>
                <div class="header-subtitle">Pantau status asap dan kondisi device</div>
            </div>
        </div>
        <div class="header-actions">
            <!-- Tombol Download CSV -->
            <a href="{{ route('smoke.export') }}" class="btn-download-csv">
                📥 <span>Download CSV</span>
            </a>

            @php
                $isOnline = ($onlineCount ?? 0) > 0;
            @endphp
            <div class="status-esp">
                <span class="dot {{ $isOnline ? 'online' : 'offline' }}"></span>
                ESP Status: {{ $isOnline ? 'ONLINE' : 'OFFLINE' }}
            </div>
        </div>
    </div>

    <!-- ================= SMOKE STATUS CARD ================= -->
    @php
        $device = $devices->first();
        $smokeValue = $device?->smoke_value ?? 0;
        $smokeStatus = strtolower($device?->status ?? 'normal');
        $statusClass = $smokeStatus;
        $statusLabel = $smokeStatus == 'danger' ? '🔴 BAHAYA' : ($smokeStatus == 'warning' ? '🟡 WARNING' : '🟢 NORMAL');
        $maxPpm = 1000;
        $percentage = min(($smokeValue / $maxPpm) * 100, 100);
    @endphp

    <div class="smoke-status-card">
        <div class="smoke-status-left">
            <div class="smoke-icon {{ $statusClass }}">
                @if($smokeStatus == 'danger') 🔥
                @elseif($smokeStatus == 'warning') ⚠️
                @else ✅
                @endif
            </div>
            <div class="smoke-info">
                <h3>Kadar Asap</h3>
                <p>
                    Status:
                    <span class="status-label {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </p>
            </div>
        </div>
        <div class="smoke-status-right">
            <div class="smoke-value {{ $statusClass }}">
                {{ number_format($smokeValue, 0) }}<small> ppm</small>
            </div>
            <div class="smoke-bar-container">
                <div class="bar-track">
                    <div class="bar-fill {{ $statusClass }}" style="width: {{ $percentage }}%;"></div>
                </div>
                <div class="bar-label">
                    <span>0 ppm</span>
                    <span style="color: #dc2626; font-weight: 600;">⚠️ {{ $maxPpm }} ppm</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TABLE LOGS ================= -->
    <div class="table-container">
        <div class="table-header">
            <h2>📋 Riwayat Log Smoke Detector</h2>
            <span class="table-info">
                Total <strong>{{ $smokeLogs->total() }}</strong> logs
            </span>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 150px;">Waktu</th>
                        <th style="width: 90px;">Nilai Asap</th>
                        <th style="width: 110px;">Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($smokeLogs as $log)
                        @php
                            $statusClass = strtolower($log->status ?? 'normal');
                            $valueClass = $log->status == 'DANGER' ? 'danger' : ($log->status == 'WARNING' ? 'warning' : 'normal');
                            $statusIcon = $log->status == 'DANGER' ? '🔴' : ($log->status == 'WARNING' ? '🟡' : '🟢');
                        @endphp
                        <tr>
                            <td>
                                <span class="time-cell">
                                    {{ $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <span class="value-cell {{ $valueClass }}">
                                    {{ $log->smoke_value ?? 0 }}
                                    <span style="font-size: 10px; font-weight: 400; color: #94a3b8;">ppm</span>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusIcon }} {{ $log->status ?? 'NORMAL' }}
                                </span>
                            </td>
                            <td>
                                <div class="message-cell">
                                    @if($log->status == 'DANGER')
                                        🔥 Asap tinggi! Periksa segera!
                                    @elseif($log->status == 'WARNING')
                                        ⚠️ Asap mulai terdeteksi, waspada!
                                    @else
                                        ✅ Kondisi aman, tidak ada asap
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <span class="empty-icon">📭</span>
                                    <h3>Belum Ada Log</h3>
                                    <p>Belum ada data smoke detector yang tercatat</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($smokeLogs->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $smokeLogs->firstItem() ?? 0 }}</strong> - <strong>{{ $smokeLogs->lastItem() ?? 0 }}</strong> dari <strong>{{ $smokeLogs->total() }}</strong> data
            </div>
            <div class="pagination-links">
                {{ $smokeLogs->appends(['perPage' => request('perPage')])->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ================= AUTO REFRESH TIMER ================= -->
<div class="auto-refresh-timer" id="autoRefreshTimer">
    <span class="icon">🔄</span>
    <span class="label">Refresh</span>
    <span class="countdown" id="countdownTimer">0:30</span>
</div>

<!-- ================= SCRIPT ================= -->
<script>
    // ================= AUTO REFRESH (30 DETIK) =================
    (function() {
        'use strict';
        
        const REFRESH_INTERVAL = 30; // 30 detik
        let seconds = REFRESH_INTERVAL;
        let countdownElement = document.getElementById('countdownTimer');
        let refreshTimer = null;
        
        function updateCountdown() {
            seconds--;
            
            if (countdownElement) {
                const secs = seconds.toString().padStart(2, '0');
                countdownElement.textContent = `0:${secs}`;
                
                countdownElement.className = 'countdown';
                if (seconds < 5) {
                    countdownElement.classList.add('danger');
                } else if (seconds < 10) {
                    countdownElement.classList.add('warning');
                }
            }
            
            if (seconds <= 0) {
                window.location.reload();
                return;
            }
        }
        
        function startCountdown() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
            }
            seconds = REFRESH_INTERVAL;
            updateCountdown();
            refreshTimer = setInterval(updateCountdown, 1000);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
        });
    })();
</script>
@endsection