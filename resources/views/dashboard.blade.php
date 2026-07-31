@extends('layouts.app')

@section('content')
<style>
    /* ================= ROOT VARIABLES ================= */
    :root {
        --primary: #0d3b66;
        --primary-dark: #082a4a;
        --primary-light: #1a4d7a;
        --primary-gradient: linear-gradient(135deg, #0d3b66 0%, #1a4d7a 50%, #2563eb 100%);
        --success: #059669;
        --success-light: #d1fae5;
        --warning: #d97706;
        --warning-light: #fef3c7;
        --danger: #dc2626;
        --danger-light: #fee2e2;
        --purple: #7c3aed;
        --purple-light: #ede9fe;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --gray-900: #0f172a;
        
        --bg-dashboard: #f0f2f5;
        --bg-card: #ffffff;
        --text-dashboard: #1e293b;
        --text-secondary-dash: #475569;
        --border-dash: rgba(226, 232, 240, 0.6);
        --shadow-dash: 0 2px 12px rgba(0, 0, 0, 0.06);
        --shadow-hover-dash: 0 8px 30px rgba(0, 0, 0, 0.10);
        --radius-card: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    [data-theme="dark"] {
        --bg-dashboard: #0f172a;
        --bg-card: #1e293b;
        --text-dashboard: #e2e8f0;
        --text-secondary-dash: #94a3b8;
        --border-dash: #334155;
        --shadow-dash: 0 2px 12px rgba(0, 0, 0, 0.2);
        --shadow-hover-dash: 0 8px 30px rgba(0, 0, 0, 0.3);
        --gray-50: #1e293b;
        --gray-100: #2d3a4f;
        --gray-200: #334155;
        --gray-300: #475569;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #94a3b8;
        --gray-700: #cbd5e1;
        --gray-800: #e2e8f0;
        --gray-900: #f1f5f9;
        --success-light: #064e3b;
        --warning-light: #78350f;
        --danger-light: #7f1d1d;
        --purple-light: #2e1065;
    }

    * { box-sizing: border-box; }

    .dashboard-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-dashboard);
        min-height: 100vh;
        transition: background 0.3s ease, color 0.3s ease;
        color: var(--text-dashboard);
    }

    /* ================= HEADER ================= */
    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 20px;
        padding: 28px 36px;
        margin-bottom: 28px;
        color: white;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(13, 59, 102, 0.25);
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -60%;
        right: -5%;
        width: 350px;
        height: 350px;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 50%;
        pointer-events: none;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -50%;
        left: 15%;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        pointer-events: none;
    }

    .dashboard-header .header-left {
        display: flex;
        flex-direction: column;
        gap: 4px;
        position: relative;
        z-index: 1;
    }

    .dashboard-header h1 {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .dashboard-header h1 i {
        font-size: 30px;
        opacity: 0.9;
        background: rgba(255,255,255,0.12);
        padding: 10px;
        border-radius: 14px;
        backdrop-filter: blur(4px);
    }

    .dashboard-header .subtitle {
        opacity: 0.8;
        margin: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 400;
    }

    .dashboard-header .subtitle i { font-size: 13px; }

    .status-legend {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        background: rgba(255,255,255,0.10);
        padding: 10px 24px;
        border-radius: 14px;
        backdrop-filter: blur(12px);
        flex-shrink: 0;
        position: relative;
        z-index: 1;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .status-legend span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 500;
        opacity: 0.95;
    }

    .status-legend .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dot-up { background: #10b981; box-shadow: 0 0 16px rgba(16,185,129,0.4); }
    .dot-warning { background: #f59e0b; box-shadow: 0 0 16px rgba(245,158,11,0.4); }
    .dot-down { background: #ef4444; box-shadow: 0 0 16px rgba(239,68,68,0.4); }

    /* ================= STATS GRID ================= */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 18px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--bg-card);
        border-radius: var(--radius-card);
        padding: 22px 24px;
        box-shadow: var(--shadow-dash);
        border: 1px solid var(--border-dash);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        cursor: pointer;
        color: var(--text-dashboard);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: 4px 0 0 4px;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        opacity: 0.03;
        pointer-events: none;
        transition: all 0.5s ease;
    }

    .stat-card:hover::after {
        transform: scale(1.5);
        opacity: 0.06;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover-dash);
        border-color: var(--gray-300);
    }

    .stat-card:active { transform: scale(0.97); }

    .stat-card.total::before { background: var(--primary); }
    .stat-card.total::after { background: var(--primary); }
    .stat-card.up::before { background: var(--success); }
    .stat-card.up::after { background: var(--success); }
    .stat-card.down::before { background: var(--danger); }
    .stat-card.down::after { background: var(--danger); }
    .stat-card.warning::before { background: var(--warning); }
    .stat-card.warning::after { background: var(--warning); }
    .stat-card.esp::before { background: var(--purple); }
    .stat-card.esp::after { background: var(--purple); }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .stat-header h3 {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        color: var(--gray-500);
        letter-spacing: 0.5px;
        text-transform: uppercase;
        transition: color 0.3s ease;
    }

    .stat-header i {
        font-size: 22px;
        opacity: 0.7;
        transition: var(--transition);
    }

    .stat-card:hover .stat-header i {
        transform: scale(1.1) rotate(-5deg);
        opacity: 1;
    }

    .stat-card.total .stat-header i { color: var(--primary); }
    .stat-card.up .stat-header i { color: var(--success); }
    .stat-card.down .stat-header i { color: var(--danger); }
    .stat-card.warning .stat-header i { color: var(--warning); }
    .stat-card.esp .stat-header i { color: var(--purple); }

    .stat-value {
        font-size: 2.6rem;
        font-weight: 800;
        margin-bottom: 2px;
        line-height: 1.1;
        letter-spacing: -1px;
        transition: color 0.3s ease;
    }

    .stat-card.total .stat-value { color: var(--primary); }
    .stat-card.up .stat-value { color: var(--success); }
    .stat-card.down .stat-value { color: var(--danger); }
    .stat-card.warning .stat-value { color: var(--warning); }
    .stat-card.esp .stat-value { color: var(--purple); }

    .stat-label {
        font-size: 12px;
        color: var(--gray-500);
        font-weight: 400;
        transition: color 0.3s ease;
    }

    .stat-clickable {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
        transition: var(--transition);
        background: var(--gray-100);
        color: var(--gray-500);
    }

    [data-theme="dark"] .stat-clickable {
        background: rgba(255,255,255,0.06);
        color: var(--gray-400);
    }

    .stat-card:hover .stat-clickable {
        background: var(--gray-200);
        transform: translateX(3px);
    }

    [data-theme="dark"] .stat-card:hover .stat-clickable {
        background: rgba(255,255,255,0.12);
    }

    .stat-clickable i { font-size: 11px; }

    .stat-card.total .stat-clickable { color: var(--primary); }
    .stat-card.up .stat-clickable { color: var(--success); }
    .stat-card.warning .stat-clickable { color: var(--warning); }
    .stat-card.down .stat-clickable { color: var(--danger); }

    /* ================= UPTIME CARD ================= */
    .uptime-card {
        background: var(--bg-card);
        border-radius: var(--radius-card);
        padding: 28px 36px;
        margin-bottom: 28px;
        box-shadow: var(--shadow-dash);
        border: 1px solid var(--border-dash);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .uptime-card:hover {
        box-shadow: var(--shadow-hover-dash);
        transform: translateY(-2px);
    }

    .uptime-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .uptime-left .uptime-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        background: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: var(--primary);
        flex-shrink: 0;
        transition: background 0.3s ease;
    }

    .uptime-left .uptime-info h3 {
        margin: 0 0 4px 0;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dashboard);
        transition: color 0.3s ease;
    }

    .uptime-left .uptime-info p {
        margin: 0;
        font-size: 13px;
        color: var(--gray-500);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        transition: color 0.3s ease;
    }

    .uptime-left .uptime-info .uptime-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        transition: var(--transition);
    }

    .uptime-left .uptime-info .uptime-status.excellent {
        background: var(--success-light);
        color: var(--success);
    }

    .uptime-left .uptime-info .uptime-status.good {
        background: var(--warning-light);
        color: var(--warning);
    }

    .uptime-left .uptime-info .uptime-status.poor {
        background: var(--danger-light);
        color: var(--danger);
    }

    .uptime-left .uptime-info .uptime-status.no-data {
        background: var(--gray-100);
        color: var(--gray-500);
    }

    .uptime-right {
        display: flex;
        align-items: center;
        gap: 28px;
        flex: 1;
        max-width: 600px;
        min-width: 200px;
    }

    .uptime-right .uptime-value {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
        white-space: nowrap;
        transition: color 0.3s ease;
    }

    .uptime-right .uptime-value.no-data {
        color: var(--gray-400);
        font-size: 2.2rem;
    }

    .uptime-right .uptime-value small {
        font-size: 1.2rem;
        font-weight: 400;
        color: var(--gray-400);
    }

    .uptime-right .uptime-bar-container {
        flex: 1;
        min-width: 100px;
    }

    .uptime-right .bar-track {
        width: 100%;
        height: 10px;
        background: var(--gray-200);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);
        transition: background 0.3s ease;
    }

    .uptime-right .bar-fill {
        height: 100%;
        border-radius: 20px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .uptime-right .bar-fill.green { 
        background: linear-gradient(90deg, #10b981, #059669); 
        box-shadow: 0 0 24px rgba(16, 185, 129, 0.25);
    }
    .uptime-right .bar-fill.yellow { 
        background: linear-gradient(90deg, #f59e0b, #d97706); 
        box-shadow: 0 0 24px rgba(245, 158, 11, 0.25);
    }
    .uptime-right .bar-fill.red { 
        background: linear-gradient(90deg, #ef4444, #dc2626); 
        box-shadow: 0 0 24px rgba(239, 68, 68, 0.25);
    }
    .uptime-right .bar-fill.gray { 
        background: linear-gradient(90deg, #cbd5e1, #94a3b8);
        animation: shimmer 2s infinite;
    }

    .uptime-right .bar-label {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: var(--gray-400);
        margin-top: 6px;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    @keyframes shimmer {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    /* ================= CHARTS GRID ================= */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: var(--bg-card);
        border-radius: var(--radius-card);
        padding: 24px 28px;
        box-shadow: var(--shadow-dash);
        border: 1px solid var(--border-dash);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .chart-card:hover {
        box-shadow: var(--shadow-hover-dash);
        transform: translateY(-2px);
    }

    .chart-card .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .chart-card h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dashboard);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }

    .chart-card h3 i {
        color: var(--primary);
        font-size: 18px;
        background: var(--gray-100);
        padding: 8px;
        border-radius: 10px;
        transition: background 0.3s ease;
    }

    [data-theme="dark"] .chart-card h3 i {
        background: var(--gray-700);
    }

    .chart-card .chart-badge {
        font-size: 11px;
        color: var(--gray-500);
        background: var(--gray-100);
        padding: 5px 16px;
        border-radius: 20px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: var(--transition);
    }

    [data-theme="dark"] .chart-card .chart-badge {
        background: var(--gray-700);
        color: var(--gray-400);
    }

    .chart-card .chart-badge i { font-size: 12px; }

    .chart-container {
        position: relative;
        height: 240px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chart-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--gray-400);
        text-align: center;
    }

    .chart-empty i {
        font-size: 48px;
        margin-bottom: 14px;
        opacity: 0.3;
        color: var(--gray-300);
    }

    .chart-empty h4 {
        color: var(--gray-600);
        margin: 0 0 6px 0;
        font-weight: 500;
        font-size: 15px;
        transition: color 0.3s ease;
    }

    .chart-empty p {
        margin: 0;
        font-size: 13px;
        color: var(--gray-400);
    }

    /* ================= DONUT CHART - TANPA TOTAL CHECK ================= */
    .donut-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .donut-chart-area {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }

    .donut-chart-area canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .donut-legend {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .donut-legend .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-dashboard);
        transition: color 0.3s ease;
    }

    .donut-legend .legend-item .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .donut-legend .legend-item .legend-dot.up { background: #10b981; }
    .donut-legend .legend-item .legend-dot.warning { background: #f59e0b; }
    .donut-legend .legend-item .legend-dot.down { background: #ef4444; }

    .donut-legend .legend-item .legend-percent {
        font-weight: 700;
        font-size: 15px;
        color: var(--text-dashboard);
        transition: color 0.3s ease;
    }

    .donut-legend .legend-item .legend-percent.up { color: #10b981; }
    .donut-legend .legend-item .legend-percent.warning { color: #f59e0b; }
    .donut-legend .legend-item .legend-percent.down { color: #ef4444; }

    /* ================= MODAL ================= */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.active { display: flex; }

    .modal-content {
        background: var(--bg-card);
        border-radius: 20px;
        max-width: 700px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        border: 1px solid var(--border-dash);
        color: var(--text-dashboard);
    }

    .modal-header {
        padding: 20px 28px;
        border-bottom: 1px solid var(--border-dash);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--gray-50);
        transition: all 0.3s ease;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dashboard);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }

    .modal-header h2 .status-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .modal-header h2 .status-dot.up { background: var(--success); }
    .modal-header h2 .status-dot.warning { background: var(--warning); }
    .modal-header h2 .status-dot.down { background: var(--danger); }
    .modal-header h2 .status-dot.total { background: var(--primary); }

    .modal-close {
        background: none;
        border: none;
        font-size: 28px;
        color: var(--gray-400);
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 8px;
        transition: var(--transition);
        line-height: 1;
    }

    .modal-close:hover {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    [data-theme="dark"] .modal-close:hover {
        background: var(--gray-700);
        color: var(--gray-200);
    }

    .modal-body {
        padding: 20px 28px;
        max-height: 55vh;
        overflow-y: auto;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 10px;
    }

    .modal-body .service-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-dash);
        transition: var(--transition);
        border-radius: 8px;
        color: var(--text-dashboard);
    }

    .modal-body .service-item:hover {
        background: var(--gray-50);
    }

    [data-theme="dark"] .modal-body .service-item:hover {
        background: var(--gray-700);
    }

    .modal-body .service-item:last-child {
        border-bottom: none;
    }

    .modal-body .service-item .service-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .modal-body .service-item .service-icon.color-1 { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .modal-body .service-item .service-icon.color-2 { background: linear-gradient(135deg, #10b981, #34d399); }
    .modal-body .service-item .service-icon.color-3 { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .modal-body .service-item .service-icon.color-4 { background: linear-gradient(135deg, #ef4444, #f87171); }
    .modal-body .service-item .service-icon.color-5 { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .modal-body .service-item .service-icon.color-6 { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
    .modal-body .service-item .service-icon.color-7 { background: linear-gradient(135deg, #ec4899, #f472b6); }
    .modal-body .service-item .service-icon.color-8 { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }

    .modal-body .service-item .service-info {
        flex: 1;
    }

    .modal-body .service-item .service-info .service-name {
        font-weight: 600;
        color: var(--text-dashboard);
        font-size: 14px;
        transition: color 0.3s ease;
    }

    .modal-body .service-item .service-info .service-detail {
        font-size: 12px;
        color: var(--gray-400);
        display: block;
        margin-top: 2px;
        transition: color 0.3s ease;
    }

    .modal-body .service-item .service-status {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 20px;
        flex-shrink: 0;
        transition: var(--transition);
    }

    .modal-body .service-item .service-status.up {
        background: var(--success-light);
        color: var(--success);
    }

    .modal-body .service-item .service-status.warning {
        background: var(--warning-light);
        color: var(--warning);
    }

    .modal-body .service-item .service-status.down {
        background: var(--danger-light);
        color: var(--danger);
    }

    .modal-body .empty-services {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray-400);
    }

    .modal-body .empty-services i {
        font-size: 40px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .modal-body .empty-services h4 {
        color: var(--gray-600);
        margin: 0 0 4px 0;
        font-weight: 500;
        font-size: 16px;
        transition: color 0.3s ease;
    }

    .modal-body .empty-services p {
        margin: 0;
        font-size: 13px;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-card { animation: fadeInUp 0.5s ease forwards; }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.20s; }
    .stat-card:nth-child(5) { animation-delay: 0.25s; }

    .uptime-card { animation: fadeInUp 0.5s ease 0.30s forwards; opacity: 0; }
    .chart-card { animation: fadeInUp 0.5s ease forwards; opacity: 0; }
    .chart-card:nth-child(1) { animation-delay: 0.35s; }
    .chart-card:nth-child(2) { animation-delay: 0.40s; }

    .auto-refresh-timer {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: rgba(10, 46, 92, 0.88);
        color: white;
        padding: 10px 18px;
        border-radius: 12px;
        z-index: 99999;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        user-select: none;
        cursor: default;
        transition: var(--transition);
    }

    .auto-refresh-timer:hover {
        transform: scale(1.05);
    }

    [data-theme="dark"] .auto-refresh-timer {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(255, 255, 255, 0.05);
    }

    .auto-refresh-timer .icon { font-size: 16px; }
    .auto-refresh-timer .label { opacity: 0.7; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 500; }
    .auto-refresh-timer .countdown {
        font-weight: 700;
        font-size: 16px;
        min-width: 42px;
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
        50% { opacity: 0.2; }
    }

    [data-theme="dark"] canvas {
        filter: brightness(0.9) contrast(1.1);
    }

    @media (max-width: 1024px) {
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
        .charts-grid { grid-template-columns: 1fr; }
        .uptime-card { flex-direction: column; align-items: stretch; }
        .uptime-right { max-width: 100%; flex-wrap: wrap; }
        .donut-chart-area { width: 180px; height: 180px; }
    }

    @media (max-width: 768px) {
        .dashboard-container { padding: 16px; }
        .dashboard-header {
            flex-direction: column;
            align-items: stretch;
            padding: 20px 24px;
            border-radius: 16px;
        }
        .dashboard-header h1 { font-size: 1.3rem; }
        .dashboard-header h1 i { font-size: 22px; padding: 8px; }
        .status-legend { justify-content: center; padding: 8px 16px; }
        .status-legend span { font-size: 11px; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .stat-card { padding: 16px 18px; }
        .stat-value { font-size: 2rem; }
        .uptime-card { padding: 20px 24px; }
        .uptime-left .uptime-icon { width: 48px; height: 48px; font-size: 22px; }
        .uptime-right .uptime-value { font-size: 2.4rem; }
        .charts-grid { grid-template-columns: 1fr; gap: 16px; }
        .chart-container { height: 200px; }
        .chart-card { padding: 18px 20px; }
        .chart-card .chart-header h3 { font-size: 14px; }
        .modal-content { width: 95%; max-height: 90vh; }
        .modal-header h2 { font-size: 16px; }
        .modal-body .service-item { padding: 10px 12px; flex-wrap: wrap; }
        .modal-body .service-item .service-status { font-size: 11px; padding: 2px 10px; }
        .stat-clickable { font-size: 9px; padding: 3px 10px; }
        .auto-refresh-timer {
            bottom: 12px;
            right: 12px;
            padding: 6px 14px;
            font-size: 11px;
        }
        .auto-refresh-timer .countdown { font-size: 14px; min-width: 32px; }
        .donut-legend { gap: 14px; }
        .donut-legend .legend-item { font-size: 11px; }
        .donut-chart-area { width: 160px; height: 160px; }
    }

    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
        .stat-card { padding: 12px 14px; border-radius: 12px; }
        .stat-value { font-size: 1.6rem; }
        .stat-header h3 { font-size: 10px; }
        .stat-header i { font-size: 16px; }
        .uptime-card { padding: 16px 18px; }
        .uptime-left { flex-wrap: wrap; }
        .uptime-right .uptime-value { font-size: 2rem; }
        .dashboard-header { padding: 16px 18px; }
        .dashboard-header h1 { font-size: 1.1rem; }
        .chart-card .chart-badge { font-size: 10px; padding: 3px 12px; }
        .modal-header { padding: 14px 16px; }
        .modal-body { padding: 14px 16px; }
        .modal-header h2 { font-size: 14px; }
        .stat-clickable { font-size: 8px; padding: 2px 8px; margin-top: 6px; }
        .donut-legend .legend-item { font-size: 10px; gap: 4px; }
        .donut-legend .legend-item .legend-percent { font-size: 11px; }
        .donut-chart-area { width: 140px; height: 140px; }
        .chart-container { height: 180px; }
    }
</style>

<div class="dashboard-container">
    <!-- ================= HEADER ================= -->
    <div class="dashboard-header">
        <div class="header-left">
            <h1>
                <i class="fas fa-chart-pie"></i>
                Dashboard Monitoring
            </h1>
            <p class="subtitle">
                <i class="fas fa-sync-alt fa-fw"></i>
                Ringkasan status layanan dan aktivitas sistem monitoring
            </p>
        </div>
        <div class="status-legend">
            <span><span class="dot dot-up"></span> UP</span>
            <span><span class="dot dot-warning"></span> WARNING</span>
            <span><span class="dot dot-down"></span> DOWN</span>
        </div>
    </div>

    <!-- ================= STATS GRID ================= -->
    @php
        $activeServices = $services->filter(function($s) {
            return !($s->is_archived ?? false);
        });
        
        $total = $activeServices->count();
        $up = $activeServices->where('last_status', 'UP')->count();
        $warning = $activeServices->where('last_status', 'WARNING')->count();
        $down = $activeServices->where('last_status', 'DOWN')->count();
        
        $onlineCount = $onlineCount ?? 0;
        $lastSmokeValue = $lastSmokeValue ?? 0;
        $lastSmokeStatus = $lastSmokeStatus ?? 'NORMAL';
        $lastSeenAt = $lastSeenAt ?? null;
    @endphp

    <div class="stats-grid">
        <div class="stat-card total" onclick="showModal('all', 'Semua Service', 'total')">
            <div class="stat-header">
                <h3>Total Service</h3>
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-value" id="statTotal">{{ $total }}</div>
            <div class="stat-label">Service terdaftar</div>
            <div class="stat-clickable">
                <i class="fas fa-chevron-circle-right"></i> Lihat Semua
            </div>
        </div>

        <div class="stat-card up" onclick="showModal('up', 'Service Running', 'up')">
            <div class="stat-header">
                <h3>Running</h3>
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value" id="statUp">{{ $up }}</div>
            <div class="stat-label">Berjalan normal</div>
            <div class="stat-clickable">
                <i class="fas fa-chevron-circle-right"></i> Lihat Detail
            </div>
        </div>

        <div class="stat-card warning" onclick="showModal('warning', 'Service Warning', 'warning')">
            <div class="stat-header">
                <h3>Warning</h3>
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-value" id="statWarning">{{ $warning }}</div>
            <div class="stat-label">Perlu perhatian</div>
            <div class="stat-clickable">
                <i class="fas fa-chevron-circle-right"></i> Lihat Detail
            </div>
        </div>

        <div class="stat-card down" onclick="showModal('down', 'Service Down', 'down')">
            <div class="stat-header">
                <h3>Down</h3>
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-value" id="statDown">{{ $down }}</div>
            <div class="stat-label">Perlu tindakan</div>
            <div class="stat-clickable">
                <i class="fas fa-chevron-circle-right"></i> Lihat Detail
            </div>
        </div>

        <div class="stat-card esp" id="espCard">
            <div class="stat-header">
                <h3>ESP Status</h3>
                <i class="fas fa-microchip"></i>
            </div>
            <div class="stat-value" id="espStatusDisplay" style="font-size: 1.6rem; display: flex; align-items: center; gap: 10px;">
                @php
                    $isOnline = ($onlineCount ?? 0) > 0;
                    $espDisplayStatus = $isOnline ? 'ONLINE' : 'OFFLINE';
                @endphp
                <span id="espDot" style="display: inline-block; width: 14px; height: 14px; border-radius: 50%; 
                    @if($espDisplayStatus == 'ONLINE') background: #10b981; box-shadow: 0 0 24px rgba(16,185,129,0.4);
                    @else background: #ef4444; box-shadow: 0 0 24px rgba(239,68,68,0.4);
                    @endif
                "></span>
                <span id="espStatusText">{{ $espDisplayStatus }}</span>
            </div>
            <div class="stat-label" id="espLastSeen">
                @if($isOnline && $lastSeenAt)
                    ✅ Terakhir: {{ \Carbon\Carbon::parse($lastSeenAt)->diffForHumans() }}
                @else
                    ❌ Tidak ada data (offline)
                @endif
            </div>
            <div class="stat-label" style="margin-top: 6px; font-size: 11px; color: var(--gray-400);">
                📊 Nilai Asap: <strong id="espSmokeValue" style="color: var(--text-dashboard);">{{ $lastSmokeValue }}</strong>
                | Status: <span id="espSmokeStatus" class="status-badge {{ strtolower($lastSmokeStatus) }}" style="font-size: 10px; padding: 2px 12px; border-radius: 12px; 
                    @if($lastSmokeStatus == 'DANGER') background: #fee2e2; color: #991b1b;
                    @elseif($lastSmokeStatus == 'WARNING') background: #fef3c7; color: #92400e;
                    @else background: #d1fae5; color: #065f46;
                    @endif
                ">
                    {{ $lastSmokeStatus }}
                </span>
            </div>
        </div>
    </div>

    <!-- ================= UPTIME CARD ================= -->
    @php
        $hasData = $total > 0;
        $uptime = $hasData ? (($up) / $total) * 100 : 0;
        $uptimeClass = $hasData ? ($uptime >= 90 ? 'green' : ($uptime >= 70 ? 'yellow' : 'red')) : 'gray';
        $percentDisplay = $hasData ? number_format($uptime, 2) : '—';
        $statusClass = $hasData ? ($uptime >= 90 ? 'excellent' : ($uptime >= 70 ? 'good' : 'poor')) : 'no-data';
        $statusText = $hasData ? ($uptime >= 90 ? 'Excellent' : ($uptime >= 70 ? 'Good' : 'Needs Attention')) : 'No Data';
        $statusIcon = $hasData ? ($uptime >= 90 ? 'fa-check-circle' : ($uptime >= 70 ? 'fa-exclamation-circle' : 'fa-times-circle')) : 'fa-minus-circle';
    @endphp

    <div class="uptime-card">
        <div class="uptime-left">
            <div class="uptime-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="uptime-info">
                <h3>Uptime Rate</h3>
                <p>
                    Status: 
                    <span class="uptime-status {{ $statusClass }}" id="uptimeStatusBadge">
                        <i class="fas {{ $statusIcon }}"></i>
                        {{ $statusText }}
                    </span>
                </p>
            </div>
        </div>
        <div class="uptime-right">
            <div class="uptime-value {{ $hasData ? '' : 'no-data' }}" id="uptimeValue">
                {{ $percentDisplay }}<small>%</small>
            </div>
            <div class="uptime-bar-container">
                <div class="bar-track">
                    @if($hasData)
                        <div class="bar-fill {{ $uptimeClass }}" id="uptimeBarFill" style="width: {{ $uptime }}%;"></div>
                    @else
                        <div class="bar-fill gray" id="uptimeBarFill" style="width: 0%;"></div>
                    @endif
                </div>
                <div class="bar-label">
                    <span>0%</span>
                    @if($hasData)
                        <span>{{ number_format($uptime, 2) }}%</span>
                    @else
                        <span style="color: var(--gray-400); font-style: italic;">Belum ada data</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ================= CHARTS GRID ================= -->
    <div class="charts-grid">
        <!-- Chart DONUT - TANPA TOTAL CHECK -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Status Service 7 Hari</h3>
                <span class="chart-badge"><i class="far fa-calendar-alt"></i> 7 Hari</span>
            </div>
            <div class="chart-container">
                @php
                    $hasDonutData = isset($donutUp) || isset($donutWarning) || isset($donutDown);
                    $donutUp = $donutUp ?? 0;
                    $donutWarning = $donutWarning ?? 0;
                    $donutDown = $donutDown ?? 0;
                    $donutTotal = $donutUp + $donutWarning + $donutDown;
                    
                    $upPercent = $donutTotal > 0 ? round(($donutUp / $donutTotal) * 100) : 0;
                    $warningPercent = $donutTotal > 0 ? round(($donutWarning / $donutTotal) * 100) : 0;
                    $downPercent = $donutTotal > 0 ? round(($donutDown / $donutTotal) * 100) : 0;
                @endphp

                @if($hasDonutData && $donutTotal > 0)
                    <div class="donut-wrapper">
                        <div class="donut-chart-area">
                            <canvas id="donutChart"></canvas>
                        </div>
                        <div class="donut-legend">
                            <span class="legend-item">
                                <span class="legend-dot up"></span>
                                UP
                                <span class="legend-percent up">{{ $upPercent }}%</span>
                            </span>
                            <span class="legend-item">
                                <span class="legend-dot warning"></span>
                                WARNING
                                <span class="legend-percent warning">{{ $warningPercent }}%</span>
                            </span>
                            <span class="legend-item">
                                <span class="legend-dot down"></span>
                                DOWN
                                <span class="legend-percent down">{{ $downPercent }}%</span>
                            </span>
                        </div>
                    </div>
                @else
                    <div class="chart-empty">
                        <i class="fas fa-chart-pie"></i>
                        <h4>Belum Ada Data Status</h4>
                        <p>Data akan muncul setelah ada service yang dimonitor</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Chart Smoke -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-fire-extinguisher"></i> Grafik Smoke Detector</h3>
                <span class="chart-badge"><i class="far fa-calendar-alt"></i> 7 Hari</span>
            </div>
            <div class="chart-container">
                @php
                    $smokeLabels = $smokeLabels ?? [];
                    $smokeData = $smokeData ?? [];
                    $hasSmokeData = count($smokeLabels) > 0 && count($smokeData) > 0;
                @endphp

                @if($hasSmokeData)
                    <canvas id="smokeChart"></canvas>
                @else
                    <div class="chart-empty">
                        <i class="fas fa-fire-extinguisher"></i>
                        <h4>Belum Ada Data Smoke Detector</h4>
                        <p>Data akan muncul setelah ada smoke detector yang terhubung</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ================= AUTO REFRESH TIMER ================= -->
<div class="auto-refresh-timer" id="autoRefreshTimer">
    <span class="icon">🔄</span>
    <span class="label">Refresh</span>
    <span class="countdown" id="countdownTimer">0:30</span>
</div>

<!-- ================= MODAL ================= -->
<div class="modal-overlay" id="serviceModal" onclick="if(event.target === this) closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">
                <span class="status-dot" id="modalDot"></span>
                <span id="modalTitleText">Daftar Service</span>
                <span style="font-size: 14px; font-weight: 400; color: var(--gray-400); margin-left: 6px;" id="modalCount"></span>
            </h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@if((isset($donutUp) || isset($donutWarning) || isset($donutDown)) || 
    (isset($smokeLabels) && isset($smokeData) && count($smokeLabels) > 0 && count($smokeData) > 0))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif

<script>
    // ====================== DATA ======================
    const allServices = @json($activeServices->values() ?? []);
    let chartInstances = {};
    let countdownSeconds = 30;
    const REFRESH_INTERVAL = 30;
    let isModalOpen = false;

    // ====================== MODAL ======================
    function showModal(status, title, dotClass) {
        const modal = document.getElementById('serviceModal');
        const modalTitle = document.getElementById('modalTitleText');
        const modalDot = document.getElementById('modalDot');
        const modalCount = document.getElementById('modalCount');
        const modalBody = document.getElementById('modalBody');

        modalTitle.textContent = title;
        modalDot.className = 'status-dot ' + dotClass;

        let filteredServices = [];
        if (status === 'all') {
            filteredServices = allServices;
        } else {
            filteredServices = allServices.filter(s => 
                s.last_status && s.last_status.toLowerCase() === status
            );
        }

        modalCount.textContent = `(${filteredServices.length})`;

        if (filteredServices.length === 0) {
            modalBody.innerHTML = `
                <div class="empty-services">
                    <i class="fas fa-inbox"></i>
                    <h4>Tidak Ada Service</h4>
                    <p>Belum ada service dengan status ${status.toUpperCase()}</p>
                </div>
            `;
        } else {
            let html = '';
            const colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8'];
            
            filteredServices.forEach((service, index) => {
                const colorClass = colors[index % colors.length];
                const initials = (service.name || '??').substring(0, 2).toUpperCase();
                const statusClass = (service.last_status || 'unknown').toLowerCase();
                const statusLabel = service.last_status || 'UNKNOWN';
                
                html += `
                    <div class="service-item">
                        <div class="service-icon ${colorClass}">${initials}</div>
                        <div class="service-info">
                            <span class="service-name">${service.name || 'Unnamed'}</span>
                            <span class="service-detail">${service.target || '-'}</span>
                        </div>
                        <span class="service-status ${statusClass}">${statusLabel}</span>
                    </div>
                `;
            });
            
            modalBody.innerHTML = html;
        }

        modal.classList.add('active');
        isModalOpen = true;
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('serviceModal');
        modal.classList.remove('active');
        isModalOpen = false;
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // ====================== RIPPLE ======================
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size/2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size/2) + 'px';
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple-animation 0.6s ease-out';
            ripple.style.pointerEvents = 'none';
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple-animation {
            to { transform: scale(4); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // ====================== COUNTDOWN ======================
    function updateCountdown() {
        countdownSeconds--;
        const el = document.getElementById('countdownTimer');
        if (el) {
            const secs = countdownSeconds.toString().padStart(2, '0');
            el.textContent = '0:' + secs;
            el.className = 'countdown';
            if (countdownSeconds < 5) el.classList.add('danger');
            else if (countdownSeconds < 10) el.classList.add('warning');
        }
        if (countdownSeconds <= 0) {
            countdownSeconds = REFRESH_INTERVAL;
            if (!isModalOpen) fetchDashboardData();
        }
    }

    // ====================== FETCH ======================
    function fetchDashboardData() {
        fetch('/dashboard/data?_=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStats(data.data.stats);
                updateEspStatus(data.data.esp);
                updateCharts(data.data.charts);
            }
        })
        .catch(error => console.error('Error fetching dashboard data:', error));
    }

    function updateStats(stats) {
        animateNumber('statTotal', stats.total);
        animateNumber('statUp', stats.up);
        animateNumber('statWarning', stats.warning);
        animateNumber('statDown', stats.down);
        
        const uptimeValue = document.getElementById('uptimeValue');
        const barFill = document.getElementById('uptimeBarFill');
        const statusBadge = document.getElementById('uptimeStatusBadge');
        
        if (uptimeValue) {
            const hasData = stats.total > 0;
            const uptime = hasData ? stats.uptime : 0;
            uptimeValue.innerHTML = `${hasData ? uptime.toFixed(2) : '—'}<small>%</small>`;
            if (!hasData) uptimeValue.classList.add('no-data');
            else uptimeValue.classList.remove('no-data');
        }
        
        if (barFill) {
            const hasData = stats.total > 0;
            const uptime = hasData ? stats.uptime : 0;
            const cls = hasData ? (uptime >= 90 ? 'green' : (uptime >= 70 ? 'yellow' : 'red')) : 'gray';
            barFill.className = `bar-fill ${cls}`;
            barFill.style.width = hasData ? `${uptime}%` : '0%';
        }
        
        if (statusBadge) {
            const hasData = stats.total > 0;
            const uptime = hasData ? stats.uptime : 0;
            const cls = hasData ? (uptime >= 90 ? 'excellent' : (uptime >= 70 ? 'good' : 'poor')) : 'no-data';
            const text = hasData ? (uptime >= 90 ? 'Excellent' : (uptime >= 70 ? 'Good' : 'Needs Attention')) : 'No Data';
            const icon = hasData ? (uptime >= 90 ? 'fa-check-circle' : (uptime >= 70 ? 'fa-exclamation-circle' : 'fa-times-circle')) : 'fa-minus-circle';
            statusBadge.className = `uptime-status ${cls}`;
            statusBadge.innerHTML = `<i class="fas ${icon}"></i> ${text}`;
        }
    }

    function animateNumber(id, target) {
        const el = document.getElementById(id);
        if (!el) return;
        const current = parseInt(el.textContent) || 0;
        if (current === target) return;
        const duration = 300;
        const startTime = performance.now();
        const startValue = current;
        function update(time) {
            const elapsed = time - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(startValue + (target - startValue) * ease);
            if (progress < 1) requestAnimationFrame(update);
            else el.textContent = target;
        }
        requestAnimationFrame(update);
    }

    function updateEspStatus(esp) {
        const dot = document.getElementById('espDot');
        if (dot) {
            dot.style.background = esp.online ? '#10b981' : '#ef4444';
            dot.style.boxShadow = esp.online ? '0 0 24px rgba(16,185,129,0.4)' : '0 0 24px rgba(239,68,68,0.4)';
        }
        const statusText = document.getElementById('espStatusText');
        if (statusText) statusText.textContent = esp.status;
        const lastSeen = document.getElementById('espLastSeen');
        if (lastSeen) {
            lastSeen.textContent = esp.online ? `✅ Terakhir: ${esp.last_seen_human || 'baru saja'}` : '❌ Tidak ada data (offline)';
        }
        const smokeValue = document.getElementById('espSmokeValue');
        if (smokeValue) smokeValue.textContent = esp.smoke_value;
        const statusBadge = document.getElementById('espSmokeStatus');
        if (statusBadge) {
            const status = esp.smoke_status || 'NORMAL';
            statusBadge.textContent = status;
            statusBadge.className = 'status-badge ' + status.toLowerCase();
            if (status === 'DANGER') {
                statusBadge.style.background = '#fee2e2';
                statusBadge.style.color = '#991b1b';
            } else if (status === 'WARNING') {
                statusBadge.style.background = '#fef3c7';
                statusBadge.style.color = '#92400e';
            } else {
                statusBadge.style.background = '#d1fae5';
                statusBadge.style.color = '#065f46';
            }
        }
    }

    function updateCharts(charts) {
        if (chartInstances.donut && charts.donut) {
            const up = charts.donut.up || 0;
            const warning = charts.donut.warning || 0;
            const down = charts.donut.down || 0;
            const total = up + warning + down;
            
            chartInstances.donut.data.datasets[0].data = [up, warning, down];
            chartInstances.donut.update();
            
            const items = document.querySelectorAll('.donut-legend .legend-item');
            if (items.length >= 3) {
                const upPct = total > 0 ? Math.round((up / total) * 100) : 0;
                const warnPct = total > 0 ? Math.round((warning / total) * 100) : 0;
                const downPct = total > 0 ? Math.round((down / total) * 100) : 0;
                items[0].innerHTML = `<span class="legend-dot up"></span> UP <span class="legend-percent up">${upPct}%</span>`;
                items[1].innerHTML = `<span class="legend-dot warning"></span> WARNING <span class="legend-percent warning">${warnPct}%</span>`;
                items[2].innerHTML = `<span class="legend-dot down"></span> DOWN <span class="legend-percent down">${downPct}%</span>`;
            }
        }
        
        if (chartInstances.smoke && charts.smoke_labels && charts.smoke_data) {
            chartInstances.smoke.data.labels = charts.smoke_labels;
            chartInstances.smoke.data.datasets[0].data = charts.smoke_data;
            chartInstances.smoke.update();
        }
    }

    // ====================== INIT CHARTS ======================
    function initCharts() {
        @php
            $donutUp = $donutUp ?? 0;
            $donutWarning = $donutWarning ?? 0;
            $donutDown = $donutDown ?? 0;
            $donutTotal = $donutUp + $donutWarning + $donutDown;
        @endphp

        @if(isset($donutUp) || isset($donutWarning) || isset($donutDown))
        {
            const ctx = document.getElementById('donutChart');
            if (ctx) {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                chartInstances.donut = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['UP', 'WARNING', 'DOWN'],
                        datasets: [{
                            data: [{{ $donutUp }}, {{ $donutWarning }}, {{ $donutDown }}],
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                            borderColor: isDark ? '#1e293b' : '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 10,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(15, 23, 42, 0.92)',
                                titleFont: { size: 12, weight: '600' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + pct + '%';
                                    }
                                }
                            }
                        },
                        animation: { animateRotate: true, duration: 1000 }
                    }
                });
            }
        }
        @endif

        @if(isset($smokeLabels) && isset($smokeData) && count($smokeLabels) > 0 && count($smokeData) > 0)
        {
            const ctx2 = document.getElementById('smokeChart');
            if (ctx2) {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                const textColor = isDark ? '#94a3b8' : '#64748b';
                const gridColor = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)';
                
                chartInstances.smoke = new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: @json($smokeLabels),
                        datasets: [{
                            label: 'Nilai Asap',
                            data: @json($smokeData),
                            backgroundColor: isDark ? 'rgba(239, 68, 68, 0.4)' : 'rgba(239, 68, 68, 0.6)',
                            borderColor: '#ef4444',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            maxBarThickness: 50,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(15, 23, 42, 0.92)',
                                titleFont: { size: 12, weight: '600' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) { return '🔥 ' + context.parsed.y; }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Nilai Asap', font: { size: 11, weight: '500' }, color: textColor },
                                grid: { color: gridColor, drawBorder: false },
                                ticks: { font: { size: 10 }, color: textColor, maxTicksLimit: 8 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 }, color: textColor, maxTicksLimit: 7 }
                            }
                        },
                        interaction: { intersect: false, mode: 'index' }
                    }
                });
            }
        }
        @endif
    }

    // ====================== DARK MODE ======================
    function updateChartsForTheme() {
        if (chartInstances.donut) chartInstances.donut.update();
        if (chartInstances.smoke) chartInstances.smoke.update();
    }

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(m) {
            if (m.attributeName === 'data-theme') {
                setTimeout(updateChartsForTheme, 300);
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });

    // ====================== ESP STATUS ======================
    function fetchEspStatus() {
        fetch('/api/smoke/status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const esp = data.data;
                    const isOnline = esp.device_status === 'ONLINE';
                    const dot = document.getElementById('espDot');
                    if (dot) {
                        dot.style.background = isOnline ? '#10b981' : '#ef4444';
                        dot.style.boxShadow = isOnline ? '0 0 24px rgba(16,185,129,0.4)' : '0 0 24px rgba(239,68,68,0.4)';
                    }
                    const statusText = document.getElementById('espStatusText');
                    if (statusText) statusText.textContent = esp.device_status;
                    const lastSeen = document.getElementById('espLastSeen');
                    if (lastSeen) {
                        lastSeen.textContent = isOnline ? `✅ Terakhir: ${esp.last_seen_human || 'baru saja'}` : '❌ Tidak ada data (offline)';
                    }
                    const smokeValue = document.getElementById('espSmokeValue');
                    if (smokeValue) {
                        smokeValue.textContent = esp.adc || esp.smoke_value || esp.ppm || 0;
                    }
                    const statusBadge = document.getElementById('espSmokeStatus');
                    if (statusBadge) {
                        const status = esp.status || 'NORMAL';
                        statusBadge.textContent = status;
                        statusBadge.className = 'status-badge ' + status.toLowerCase();
                        if (status === 'DANGER') {
                            statusBadge.style.background = '#fee2e2';
                            statusBadge.style.color = '#991b1b';
                        } else if (status === 'WARNING') {
                            statusBadge.style.background = '#fef3c7';
                            statusBadge.style.color = '#92400e';
                        } else {
                            statusBadge.style.background = '#d1fae5';
                            statusBadge.style.color = '#065f46';
                        }
                    }
                }
            })
            .catch(error => console.error('Error fetching ESP status:', error));
    }

    // ====================== INIT ======================
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        setInterval(updateCountdown, 1000);
        setTimeout(fetchEspStatus, 1000);
        setInterval(fetchEspStatus, 5000);
        setTimeout(fetchDashboardData, 2000);
        setInterval(fetchDashboardData, REFRESH_INTERVAL * 1000);
    });

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (chartInstances.donut) chartInstances.donut.resize();
            if (chartInstances.smoke) chartInstances.smoke.resize();
        }, 250);
    });
</script>
@endsection