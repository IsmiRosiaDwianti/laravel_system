@extends('layouts.app')

@section('content')
<style>
    /* ================= ROOT VARIABLES ================= */
    :root {
        --bg-service: #f0f2f5;
        --bg-card-service: #ffffff;
        --bg-table-service: #fafbfc;
        --bg-hover-service: #f8fafc;
        --bg-input-service: #ffffff;
        --bg-toast-service: #ffffff;
        --bg-modal-service: #ffffff;
        --bg-modal-header-service: #fafbfc;
        --bg-modal-footer-service: #fafbfc;
        --bg-status-bar-service: #f8fafc;
        --bg-detail-service: #f8fafc;
        --bg-detail-alt-service: #f1f5f9;
        --bg-info-box-service: #eff6ff;
        --border-info-box-service: #93c5fd;
        --text-info-box-service: #1e40af;
        --bg-delete-modal-service: #ffffff;
        
        --text-service: #1e293b;
        --text-secondary-service: #475569;
        --text-muted-service: #94a3b8;
        --text-light-service: #6b7280;
        --border-service: #e8ecf1;
        --border-table-service: #f1f4f8;
        --shadow-service: rgba(0, 0, 0, 0.04);
        --shadow-hover-service: rgba(0, 0, 0, 0.08);
    }

    /* Dark mode override dari layout utama */
    [data-theme="dark"] {
        --bg-service: #0f172a;
        --bg-card-service: #1e293b;
        --bg-table-service: #1e293b;
        --bg-hover-service: #2d3a4f;
        --bg-input-service: #1e293b;
        --bg-toast-service: #1e293b;
        --bg-modal-service: #1e293b;
        --bg-modal-header-service: #1e293b;
        --bg-modal-footer-service: #1e293b;
        --bg-status-bar-service: #1e293b;
        --bg-detail-service: #2d3a4f;
        --bg-detail-alt-service: #1e293b;
        --bg-info-box-service: #1a2332;
        --border-info-box-service: #3b82f6;
        --text-info-box-service: #93c5fd;
        --bg-delete-modal-service: #1e293b;
        
        --text-service: #e2e8f0;
        --text-secondary-service: #94a3b8;
        --text-muted-service: #64748b;
        --text-light-service: #94a3b8;
        --border-service: #334155;
        --border-table-service: #334155;
        --shadow-service: rgba(0, 0, 0, 0.2);
        --shadow-hover-service: rgba(0, 0, 0, 0.3);
    }

    /* ================= STYLE UTAMA ================= */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .service-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        background: var(--bg-service);
        min-height: 100vh;
        transition: background 0.3s ease, color 0.3s ease;
        color: var(--text-service);
    }

    /* ================= HEADER ================= */
    .service-header {
        background: linear-gradient(135deg, #0a2e5c 0%, #1a4d7a 50%, #1e5f8e 100%);
        padding: 28px 36px;
        border-radius: 16px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(10, 46, 92, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: box-shadow 0.3s ease;
    }

    .service-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        pointer-events: none;
    }

    .service-header::after {
        content: '';
        position: absolute;
        bottom: -60%;
        left: 20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 50%;
        pointer-events: none;
    }

    .service-header .header-left {
        display: flex;
        align-items: center;
        gap: 18px;
        position: relative;
        z-index: 1;
    }

    .service-header .header-icon {
        width: 56px;
        height: 56px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .service-header .header-text h1 {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0;
        letter-spacing: -0.3px;
        line-height: 1.2;
    }

    .service-header .header-text .header-subtitle {
        color: rgba(255, 255, 255, 0.75);
        font-size: 13px;
        font-weight: 400;
        margin-top: 2px;
    }

    .service-header .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .btn-primary {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        padding: 11px 24px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        backdrop-filter: blur(10px);
    }

    .btn-primary:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .btn-primary svg {
        width: 18px;
        height: 18px;
    }

    /* ================= AUTO REFRESH TIMER (POJOK) ================= */
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
        transition: background 0.3s ease;
    }

    [data-theme="dark"] .auto-refresh-timer {
        background: rgba(15, 23, 42, 0.9);
        border-color: rgba(255, 255, 255, 0.05);
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

    /* ================= TOAST ================= */
    .toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 420px;
        width: 100%;
    }

    .toast {
        background: var(--bg-toast-service);
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 10px 40px var(--shadow-service);
        border-left: 5px solid;
        animation: slideInRight 0.4s ease;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        color: var(--text-service);
        border: 1px solid var(--border-service);
    }

    .toast.hide { animation: slideOutRight 0.4s ease forwards; }
    .toast-success { border-left-color: #10b981; }
    .toast-error { border-left-color: #ef4444; }
    .toast-warning { border-left-color: #f59e0b; }
    .toast-info { border-left-color: #3b82f6; }

    .toast .toast-icon { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
    .toast .toast-content { flex: 1; }
    .toast .toast-title { font-weight: 600; font-size: 14px; color: var(--text-service); }
    .toast .toast-message { font-size: 13px; color: var(--text-secondary-service); margin-top: 2px; }
    .toast .toast-close {
        background: none;
        border: none;
        font-size: 20px;
        color: var(--text-muted-service);
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
        transition: color 0.2s ease;
    }
    .toast .toast-close:hover { color: var(--text-service); }

    @keyframes slideInRight {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(120%); opacity: 0; }
    }

    /* ================= STATS ================= */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-item {
        background: var(--bg-card-service);
        padding: 20px 24px;
        border-radius: 14px;
        border: 1px solid var(--border-service);
        box-shadow: 0 2px 8px var(--shadow-service);
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        color: var(--text-service);
    }

    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: 4px 0 0 4px;
    }
    .stat-item:nth-child(1)::before { background: #4f46e5; }
    .stat-item:nth-child(2)::before { background: #059669; }
    .stat-item:nth-child(3)::before { background: #d97706; }
    .stat-item:nth-child(4)::before { background: #dc2626; }

    .stat-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px var(--shadow-hover-service);
    }

    .stat-item .stat-number {
        font-size: 30px;
        font-weight: 800;
        color: var(--text-service);
        display: block;
        letter-spacing: -0.5px;
        line-height: 1.2;
        transition: color 0.3s ease;
    }
    .stat-item .stat-number.purple { color: #4f46e5; }
    .stat-item .stat-number.green { color: #059669; }
    .stat-item .stat-number.yellow { color: #d97706; }
    .stat-item .stat-number.red { color: #dc2626; }

    .stat-item .stat-label {
        font-size: 12px;
        color: var(--text-muted-service);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
        transition: color 0.3s ease;
    }

    /* ================= UPTIME ================= */
    .uptime-value { font-size: 16px; font-weight: 700; transition: color 0.3s ease; }
    .uptime-value.green { color: #059669; }
    .uptime-value.yellow { color: #d97706; }
    .uptime-value.red { color: #dc2626; }

    .uptime-bar {
        width: 100%;
        height: 4px;
        background: var(--border-service);
        border-radius: 2px;
        margin-top: 4px;
        overflow: hidden;
        transition: background 0.3s ease;
    }
    .uptime-bar .uptime-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease;
    }
    .uptime-bar .uptime-fill.green { background: #059669; }
    .uptime-bar .uptime-fill.yellow { background: #d97706; }
    .uptime-bar .uptime-fill.red { background: #dc2626; }

    /* ================= SEARCH BOX ================= */
    .search-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        max-width: 500px;
    }

    .search-wrapper .search-input-wrap {
        position: relative;
        flex: 1;
    }

    .search-wrapper .search-input-wrap .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted-service);
        font-size: 16px;
        pointer-events: none;
        transition: color 0.3s ease;
    }

    .search-wrapper .search-input-wrap input {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border: 1px solid var(--border-service);
        border-radius: 10px;
        font-size: 14px;
        background: var(--bg-input-service);
        color: var(--text-service);
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .search-wrapper .search-input-wrap input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        background: var(--bg-input-service);
    }

    .search-wrapper .search-input-wrap input::placeholder {
        color: var(--text-muted-service);
    }

    .search-wrapper .btn-search {
        background: #4f46e5;
        color: white;
        padding: 10px 22px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .search-wrapper .btn-search:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    .search-wrapper .btn-search:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .search-wrapper .btn-reset {
        background: var(--border-service);
        color: var(--text-secondary-service);
        padding: 10px 16px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .search-wrapper .btn-reset:hover {
        background: var(--text-muted-service);
        color: var(--bg-service);
        transform: translateY(-1px);
    }

    /* ================= SEARCH STATUS BAR ================= */
    .search-status {
        display: none;
        padding: 10px 16px;
        background: var(--bg-status-bar-service);
        border-bottom: 1px solid var(--border-service);
        font-size: 13px;
        color: var(--text-secondary-service);
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .search-status.active {
        display: flex;
    }

    .search-status .status-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid var(--border-service);
        border-top: 2px solid #4f46e5;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        flex-shrink: 0;
    }

    .search-status .status-text {
        flex: 1;
        color: var(--text-secondary-service);
    }

    .search-status .status-cancel {
        background: none;
        border: none;
        color: var(--text-muted-service);
        cursor: pointer;
        font-size: 18px;
        padding: 0 4px;
        transition: color 0.2s ease;
    }

    .search-status .status-cancel:hover {
        color: var(--text-service);
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ================= TABLE ================= */
    .table-container {
        background: var(--bg-card-service);
        border-radius: 14px;
        box-shadow: 0 2px 8px var(--shadow-service);
        border: 1px solid var(--border-service);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-table-service);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: var(--bg-table-service);
        transition: all 0.3s ease;
    }

    .table-header .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-service);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s ease;
    }

    .table-header .table-info {
        font-size: 13px;
        color: var(--text-muted-service);
        transition: color 0.3s ease;
    }
    .table-header .table-info strong { color: var(--text-service); }

    /* ===== PAGINATION & PERPAGE ===== */
    .table-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-secondary-service);
        transition: color 0.3s ease;
    }

    .perpage-selector select {
        padding: 6px 12px;
        border: 1px solid var(--border-service);
        border-radius: 6px;
        background: var(--bg-input-service);
        color: var(--text-service);
        font-size: 13px;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
    }
    .perpage-selector select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .perpage-selector select option {
        background: var(--bg-input-service);
        color: var(--text-service);
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
        color: var(--text-muted-service);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-table-service);
        background: var(--bg-table-service);
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .table-container tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-table-service);
        color: var(--text-service);
        font-size: 14px;
        vertical-align: middle;
        transition: all 0.3s ease;
    }
    .table-container tbody tr:last-child td { border-bottom: none; }
    .table-container tbody tr:hover { background: var(--bg-hover-service); }

    /* ================= SERVICE INFO ================= */
    .service-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .service-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .service-avatar.color-1 { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
    .service-avatar.color-2 { background: linear-gradient(135deg, #059669, #10b981); }
    .service-avatar.color-3 { background: linear-gradient(135deg, #d97706, #f59e0b); }
    .service-avatar.color-4 { background: linear-gradient(135deg, #dc2626, #ef4444); }
    .service-avatar.color-5 { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .service-avatar.color-6 { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
    .service-avatar.color-7 { background: linear-gradient(135deg, #db2777, #ec4899); }
    .service-avatar.color-8 { background: linear-gradient(135deg, #0d9488, #14b8a6); }

    .service-name {
        font-weight: 600;
        color: var(--text-service);
        font-size: 14px;
        transition: color 0.3s ease;
    }
    .service-type {
        font-size: 11px;
        color: var(--text-muted-service);
        display: block;
        margin-top: 1px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: color 0.3s ease;
    }

    .service-target {
        font-size: 13px;
        color: var(--text-secondary-service);
        font-family: 'SF Mono', 'Courier New', monospace;
        background: var(--bg-hover-service);
        padding: 3px 12px;
        border-radius: 4px;
        display: inline-block;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid var(--border-service);
        transition: all 0.3s ease;
    }

    /* ================= STATUS BADGE ================= */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid;
        transition: all 0.3s ease;
    }
    .status-badge .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-badge.up {
        background: #d1fae5;
        color: #065f46;
        border-color: #6ee7b7;
    }
    .status-badge.up .status-dot {
        background: #059669;
        animation: pulse 2s infinite;
    }

    .status-badge.down {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }
    .status-badge.down .status-dot {
        background: #dc2626;
        animation: pulse 1s infinite;
    }

    .status-badge.warning {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }
    .status-badge.warning .status-dot {
        background: #d97706;
        animation: pulse 1.5s infinite;
    }

    .status-badge.unknown {
        background: var(--bg-hover-service);
        color: var(--text-muted-service);
        border-color: var(--border-service);
    }
    .status-badge.unknown .status-dot { background: var(--text-muted-service); }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.8); }
    }

    .service-no {
        font-weight: 600;
        color: var(--text-muted-service);
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        min-width: 30px;
        display: inline-block;
        transition: color 0.3s ease;
    }

    /* ================= BUTTONS ================= */
    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
        color: white;
    }
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-detail { background: #2563eb; }
    .btn-detail:hover { background: #1d4ed8; }

    .btn-download { background: #059669; }
    .btn-download:hover { background: #047857; }

    .btn-edit { background: #d97706; }
    .btn-edit:hover { background: #b45309; }

    .btn-delete { background: #dc2626; }
    .btn-delete:hover { background: #b91c1c; }

    .btn-check {
        background: #7c3aed;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-check:hover {
        background: #6d28d9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }
    .btn-check:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* ================= PAGINATION ================= */
    .pagination-wrapper {
        padding: 16px 24px 20px;
        border-top: 1px solid var(--border-table-service);
        background: var(--bg-table-service);
        border-radius: 0 0 14px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-secondary-service);
        transition: color 0.3s ease;
    }
    .pagination-info strong { color: var(--text-service); }

    .pagination-links {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
    }

    .pagination-links .page-link {
        padding: 6px 12px;
        background: var(--bg-card-service);
        border: 1px solid var(--border-service);
        border-radius: 6px;
        font-size: 13px;
        color: var(--text-secondary-service);
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 36px;
        text-align: center;
    }
    .pagination-links .page-link:hover:not(.active) {
        background: var(--bg-hover-service);
        border-color: var(--text-muted-service);
        transform: translateY(-1px);
    }
    .pagination-links .page-link.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    .pagination-links .page-link.disabled {
        background: var(--bg-hover-service);
        color: var(--text-muted-service);
        cursor: not-allowed;
        pointer-events: none;
        border-color: var(--border-service);
    }
    .pagination-links .page-dots {
        padding: 6px 4px;
        color: var(--text-muted-service);
    }

    /* ================= CUSTOM MODAL ================= */
    .custom-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        z-index: 99999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.25s ease;
    }
    .custom-modal-overlay.active { display: flex; }

    .custom-modal {
        background: var(--bg-delete-modal-service);
        border-radius: 20px;
        max-width: 450px;
        width: 92%;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        border: 1px solid var(--border-service);
        color: var(--text-service);
    }

    .custom-modal-header {
        padding: 24px 28px 16px;
        text-align: center;
    }

    .custom-modal-header .modal-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        margin: 0 auto 12px;
    }

    .custom-modal-header .modal-icon.warning {
        background: #fef3c7;
        color: #d97706;
    }

    .custom-modal-header .modal-icon.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .custom-modal-header .modal-icon.success {
        background: #d1fae5;
        color: #059669;
    }

    .custom-modal-header .modal-icon.info {
        background: #dbeafe;
        color: #2563eb;
    }

    .custom-modal-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-service);
        margin: 0 0 6px;
        transition: color 0.3s ease;
    }

    .custom-modal-header p {
        font-size: 14px;
        color: var(--text-secondary-service);
        margin: 0;
        line-height: 1.6;
        transition: color 0.3s ease;
    }

    .custom-modal-body {
        padding: 0 28px 20px;
    }

    .custom-modal-body .highlight-name {
        font-weight: 700;
        color: var(--text-service);
        background: var(--bg-hover-service);
        padding: 2px 10px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .custom-modal-footer {
        padding: 16px 28px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-top: 1px solid var(--border-service);
        background: var(--bg-modal-footer-service);
        transition: all 0.3s ease;
    }

    .custom-modal-footer .btn-modal {
        padding: 10px 28px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .custom-modal-footer .btn-modal:hover {
        transform: translateY(-2px);
    }

    .custom-modal-footer .btn-cancel {
        background: var(--bg-hover-service);
        color: var(--text-secondary-service);
        border: 1px solid var(--border-service);
    }

    .custom-modal-footer .btn-cancel:hover {
        background: var(--border-service);
        box-shadow: 0 4px 12px var(--shadow-service);
    }

    .custom-modal-footer .btn-confirm {
        background: #dc2626;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    .custom-modal-footer .btn-confirm:hover {
        background: #b91c1c;
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
    }

    .btn-modal:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* ================= MODAL FORM ================= */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(6px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.25s ease;
    }
    .modal-overlay.active { display: flex; }

    .modal-content {
        background: var(--bg-modal-service);
        border-radius: 16px;
        max-width: 580px;
        width: 92%;
        max-height: 92vh;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.3s ease;
        border: 1px solid var(--border-service);
        color: var(--text-service);
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-table-service);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-modal-header-service);
        transition: all 0.3s ease;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-service);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }
    .modal-header h2 .modal-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 26px;
        color: var(--text-muted-service);
        cursor: pointer;
        padding: 0 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
        line-height: 1;
    }
    .modal-close:hover {
        background: var(--bg-hover-service);
        color: var(--text-service);
    }

    .modal-close:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .modal-body {
        padding: 24px;
        max-height: 55vh;
        overflow-y: auto;
    }
    .modal-body::-webkit-scrollbar { width: 5px; }
    .modal-body::-webkit-scrollbar-track {
        background: var(--bg-hover-service);
        border-radius: 10px;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: var(--text-muted-service);
        border-radius: 10px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-table-service);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: var(--bg-modal-footer-service);
        border-radius: 0 0 16px 16px;
        transition: all 0.3s ease;
    }

    .modal-footer .btn-cancel-modal:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    .modal-footer .btn-submit-modal:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* ================= DETAIL ================= */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .detail-item {
        background: var(--bg-detail-service);
        border-radius: 10px;
        padding: 14px 16px;
        border: 1px solid var(--border-service);
        transition: all 0.3s ease;
    }
    .detail-item.full-width { grid-column: 1 / -1; }

    .detail-item .detail-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted-service);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        transition: color 0.3s ease;
    }

    .detail-item .detail-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-service);
        word-break: break-all;
        transition: color 0.3s ease;
    }

    .detail-item .detail-value .status-badge { font-size: 11px; padding: 4px 12px; }

    .detail-item .detail-value .response-code {
        font-family: 'SF Mono', 'Courier New', monospace;
        font-weight: 700;
        padding: 2px 12px;
        border-radius: 4px;
        display: inline-block;
    }
    .detail-item .detail-value .response-code.success {
        background: #d1fae5;
        color: #065f46;
    }
    [data-theme="dark"] .detail-item .detail-value .response-code.success {
        background: #064e3b;
        color: #6ee7b7;
    }
    .detail-item .detail-value .response-code.error {
        background: #fee2e2;
        color: #991b1b;
    }
    [data-theme="dark"] .detail-item .detail-value .response-code.error {
        background: #7f1d1d;
        color: #fca5a5;
    }
    .detail-item .detail-value .response-code.warning {
        background: #fef3c7;
        color: #92400e;
    }
    [data-theme="dark"] .detail-item .detail-value .response-code.warning {
        background: #78350f;
        color: #fcd34d;
    }

    .detail-item .detail-value .response-time {
        font-family: 'SF Mono', 'Courier New', monospace;
        font-weight: 600;
    }
    .detail-item .detail-value .response-time.fast { color: #059669; }
    .detail-item .detail-value .response-time.slow { color: #dc2626; }
    .detail-item .detail-value .response-time.medium { color: #d97706; }

    .detail-timestamp {
        font-size: 13px;
        color: var(--text-secondary-service);
        font-family: 'SF Mono', 'Courier New', monospace;
        background: var(--bg-hover-service);
        padding: 4px 12px;
        border-radius: 6px;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .detail-message {
        background: var(--bg-hover-service);
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 14px;
        color: var(--text-service);
        border-left: 4px solid #4f46e5;
        word-break: break-word;
        max-height: 80px;
        overflow-y: auto;
        font-weight: 400;
        transition: all 0.3s ease;
    }

    .detail-message.empty-message {
        border-left-color: #f59e0b;
        background: var(--bg-info-box-service);
        color: var(--text-info-box-service);
    }
    .detail-message.empty-message::before {
        content: '📄 ';
        font-size: 16px;
    }

    [data-theme="dark"] .detail-message {
        background: var(--bg-detail-alt-service);
    }

    .detail-action {
        background: var(--bg-info-box-service);
        border: 1px solid var(--border-info-box-service);
        border-radius: 10px;
        padding: 14px 18px;
        grid-column: 1 / -1;
        transition: all 0.3s ease;
    }

    .detail-action .detail-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-info-box-service);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.3s ease;
    }

    .detail-action .detail-value {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-service);
        word-break: break-word;
        transition: color 0.3s ease;
    }

    .detail-action .action-badge {
        display: inline-block;
        background: #3b82f6;
        color: white;
        padding: 4px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
    }
    .detail-action .action-badge.no-action {
        background: var(--bg-hover-service);
        color: var(--text-muted-service);
    }
    .detail-action .action-badge.empty-action {
        background: #f59e0b;
        color: white;
    }

    .detail-message::-webkit-scrollbar { width: 4px; }
    .detail-message::-webkit-scrollbar-thumb {
        background: var(--text-muted-service);
        border-radius: 10px;
    }

    /* ================= DOWNLOAD MODAL ================= */
    .download-service-info {
        background: var(--bg-detail-service);
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
        border: 1px solid var(--border-service);
        transition: all 0.3s ease;
    }

    .download-service-info .service-name-display {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-service);
        transition: color 0.3s ease;
    }
    .download-service-info .service-meta {
        font-size: 13px;
        color: var(--text-secondary-service);
        margin-top: 4px;
        transition: color 0.3s ease;
    }
    .download-service-info .service-meta span { margin-right: 16px; }

    .download-period {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .period-btn {
        padding: 7px 16px;
        border: 2px solid var(--border-service);
        border-radius: 8px;
        background: var(--bg-card-service);
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary-service);
        transition: all 0.2s ease;
        font-family: inherit;
    }
    .period-btn:hover {
        border-color: #059669;
        color: #059669;
    }
    .period-btn.active {
        border-color: #059669;
        background: #ecfdf5;
        color: #065f46;
        font-weight: 600;
    }
    [data-theme="dark"] .period-btn.active {
        background: #064e3b;
        color: #6ee7b7;
    }

    .download-date-range {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .download-date-range .date-group { flex: 1; min-width: 130px; }
    .download-date-range .date-group label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary-service);
        display: block;
        margin-bottom: 4px;
        transition: color 0.3s ease;
    }
    .download-date-range .date-group input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border-service);
        border-radius: 8px;
        font-size: 13px;
        background: var(--bg-input-service);
        color: var(--text-service);
        transition: all 0.2s ease;
        outline: none;
        font-family: inherit;
    }
    .download-date-range .date-group input:focus {
        border-color: #059669;
        background: var(--bg-input-service);
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
    }
    .download-date-range .date-group input::-webkit-calendar-picker-indicator {
        filter: var(--date-picker-filter);
    }
    [data-theme="dark"] .download-date-range .date-group input {
        --date-picker-filter: invert(1);
    }

    /* ================= FORM DALAM MODAL ================= */
    .modal-body .form-group { margin-bottom: 18px; }
    .modal-body .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-service);
        margin-bottom: 6px;
        transition: color 0.3s ease;
    }
    .modal-body .form-group label .required { color: #dc2626; margin-left: 2px; }
    .modal-body .form-group .helper-text {
        font-size: 12px;
        color: var(--text-muted-service);
        margin-top: 4px;
        transition: color 0.3s ease;
    }

    .modal-body .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-service);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-service);
        transition: all 0.2s ease;
        background: var(--bg-input-service);
        outline: none;
        font-family: inherit;
    }
    .modal-body .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        background: var(--bg-input-service);
    }
    .modal-body .form-control.error { border-color: #dc2626; }
    .modal-body .form-control::placeholder {
        color: var(--text-muted-service);
    }

    .modal-body select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }
    [data-theme="dark"] .modal-body select.form-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    }

    .modal-body .error-message {
        color: #dc2626;
        font-size: 13px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* ================= BUTTONS MODAL ================= */
    .btn-submit-modal {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }
    .btn-submit-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
    }
    .btn-submit-modal:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    .btn-submit-modal.edit-mode {
        background: linear-gradient(135deg, #d97706, #b45309);
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
    }
    .btn-submit-modal.edit-mode:hover {
        box-shadow: 0 6px 20px rgba(217, 119, 6, 0.35);
    }

    .btn-download-modal {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }
    .btn-download-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
    }
    .btn-download-modal:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .btn-cancel-modal {
        background: var(--bg-hover-service);
        color: var(--text-secondary-service);
        padding: 10px 24px;
        border: 1px solid var(--border-service);
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: inherit;
    }
    .btn-cancel-modal:hover {
        background: var(--border-service);
        transform: translateY(-1px);
    }

    .btn-cancel-modal:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    /* ================= EMPTY STATE ================= */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted-service);
    }
    .empty-state .empty-icon {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.6;
    }
    .empty-state h3 {
        color: var(--text-service);
        font-size: 18px;
        margin: 0 0 8px;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    .empty-state p {
        margin: 0;
        font-size: 14px;
        color: var(--text-secondary-service);
        transition: color 0.3s ease;
    }

    .btn-empty-primary {
        background: #4f46e5;
        color: white;
        padding: 10px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        font-family: inherit;
        margin-top: 16px;
    }
    .btn-empty-primary:hover {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    /* ================= ANIMATIONS ================= */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.96);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-item {
        animation: fadeInUp 0.5s ease forwards;
    }
    .stat-item:nth-child(1) { animation-delay: 0.05s; }
    .stat-item:nth-child(2) { animation-delay: 0.10s; }
    .stat-item:nth-child(3) { animation-delay: 0.15s; }
    .stat-item:nth-child(4) { animation-delay: 0.20s; }

    /* ================= SEARCH HIGHLIGHT ================= */
    mark {
        background: #fbbf24;
        padding: 0 2px;
        border-radius: 2px;
        color: #0f172a;
    }

    [data-theme="dark"] mark {
        background: #f59e0b;
        color: #0f172a;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 1024px) {
        .stats-bar { grid-template-columns: repeat(2, 1fr); }
        .detail-grid { grid-template-columns: 1fr; }
        .search-wrapper { max-width: 100%; }
    }

    @media (max-width: 768px) {
        .service-container { padding: 16px; }
        .service-header {
            padding: 20px 24px;
            flex-direction: column;
            align-items: stretch;
            border-radius: 12px;
        }
        .service-header h1 { font-size: 20px; }
        .stats-bar { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-item .stat-number { font-size: 22px; }
        .stat-item { padding: 14px 16px; }
        .table-scroll { padding: 0 12px 12px; }
        .table-container thead th,
        .table-container tbody td { padding: 10px 10px; font-size: 12px; }
        .pagination-wrapper { flex-direction: column; align-items: stretch; padding: 12px 16px; }
        .pagination-links { justify-content: center; }
        .modal-content { width: 95%; }
        .modal-footer { flex-direction: column; }
        .btn-submit-modal, .btn-cancel-modal, .btn-download-modal { justify-content: center; }
        .toast-container { top: 16px; right: 16px; max-width: calc(100% - 32px); }
        .detail-grid { grid-template-columns: 1fr; }
        .action-buttons { flex-wrap: wrap; }
        .download-date-range { flex-direction: column; }
        .download-date-range .date-group { min-width: 100%; }
        .download-period { justify-content: center; }
        .service-target { max-width: 120px; font-size: 11px; }
        .auto-refresh-timer {
            bottom: 10px;
            right: 10px;
            padding: 6px 12px;
            font-size: 10px;
        }
        .auto-refresh-timer .countdown {
            font-size: 12px;
            min-width: 30px;
        }
        .uptime-value { font-size: 14px; }
        .table-header {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        .table-header-right {
            justify-content: space-between;
        }
        .perpage-selector {
            font-size: 12px;
        }
        .perpage-selector select {
            padding: 4px 8px;
            font-size: 12px;
        }
        .search-wrapper {
            flex-wrap: wrap;
        }
        .search-wrapper .btn-search,
        .search-wrapper .btn-reset {
            flex: 1;
            justify-content: center;
            padding: 8px 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .stats-bar { grid-template-columns: 1fr 1fr; gap: 8px; }
        .stat-item { padding: 12px 14px; border-radius: 10px; }
        .stat-item .stat-number { font-size: 18px; }
        .stat-item .stat-label { font-size: 10px; }
        .service-header h1 { font-size: 17px; }
        .btn-primary { font-size: 12px; padding: 8px 16px; }
        .modal-header h2 { font-size: 15px; }
        .modal-body { padding: 14px; }
        .status-badge { font-size: 9px; padding: 3px 10px; gap: 5px; }
        .status-badge .status-dot { width: 6px; height: 6px; }
        .btn-action { font-size: 10px; padding: 4px 8px; }
        .btn-check { font-size: 10px; padding: 4px 8px; }
        .detail-item .detail-value { font-size: 13px; }
        .uptime-value { font-size: 12px; }
        .perpage-selector {
            font-size: 11px;
        }
        .perpage-selector select {
            padding: 3px 6px;
            font-size: 11px;
        }
        .pagination-links .page-link {
            padding: 4px 8px;
            font-size: 11px;
            min-width: 30px;
        }
        .search-wrapper .btn-search,
        .search-wrapper .btn-reset {
            font-size: 11px;
            padding: 6px 10px;
        }
        .search-wrapper .search-input-wrap input {
            padding: 8px 10px 8px 32px;
            font-size: 12px;
        }
        .search-wrapper .search-input-wrap .search-icon {
            font-size: 13px;
            left: 10px;
        }
    }
</style>

<!-- ================= DATA SERVICE UNTUK INSTANT EDIT ================= -->
<script>
    // 🔥 SIMPAN DATA SEMUA SERVICE DALAM JAVASCRIPT (INSTANT ACCESS)
    const servicesMap = {};
    @foreach($services as $service)
        servicesMap[{{ $service->id }}] = {
            id: {{ $service->id }},
            name: '{{ addslashes($service->name) }}',
            target: '{{ addslashes($service->target) }}',
            type: '{{ $service->type ?? 'http' }}'
        };
    @endforeach
</script>

<div class="service-container">
    <!-- ================= TOAST CONTAINER ================= -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ================= CUSTOM CONFIRM MODAL ================= -->
    <div class="custom-modal-overlay" id="customConfirmModal">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <div class="modal-icon" id="confirmIcon">⚠️</div>
                <h3 id="confirmTitle">Konfirmasi</h3>
                <p id="confirmMessage">Apakah Anda yakin?</p>
            </div>
            <div class="custom-modal-body">
                <p id="confirmDetail" style="font-size: 14px; color: var(--text-secondary-service); text-align: center;"></p>
            </div>
            <div class="custom-modal-footer">
                <button class="btn-modal btn-cancel" onclick="closeConfirmModal()">✕ Batal</button>
                <button class="btn-modal btn-confirm" id="confirmBtn" onclick="executeConfirm()">✔ Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- ================= HEADER ================= -->
    <div class="service-header">
        <div class="header-left">
            <div class="header-icon">⚙️</div>
            <div class="header-text">
                <h1>Manajemen Service</h1>
                <div class="header-subtitle">Kelola dan pantau seluruh layanan Anda</div>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openCreateModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Tambah Service
            </button>
        </div>
    </div>

    <!-- ================= STATS ================= -->
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number purple">{{ $totalServices ?? 0 }}</span>
            <span class="stat-label">Total Service</span>
        </div>
        <div class="stat-item">
            <span class="stat-number green">{{ $totalUp ?? 0 }}</span>
            <span class="stat-label">Aktif (UP)</span>
        </div>
        <div class="stat-item">
            <span class="stat-number yellow">{{ $totalWarning ?? 0 }}</span>
            <span class="stat-label">Peringatan</span>
        </div>
        <div class="stat-item">
            <span class="stat-number red">{{ $totalDown ?? 0 }}</span>
            <span class="stat-label">Nonaktif (DOWN)</span>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="table-container">
        <div class="table-header">
            <div class="header-left">
                <h2>📋 Daftar Service</h2>
            </div>
            
            <!-- ================= SEARCH BOX ================= -->
            <div class="search-wrapper">
                <div class="search-input-wrap">
                    <span class="search-icon">🔍</span>
                    <input 
                        type="text" 
                        id="searchService" 
                        placeholder="Cari service berdasarkan nama atau target..." 
                        autocomplete="off"
                    >
                </div>
                <button onclick="searchServices()" class="btn-search" id="btnSearch">🔍 Cari</button>
                <button onclick="resetSearch()" class="btn-reset">↺ Reset</button>
            </div>

            <div class="table-header-right">
                <div class="perpage-selector">
                    <label for="perPage">Tampilkan:</label>
                    <select id="perPage" onchange="changePerPage(this.value)">
                        <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data</span>
                </div>
                <span class="table-info" id="tableInfo">
                    Menampilkan <strong>{{ $services->firstItem() ?? 0 }}</strong> - <strong>{{ $services->lastItem() ?? 0 }}</strong> dari <strong>{{ $services->total() }}</strong> service
                </span>
            </div>
        </div>

        <!-- ================= SEARCH STATUS BAR ================= -->
        <div class="search-status" id="searchStatus">
            <div class="status-spinner"></div>
            <span class="status-text" id="searchStatusText">🔍 Sedang mencari...</span>
            <button class="status-cancel" onclick="cancelSearch()" title="Batalkan pencarian">✕</button>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Service</th>
                        <th>Target</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 100px;">Uptime 30d</th>
                        <th style="width: 150px;">Terakhir Diperiksa</th>
                        <th style="width: 280px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($services as $index => $service)
                        @php
                            $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8'];
                            $colorClass = $colors[$index % count($colors)];
                            $initials = strtoupper(substr($service->name, 0, 2));
                            $statusLabel = $service->last_status ?? 'UNKNOWN';
                            $no = ($services->currentPage() - 1) * $services->perPage() + $loop->iteration;
                            $lastChecked = $service->last_check_at ?? $service->updated_at;
                            $uptime = $service->uptime ?? 0;
                            $uptimeColor = $uptime >= 70 ? 'green' : ($uptime >= 50 ? 'yellow' : 'red');
                        @endphp
                        <tr>
                            <td><span class="service-no">{{ $no }}</span></td>
                            <td>
                                <div class="service-info">
                                    <div class="service-avatar {{ $colorClass }}">{{ $initials }}</div>
                                    <div>
                                        <div class="service-name">{{ $service->name }}</div>
                                        <span class="service-type">{{ strtoupper($service->type ?? 'HTTP') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="service-target">{{ $service->target }}</span></td>
                            <td>
                                @if($statusLabel == 'UP')
                                    <span class="status-badge up"><span class="status-dot"></span> UP</span>
                                @elseif($statusLabel == 'DOWN')
                                    <span class="status-badge down"><span class="status-dot"></span> DOWN</span>
                                @elseif($statusLabel == 'WARNING')
                                    <span class="status-badge warning"><span class="status-dot"></span> WARNING</span>
                                @else
                                    <span class="status-badge unknown"><span class="status-dot"></span> UNKNOWN</span>
                                @endif
                            </td>
                            <td>
                                <div class="uptime-value {{ $uptimeColor }}">{{ number_format($uptime, 2) }}%</div>
                                <div class="uptime-bar">
                                    <div class="uptime-fill {{ $uptimeColor }}" style="width: {{ $uptime }}%;"></div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: var(--text-secondary-service);">
                                    {{ $lastChecked ? \Carbon\Carbon::parse($lastChecked)->diffForHumans() : '-' }}
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted-service); font-family: 'Courier New', monospace;">
                                    {{ $lastChecked ? \Carbon\Carbon::parse($lastChecked)->setTimezone('Asia/Jakarta')->format('H:i:s') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="openDetailModal({{ $service->id }})" class="btn-action btn-detail" title="Detail">👁️ Detail</button>
                                    <button onclick="openDownloadModal({{ $service->id }}, '{{ addslashes($service->name) }}')" class="btn-action btn-download" title="Download Laporan PDF">📥 PDF</button>
                                    <button onclick="openEditModal({{ $service->id }})" class="btn-action btn-edit" title="Edit">✏️ Edit</button>
                                    <button onclick="confirmDelete({{ $service->id }}, '{{ addslashes($service->name) }}')" class="btn-action btn-delete" title="Hapus">🗑️ Hapus</button>
                                    <button onclick="checkService({{ $service->id }})" class="btn-check" title="Check Now" id="checkBtn{{ $service->id }}">🔄</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="empty-icon">📭</span>
                                    <h3>Belum Ada Service</h3>
                                    <p>Mulai dengan menambahkan service pertama Anda</p>
                                    <button onclick="openCreateModal()" class="btn-empty-primary">+ Tambah Service</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($services, 'hasPages') && $services->hasPages())
        <div class="pagination-wrapper" id="paginationWrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $services->firstItem() ?? 0 }}</strong> - <strong>{{ $services->lastItem() ?? 0 }}</strong> dari <strong>{{ $services->total() }}</strong> data
            </div>
            <div class="pagination-links">
                @if($services->onFirstPage())
                    <span class="page-link disabled">‹</span>
                @else
                    <a href="{{ $services->previousPageUrl() }}" class="page-link">‹</a>
                @endif

                @php
                    $currentPage = $services->currentPage();
                    $lastPage = $services->lastPage();
                    $windowSize = 5;
                    $start = max(1, $currentPage - $windowSize);
                    $end = min($lastPage, $currentPage + $windowSize);
                    if ($end - $start + 1 < 10) {
                        if ($start == 1) {
                            $end = min($lastPage, 10);
                        } else {
                            $start = max(1, $lastPage - 9);
                            $end = $lastPage;
                        }
                    }
                @endphp

                @if($start > 1)
                    <a href="{{ $services->url(1) }}" class="page-link">1</a>
                    @if($start > 2)
                        <span class="page-dots">…</span>
                    @endif
                @endif

                @foreach(range($start, $end) as $page)
                    @if($page == $services->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $services->url($page) }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                        <span class="page-dots">…</span>
                    @endif
                    <a href="{{ $services->url($lastPage) }}" class="page-link">{{ $lastPage }}</a>
                @endif

                @if($services->hasMorePages())
                    <a href="{{ $services->nextPageUrl() }}" class="page-link">›</a>
                @else
                    <span class="page-link disabled">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ================= AUTO REFRESH TIMER ================= -->
<div class="auto-refresh-timer" id="autoRefreshTimer">
    <span class="icon">🔄</span>
    <span class="label">Refresh</span>
    <span class="countdown" id="countdownTimer">5:00</span>
</div>

<!-- ================= MODAL DETAIL ================= -->
<div class="modal-overlay" id="detailModal" onclick="if(event.target === this) closeDetailModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">📊</span>
                <span id="detailModalTitle">Detail Service</span>
            </h2>
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body" id="detailModalBody">
            <div style="text-align: center; padding: 40px 0; color: var(--text-secondary-service);">
                <span style="font-size: 32px; display: block; margin-bottom: 8px;">⏳</span>
                <p>Memuat data...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeDetailModal()">✕ Tutup</button>
            <button class="btn-submit-modal" id="refreshDetailBtn" onclick="refreshDetail()" style="background: linear-gradient(135deg, #059669, #10b981);">🔄 Refresh</button>
        </div>
    </div>
</div>

<!-- ================= MODAL DOWNLOAD ================= -->
<div class="modal-overlay" id="downloadModal" onclick="if(event.target === this) closeDownloadModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" style="background: linear-gradient(135deg, #059669, #10b981);">📥</span>
                <span id="downloadModalTitle">Download Laporan PDF</span>
            </h2>
            <button class="modal-close" onclick="closeDownloadModal()">&times;</button>
        </div>
        <div class="modal-body" id="downloadModalBody">
            <div class="download-service-info">
                <div class="service-name-display" id="downloadServiceName">Memuat...</div>
                <div class="service-meta">
                    <span id="downloadServiceTarget">-</span>
                    <span id="downloadServiceType">-</span>
                </div>
            </div>

            <div class="form-group">
                <label>Periode Laporan</label>
                <div class="download-period" id="periodSelector">
                    <button class="period-btn active" data-period="7" onclick="selectPeriod(this, 7)">7 Hari</button>
                    <button class="period-btn" data-period="14" onclick="selectPeriod(this, 14)">14 Hari</button>
                    <button class="period-btn" data-period="30" onclick="selectPeriod(this, 30)">30 Hari</button>
                    <button class="period-btn" data-period="60" onclick="selectPeriod(this, 60)">60 Hari</button>
                    <button class="period-btn" data-period="90" onclick="selectPeriod(this, 90)">90 Hari</button>
                </div>
                <div class="helper-text">Pilih rentang waktu laporan</div>
            </div>

            <div class="form-group">
                <label>Tanggal Kustom</label>
                <div class="download-date-range">
                    <div class="date-group">
                        <label>Dari</label>
                        <input type="date" id="dateFrom">
                    </div>
                    <div class="date-group">
                        <label>Sampai</label>
                        <input type="date" id="dateTo">
                    </div>
                </div>
                <div class="helper-text">Atau pilih tanggal secara manual</div>
            </div>

            <div id="downloadLoading" style="display: none; text-align: center; padding: 20px;">
                <span style="font-size: 24px; display: block; margin-bottom: 8px;">⏳</span>
                <p style="color: var(--text-secondary-service);">Sedang memproses laporan PDF...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeDownloadModal()">✕ Batal</button>
            <button class="btn-download-modal" id="btnDownloadNow" onclick="downloadReport()">📥 Download PDF</button>
        </div>
    </div>
</div>

<!-- ================= MODAL CREATE / EDIT ================= -->
<div class="modal-overlay" id="serviceModal" onclick="if(event.target === this) closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" id="modalIcon">➕</span>
                <span id="modalTitle">Tambah Service</span>
            </h2>
            <button class="modal-close" id="modalCloseBtn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="serviceForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="service_id" id="serviceId" value="">

                <div class="form-group">
                    <label for="modal_name">
                        Nama Service
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="modal_name"
                        class="form-control"
                        placeholder="Contoh: Website Utama, API Gateway"
                        required
                    >
                    <div class="helper-text">Nama yang mudah diingat untuk service ini</div>
                </div>

                <div class="form-group">
                    <label for="modal_target">
                        Target URL / IP
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="target" 
                        id="modal_target"
                        class="form-control"
                        placeholder="Contoh: https://example.com atau 192.168.1.1"
                        required
                    >
                    <div class="helper-text">URL lengkap dengan protocol (http:// atau https://) atau alamat IP</div>
                </div>

                <div class="form-group">
                    <label for="modal_type">
                        Tipe Monitoring
                        <span class="required">*</span>
                    </label>
                    <select name="type" id="modal_type" class="form-control" required>
                        <option value="http">HTTP / HTTPS</option>
                        <option value="ping">PING</option>
                    </select>
                    <div class="helper-text">Jenis monitoring yang akan digunakan</div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" id="btnCancelModal" onclick="closeModal()">✕ Batal</button>
            <button class="btn-submit-modal" id="btnSubmitModal" onclick="submitForm()">💾 Simpan Service</button>
        </div>
    </div>
</div>

<script>
    // ================= CONFIRM MODAL =================
    let confirmCallback = null;
    let confirmData = null;

    function confirmDelete(id, name) {
        confirmData = { id: id, name: name };
        document.getElementById('confirmIcon').className = 'modal-icon danger';
        document.getElementById('confirmIcon').textContent = '🗑️';
        document.getElementById('confirmTitle').textContent = 'Hapus Service';
        document.getElementById('confirmMessage').textContent = 'Apakah Anda yakin ingin menghapus service ini?';
        document.getElementById('confirmDetail').innerHTML = 'Service <span class="highlight-name">' + name + '</span> akan dihapus secara permanen.';
        document.getElementById('confirmBtn').textContent = '🗑️ Ya, Hapus';
        document.getElementById('confirmBtn').className = 'btn-modal btn-confirm';
        document.getElementById('customConfirmModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        
        confirmCallback = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/services/' + id;
            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        };
    }

    function closeConfirmModal() {
        document.getElementById('customConfirmModal').classList.remove('active');
        document.body.style.overflow = '';
        confirmCallback = null;
        confirmData = null;
    }

    function executeConfirm() {
        if (confirmCallback) {
            confirmCallback();
        }
        closeConfirmModal();
    }

    // ================= AUTO REFRESH =================
    (function() {
        'use strict';
        
        const REFRESH_INTERVAL = 300;
        let seconds = REFRESH_INTERVAL;
        let countdownElement = document.getElementById('countdownTimer');
        let refreshTimer = null;
        
        function updateCountdown() {
            seconds--;
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            
            if (countdownElement) {
                countdownElement.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
                countdownElement.className = 'countdown';
                if (seconds < 10) {
                    countdownElement.classList.add('danger');
                } else if (seconds < 30) {
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
        
        document.addEventListener('modalOpened', function() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
            }
        });
        
        document.addEventListener('modalClosed', function() {
            startCountdown();
        });
    })();

    // ================= SEARCH STATUS FUNCTIONS =================
    function showSearchStatus(text, showSpinner = true) {
        const status = document.getElementById('searchStatus');
        const textEl = document.getElementById('searchStatusText');
        const spinner = status.querySelector('.status-spinner');
        
        textEl.textContent = text;
        spinner.style.display = showSpinner ? 'block' : 'none';
        status.classList.add('active');
    }

    function hideSearchStatus() {
        const status = document.getElementById('searchStatus');
        status.classList.remove('active');
    }

    function cancelSearch() {
        clearTimeout(searchTimeout);
        hideSearchStatus();
        document.getElementById('searchService').value = '';
        resetSearch();
        showToast('info', 'Info', 'Pencarian dibatalkan');
    }

    // ================= VARIABEL GLOBAL =================
    let currentDetailId = null;
    let currentDownloadId = null;
    let selectedPeriod = 7;
    let searchTimeout = null;
    let isSearching = false;

    // ================= SEARCH SERVICES (AJAX) =================
    function searchServices() {
        const query = document.getElementById('searchService').value.trim();
        
        if (query.length === 0) {
            hideSearchStatus();
            showToast('warning', 'Peringatan!', 'Masukkan kata kunci pencarian');
            return;
        }
        
        if (isSearching) {
            showToast('info', 'Info', 'Pencarian sedang berlangsung...');
            return;
        }
        
        isSearching = true;
        const btnSearch = document.getElementById('btnSearch');
        
        // Tampilkan status mencari
        showSearchStatus('🔍 Sedang mencari "' + query + '"...');
        btnSearch.disabled = true;
        btnSearch.textContent = '⏳';
        
        fetch(`/services/search?q=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            isSearching = false;
            btnSearch.disabled = false;
            btnSearch.textContent = '🔍 Cari';
            
            if (data.success) {
                renderSearchResult(data.data, data.pagination, query);
                hideSearchStatus();
                showToast('success', 'Berhasil!', `Ditemukan ${data.data.length} data untuk "${query}"`);
            } else {
                hideSearchStatus();
                showToast('error', 'Gagal!', data.message || 'Gagal mencari data');
                window.location.reload();
            }
        })
        .catch(error => {
            isSearching = false;
            btnSearch.disabled = false;
            btnSearch.textContent = '🔍 Cari';
            hideSearchStatus();
            showToast('error', 'Error!', 'Terjadi kesalahan: ' + error.message);
            window.location.reload();
        });
    }

    function resetSearch() {
        document.getElementById('searchService').value = '';
        hideSearchStatus();
        window.location.reload();
    }

    function renderSearchResult(services, pagination, query) {
        const tbody = document.getElementById('tableBody');
        const info = document.getElementById('tableInfo');
        const paginationWrapper = document.getElementById('paginationWrapper');
        
        if (!tbody) return;
        
        if (services.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <span class="empty-icon">🔍</span>
                            <h3>Service Tidak Ditemukan</h3>
                            <p>Tidak ada service yang sesuai dengan "<strong>${query}</strong>"</p>
                            <button onclick="resetSearch()" class="btn-empty-primary">↺ Reset Pencarian</button>
                        </div>
                    </td>
                </tr>
            `;
            if (info) info.innerHTML = 'Menampilkan <strong>0</strong> - <strong>0</strong> dari <strong>0</strong> service';
            if (paginationWrapper) paginationWrapper.style.display = 'none';
            return;
        }
        
        let html = '';
        const colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8'];
        
        services.forEach((service, index) => {
            const colorClass = colors[index % colors.length];
            const initials = service.name.substring(0, 2).toUpperCase();
            const statusLabel = service.last_status || 'UNKNOWN';
            const uptime = service.uptime || 0;
            const uptimeColor = uptime >= 70 ? 'green' : (uptime >= 50 ? 'yellow' : 'red');
            const no = index + 1;
            
            // Highlight matching text
            let displayName = service.name;
            let displayTarget = service.target;
            
            if (query) {
                const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                displayName = service.name.replace(regex, '<mark>$1</mark>');
                displayTarget = service.target.replace(regex, '<mark>$1</mark>');
            }
            
            html += `
                <tr>
                    <td><span class="service-no">${no}</span></td>
                    <td>
                        <div class="service-info">
                            <div class="service-avatar ${colorClass}">${initials}</div>
                            <div>
                                <div class="service-name">${displayName}</div>
                                <span class="service-type">${(service.type || 'HTTP').toUpperCase()}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="service-target">${displayTarget}</span></td>
                    <td>
                        ${statusLabel == 'UP' ? '<span class="status-badge up"><span class="status-dot"></span> UP</span>' : 
                          statusLabel == 'DOWN' ? '<span class="status-badge down"><span class="status-dot"></span> DOWN</span>' :
                          statusLabel == 'WARNING' ? '<span class="status-badge warning"><span class="status-dot"></span> WARNING</span>' :
                          '<span class="status-badge unknown"><span class="status-dot"></span> UNKNOWN</span>'}
                    </td>
                    <td>
                        <div class="uptime-value ${uptimeColor}">${Number(uptime).toFixed(2)}%</div>
                        <div class="uptime-bar">
                            <div class="uptime-fill ${uptimeColor}" style="width: ${uptime}%;"></div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 12px; color: var(--text-secondary-service);">
                            ${service.last_check_at ? new Date(service.last_check_at).toLocaleDateString('id-ID') + ' ' + new Date(service.last_check_at).toLocaleTimeString('id-ID') : '-'}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted-service); font-family: 'Courier New', monospace;">
                            ${service.last_check_at ? new Date(service.last_check_at).toLocaleTimeString('id-ID') : '-'}
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="openDetailModal(${service.id})" class="btn-action btn-detail" title="Detail">👁️ Detail</button>
                            <button onclick="openDownloadModal(${service.id}, '${service.name.replace(/'/g, "\\'")}')" class="btn-action btn-download" title="Download Laporan PDF">📥 PDF</button>
                            <button onclick="openEditModal(${service.id})" class="btn-action btn-edit" title="Edit">✏️ Edit</button>
                            <button onclick="confirmDelete(${service.id}, '${service.name.replace(/'/g, "\\'")}')" class="btn-action btn-delete" title="Hapus">🗑️ Hapus</button>
                            <button onclick="checkService(${service.id})" class="btn-check" title="Check Now" id="checkBtn${service.id}">🔄</button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        
        if (info && pagination) {
            info.innerHTML = `Menampilkan <strong>${pagination.from || 0}</strong> - <strong>${pagination.to || 0}</strong> dari <strong>${pagination.total || 0}</strong> service`;
        }
        
        if (paginationWrapper) {
            paginationWrapper.style.display = 'flex';
            paginationWrapper.innerHTML = `
                <div class="pagination-info">
                    Menampilkan <strong>${pagination.from || 0}</strong> - <strong>${pagination.to || 0}</strong> dari <strong>${pagination.total || 0}</strong> data
                </div>
                <div class="pagination-links">
                    ${pagination.prev_page_url ? `<a href="#" onclick="loadPage('${pagination.prev_page_url}')" class="page-link">‹</a>` : `<span class="page-link disabled">‹</span>`}
                    <span class="page-link active">${pagination.current_page || 1}</span>
                    ${pagination.next_page_url ? `<a href="#" onclick="loadPage('${pagination.next_page_url}')" class="page-link">›</a>` : `<span class="page-link disabled">›</span>`}
                </div>
            `;
        }
    }

    function loadPage(url) {
        showToast('info', 'Info', 'Fitur pagination pada hasil pencarian akan segera hadir');
    }

    // ================= EVENT LISTENER =================
    document.addEventListener('DOMContentLoaded', function() {
        // Flash message dari server
        @if(session('success'))
            showToast('success', 'Berhasil!', '{{ session('success') }}');
        @endif
        @if(session('error'))
            showToast('error', 'Gagal!', '{{ session('error') }}');
        @endif
        @if(session('warning'))
            showToast('warning', 'Peringatan!', '{{ session('warning') }}');
        @endif
        @if(session('info'))
            showToast('info', 'Info', '{{ session('info') }}');
        @endif

        // Set default date for download
        const today = new Date();
        const weekAgo = new Date();
        weekAgo.setDate(weekAgo.getDate() - 7);
        document.getElementById('dateFrom').value = weekAgo.toISOString().split('T')[0];
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];

        const typeSelect = document.getElementById('modal_type');
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                updateHelperText(this.value);
            });
            updateHelperText(typeSelect.value);
        }

        // Search with debounce
        const searchInput = document.getElementById('searchService');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchServices();
                }
            });
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length === 0) {
                    hideSearchStatus();
                    resetSearch();
                    return;
                }
                
                if (query.length >= 2) {
                    showSearchStatus('✍️ Mengetik...', false);
                    searchTimeout = setTimeout(function() {
                        searchServices();
                    }, 800);
                } else {
                    hideSearchStatus();
                }
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchService').focus();
                document.getElementById('searchService').select();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('searchService').focus();
                document.getElementById('searchService').select();
            }
        });
    });

    // ================= TOAST =================
    function showToast(type, title, message) {
        const container = document.getElementById('toastContainer');
        const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

        // Cek apakah toast dengan pesan yang sama sudah ada
        const existingToasts = container.querySelectorAll('.toast');
        for (let toast of existingToasts) {
            const msgEl = toast.querySelector('.toast-message');
            if (msgEl && msgEl.textContent === message) {
                return; // Jangan duplikat
            }
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()">&times;</button>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 400);
            }
        }, 5000);
    }

    // ================= HELPER TEXT =================
    function updateHelperText(type) {
        const targetInput = document.getElementById('modal_target');
        const helperText = targetInput?.parentElement?.querySelector('.helper-text');
        
        if (!targetInput || !helperText) return;
        
        if (type === 'ping') {
            targetInput.placeholder = 'Contoh: 192.168.1.1 atau 8.8.8.8';
            helperText.textContent = 'Masukkan alamat IP (contoh: 192.168.1.1)';
        } else {
            targetInput.placeholder = 'Contoh: https://example.com atau http://localhost';
            helperText.textContent = 'URL lengkap dengan protocol (http:// atau https://)';
        }
    }

    // ================= CHECK SERVICE =================
    function checkService(id) {
        const btn = document.getElementById('checkBtn' + id);
        
        if (!navigator.onLine) {
            showToast('error', 'Jaringan Terputus!', 'Tidak ada koneksi internet. Periksa router/modem Anda.');
            return;
        }
        
        if (btn) {
            btn.disabled = true;
            btn.textContent = '⏳';
        }
        
        // Alert proses
        showToast('info', 'Memproses...', '🔄 Sedang mengecek service...');
        
        fetch(`/services/${id}/check`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', '✅ Berhasil!', data.message || 'Service berhasil di-check');
                // Reload setelah 1 detik
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', '❌ Gagal!', data.message || 'Gagal check service');
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '🔄';
                }
            }
        })
        .catch(error => {
            showToast('error', '❌ Error!', 'Terjadi kesalahan: ' + error.message);
            if (btn) {
                btn.disabled = false;
                btn.textContent = '🔄';
            }
        });
    }

    // ================= PERPAGE =================
    function changePerPage(value) {
        let url = new URL(window.location.href);
        url.searchParams.set('perPage', value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // ================= DETAIL MODAL =================
    function openDetailModal(id) {
        currentDetailId = id;
        const modal = document.getElementById('detailModal');
        const body = document.getElementById('detailModalBody');
        body.innerHTML = `<div style="text-align: center; padding: 40px 0; color: var(--text-secondary-service);"><span style="font-size: 32px; display: block; margin-bottom: 8px;">⏳</span><p>Memuat data...</p></div>`;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
        fetchDetailData(id);
    }

    function fetchDetailData(id) {
        fetch(`/services/${id}/detail`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderDetail(data.data);
            } else {
                document.getElementById('detailModalBody').innerHTML = `
                    <div style="text-align: center; padding: 40px 0; color: #dc2626;">
                        <span style="font-size: 32px; display: block; margin-bottom: 8px;">❌</span>
                        <p>Gagal memuat data service</p>
                        <p style="font-size: 13px; color: var(--text-secondary-service);">${data.message || 'Terjadi kesalahan'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('detailModalBody').innerHTML = `
                <div style="text-align: center; padding: 40px 0; color: #dc2626;">
                    <span style="font-size: 32px; display: block; margin-bottom: 8px;">❌</span>
                    <p>Gagal memuat data</p>
                    <p style="font-size: 13px; color: var(--text-secondary-service);">${error.message}</p>
                </div>
            `;
        });
    }

    function renderDetail(service) {
        const body = document.getElementById('detailModalBody');
        document.getElementById('detailModalTitle').textContent = `📊 Detail Service: ${service.name}`;

        const statusClass = service.last_status?.toLowerCase() || 'unknown';
        const statusBadge = service.last_status || 'UNKNOWN';
        const responseCode = service.last_response_code ?? '-';
        const responseTime = service.last_response_time ?? 0;
        const timeClass = responseTime < 1 ? 'fast' : (responseTime < 3 ? 'medium' : 'slow');
        const codeClass = responseCode < 400 ? 'success' : (responseCode < 500 ? 'warning' : 'error');
        const action = service.last_action || '-';
        const isNoAction = action === '-';
        const isEmptyAction = action.includes('kosong') || action.includes('EMPTY');
        const message = service.last_message || '-';
        const isEmptyPage = message.includes('konten kosong') || message.includes('EMPTY_RESPONSE');

        const messageClass = isEmptyPage ? 'empty-message' : '';
        const actionClass = isEmptyAction ? 'empty-action' : (isNoAction ? 'no-action' : '');
        const actionBadgeText = isEmptyAction ? '📄 ' + action : action;

        body.innerHTML = `
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-label">Nama Service</div><div class="detail-value">${service.name}</div></div>
                <div class="detail-item"><div class="detail-label">Tipe</div><div class="detail-value">${service.type?.toUpperCase() || '-'}</div></div>
                <div class="detail-item full-width"><div class="detail-label">Target URL / IP</div><div class="detail-value" style="font-family: 'SF Mono', 'Courier New', monospace; font-size: 14px; word-break: break-all;">${service.target}</div></div>
                <div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge ${statusClass}"><span class="status-dot"></span> ${statusBadge}</span></div></div>
                <div class="detail-item"><div class="detail-label">Response Code</div><div class="detail-value"><span class="response-code ${codeClass}">${responseCode}</span></div></div>
                <div class="detail-item"><div class="detail-label">Response Time</div><div class="detail-value"><span class="response-time ${timeClass}">${Number(responseTime).toFixed(2)} <span style="font-size: 12px; color: var(--text-muted-service);">s</span></span></div></div>
                <div class="detail-item full-width"><div class="detail-label">Pesan</div><div class="detail-message ${messageClass}">${message}</div></div>
                <div class="detail-action"><div class="detail-label">🔧 Tindakan yang Disarankan</div><div class="detail-value"><span class="action-badge ${actionClass}">${actionBadgeText}</span></div></div>
                <div class="detail-item full-width" style="background: var(--bg-detail-alt-service); border-color: var(--border-service);"><div class="detail-label">Informasi Tambahan</div><div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 4px; font-size: 13px; color: var(--text-secondary-service);"><span><strong>ID:</strong> ${service.id}</span><span><strong>Dibuat:</strong> ${service.created_at || '-'}</span><span><strong>Diupdate:</strong> ${service.updated_at || '-'}</span></div></div>
            </div>
        `;
    }

    function refreshDetail() {
        if (currentDetailId) {
            const btn = document.getElementById('refreshDetailBtn');
            btn.innerHTML = `<span class="spin">🔄</span> Memuat...`;
            btn.disabled = true;
            
            fetchDetailData(currentDetailId);
            
            setTimeout(() => {
                btn.innerHTML = '🔄 Refresh';
                btn.disabled = false;
            }, 1000);
        }
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.remove('active');
        document.body.style.overflow = '';
        currentDetailId = null;
        document.dispatchEvent(new Event('modalClosed'));
    }

    // ================= DOWNLOAD MODAL =================
    function openDownloadModal(id, name) {
        currentDownloadId = id;
        const modal = document.getElementById('downloadModal');
        document.getElementById('downloadModalTitle').textContent = '📥 Download Laporan PDF';
        document.getElementById('downloadServiceName').textContent = name;
        
        fetch(`/services/${id}/detail`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('downloadServiceTarget').textContent = `🎯 ${data.data.target}`;
                document.getElementById('downloadServiceType').textContent = `📌 ${data.data.type?.toUpperCase() || 'HTTP'}`;
            }
        })
        .catch(() => {
            document.getElementById('downloadServiceTarget').textContent = '🎯 -';
            document.getElementById('downloadServiceType').textContent = '📌 -';
        });
        
        document.querySelectorAll('.period-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelector('.period-btn[data-period="7"]').classList.add('active');
        selectedPeriod = 7;
        
        const today = new Date();
        const weekAgo = new Date();
        weekAgo.setDate(weekAgo.getDate() - 7);
        document.getElementById('dateFrom').value = weekAgo.toISOString().split('T')[0];
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        
        document.getElementById('downloadLoading').style.display = 'none';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
    }

    function selectPeriod(element, days) {
        document.querySelectorAll('.period-btn').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
        selectedPeriod = days;
        
        const today = new Date();
        const pastDate = new Date();
        pastDate.setDate(pastDate.getDate() - days);
        document.getElementById('dateFrom').value = pastDate.toISOString().split('T')[0];
        document.getElementById('dateTo').value = today.toISOString().split('T')[0];
    }

    function downloadReport() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        if (!dateFrom || !dateTo) {
            showToast('warning', 'Peringatan!', 'Silakan pilih periode laporan terlebih dahulu');
            return;
        }
        
        if (new Date(dateFrom) > new Date(dateTo)) {
            showToast('warning', 'Peringatan!', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir');
            return;
        }
        
        const btn = document.getElementById('btnDownloadNow');
        const loading = document.getElementById('downloadLoading');
        
        btn.disabled = true;
        btn.textContent = '⏳ Memproses...';
        loading.style.display = 'block';
        
        const url = `/services/${currentDownloadId}/download-report?` + new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
            format: 'pdf'
        });
        
        window.open(url, '_blank');
        
        setTimeout(() => {
            btn.disabled = false;
            btn.textContent = '📥 Download PDF';
            loading.style.display = 'none';
            showToast('success', 'Berhasil!', 'Laporan PDF berhasil diunduh');
        }, 3000);
    }

    function closeDownloadModal() {
        document.getElementById('downloadModal').classList.remove('active');
        document.body.style.overflow = '';
        currentDownloadId = null;
        document.dispatchEvent(new Event('modalClosed'));
    }

    // ================= CREATE / EDIT MODAL =================
    function openCreateModal() {
        const modal = document.getElementById('serviceModal');
        const form = document.getElementById('serviceForm');
        const closeBtn = document.getElementById('modalCloseBtn');
        const cancelBtn = document.getElementById('btnCancelModal');
        const submitBtn = document.getElementById('btnSubmitModal');
        
        closeBtn.disabled = false;
        cancelBtn.disabled = false;
        submitBtn.disabled = false;
        submitBtn.textContent = '💾 Simpan Service';
        submitBtn.className = 'btn-submit-modal';
        
        form.reset();
        document.getElementById('serviceId').value = '';
        document.getElementById('formMethod').value = 'POST';
        form.action = '{{ route('services.store') }}';
        
        document.getElementById('modal_type').value = 'http';
        updateHelperText('http');
        
        document.getElementById('modalTitle').textContent = 'Tambah Service';
        document.getElementById('modalIcon').textContent = '➕';
        document.getElementById('modalIcon').style.background = 'linear-gradient(135deg, #4f46e5, #7c3aed)';
        
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
        setTimeout(() => document.getElementById('modal_name').focus(), 100);
    }

    function openEditModal(id) {
        const modal = document.getElementById('serviceModal');
        const submitBtn = document.getElementById('btnSubmitModal');
        const cancelBtn = document.getElementById('btnCancelModal');
        const closeBtn = document.getElementById('modalCloseBtn');
        
        const service = servicesMap[id];
        
        if (!service) {
            showToast('error', 'Gagal!', 'Data service tidak ditemukan');
            return;
        }
        
        document.getElementById('modalTitle').textContent = 'Edit Service';
        document.getElementById('modalIcon').textContent = '✏️';
        document.getElementById('modalIcon').style.background = 'linear-gradient(135deg, #d97706, #b45309)';
        
        submitBtn.disabled = false;
        submitBtn.textContent = '💾 Update Service';
        submitBtn.className = 'btn-submit-modal edit-mode';
        cancelBtn.disabled = false;
        closeBtn.disabled = false;
        
        document.getElementById('modal_name').value = service.name;
        document.getElementById('modal_target').value = service.target;
        document.getElementById('modal_type').value = service.type || 'http';
        document.getElementById('serviceId').value = service.id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('serviceForm').action = `/services/${service.id}`;
        
        updateHelperText(service.type || 'http');
        
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
        
        setTimeout(() => document.getElementById('modal_name').focus(), 100);
    }

    function closeModal() {
        document.getElementById('modalCloseBtn').disabled = false;
        document.getElementById('btnCancelModal').disabled = false;
        document.getElementById('btnSubmitModal').disabled = false;
        
        document.getElementById('serviceModal').classList.remove('active');
        document.body.style.overflow = '';
        document.dispatchEvent(new Event('modalClosed'));
    }

    function submitForm() {
        const form = document.getElementById('serviceForm');
        const submitBtn = document.getElementById('btnSubmitModal');
        const cancelBtn = document.getElementById('btnCancelModal');
        const closeBtn = document.getElementById('modalCloseBtn');
        const name = document.getElementById('modal_name');
        const target = document.getElementById('modal_target');
        const type = document.getElementById('modal_type');
        const serviceId = document.getElementById('serviceId').value;
        const isEdit = serviceId !== '';
        
        let hasError = false;
        
        if (name.value.trim() === '') {
            showFieldError(name, 'Nama service wajib diisi');
            hasError = true;
        } else if (name.value.length < 3) {
            showFieldError(name, 'Nama minimal 3 karakter');
            hasError = true;
        } else {
            removeFieldError(name);
        }
        
        if (target.value.trim() === '') {
            showFieldError(target, 'Target wajib diisi');
            hasError = true;
        } else if (type.value !== 'ping') {
            if (!/^https?:\/\/.+/i.test(target.value.trim())) {
                showFieldError(target, 'URL harus diawali dengan http:// atau https://');
                hasError = true;
            } else {
                removeFieldError(target);
            }
        } else {
            removeFieldError(target);
        }
        
        if (hasError) {
            const firstError = document.querySelector('.form-control.error');
            if (firstError) {
                firstError.focus();
            }
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Menyimpan...';
        cancelBtn.disabled = true;
        closeBtn.disabled = true;
        
        form.submit();
    }

    function showFieldError(input, message) {
        input.classList.add('error');
        let errorDiv = input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.marginTop = '4px';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.innerHTML = '⚠️ ' + message;
    }

    function removeFieldError(input) {
        input.classList.remove('error');
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();
    }

    // ================= KEYBOARD SHORTCUTS =================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeDetailModal();
            closeDownloadModal();
            closeConfirmModal();
            hideSearchStatus();
        }
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            const modal = document.getElementById('serviceModal');
            if (modal.classList.contains('active')) {
                e.preventDefault();
                submitForm();
            }
        }
    });

    // ================= REAL-TIME VALIDATION =================
    document.getElementById('modal_name').addEventListener('input', function() {
        if (this.value.trim() !== '' && this.value.length >= 3) {
            this.classList.remove('error');
            removeFieldError(this);
        }
    });

    document.getElementById('modal_target').addEventListener('input', function() {
        if (this.value.trim() !== '') {
            this.classList.remove('error');
            removeFieldError(this);
        }
    });
</script>
@endsection