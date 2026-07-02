@extends('layouts.app')

@section('content')
<style>
    /* ================= ROOT VARIABLES ================= */
    :root {
        --primary: #0d3b66;
        --primary-dark: #082a4a;
        --primary-light: #1a4d7a;
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
    }

    * {
        box-sizing: border-box;
    }

    .dashboard-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #ffffff;
        min-height: 100vh;
    }

    /* ================= HEADER ================= */
    .dashboard-header {
        background: linear-gradient(135deg, #0d3b66 0%, #1a4d7a 50%, #2563eb 100%);
        border-radius: 20px;
        padding: 24px 32px;
        margin-bottom: 24px;
        color: white;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(13, 59, 102, 0.3);
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -40%;
        left: 20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        pointer-events: none;
    }

    .dashboard-header .header-left {
        display: flex;
        flex-direction: column;
        gap: 2px;
        position: relative;
        z-index: 1;
    }

    .dashboard-header h1 {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .dashboard-header h1 i {
        font-size: 28px;
        opacity: 0.9;
        background: rgba(255,255,255,0.15);
        padding: 8px;
        border-radius: 12px;
    }

    .dashboard-header .subtitle {
        opacity: 0.85;
        margin: 0;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 400;
    }

    .dashboard-header .subtitle i {
        font-size: 12px;
    }

    .status-legend {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        background: rgba(255,255,255,0.12);
        padding: 8px 20px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
        flex-shrink: 0;
        position: relative;
        z-index: 1;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .status-legend span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 500;
        opacity: 0.95;
    }

    .status-legend .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        box-shadow: 0 0 12px rgba(255,255,255,0.2);
    }

    .dot-up { background: #10b981; }
    .dot-warning { background: #f59e0b; }
    .dot-down { background: #ef4444; }

    /* ================= STATS GRID ================= */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
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
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        border-color: var(--gray-300);
    }

    .stat-card:active {
        transform: scale(0.97);
    }

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
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-600);
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .stat-header i {
        font-size: 20px;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .stat-card:hover .stat-header i {
        transform: scale(1.1) rotate(-5deg);
    }

    .stat-card.total .stat-header i { color: var(--primary); }
    .stat-card.up .stat-header i { color: var(--success); }
    .stat-card.down .stat-header i { color: var(--danger); }
    .stat-card.warning .stat-header i { color: var(--warning); }
    .stat-card.esp .stat-header i { color: var(--purple); }

    .stat-value {
        font-size: 2.4rem;
        font-weight: 800;
        margin-bottom: 2px;
        line-height: 1.1;
        letter-spacing: -1px;
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
    }

    .stat-clickable {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        background: rgba(0,0,0,0.06);
        color: var(--gray-500);
    }

    .stat-card:hover .stat-clickable {
        background: rgba(0,0,0,0.1);
        transform: translateX(2px);
    }

    .stat-clickable i {
        font-size: 11px;
    }

    .stat-card.total .stat-clickable { color: var(--primary); }
    .stat-card.up .stat-clickable { color: var(--success); }
    .stat-card.warning .stat-clickable { color: var(--warning); }
    .stat-card.down .stat-clickable { color: var(--danger); }

    /* Click Ripple Effect */
    .stat-card .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }

    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    /* ================= UPTIME CARD ================= */
    .uptime-card {
        background: white;
        border-radius: 16px;
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .uptime-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .uptime-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .uptime-left .uptime-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: #e0e7ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: var(--primary);
        flex-shrink: 0;
    }

    .uptime-left .uptime-info h3 {
        margin: 0 0 2px 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-800);
    }

    .uptime-left .uptime-info p {
        margin: 0;
        font-size: 13px;
        color: var(--gray-500);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .uptime-left .uptime-info .uptime-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
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
        gap: 24px;
        flex: 1;
        max-width: 600px;
        min-width: 200px;
    }

    .uptime-right .uptime-value {
        font-size: 2.8rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
        white-space: nowrap;
    }

    .uptime-right .uptime-value.no-data {
        color: var(--gray-400);
        font-size: 2rem;
    }

    .uptime-right .uptime-value small {
        font-size: 1.2rem;
        font-weight: 400;
        color: var(--gray-400);
    }

    .uptime-right .uptime-bar-container {
        flex: 1;
        min-width: 80px;
    }

    .uptime-right .bar-track {
        width: 100%;
        height: 8px;
        background: var(--gray-200);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
    }

    .uptime-right .bar-fill {
        height: 100%;
        border-radius: 20px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .uptime-right .bar-fill.green { 
        background: linear-gradient(90deg, #10b981, #059669); 
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }
    .uptime-right .bar-fill.yellow { 
        background: linear-gradient(90deg, #f59e0b, #d97706); 
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
    }
    .uptime-right .bar-fill.red { 
        background: linear-gradient(90deg, #ef4444, #dc2626); 
        box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
    }
    .uptime-right .bar-fill.gray { 
        background: linear-gradient(90deg, #cbd5e1, #94a3b8);
        animation: shimmer 2s infinite;
    }

    .uptime-right .bar-label {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        color: var(--gray-400);
        margin-top: 4px;
        font-weight: 500;
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
        gap: 20px;
        margin-bottom: 24px;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: all 0.3s ease;
    }

    .chart-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .chart-card .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .chart-card h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-card h3 i {
        color: var(--primary);
        font-size: 18px;
    }

    .chart-card .chart-badge {
        font-size: 11px;
        color: var(--gray-500);
        background: var(--gray-100);
        padding: 4px 14px;
        border-radius: 20px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .chart-card .chart-badge i {
        font-size: 11px;
    }

    .chart-container {
        position: relative;
        height: 230px;
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
        font-size: 44px;
        margin-bottom: 12px;
        opacity: 0.3;
        color: var(--gray-300);
    }

    .chart-empty h4 {
        color: var(--gray-600);
        margin: 0 0 4px 0;
        font-weight: 500;
        font-size: 14px;
    }

    .chart-empty p {
        margin: 0;
        font-size: 12px;
        color: var(--gray-400);
    }

    /* ================= MODAL SERVICE ================= */
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

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        max-width: 700px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--gray-50);
    }

    .modal-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 10px;
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
        font-size: 24px;
        color: var(--gray-400);
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
        line-height: 1;
    }

    .modal-close:hover {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .modal-body {
        padding: 20px 24px;
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
        padding: 12px 14px;
        border-bottom: 1px solid var(--gray-100);
        transition: all 0.2s ease;
        border-radius: 8px;
    }

    .modal-body .service-item:hover {
        background: var(--gray-50);
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
        color: var(--gray-800);
        font-size: 14px;
    }

    .modal-body .service-item .service-info .service-detail {
        font-size: 12px;
        color: var(--gray-400);
        display: block;
        margin-top: 1px;
    }

    .modal-body .service-item .service-status {
        font-size: 12px;
        font-weight: 600;
        padding: 3px 12px;
        border-radius: 20px;
        flex-shrink: 0;
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
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* ================= ANIMATIONS ================= */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card {
        animation: fadeInUp 0.5s ease forwards;
    }

    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.20s; }
    .stat-card:nth-child(5) { animation-delay: 0.25s; }

    .uptime-card {
        animation: fadeInUp 0.5s ease 0.30s forwards;
        opacity: 0;
    }

    .chart-card {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }

    .chart-card:nth-child(1) { animation-delay: 0.35s; }
    .chart-card:nth-child(2) { animation-delay: 0.40s; }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .charts-grid {
            grid-template-columns: 1fr;
        }
        .uptime-card {
            flex-direction: column;
            align-items: stretch;
        }
        .uptime-right {
            max-width: 100%;
            flex-wrap: wrap;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 16px;
        }

        .dashboard-header {
            flex-direction: column;
            align-items: stretch;
            padding: 20px 24px;
            border-radius: 16px;
        }

        .dashboard-header h1 {
            font-size: 1.2rem;
        }

        .status-legend {
            justify-content: center;
            padding: 6px 14px;
        }

        .status-legend span {
            font-size: 10px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stat-card {
            padding: 16px 18px;
        }

        .stat-value {
            font-size: 1.8rem;
        }

        .uptime-card {
            padding: 20px 24px;
        }

        .uptime-left .uptime-icon {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }

        .uptime-right .uptime-value {
            font-size: 2.2rem;
        }

        .charts-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .chart-container {
            height: 200px;
        }

        .chart-card {
            padding: 16px 18px;
        }

        .chart-card .chart-header h3 {
            font-size: 14px;
        }

        .modal-content {
            width: 95%;
            max-height: 90vh;
        }

        .modal-header h2 {
            font-size: 16px;
        }

        .modal-body .service-item {
            padding: 10px 12px;
            flex-wrap: wrap;
        }

        .modal-body .service-item .service-status {
            font-size: 11px;
            padding: 2px 10px;
        }

        .stat-clickable {
            font-size: 9px;
            padding: 3px 10px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-card {
            padding: 12px 14px;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 1.4rem;
        }

        .stat-header h3 {
            font-size: 10px;
        }

        .stat-header i {
            font-size: 16px;
        }

        .uptime-card {
            padding: 16px 18px;
        }

        .uptime-left {
            flex-wrap: wrap;
        }

        .uptime-right .uptime-value {
            font-size: 1.8rem;
        }

        .dashboard-header {
            padding: 16px 18px;
        }

        .dashboard-header h1 {
            font-size: 1rem;
        }

        .chart-card .chart-badge {
            font-size: 10px;
            padding: 3px 10px;
        }

        .modal-header {
            padding: 14px 16px;
        }

        .modal-body {
            padding: 14px 16px;
        }

        .modal-header h2 {
            font-size: 14px;
        }

        .stat-clickable {
            font-size: 8px;
            padding: 2px 8px;
            margin-top: 6px;
        }
    }
</style>

<div class="dashboard-container">
    <!-- ================= HEADER ================= -->
    <div class="dashboard-header">
        <div class="header-left">
            <h1>
                <i class="fas fa-chart-line"></i>
                Dashboard Monitoring
            </h1>
            <p class="subtitle">
                <i class="fas fa-info-circle"></i>
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
        $total = $total ?? 0;
        $up = $up ?? 0;
        $warning = $warning ?? 0;
        $down = $down ?? 0;
        $onlineCount = $onlineCount ?? 0;
        $services = $services ?? collect();
    @endphp

    <div class="stats-grid">
        <div class="stat-card total" onclick="showModal('all', 'Semua Service', 'total')">
            <div class="stat-header">
                <h3>Total Service</h3>
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-value">{{ $total }}</div>
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
            <div class="stat-value">{{ $up }}</div>
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
            <div class="stat-value">{{ $warning }}</div>
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
            <div class="stat-value">{{ $down }}</div>
            <div class="stat-label">Perlu tindakan</div>
            <div class="stat-clickable">
                <i class="fas fa-chevron-circle-right"></i> Lihat Detail
            </div>
        </div>

        <!-- ================= ESP STATUS ================= -->
        <div class="stat-card esp">
            <div class="stat-header">
                <h3>ESP Status</h3>
                <i class="fas fa-microchip"></i>
            </div>
            <div class="stat-value" style="font-size: 1.6rem; display: flex; align-items: center; gap: 8px;">
                @php
                    $isOnline = ($onlineCount ?? 0) > 0;
                    $espDisplayStatus = $isOnline ? 'ONLINE' : 'OFFLINE';
                @endphp
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; 
                    @if($espDisplayStatus == 'ONLINE') background: #10b981; box-shadow: 0 0 20px rgba(16,185,129,0.4);
                    @else background: #ef4444; box-shadow: 0 0 20px rgba(239,68,68,0.4);
                    @endif
                "></span>
                {{ $espDisplayStatus }}
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
                    <span class="uptime-status {{ $statusClass }}">
                        <i class="fas {{ $statusIcon }}"></i>
                        {{ $statusText }}
                    </span>
                </p>
            </div>
        </div>
        <div class="uptime-right">
            <div class="uptime-value {{ $hasData ? '' : 'no-data' }}">
                {{ $percentDisplay }}<small>%</small>
            </div>
            <div class="uptime-bar-container">
                <div class="bar-track">
                    @if($hasData)
                        <div class="bar-fill {{ $uptimeClass }}" style="width: {{ $uptime }}%;"></div>
                    @else
                        <div class="bar-fill gray" style="width: 0%;"></div>
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

    <!-- ================= CHARTS GRID (7 HARI - PER HARI) ================= -->
    <div class="charts-grid">
        <!-- Chart Service - UPTIME 7 HARI -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-area"></i> Uptime 7 Hari Terakhir</h3>
                <span class="chart-badge"><i class="far fa-clock"></i> 7 Hari</span>
            </div>
            <div class="chart-container">
                @php
                    $chartLabels = $chartLabels ?? [];
                    $uptimeData = $uptimeData ?? [];
                    $hasChartData = count($chartLabels) > 0 && count($uptimeData) > 0;
                @endphp

                @if($hasChartData)
                    <canvas id="uptimeChart"></canvas>
                @else
                    <div class="chart-empty">
                        <i class="fas fa-chart-line"></i>
                        <h4>Belum Ada Data Uptime</h4>
                        <p>Data akan muncul setelah ada service yang dimonitor</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Chart Smoke Detector -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-fire-extinguisher"></i> Grafik Smoke Detector</h3>
                <span class="chart-badge"><i class="far fa-clock"></i> 7 Hari Terakhir</span>
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

<!-- ================= MODAL SERVICE ================= -->
<div class="modal-overlay" id="serviceModal" onclick="if(event.target === this) closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">
                <span class="status-dot" id="modalDot"></span>
                <span id="modalTitleText">Daftar Service</span>
                <span style="font-size: 14px; font-weight: 400; color: var(--gray-400); margin-left: 4px;" id="modalCount"></span>
            </h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content akan diisi oleh JavaScript -->
        </div>
    </div>
</div>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@if((isset($chartLabels) && isset($uptimeData) && count($chartLabels) > 0 && count($uptimeData) > 0) || 
    (isset($smokeLabels) && isset($smokeData) && count($smokeLabels) > 0 && count($smokeData) > 0))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ====================== UPTIME CHART (7 Hari) ======================
        @if(isset($chartLabels) && isset($uptimeData) && count($chartLabels) > 0 && count($uptimeData) > 0)
        {
            const ctx1 = document.getElementById('uptimeChart');
            if (ctx1) {
                const isMobile = window.innerWidth < 576;
                const gradient = ctx1.getContext('2d').createLinearGradient(0, 0, 0, 200);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.01)');

                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Uptime %',
                            data: @json($uptimeData),
                            borderColor: '#4f46e5',
                            backgroundColor: gradient,
                            borderWidth: 2.5,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: function(context) {
                                const value = context.dataset.data[context.dataIndex];
                                if (value >= 95) return '#10b981';
                                if (value >= 70) return '#f59e0b';
                                return '#ef4444';
                            },
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: isMobile ? 4 : 6,
                            pointHoverRadius: isMobile ? 7 : 9,
                            pointHoverBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                titleFont: { size: 12, weight: '600' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        let status = '✅ UP';
                                        if (value < 70) status = '❌ DOWN';
                                        else if (value < 95) status = '⚠️ WARNING';
                                        return '📊 ' + value.toFixed(1) + '% ' + status;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: isMobile ? 'Uptime %' : 'Uptime (%)',
                                    font: { size: isMobile ? 9 : 11, weight: '500' },
                                    color: '#94a3b8'
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.04)',
                                    drawBorder: false,
                                    drawTicks: false,
                                },
                                ticks: {
                                    font: { size: isMobile ? 8 : 10 },
                                    color: '#94a3b8',
                                    maxTicksLimit: isMobile ? 5 : 8,
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: isMobile ? 8 : 10 },
                                    color: '#94a3b8',
                                    maxTicksLimit: 7,
                                }
                            }
                        },
                        interaction: { intersect: false, mode: 'index' },
                        elements: { line: { borderJoinStyle: 'round' } }
                    }
                });
            }
        }
        @endif

        // ====================== SMOKE CHART ======================
        @if(isset($smokeLabels) && isset($smokeData) && count($smokeLabels) > 0 && count($smokeData) > 0)
        {
            const ctx2 = document.getElementById('smokeChart');
            if (ctx2) {
                const isMobile = window.innerWidth < 576;
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: @json($smokeLabels),
                        datasets: [{
                            label: 'Smoke Level (ppm)',
                            data: @json($smokeData),
                            backgroundColor: 'rgba(239, 68, 68, 0.6)',
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
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                titleFont: { size: 12, weight: '600' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        return '🔥 ' + context.parsed.y + ' ppm';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: isMobile ? 'ppm' : 'Nilai Asap (ppm)',
                                    font: { size: isMobile ? 9 : 11, weight: '500' },
                                    color: '#94a3b8'
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.04)',
                                    drawBorder: false,
                                    drawTicks: false,
                                },
                                ticks: {
                                    font: { size: isMobile ? 8 : 10 },
                                    color: '#94a3b8',
                                    maxTicksLimit: isMobile ? 5 : 8,
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: isMobile ? 8 : 10 },
                                    color: '#94a3b8',
                                    maxTicksLimit: 7,
                                }
                            }
                        },
                        interaction: { intersect: false, mode: 'index' }
                    }
                });
            }
        }
        @endif

        // Resize handler
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Charts will auto-resize
            }, 250);
        });
    });
</script>
@endif

<script>
    // ====================== DATA SERVICES ======================
    const allServices = @json($services ?? []);

    // ====================== SERVICE MODAL ======================
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
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('serviceModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // ====================== KEYBOARD SHORTCUTS ======================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // ====================== RIPPLE EFFECT ======================
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size/2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size/2) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // ====================== AUTO REFRESH ======================
    let refreshInterval = setTimeout(function() {
        location.reload();
    }, 60000);

    window.addEventListener('beforeunload', function() {
        clearTimeout(refreshInterval);
    });
</script>
@endsection