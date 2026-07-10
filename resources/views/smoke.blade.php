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
        --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        --radius: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .smoke-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-main);
        min-height: 100vh;
    }

    /* ========== HEADER ========== */
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
        background: rgba(255, 255, 255, 0.15);
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

    .btn-download-csv:active {
        transform: translateY(0);
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

    /* ========== AUTO REFRESH TIMER ========== */
    .auto-refresh-timer {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: rgba(10, 46, 92, 0.9);
        color: white;
        padding: 10px 18px;
        border-radius: 12px;
        z-index: 99999;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        user-select: none;
        cursor: default;
        transition: var(--transition);
    }

    .auto-refresh-timer:hover {
        transform: scale(1.05);
        background: rgba(10, 46, 92, 0.95);
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

    /* ========== SMOKE STATUS CARD ========== */
    .smoke-status-card {
        background: white;
        border-radius: var(--radius);
        padding: 32px 40px;
        margin-bottom: 28px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(226, 232, 240, 0.6);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 24px;
        transition: var(--transition);
    }

    .smoke-status-card:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
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
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #059669;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
    }

    .smoke-status-left .smoke-icon.warning {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #d97706;
        box-shadow: 0 4px 16px rgba(245, 158, 11, 0.2);
    }

    .smoke-status-left .smoke-icon.danger {
        background: linear-gradient(135deg, #fee2e2, #fca5a5);
        color: #dc2626;
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
        color: #0f172a;
    }

    .smoke-status-left .smoke-info p {
        margin: 0;
        font-size: 14px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
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
        animation: pulse-danger 1s infinite;
    }

    @keyframes pulse-danger {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    /* ========== SMOKE STATUS RIGHT ========== */
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
        color: #94a3b8;
        margin-left: 2px;
    }

    .smoke-status-right .smoke-value.normal { color: #059669; }
    .smoke-status-right .smoke-value.warning { color: #d97706; }
    .smoke-status-right .smoke-value.danger { color: #dc2626; }

    .smoke-status-right .smoke-label {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .smoke-status-right .smoke-bar-container {
        flex: 1;
        min-width: 140px;
    }

    .smoke-status-right .bar-track {
        width: 100%;
        height: 10px;
        background: #e5e7eb;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        position: relative;
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

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .smoke-status-right .bar-fill.normal {
        background: linear-gradient(90deg, #34d399, #059669);
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.3);
    }

    .smoke-status-right .bar-fill.warning {
        background: linear-gradient(90deg, #fbbf24, #d97706);
        box-shadow: 0 0 30px rgba(245, 158, 11, 0.3);
    }

    .smoke-status-right .bar-fill.danger {
        background: linear-gradient(90deg, #f87171, #dc2626);
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
        color: #94a3b8;
        font-weight: 500;
    }

    .smoke-status-right .bar-labels .min-label {
        color: #64748b;
    }

    .smoke-status-right .bar-labels .max-label {
        color: #dc2626;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .smoke-status-right .bar-labels .current-value {
        color: #0f172a;
        font-weight: 700;
        font-size: 12px;
        background: #f1f5f9;
        padding: 0 10px;
        border-radius: 10px;
        display: inline-block;
    }

    /* ========== TABLE LOGS ========== */
    .table-container {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(226, 232, 240, 0.6);
        overflow: hidden;
        transition: var(--transition);
    }

    .table-container:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    }

    .table-header {
        padding: 20px 28px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        background: linear-gradient(135deg, #fafbfc, #f8fafc);
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-header h2 span {
        background: #e2e8f0;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        color: #475569;
    }

    .table-header .table-info {
        font-size: 14px;
        color: #94a3b8;
    }

    .table-header .table-info strong {
        color: #0f172a;
        font-weight: 700;
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
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
        transition: var(--transition);
    }

    .table-container tbody tr {
        transition: var(--transition);
    }

    .table-container tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .table-container tbody tr:last-child td {
        border-bottom: none;
    }

    /* ========== STATUS BADGE ========== */
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
        background: #fee2e2;
        color: #991b1b;
    }
    .status-badge.danger::before {
        background: var(--danger);
        animation: pulse-offline 1s infinite;
    }

    .status-badge.warning {
        background: #fef3c7;
        color: #92400e;
    }
    .status-badge.warning::before {
        background: var(--warning);
        animation: pulse-online 1.5s infinite;
    }

    .status-badge.normal {
        background: #d1fae5;
        color: #065f46;
    }
    .status-badge.normal::before {
        background: var(--success);
        animation: pulse-online 2s infinite;
    }

    .value-cell {
        font-weight: 700;
        font-family: 'Courier New', monospace;
        font-size: 15px;
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
        color: #64748b;
        font-family: 'Courier New', monospace;
        white-space: nowrap;
    }

    .message-cell {
        font-size: 13px;
        color: #475569;
        max-width: 280px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .device-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 14px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }

    .empty-state .empty-icon {
        font-size: 48px;
        display: block;
        margin-bottom: 16px;
        opacity: 0.6;
    }

    .empty-state h3 {
        color: #0f172a;
        font-size: 18px;
        margin: 0 0 6px;
        font-weight: 600;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
    }

    /* ========== PAGINATION ========== */
    .pagination-wrapper {
        padding: 16px 28px 20px;
        border-top: 1px solid #f1f5f9;
        background: linear-gradient(135deg, #fafbfc, #f8fafc);
        border-radius: 0 0 var(--radius) var(--radius);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .pagination-info {
        font-size: 14px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pagination-info strong {
        color: #0f172a;
        font-weight: 600;
    }

    .pagination-info .separator {
        color: #cbd5e1;
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
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        line-height: 1;
        cursor: pointer;
        user-select: none;
    }

    .pagination .page-link:hover:not(.active):not(.disabled) {
        background: #f1f5f9;
        border-color: #94a3b8;
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
        background: #f1f5f9;
        color: #cbd5e1;
        cursor: not-allowed;
        pointer-events: none;
        opacity: 0.6;
        border-color: #e2e8f0;
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

    /* ========== PERPAGE SELECTOR ========== */
    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #64748b;
    }

    .perpage-selector select {
        padding: 6px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        font-size: 14px;
        color: #0f172a;
        cursor: pointer;
        outline: none;
        transition: var(--transition);
        font-weight: 500;
    }

    .perpage-selector select:hover {
        border-color: #94a3b8;
    }

    .perpage-selector select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .new-log-flash {
        animation: flashRow 0.6s ease;
    }

    @keyframes flashRow {
        0% { background: #dbeafe; }
        100% { background: transparent; }
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

            <!-- 🔥 STATUS ESP - LANGSUNG DARI API -->
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
        $maxPpm = 1000;
        $percentage = min(($smokeValue / $maxPpm) * 100, 100);
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
                    {{ number_format($smokeValue, 0) }}<small>ppm</small>
                </div>
                <div class="smoke-label">Nilai Asap</div>
            </div>

            <div class="smoke-bar-container">
                <div class="bar-track">
                    <div class="bar-fill {{ $statusClass }}" id="smokeBar" style="width: {{ $percentage }}%;"></div>
                </div>
                <div class="bar-labels">
                    <span class="min-label">0 ppm</span>
                    <span class="current-value">{{ number_format($smokeValue, 0) }} ppm</span>
                    <span class="max-label">⚠️ {{ $maxPpm }} ppm</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== TABLE LOGS ========== -->
    <div class="table-container">
        <div class="table-header">
            <h2>
                📋 Riwayat Log Smoke Detector
                <span id="logCount">{{ $smokeLogs->total() }}</span>
            </h2>
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
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
                    Total <strong id="totalLogs">{{ $smokeLogs->total() }}</strong> logs
                </span>
            </div>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 180px;">🕐 Waktu</th>
                        <th style="width: 120px;">📊 Nilai Asap</th>
                        <th style="width: 140px;">📌 Status</th>
                        <th>📝 Keterangan</th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    @forelse($smokeLogs as $log)
                        @php
                            $statusClass = strtolower($log->status ?? 'normal');
                            $valueClass = $log->status == 'DANGER' ? 'danger' : ($log->status == 'WARNING' ? 'warning' : 'normal');
                            $statusIcon = $log->status == 'DANGER' ? '🔴' : ($log->status == 'WARNING' ? '🟡' : '🟢');
                            $logMessage = $log->status == 'DANGER' ? '🔥 Asap tinggi! Periksa segera!' : ($log->status == 'WARNING' ? '⚠️ Asap mulai terdeteksi, waspada!' : '✅ Kondisi aman, tidak ada asap');
                        @endphp
                        <tr data-log-id="{{ $log->id }}" data-log-status="{{ $log->status }}">
                            <td>
                                <span class="time-cell">
                                    {{ $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <span class="value-cell {{ $valueClass }}">
                                    {{ $log->smoke_value ?? 0 }}
                                    <span style="font-size: 11px; font-weight: 400; color: #94a3b8;">ppm</span>
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

        <!-- ========== PAGINATION ========== -->
        @if($smokeLogs->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan 
                <strong>{{ $smokeLogs->firstItem() ?? 0 }}</strong> 
                <span class="separator">-</span> 
                <strong>{{ $smokeLogs->lastItem() ?? 0 }}</strong> 
                <span class="separator">dari</span> 
                <strong>{{ $smokeLogs->total() }}</strong> 
                data
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
    <span class="countdown" id="countdownTimer">0:30</span>
</div>

<!-- ========== SCRIPT ========== -->
<script>
    // ========== KONFIGURASI ==========
    const REFRESH_INTERVAL = 30;
    let countdownSeconds = REFRESH_INTERVAL;
    let countdownElement = document.getElementById('countdownTimer');
    let lastLogCount = {{ $smokeLogs->total() }};
    let isUpdating = false;
    let lastPpm = {{ $smokeValue }};
    let lastStatus = '{{ $smokeStatus }}';

    // ========== FETCH ESP STATUS (SAMA SEPERTI DASHBOARD) ==========
    function fetchEspStatus() {
        fetch('/api/smoke/status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const esp = data.data;
                    const isOnline = esp.device_status === 'ONLINE';
                    const ppm = esp.ppm || 0;
                    const status = esp.status || 'NORMAL';
                    
                    // 🔥 UPDATE ESP STATUS - SAMA SEPERTI DASHBOARD
                    const dot = document.getElementById('espDot');
                    const statusText = document.getElementById('espStatusText');
                    if (dot && statusText) {
                        dot.className = 'dot ' + (isOnline ? 'online' : 'offline');
                        statusText.textContent = isOnline ? 'ONLINE' : 'OFFLINE';
                        statusText.style.color = isOnline ? '#10b981' : '#ef4444';
                    }
                    
                    // 🔥 UPDATE TAMPILAN SMOKE
                    updateSmokeDisplay(esp);
                }
            })
            .catch(error => console.error('Error fetching ESP status:', error));
    }

    // ========== UPDATE TAMPILAN SMOKE ==========
    function updateSmokeDisplay(data) {
        const ppm = data.ppm || 0;
        const status = data.status || 'NORMAL';
        const statusClass = data.status_class || 'normal';

        // Update PPM
        const smokeValueElement = document.getElementById('smokeValue');
        if (smokeValueElement) {
            smokeValueElement.innerHTML = numberFormat(ppm) + '<small>ppm</small>';
            smokeValueElement.className = 'smoke-value ' + statusClass.toLowerCase();
        }

        // Update Status Label
        const statusLabelElement = document.getElementById('statusLabel');
        if (statusLabelElement) {
            const statusIcon = status === 'DANGER' ? '🔴' : (status === 'WARNING' ? '🟡' : '🟢');
            const statusText = status === 'DANGER' ? 'BAHAYA' : (status === 'WARNING' ? 'WARNING' : 'NORMAL');
            statusLabelElement.textContent = statusIcon + ' ' + statusText;
            statusLabelElement.className = 'status-label ' + statusClass.toLowerCase();
        }

        // Update Icon
        const smokeIcon = document.getElementById('smokeIcon');
        if (smokeIcon) {
            smokeIcon.textContent = status === 'DANGER' ? '🔥' : (status === 'WARNING' ? '⚠️' : '✅');
            smokeIcon.className = 'smoke-icon ' + statusClass.toLowerCase();
        }

        // Update Progress Bar
        const maxPpm = 1000;
        const percentage = Math.min((ppm / maxPpm) * 100, 100);
        const barFill = document.getElementById('smokeBar');
        if (barFill) {
            barFill.style.width = percentage + '%';
            barFill.className = 'bar-fill ' + statusClass.toLowerCase();
        }

        // Update Current Value
        const currentValueLabels = document.querySelectorAll('.bar-labels .current-value');
        if (currentValueLabels.length > 0) {
            currentValueLabels.forEach(el => {
                el.textContent = numberFormat(ppm) + ' ppm';
            });
        }
    }

    // ========== AMBIL LOG TERBARU ==========
    function fetchLatestLogs() {
        const perPage = document.getElementById('perPage')?.value || 10;
        fetch('/api/smoke/logs?limit=' + perPage)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const newLogCount = data.total || data.data.length;
                    if (newLogCount > lastLogCount) {
                        updateLogTable(data.data);
                        lastLogCount = newLogCount;
                        document.getElementById('totalLogs').textContent = newLogCount;
                        document.getElementById('logCount').textContent = newLogCount;
                    }
                }
            })
            .catch(error => console.error('Error fetching logs:', error));
    }

    // ========== UPDATE TABEL LOG ==========
    function updateLogTable(logs) {
        const tbody = document.getElementById('logTableBody');
        if (!tbody) return;

        const existingIds = Array.from(tbody.querySelectorAll('tr[data-log-id]')).map(tr => parseInt(tr.dataset.logId));
        const newLogs = logs.filter(log => !existingIds.includes(log.id));

        if (newLogs.length === 0) return;

        newLogs.forEach(log => {
            const row = document.createElement('tr');
            row.dataset.logId = log.id;
            row.dataset.logStatus = log.status || 'NORMAL';
            const statusClass = log.status ? log.status.toLowerCase() : 'normal';
            const statusIcon = log.status === 'DANGER' ? '🔴' : (log.status === 'WARNING' ? '🟡' : '🟢');
            const logMessage = log.status === 'DANGER' ? '🔥 Asap tinggi! Periksa segera!' : 
                              (log.status === 'WARNING' ? '⚠️ Asap mulai terdeteksi, waspada!' : '✅ Kondisi aman, tidak ada asap');
            
            row.innerHTML = `
                <td><span class="time-cell">${formatDate(log.created_at)}</span></td>
                <td><span class="value-cell ${statusClass}">${numberFormat(log.ppm)} <span style="font-size:11px;font-weight:400;color:#94a3b8;">ppm</span></span></td>
                <td><span class="status-badge ${statusClass}">${statusIcon} ${log.status || 'NORMAL'}</span></td>
                <td><div class="message-cell" title="${logMessage}">${logMessage}</div></td>
            `;
            
            tbody.insertBefore(row, tbody.firstChild);
            
            row.classList.add('new-log-flash');
            setTimeout(() => {
                row.classList.remove('new-log-flash');
            }, 600);
            
            const perPage = parseInt(document.getElementById('perPage')?.value || 10);
            while (tbody.children.length > perPage) {
                tbody.removeChild(tbody.lastChild);
            }
        });
    }

    // ========== HELPER FUNCTIONS ==========
    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.getDate().toString().padStart(2, '0') + '/' + 
               (date.getMonth() + 1).toString().padStart(2, '0') + '/' + 
               date.getFullYear() + ' ' + 
               date.getHours().toString().padStart(2, '0') + ':' + 
               date.getMinutes().toString().padStart(2, '0') + ':' + 
               date.getSeconds().toString().padStart(2, '0');
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
            fetchLatestLogs();
        }
    }

    // ========== JALANKAN SAAT HALAMAN DIMUAT ==========
    document.addEventListener('DOMContentLoaded', function() {
        // 🔥 FETCH DATA LANGSUNG SAAT HALAMAN DIMUAT
        fetchEspStatus();
        fetchLatestLogs();
        
        // Jalankan countdown setiap detik
        setInterval(updateCountdown, 1000);
        
        // 🔥 FETCH ESP STATUS SETIAP 3 DETIK (SAMA SEPERTI DASHBOARD)
        setInterval(fetchEspStatus, 3000);
        
        // Fetch logs setiap 10 detik
        setInterval(fetchLatestLogs, 10000);
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