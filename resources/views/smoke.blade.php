@extends('layouts.app')

@section('content')
<style>
    /* ========== ROOT VARIABLES ========== */
    :root {
        --primary: #0d3b66;
        --primary-light: #1a4d7a;
        --primary-lighter: #2563eb;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --bg-main: #f0f2f5;
        --bg-card: #ffffff;
        --bg-table-header: #fafbfc;
        --bg-hover-row: #f8fafc;
        --bg-status-bar: #f8fafc;
        --bg-download-btn: rgba(255,255,255,0.15);
        --bg-timer: rgba(10, 46, 92, 0.9);
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --text-light: #64748b;
        --border-color: #e8ecf1;
        --border-table: #f1f5f9;
        --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.08);
        --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.12);
        --shadow-timer: 0 8px 32px rgba(0, 0, 0, 0.3);
        --radius: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        
        /* Status badge colors - light mode */
        --badge-danger-bg: #fee2e2;
        --badge-danger-text: #991b1b;
        --badge-warning-bg: #fef3c7;
        --badge-warning-text: #92400e;
        --badge-normal-bg: #d1fae5;
        --badge-normal-text: #065f46;
        
        /* Icon colors */
        --icon-normal-bg: linear-gradient(135deg, #d1fae5, #a7f3d0);
        --icon-normal-color: #059669;
        --icon-warning-bg: linear-gradient(135deg, #fef3c7, #fde68a);
        --icon-warning-color: #d97706;
        --icon-danger-bg: linear-gradient(135deg, #fee2e2, #fca5a5);
        --icon-danger-color: #dc2626;
        
        /* Bar colors */
        --bar-track: #e5e7eb;
        --bar-fill-normal: linear-gradient(90deg, #34d399, #059669);
        --bar-fill-warning: linear-gradient(90deg, #fbbf24, #d97706);
        --bar-fill-danger: linear-gradient(90deg, #f87171, #dc2626);
        
        --date-picker-filter: none;
    }

    /* Dark mode override dari layout utama */
    [data-theme="dark"] {
        --bg-main: #0f172a;
        --bg-card: #1e293b;
        --bg-table-header: #1e293b;
        --bg-hover-row: #2d3a4f;
        --bg-status-bar: #1e293b;
        --bg-download-btn: rgba(255,255,255,0.08);
        --bg-timer: rgba(15, 23, 42, 0.95);
        --text-primary: #e2e8f0;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;
        --text-light: #94a3b8;
        --border-color: #334155;
        --border-table: #334155;
        --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.2);
        --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.3);
        --shadow-timer: 0 8px 32px rgba(0, 0, 0, 0.5);
        
        /* Status badge colors - dark mode */
        --badge-danger-bg: #7f1d1d;
        --badge-danger-text: #fca5a5;
        --badge-warning-bg: #78350f;
        --badge-warning-text: #fcd34d;
        --badge-normal-bg: #064e3b;
        --badge-normal-text: #6ee7b7;
        
        /* Icon colors - dark mode */
        --icon-normal-bg: linear-gradient(135deg, #064e3b, #065f46);
        --icon-normal-color: #6ee7b7;
        --icon-warning-bg: linear-gradient(135deg, #78350f, #92400e);
        --icon-warning-color: #fcd34d;
        --icon-danger-bg: linear-gradient(135deg, #7f1d1d, #991b1b);
        --icon-danger-color: #fca5a5;
        
        /* Bar colors - dark mode */
        --bar-track: #334155;
        --bar-fill-normal: linear-gradient(90deg, #065f46, #059669);
        --bar-fill-warning: linear-gradient(90deg, #92400e, #d97706);
        --bar-fill-danger: linear-gradient(90deg, #991b1b, #dc2626);
        
        --date-picker-filter: invert(1);
    }

    .smoke-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-main);
        min-height: 100vh;
        transition: background 0.3s ease, color 0.3s ease;
        color: var(--text-primary);
    }

    .smoke-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 50%, var(--primary-lighter) 100%);
        padding: 28px 36px;
        border-radius: var(--radius);
        margin-bottom: 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(13, 59, 102, 0.3);
        transition: box-shadow 0.3s ease;
    }

    .smoke-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .smoke-header .header-left {
        display: flex;
        align-items: center;
        gap: 18px;
        position: relative;
        z-index: 1;
    }

    .smoke-header .header-icon {
        width: 56px;
        height: 56px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: var(--transition);
    }

    .smoke-header .header-icon:hover {
        transform: scale(1.05) rotate(-5deg);
        background: rgba(255, 255, 255, 0.25);
    }

    .smoke-header h1 {
        font-size: 26px;
        font-weight: 700;
        color: white;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .smoke-header .header-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        font-weight: 400;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .smoke-header .header-subtitle span {
        background: rgba(255, 255, 255, 0.15);
        padding: 2px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .smoke-header .header-actions {
        display: flex;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .btn-download-csv {
        background: var(--bg-download-btn);
        color: white;
        padding: 10px 22px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: var(--transition);
        backdrop-filter: blur(10px);
        cursor: pointer;
    }

    .btn-download-csv:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        border-color: rgba(255, 255, 255, 0.4);
    }

    .status-esp {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.12);
        padding: 8px 20px;
        border-radius: 24px;
        color: white;
        font-size: 13px;
        font-weight: 500;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .status-esp .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        transition: var(--transition);
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }

    .status-esp .dot.online {
        background: var(--success);
        animation: pulse-online 2s infinite;
    }

    .status-esp .dot.offline {
        background: var(--danger);
        animation: pulse-offline 1s infinite;
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);
    }

    @keyframes pulse-online {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.2); }
    }

    @keyframes pulse-offline {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.3; transform: scale(0.8); }
    }

    .auto-refresh-timer {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--bg-timer);
        color: white;
        padding: 10px 18px;
        border-radius: 12px;
        z-index: 99999;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: var(--shadow-timer);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        user-select: none;
        cursor: default;
        transition: var(--transition);
    }

    .auto-refresh-timer:hover {
        transform: scale(1.05);
        background: var(--bg-timer);
    }

    .auto-refresh-timer .icon {
        font-size: 16px;
    }

    .auto-refresh-timer .label {
        opacity: 0.7;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .auto-refresh-timer .countdown {
        font-weight: 700;
        font-size: 16px;
        min-width: 45px;
        text-align: center;
        color: #6ee7b7;
        transition: var(--transition);
    }

    .auto-refresh-timer .countdown.warning {
        color: var(--warning);
    }

    .auto-refresh-timer .countdown.danger {
        color: var(--danger);
        animation: blink 0.5s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.2; }
    }

    .smoke-status-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 32px 40px;
        margin-bottom: 28px;
        box-shadow: var(--shadow-card);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 24px;
        transition: var(--transition);
        color: var(--text-primary);
    }

    .smoke-status-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
    }

    .smoke-status-left {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .smoke-status-left .smoke-icon {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        flex-shrink: 0;
        transition: var(--transition);
    }

    .smoke-status-left .smoke-icon.normal {
        background: var(--icon-normal-bg);
        color: var(--icon-normal-color);
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
    }

    .smoke-status-left .smoke-icon.warning {
        background: var(--icon-warning-bg);
        color: var(--icon-warning-color);
        box-shadow: 0 4px 16px rgba(245, 158, 11, 0.2);
    }

    .smoke-status-left .smoke-icon.danger {
        background: var(--icon-danger-bg);
        color: var(--icon-danger-color);
        box-shadow: 0 4px 16px rgba(239, 68, 68, 0.2);
        animation: shake 0.5s infinite;
    }

    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }

    .smoke-status-left .smoke-info h3 {
        margin: 0 0 4px 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        transition: color 0.3s ease;
    }

    .smoke-status-left .smoke-info p {
        margin: 0;
        font-size: 14px;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        transition: color 0.3s ease;
    }

    .smoke-status-left .smoke-info .status-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        transition: var(--transition);
    }

    .smoke-status-left .smoke-info .status-label.normal {
        background: var(--badge-normal-bg);
        color: var(--badge-normal-text);
    }

    .smoke-status-left .smoke-info .status-label.warning {
        background: var(--badge-warning-bg);
        color: var(--badge-warning-text);
    }

    .smoke-status-left .smoke-info .status-label.danger {
        background: var(--badge-danger-bg);
        color: var(--badge-danger-text);
        animation: pulse-danger 1s infinite;
    }

    @keyframes pulse-danger {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .smoke-status-right {
        display: flex;
        align-items: center;
        gap: 28px;
        flex: 1;
        max-width: 600px;
        min-width: 280px;
    }

    .smoke-status-right .smoke-value-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 110px;
    }

    .smoke-status-right .smoke-value {
        font-size: 3.2rem;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        transition: color 0.5s ease;
    }

    .smoke-status-right .smoke-value small {
        font-size: 1.4rem;
        font-weight: 400;
        color: var(--text-muted);
        margin-left: 2px;
    }

    .smoke-status-right .smoke-value.normal { color: #059669; }
    .smoke-status-right .smoke-value.warning { color: #d97706; }
    .smoke-status-right .smoke-value.danger { color: #dc2626; }

    .smoke-status-right .smoke-label {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 4px;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: color 0.3s ease;
    }

    .smoke-status-right .smoke-bar-container {
        flex: 1;
        min-width: 140px;
    }

    .smoke-status-right .bar-track {
        width: 100%;
        height: 10px;
        background: var(--bar-track);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
        transition: background 0.3s ease;
    }

    .smoke-status-right .bar-fill {
        height: 100%;
        border-radius: 20px;
        transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .smoke-status-right .bar-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }

    [data-theme="dark"] .smoke-status-right .bar-fill::after {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .smoke-status-right .bar-fill.normal {
        background: var(--bar-fill-normal);
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.3);
    }

    .smoke-status-right .bar-fill.warning {
        background: var(--bar-fill-warning);
        box-shadow: 0 0 30px rgba(245, 158, 11, 0.3);
    }

    .smoke-status-right .bar-fill.danger {
        background: var(--bar-fill-danger);
        box-shadow: 0 0 30px rgba(239, 68, 68, 0.3);
        animation: pulse-bar 1s infinite;
    }

    @keyframes pulse-bar {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .smoke-status-right .bar-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 6px;
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .smoke-status-right .bar-labels .min-label {
        color: var(--text-light);
    }

    .smoke-status-right .bar-labels .max-label {
        color: #dc2626;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .smoke-status-right .bar-labels .current-value {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 12px;
        background: var(--bg-hover-row);
        padding: 0 10px;
        border-radius: 10px;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .table-container {
        background: var(--bg-card);
        border-radius: var(--radius);
        box-shadow: var(--shadow-card);
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: var(--transition);
    }

    .table-container:hover {
        box-shadow: var(--shadow-hover);
    }

    .table-header {
        padding: 20px 28px;
        border-bottom: 1px solid var(--border-table);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        background: var(--bg-table-header);
        transition: all 0.3s ease;
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }

    .table-header h2 span {
        background: var(--border-color);
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        transition: all 0.3s ease;
    }

    .table-header .table-info {
        font-size: 14px;
        color: var(--text-muted);
        transition: color 0.3s ease;
    }

    .table-header .table-info strong {
        color: var(--text-primary);
        font-weight: 700;
        transition: color 0.3s ease;
    }

    .table-scroll {
        overflow-x: auto;
        padding: 0 28px 28px;
    }

    .table-container table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-container thead th {
        text-align: left;
        padding: 14px 16px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-table);
        background: var(--bg-table-header);
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .table-container tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-table);
        color: var(--text-primary);
        font-size: 14px;
        vertical-align: middle;
        transition: var(--transition);
    }

    .table-container tbody tr {
        transition: var(--transition);
    }

    .table-container tbody tr:hover {
        background: var(--bg-hover-row);
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    .table-container tbody tr.status-change {
        border-left: 4px solid #8b5cf6;
    }

    .table-container tbody tr.status-change td:first-child {
        padding-left: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: var(--transition);
    }

    .status-badge::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .status-badge.danger {
        background: var(--badge-danger-bg);
        color: var(--badge-danger-text);
    }
    .status-badge.danger::before {
        background: var(--danger);
        animation: pulse-offline 1s infinite;
    }

    .status-badge.warning {
        background: var(--badge-warning-bg);
        color: var(--badge-warning-text);
    }
    .status-badge.warning::before {
        background: var(--warning);
        animation: pulse-online 1.5s infinite;
    }

    .status-badge.normal {
        background: var(--badge-normal-bg);
        color: var(--badge-normal-text);
    }
    .status-badge.normal::before {
        background: var(--success);
        animation: pulse-online 2s infinite;
    }

    .value-cell {
        font-weight: 700;
        font-family: 'Courier New', monospace;
        font-size: 15px;
        transition: color 0.3s ease;
    }

    .value-cell.danger {
        color: var(--danger);
    }
    .value-cell.warning {
        color: var(--warning);
    }
    .value-cell.normal {
        color: var(--success);
    }

    .time-cell {
        font-size: 13px;
        color: var(--text-secondary);
        font-family: 'Courier New', monospace;
        white-space: nowrap;
        transition: color 0.3s ease;
    }

    .message-cell {
        font-size: 13px;
        color: var(--text-secondary);
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: color 0.3s ease;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state .empty-icon {
        font-size: 48px;
        display: block;
        margin-bottom: 16px;
        opacity: 0.6;
    }

    .empty-state h3 {
        color: var(--text-primary);
        font-size: 18px;
        margin: 0 0 6px;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
        color: var(--text-secondary);
    }

    .pagination-wrapper {
        padding: 16px 28px 20px;
        border-top: 1px solid var(--border-table);
        background: var(--bg-table-header);
        border-radius: 0 0 var(--radius) var(--radius);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        transition: all 0.3s ease;
    }

    .pagination-info {
        font-size: 14px;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        transition: color 0.3s ease;
    }

    .pagination-info strong {
        color: var(--text-primary);
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .pagination-info .separator {
        color: var(--text-muted);
        margin: 0 4px;
    }

    .pagination {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .pagination .page-item {
        display: inline-block;
    }

    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        min-width: 36px;
        height: 36px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        line-height: 1;
        cursor: pointer;
        user-select: none;
    }

    .pagination .page-link:hover:not(.active):not(.disabled) {
        background: var(--bg-hover-row);
        border-color: var(--text-muted);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .pagination .page-link:active:not(.active):not(.disabled) {
        transform: translateY(0);
    }

    .pagination .page-link.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border-color: #6366f1;
        box-shadow: 0 2px 12px rgba(99, 102, 241, 0.25);
        font-weight: 600;
    }

    .pagination .page-link.active:hover {
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.35);
        transform: translateY(-1px);
    }

    .pagination .page-link.disabled {
        background: var(--bg-hover-row);
        color: var(--text-muted);
        cursor: not-allowed;
        pointer-events: none;
        opacity: 0.6;
        border-color: var(--border-color);
    }

    .pagination .page-link .arrow {
        font-size: 14px;
        line-height: 1;
    }

    .pagination .page-link .arrow-left {
        margin-right: 2px;
    }

    .pagination .page-link .arrow-right {
        margin-left: 2px;
    }

    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: var(--text-secondary);
        transition: color 0.3s ease;
    }

    .perpage-selector select {
        padding: 6px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        font-size: 14px;
        color: var(--text-primary);
        cursor: pointer;
        outline: none;
        transition: var(--transition);
        font-weight: 500;
    }

    .perpage-selector select:hover {
        border-color: var(--text-muted);
    }

    .perpage-selector select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .perpage-selector select option {
        background: var(--bg-card);
        color: var(--text-primary);
    }

    .new-log-flash {
        animation: flashRow 0.6s ease;
    }

    @keyframes flashRow {
        0% { background: #dbeafe; }
        100% { background: transparent; }
    }

    [data-theme="dark"] .new-log-flash {
        animation: flashRowDark 0.6s ease;
    }

    @keyframes flashRowDark {
        0% { background: #1a2332; }
        100% { background: transparent; }
    }

    /* ========== ROW NUMBER ========== */
    .row-number {
        text-align: center;
        font-weight: 600;
        color: #8b5cf6;
        font-size: 12px;
        min-width: 30px;
        display: inline-block;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1024px) {
        .smoke-status-right {
            max-width: 100%;
            flex-wrap: wrap;
            gap: 16px;
        }
        .smoke-status-right .smoke-value-wrapper {
            min-width: 80px;
        }
        .smoke-status-right .smoke-bar-container {
            min-width: 100px;
        }
    }

    @media (max-width: 768px) {
        .smoke-container {
            padding: 16px;
        }
        .smoke-header {
            padding: 20px 24px;
            flex-direction: column;
            align-items: stretch;
            border-radius: 14px;
        }
        .smoke-header h1 {
            font-size: 20px;
        }
        .smoke-header .header-icon {
            width: 44px;
            height: 44px;
            font-size: 22px;
        }
        .smoke-header .header-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .btn-download-csv {
            justify-content: center;
        }
        .status-esp {
            justify-content: center;
        }
        .smoke-status-card {
            flex-direction: column;
            align-items: stretch;
            padding: 20px 24px;
        }
        .smoke-status-left {
            gap: 16px;
        }
        .smoke-status-left .smoke-icon {
            width: 56px;
            height: 56px;
            font-size: 28px;
        }
        .smoke-status-left .smoke-info h3 {
            font-size: 16px;
        }
        .smoke-status-right {
            max-width: 100%;
            flex-wrap: wrap;
            gap: 12px;
        }
        .smoke-status-right .smoke-value-wrapper {
            min-width: 70px;
        }
        .smoke-status-right .smoke-value {
            font-size: 2.4rem;
        }
        .smoke-status-right .smoke-value small {
            font-size: 1.1rem;
        }
        .smoke-status-right .smoke-bar-container {
            min-width: 80px;
        }
        .table-scroll {
            padding: 0 16px 16px;
        }
        .table-header {
            padding: 16px 20px;
        }
        .table-container thead th,
        .table-container tbody td {
            padding: 10px 12px;
            font-size: 12px;
        }
        .status-badge {
            font-size: 11px;
            padding: 3px 12px;
        }
        .value-cell {
            font-size: 13px;
        }
        .time-cell {
            font-size: 11px;
        }
        .message-cell {
            max-width: 120px;
            font-size: 11px;
        }
        .pagination-wrapper {
            flex-direction: column;
            align-items: center;
            padding: 16px 20px;
            gap: 12px;
        }
        .pagination-info {
            font-size: 13px;
            justify-content: center;
        }
        .pagination {
            gap: 3px;
        }
        .pagination .page-link {
            padding: 5px 10px;
            min-width: 32px;
            height: 32px;
            font-size: 12px;
        }
        .pagination .page-link .arrow {
            font-size: 12px;
        }
        .auto-refresh-timer {
            bottom: 16px;
            right: 16px;
            padding: 8px 14px;
            font-size: 11px;
        }
        .auto-refresh-timer .countdown {
            font-size: 14px;
            min-width: 35px;
        }
        .perpage-selector {
            font-size: 12px;
        }
        .perpage-selector select {
            padding: 4px 10px;
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .smoke-header {
            padding: 16px;
        }
        .smoke-header h1 {
            font-size: 17px;
        }
        .smoke-header .header-subtitle {
            font-size: 12px;
            flex-wrap: wrap;
        }
        .smoke-status-card {
            padding: 16px;
        }
        .smoke-status-left .smoke-icon {
            width: 48px;
            height: 48px;
            font-size: 24px;
        }
        .smoke-status-left .smoke-info h3 {
            font-size: 14px;
        }
        .smoke-status-left .smoke-info p {
            font-size: 12px;
        }
        .smoke-status-left .smoke-info .status-label {
            font-size: 11px;
            padding: 3px 12px;
        }
        .smoke-status-right .smoke-value-wrapper {
            min-width: 60px;
        }
        .smoke-status-right .smoke-value {
            font-size: 2rem;
        }
        .smoke-status-right .smoke-value small {
            font-size: 0.9rem;
        }
        .smoke-status-right .smoke-bar-container {
            min-width: 60px;
        }
        .smoke-status-right .bar-labels {
            font-size: 10px;
        }
        .smoke-status-right .bar-labels .current-value {
            font-size: 10px;
            padding: 0 6px;
        }
        .table-container thead th,
        .table-container tbody td {
            padding: 8px 8px;
            font-size: 11px;
        }
        .status-badge {
            font-size: 10px;
            padding: 2px 10px;
            gap: 5px;
        }
        .status-badge::before {
            width: 6px;
            height: 6px;
        }
        .value-cell {
            font-size: 12px;
        }
        .time-cell {
            font-size: 10px;
        }
        .message-cell {
            max-width: 80px;
            font-size: 10px;
        }
        .pagination-wrapper {
            padding: 12px 16px;
        }
        .pagination-info {
            font-size: 12px;
        }
        .pagination {
            gap: 2px;
        }
        .pagination .page-link {
            padding: 4px 8px;
            min-width: 28px;
            height: 28px;
            font-size: 11px;
            border-radius: 6px;
        }
        .pagination .page-link .arrow {
            font-size: 10px;
        }
        .btn-download-csv {
            padding: 8px 16px;
            font-size: 12px;
        }
        .btn-download-csv span {
            display: inline;
        }
        .perpage-selector {
            font-size: 11px;
        }
        .perpage-selector select {
            padding: 3px 8px;
            font-size: 11px;
        }
        .table-header h2 {
            font-size: 14px;
        }
        .table-header .table-info {
            font-size: 12px;
        }
        .empty-state {
            padding: 40px 16px;
        }
        .empty-state .empty-icon {
            font-size: 36px;
        }
        .empty-state h3 {
            font-size: 16px;
        }
        .empty-state p {
            font-size: 12px;
        }
    }
</style>

<div class="smoke-container">
    <!-- ========== HEADER ========== -->
    <div class="smoke-header">
        <div class="header-left">
            <div class="header-icon">🔥</div>
            <div>
                <h1>Smoke Detector Monitoring</h1>
                <div class="header-subtitle">
                    Pantau status asap dan kondisi device
                    <span>Real-time</span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('smoke.export') }}" class="btn-download-csv">
                📥 <span>Download CSV</span>
            </a>
            <div class="status-esp" id="espStatus">
                <span class="dot offline" id="espDot"></span>
                <span>ESP Status:</span>
                <span id="espStatusText" style="font-weight: 700;">Memuat...</span>
            </div>
        </div>
    </div>

    <!-- ========== SMOKE STATUS CARD ========== -->
    @php
        $device = $devices->first();
        $smokeValue = $device?->smoke_value ?? 0;
        $smokeStatus = strtolower($device?->status ?? 'normal');
        $statusClass = $smokeStatus;
        $statusLabel = $smokeStatus == 'danger' ? '🔴 BAHAYA' : ($smokeStatus == 'warning' ? '🟡 WARNING' : '🟢 NORMAL');
        $maxAdc = 1000;
        $percentage = min(($smokeValue / $maxAdc) * 100, 100);
    @endphp

    <div class="smoke-status-card" id="smokeStatusCard">
        <div class="smoke-status-left">
            <div class="smoke-icon {{ $statusClass }}" id="smokeIcon">
                @if($smokeStatus == 'danger') 🔥
                @elseif($smokeStatus == 'warning') ⚠️
                @else ✅
                @endif
            </div>
            <div class="smoke-info">
                <h3>Kadar Asap</h3>
                <p>
                    Status:
                    <span class="status-label {{ $statusClass }}" id="statusLabel">
                        {{ $statusLabel }}
                    </span>
                </p>
            </div>
        </div>
        <div class="smoke-status-right">
            <div class="smoke-value-wrapper">
                <div class="smoke-value {{ $statusClass }}" id="smokeValue">
                    {{ number_format($smokeValue, 0) }}<small>ADC</small>
                </div>
                <div class="smoke-label">Nilai ADC</div>
            </div>
            <div class="smoke-bar-container">
                <div class="bar-track">
                    <div class="bar-fill {{ $statusClass }}" id="smokeBar" style="width: {{ $percentage }}%;"></div>
                </div>
                <div class="bar-labels">
                    <span class="min-label">0 ADC</span>
                    <span class="current-value">{{ number_format($smokeValue, 0) }} ADC</span>
                    <span class="max-label">⚠️ {{ $maxAdc }} ADC</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== TABLE LOGS ========== -->
    <div class="table-container">
        <div class="table-header">
            <h2>
                📋 Logs Smoke Detector
                <span id="logCount">{{ $smokeLogs->total() }}</span>
            </h2>
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
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
                    Total <strong id="totalLogs">{{ $smokeLogs->total() }}</strong> logs
                </span>
            </div>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 180px;">🕐 Waktu</th>
                        <th style="width: 120px;">📊 Nilai ADC</th>
                        <th style="width: 140px;">📌 Status</th>
                        <th>📝 Keterangan</th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    @forelse($smokeLogs as $index => $log)
                        @php
                            $statusClass = strtolower($log->status ?? 'normal');
                            $valueClass = $log->status == 'DANGER' ? 'danger' : ($log->status == 'WARNING' ? 'warning' : 'normal');
                            $statusIcon = $log->status == 'DANGER' ? '🔴' : ($log->status == 'WARNING' ? '🟡' : '🟢');
                            $logMessage = $log->status == 'DANGER' ? '🔥 Asap tinggi! Periksa segera!' : ($log->status == 'WARNING' ? '⚠️ Asap mulai terdeteksi, waspada!' : '✅ Kondisi dalam status aman');
                            
                            $rowNumber = $smokeLogs->firstItem() + $index;
                        @endphp
                        <tr data-log-id="{{ $log->id }}" data-log-status="{{ $log->status }}" class="status-change">
                            <td style="text-align: center;">
                                <span class="row-number">{{ $rowNumber }}</span>
                            </td>
                            <td>
                                <span class="time-cell" data-updated-at="{{ $log->updated_at ?? $log->created_at }}">
                                    {{ ($log->updated_at ?? $log->created_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <span class="value-cell {{ $valueClass }}">
                                    {{ $log->smoke_value ?? 0 }}
                                    <span style="font-size: 11px; font-weight: 400; color: var(--text-muted);">ADC</span>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusIcon }} {{ $log->status ?? 'NORMAL' }}
                                </span>
                            </td>
                            <td>
                                <div class="message-cell" title="{{ $logMessage }}">
                                    {{ $logMessage }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
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

        @if($smokeLogs->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan 
                <strong>{{ $smokeLogs->firstItem() ?? 0 }}</strong> 
                <span class="separator">-</span> 
                <strong>{{ $smokeLogs->lastItem() ?? 0 }}</strong> 
                <span class="separator">dari</span> 
                <strong>{{ $smokeLogs->total() }}</strong> 
                logs
            </div>
            <ul class="pagination">
                @if ($smokeLogs->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <span class="arrow arrow-left">‹</span>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $smokeLogs->previousPageUrl() }}" rel="prev">
                            <span class="arrow arrow-left">‹</span>
                        </a>
                    </li>
                @endif

                @php
                    $start = max(1, $smokeLogs->currentPage() - 2);
                    $end = min($start + 4, $smokeLogs->lastPage());
                    if ($end - $start < 4) {
                        $start = max(1, $end - 4);
                    }
                @endphp

                @if ($start > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $smokeLogs->url(1) }}">1</a>
                    </li>
                    @if ($start > 2)
                        <li class="page-item disabled">
                            <span class="page-link">…</span>
                        </li>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    <li class="page-item">
                        <a class="page-link {{ $smokeLogs->currentPage() == $i ? 'active' : '' }}" 
                           href="{{ $smokeLogs->url($i) }}">
                            {{ $i }}
                        </a>
                    </li>
                @endfor

                @if ($end < $smokeLogs->lastPage())
                    @if ($end < $smokeLogs->lastPage() - 1)
                        <li class="page-item disabled">
                            <span class="page-link">…</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $smokeLogs->url($smokeLogs->lastPage()) }}">
                            {{ $smokeLogs->lastPage() }}
                        </a>
                    </li>
                @endif

                @if ($smokeLogs->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $smokeLogs->nextPageUrl() }}" rel="next">
                            <span class="arrow arrow-right">›</span>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <span class="arrow arrow-right">›</span>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</div>

<!-- ========== AUTO REFRESH TIMER ========== -->
<div class="auto-refresh-timer" id="autoRefreshTimer">
    <span class="icon">🔄</span>
    <span class="label">Refresh</span>
    <span class="countdown" id="countdownTimer">0:05</span>
</div>

<!-- ========== SCRIPT ========== -->
<script>
    // ========== KONFIGURASI ==========
    const REFRESH_INTERVAL = 5;
    let countdownSeconds = REFRESH_INTERVAL;
    let countdownElement = document.getElementById('countdownTimer');
    let totalLogsCount = {{ $smokeLogs->total() }};
    let isFetching = false;
    let lastLogId = null;
    let isFirstLoad = true;
    
    // 🔥 TRACK STATUS DAN ADC SAAT INI
    let currentStatus = '{{ strtoupper($smokeStatus) }}';
    let currentAdc = {{ $smokeValue }};

    // ========== UPDATE TOTAL LOGS ==========
    function updateTotalLogs(count) {
        totalLogsCount = count;
        document.getElementById('totalLogs').textContent = count;
        document.getElementById('logCount').textContent = count;
    }

    // ========== FORMAT NUMBER ==========
    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // ========== FORMAT DATE ==========
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const seconds = date.getSeconds().toString().padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    }

    // ========== GET STATUS ICON ==========
    function getStatusIcon(status) {
        if (status === 'DANGER') return '🔴';
        if (status === 'WARNING') return '🟡';
        return '🟢';
    }

    // ========== GET STATUS CLASS ==========
    function getStatusClass(status) {
        if (status === 'DANGER') return 'danger';
        if (status === 'WARNING') return 'warning';
        return 'normal';
    }

    // ========== GET STATUS MESSAGE ==========
    function getStatusMessage(status) {
        if (status === 'DANGER') return '🔥 Asap tinggi! Periksa segera!';
        if (status === 'WARNING') return '⚠️ Asap mulai terdeteksi, waspada!';
        return '✅ Kondisi dalam status aman';
    }

    // ========== FETCH ESP STATUS ==========
    function fetchEspStatus() {
        if (isFetching) return;
        isFetching = true;
        
        const url = '/api/smoke/status?_=' + Date.now();
        
        fetch(url, {
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const esp = data.data;
                const isOnline = esp.device_status === 'ONLINE';
                const adc = esp.adc || 0;
                const status = esp.status || 'NORMAL';
                const isStatusChanged = esp.is_status_changed || false;
                const isAdcUpdated = esp.is_adc_updated || false;
                const latestLog = esp.latest_log || null;
                const serverLogId = latestLog ? latestLog.id : null;
                
                // UPDATE ESP STATUS
                const dot = document.getElementById('espDot');
                const statusText = document.getElementById('espStatusText');
                if (dot && statusText) {
                    dot.className = 'dot ' + (isOnline ? 'online' : 'offline');
                    statusText.textContent = isOnline ? 'ONLINE' : 'OFFLINE';
                    statusText.style.color = isOnline ? '#10b981' : '#ef4444';
                }
                
                // UPDATE TAMPILAN SMOKE
                updateSmokeDisplay(esp);
                
                // 🔥 LOGIKA DETEKSI PERUBAHAN
                const oldStatus = currentStatus;
                const oldAdc = currentAdc;
                
                // 🔥 1. CEK STATUS BERUBAH (LOG BARU)
                if (isStatusChanged || status !== oldStatus) {
                    console.log('📝 STATUS BERUBAH!', oldStatus, '→', status);
                    addNewLog(esp, latestLog);
                    lastLogId = serverLogId;
                    currentStatus = status;
                    currentAdc = adc;
                }
                // 🔥 2. ADC BERUBAH TAPI STATUS SAMA
                else if ((isAdcUpdated || adc !== oldAdc) && status === oldStatus) {
                    console.log('🔄 ADC UPDATED:', oldAdc, '→', adc);
                    updateLastLogAdc(esp);
                    currentAdc = adc;
                }
                
                if (isFirstLoad) {
                    lastLogId = serverLogId;
                    currentStatus = status;
                    currentAdc = adc;
                    isFirstLoad = false;
                }
            }
            isFetching = false;
        })
        .catch(error => {
            console.error('Error fetching ESP status:', error);
            isFetching = false;
        });
    }

    // ========== TAMBAHKAN LOG BARU ==========
    function addNewLog(data, logData) {
        const tbody = document.getElementById('logTableBody');
        if (!tbody) return;
        
        const status = data.status || 'NORMAL';
        const adc = data.adc || 0;
        const statusClass = getStatusClass(status);
        const statusIcon = getStatusIcon(status);
        const logMessage = logData ? logData.message : getStatusMessage(status);
        const createdAt = logData ? logData.created_at : new Date().toISOString();
        const logId = logData ? logData.id : Date.now();
        
        // CEK DUPLICATE
        const existingRows = tbody.querySelectorAll('tr[data-log-id="' + logId + '"]');
        if (existingRows.length > 0) {
            console.log('⚠️ Log sudah ada, skip duplicate');
            return;
        }
        
        // TAMBAH TOTAL LOGS
        totalLogsCount++;
        updateTotalLogs(totalLogsCount);
        
        // HITUNG NOMOR URUT
        const firstRow = tbody.querySelector('tr');
        let rowNumber = 1;
        if (firstRow) {
            const firstNumberSpan = firstRow.querySelector('.row-number');
            if (firstNumberSpan) {
                rowNumber = parseInt(firstNumberSpan.textContent) + 1;
            }
        }
        
        const currentTime = formatDate(createdAt);
        
        // BUAT ROW BARU
        const row = document.createElement('tr');
        row.dataset.logId = logId;
        row.dataset.logStatus = status;
        row.className = 'status-change new-log-flash';
        
        row.innerHTML = `
            <td style="text-align: center;">
                <span class="row-number">${rowNumber}</span>
            </td>
            <td><span class="time-cell" data-updated-at="${createdAt}">${currentTime}</span></td>
            <td><span class="value-cell ${statusClass}">${numberFormat(adc)} <span style="font-size:11px;font-weight:400;color:var(--text-muted);">ADC</span></span></td>
            <td><span class="status-badge ${statusClass}">${statusIcon} ${status}</span></td>
            <td><div class="message-cell" title="${logMessage}">${logMessage}</div></td>
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
        updateRowNumbers();
        
        // HAPUS ROW KELEBIHAN
        const perPage = parseInt(document.getElementById('perPage')?.value || 10);
        while (tbody.children.length > perPage && tbody.children.length > 1) {
            const lastRow = tbody.lastChild;
            if (lastRow) {
                tbody.removeChild(lastRow);
            }
        }
        
        setTimeout(() => {
            row.classList.remove('new-log-flash');
        }, 600);
        
        lastLogId = logId;
        console.log('✅ Log baru ditambahkan:', status, adc, 'ID:', logId);
    }

    // ========== UPDATE ADC DI LOG TERAKHIR ==========
    function updateLastLogAdc(data) {
        const tbody = document.getElementById('logTableBody');
        if (!tbody) return;
        
        const rows = tbody.querySelectorAll('tr[data-log-id]');
        if (rows.length === 0) return;
        
        const targetRow = rows[0];
        if (!targetRow) return;
        
        const currentTime = formatDate(new Date().toISOString());
        const statusClass = getStatusClass(data.status || 'NORMAL');
        const adc = data.adc || 0;
        
        const valueCell = targetRow.querySelector('.value-cell');
        if (valueCell) {
            valueCell.innerHTML = `${numberFormat(adc)} <span style="font-size:11px;font-weight:400;color:var(--text-muted);">ADC</span>`;
            valueCell.className = 'value-cell ' + statusClass;
        }
        
        const timeCell = targetRow.querySelector('.time-cell');
        if (timeCell) {
            timeCell.textContent = currentTime;
            timeCell.dataset.updatedAt = new Date().toISOString();
        }
        
        targetRow.classList.add('new-log-flash');
        setTimeout(() => {
            targetRow.classList.remove('new-log-flash');
        }, 600);
        
        console.log('✅ Log diupdate ADC:', adc);
    }

    // ========== UPDATE TAMPILAN SMOKE ==========
    function updateSmokeDisplay(data) {
        const adc = data.adc || 0;
        const status = data.status || 'NORMAL';
        const statusClass = getStatusClass(status);

        const smokeValueElement = document.getElementById('smokeValue');
        if (smokeValueElement) {
            smokeValueElement.innerHTML = numberFormat(adc) + '<small>ADC</small>';
            smokeValueElement.className = 'smoke-value ' + statusClass;
        }

        const statusLabelElement = document.getElementById('statusLabel');
        if (statusLabelElement) {
            const statusIcon = getStatusIcon(status);
            const statusText = status === 'DANGER' ? 'BAHAYA' : (status === 'WARNING' ? 'WARNING' : 'NORMAL');
            statusLabelElement.textContent = statusIcon + ' ' + statusText;
            statusLabelElement.className = 'status-label ' + statusClass;
        }

        const smokeIcon = document.getElementById('smokeIcon');
        if (smokeIcon) {
            smokeIcon.textContent = status === 'DANGER' ? '🔥' : (status === 'WARNING' ? '⚠️' : '✅');
            smokeIcon.className = 'smoke-icon ' + statusClass;
        }

        const maxAdc = 1000;
        const percentage = Math.min((adc / maxAdc) * 100, 100);
        const barFill = document.getElementById('smokeBar');
        if (barFill) {
            barFill.style.width = percentage + '%';
            barFill.className = 'bar-fill ' + statusClass;
        }

        const currentValueLabels = document.querySelectorAll('.bar-labels .current-value');
        if (currentValueLabels.length > 0) {
            currentValueLabels.forEach(el => {
                el.textContent = numberFormat(adc) + ' ADC';
            });
        }
    }

    // ========== UPDATE NOMOR URUT ==========
    function updateRowNumbers() {
        const rows = document.querySelectorAll('#logTableBody tr');
        const firstItem = {{ $smokeLogs->firstItem() ?? 1 }};
        rows.forEach((row, index) => {
            const firstCell = row.querySelector('td:first-child');
            if (firstCell) {
                const numberSpan = firstCell.querySelector('.row-number');
                if (numberSpan) {
                    numberSpan.textContent = firstItem + index;
                }
            }
        });
    }

    // ========== COUNTDOWN TIMER ==========
    function updateCountdown() {
        countdownSeconds--;
        if (countdownElement) {
            const secs = countdownSeconds.toString().padStart(2, '0');
            countdownElement.textContent = '0:' + secs;
            countdownElement.className = 'countdown';
            if (countdownSeconds < 3) {
                countdownElement.classList.add('danger');
            } else if (countdownSeconds < 8) {
                countdownElement.classList.add('warning');
            }
        }
        if (countdownSeconds <= 0) {
            countdownSeconds = REFRESH_INTERVAL;
            fetchEspStatus();
        }
    }

    // ========== INITIAL ==========
    document.addEventListener('DOMContentLoaded', function() {
        // AMBIL LOG ID PERTAMA
        const firstRow = document.querySelector('#logTableBody tr[data-log-id]');
        if (firstRow) {
            lastLogId = firstRow.dataset.logId;
            currentStatus = firstRow.dataset.logStatus || 'NORMAL';
        }
        
        // AMBIL ADC PERTAMA
        const firstValue = document.querySelector('#logTableBody tr:first-child .value-cell');
        if (firstValue) {
            const adcText = firstValue.textContent.trim();
            const adcMatch = adcText.match(/(\d+)/);
            if (adcMatch) {
                currentAdc = parseInt(adcMatch[0]);
            }
        }
        
        fetchEspStatus();
        setTimeout(updateRowNumbers, 100);
        
        // 🔥 INTERVAL 3 DETIK
        setInterval(fetchEspStatus, 3000);
        setInterval(updateCountdown, 1000);
    });

    // ========== PERPAGE ==========
    function changePerPage(value) {
        let url = new URL(window.location.href);
        url.searchParams.set('perPage', value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>
@endsection