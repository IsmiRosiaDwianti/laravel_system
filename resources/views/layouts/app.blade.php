<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring System DISKOMINFOTIK</title>

    <style>
        /* ====================== SEMUA STYLE YANG SUDAH ADA ====================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f8;
            color: #1e293b;
            min-height: 100vh;
        }

        /* ====================== SIDEBAR ====================== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 270px;
            height: 100vh;
            background: linear-gradient(180deg, #0f2b4b 0%, #1a3a5c 50%, #0f2b4b 100%);
            color: #e8edf5;
            padding: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(15, 43, 75, 0.3);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-header {
            padding: 28px 24px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
        }

        .sidebar .logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .sidebar .logo .logo-image {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 12px;
            flex-shrink: 0;
            background: white;
            padding: 4px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .sidebar .logo .logo-text h2 {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.3px;
            color: #ffffff;
            line-height: 1.2;
        }

        .sidebar .logo .logo-text .subtitle {
            font-size: 11px;
            opacity: 0.7;
            font-weight: 400;
            display: block;
            margin-top: 2px;
            letter-spacing: 0.5px;
            color: #94b8d9;
        }

        .sidebar-nav {
            padding: 16px 16px 24px;
        }

        .sidebar .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.3);
            margin: 12px 0 8px 14px;
            font-weight: 600;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 10px;
            transition: all 0.25s ease;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 2px;
            position: relative;
        }

        .sidebar a i {
            width: 22px;
            text-align: center;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.4);
            transition: all 0.25s ease;
        }

        .sidebar a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(4px);
        }

        .sidebar a:hover i {
            color: #60a5fa;
        }

        .sidebar a.active {
            background: rgba(37, 99, 235, 0.2);
            color: #60a5fa;
            box-shadow: inset 3px 0 0 #3b82f6;
        }

        .sidebar a.active i {
            color: #60a5fa;
        }

        .sidebar a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: linear-gradient(180deg, #3b82f6, #2563eb);
            border-radius: 0 3px 3px 0;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        /* ====================== MAIN CONTENT ====================== */
        .main {
            margin-left: 270px;
            padding: 24px 32px 32px;
            min-height: 100vh;
            transition: all 0.3s ease;
            background: #f0f4f8;
        }

        /* ====================== TOP BAR ====================== */
        .topbar {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            border: 1px solid #e8edf5;
        }

        .topbar .page-title {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .topbar .page-title h1 {
            font-size: 20px;
            font-weight: 700;
            color: #0f2b4b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Poppins', 'Inter', sans-serif;
            letter-spacing: -0.5px;
        }

        .topbar .page-title h1 .welcome-icon {
            color: #f59e0b;
            font-size: 22px;
        }

        .topbar .page-title h1 .username {
            color: #2563eb;
            font-weight: 700;
        }

        .topbar .page-title .breadcrumb {
            font-size: 13px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 400;
        }

        .topbar .right-section {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .topbar .right-section .time {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 6px 14px;
            border-radius: 10px;
            border: 1px solid #e8edf5;
            font-weight: 500;
            font-family: 'Poppins', 'Inter', sans-serif;
            min-width: 120px;
            justify-content: center;
        }

        .topbar .right-section .time i {
            color: #3b82f6;
        }

        /* ====================== USER DROPDOWN ====================== */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-dropdown .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.25s ease;
            border: 2px solid transparent;
        }

        .user-dropdown .avatar:hover {
            transform: scale(1.05);
            border-color: #60a5fa;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
        }

        .user-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            padding: 8px 0;
            z-index: 1000;
            border: 1px solid #e8edf5;
        }

        .user-dropdown .dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .user-dropdown .dropdown-menu .user-info {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .user-dropdown .dropdown-menu .user-info .user-username {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            font-family: 'Poppins', 'Inter', sans-serif;
        }

        .user-dropdown .dropdown-menu .dropdown-divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 4px 12px;
        }

        .user-dropdown .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: #0f172a;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s ease;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-family: 'Inter', sans-serif;
        }

        .user-dropdown .dropdown-menu .dropdown-item:hover {
            background: #f1f5f9;
        }

        .user-dropdown .dropdown-menu .dropdown-item.logout {
            color: #ef4444;
        }

        .user-dropdown .dropdown-menu .dropdown-item.logout:hover {
            background: #fef2f2;
        }

        .user-dropdown .dropdown-menu .dropdown-item i {
            width: 18px;
            text-align: center;
        }

        /* ====================== TOMBOL REFRESH ====================== */
        .btn-refresh {
            background: #f8fafc;
            border: 1px solid #e8edf5;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
            font-family: 'Inter', sans-serif;
        }

        .btn-refresh:hover {
            background: #ffffff;
            border-color: #3b82f6;
            color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.15);
        }

        .btn-refresh:active {
            transform: translateY(0);
        }

        .btn-refresh .spinning {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ====================== CONTENT WRAPPER ====================== */
        .content-wrapper {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            border: 1px solid #e8edf5;
        }

        /* ====================== NETWORK ALERT ====================== */
        .network-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99999;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            display: none;
            align-items: center;
            gap: 14px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideDown 0.5s ease;
            max-width: 90%;
            backdrop-filter: blur(10px);
        }

        .network-alert.show {
            display: flex;
        }

        .network-alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .network-alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .network-alert .alert-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .network-alert .alert-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .network-alert .alert-title {
            font-weight: 700;
            font-size: 15px;
        }

        .network-alert .alert-message {
            font-weight: 400;
            font-size: 13px;
            opacity: 0.9;
        }

        .network-alert .alert-close {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: inherit;
            opacity: 0.6;
            padding: 0 4px;
            transition: opacity 0.2s ease;
            margin-left: auto;
        }

        .network-alert .alert-close:hover {
            opacity: 1;
        }

        @keyframes slideDown {
            from {
                transform: translateX(-50%) translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
            to {
                transform: translateX(-50%) translateY(-100px);
                opacity: 0;
            }
        }

        .network-alert.hide {
            animation: slideUp 0.5s ease forwards;
        }

        /* ====================== RESPONSIVE ====================== */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap');

        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header {
                padding: 16px 12px;
            }
            .sidebar .logo {
                justify-content: center;
            }
            .sidebar .logo .logo-text h2,
            .sidebar .logo .logo-text .subtitle,
            .sidebar .nav-label,
            .sidebar a span {
                display: none;
            }
            .sidebar .logo .logo-image {
                width: 40px;
                height: 40px;
            }
            .sidebar a {
                justify-content: center;
                padding: 12px;
                font-size: 18px;
            }
            .sidebar a i {
                font-size: 20px;
                width: auto;
                margin: 0;
            }
            .sidebar a.active::before {
                display: none;
            }
            .sidebar a.active {
                box-shadow: none;
                background: rgba(37, 99, 235, 0.25);
            }
            .main {
                margin-left: 80px;
                padding: 20px 24px;
            }
            .topbar .page-title h1 {
                font-size: 17px;
            }
            .network-alert {
                top: 10px;
                padding: 12px 18px;
                font-size: 13px;
                width: 95%;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 0;
                flex-direction: row;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 4px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }
            .sidebar-header {
                padding: 12px 16px;
                border-bottom: none;
                flex: 1;
                background: transparent;
            }
            .sidebar .logo .logo-text h2 {
                display: block;
                font-size: 14px;
            }
            .sidebar .logo .logo-text .subtitle {
                display: block;
                font-size: 10px;
            }
            .sidebar .logo .logo-image {
                width: 36px;
                height: 36px;
            }
            .sidebar-nav {
                padding: 8px 12px 12px;
                display: flex;
                flex-wrap: wrap;
                gap: 2px;
                width: 100%;
            }
            .sidebar .nav-label {
                display: none;
            }
            .sidebar a {
                padding: 8px 14px;
                font-size: 13px;
                margin-bottom: 0;
                flex: 0 0 auto;
            }
            .sidebar a span {
                display: inline;
            }
            .sidebar a i {
                font-size: 14px;
                width: 18px;
            }
            .sidebar a.active::before {
                display: none;
            }
            .main {
                margin-left: 0;
                padding: 16px;
            }
            .topbar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                padding: 16px 20px;
            }
            .topbar .page-title h1 {
                font-size: 17px;
            }
            .topbar .right-section {
                justify-content: space-between;
            }
            .network-alert {
                top: 10px;
                padding: 12px 16px;
                font-size: 13px;
                width: 95%;
            }
        }

        @media (max-width: 480px) {
            .main {
                padding: 12px;
            }
            .topbar {
                padding: 14px 16px;
            }
            .topbar .page-title h1 {
                font-size: 15px;
            }
            .topbar .page-title .breadcrumb {
                font-size: 11px;
            }
            .topbar .right-section .time {
                font-size: 11px;
                padding: 4px 10px;
                min-width: 80px;
            }
            .btn-refresh {
                padding: 6px 12px;
                font-size: 12px;
            }
            .btn-refresh span {
                display: none;
            }
            .content-wrapper {
                padding: 16px;
            }
            .network-alert {
                top: 8px;
                padding: 10px 14px;
                font-size: 12px;
                width: 97%;
                border-radius: 8px;
            }
            .network-alert .alert-icon {
                font-size: 16px;
            }
            .network-alert .alert-title {
                font-size: 12px;
            }
            .network-alert .alert-message {
                font-size: 11px;
            }
            .network-alert .alert-close {
                font-size: 18px;
            }
        }
    </style>

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>

<!-- ====================== NETWORK ALERT ====================== -->
<div class="network-alert" id="networkAlert">
    <span class="alert-icon" id="alertIcon">⚠️</span>
    <div class="alert-content">
        <span class="alert-title" id="alertTitle">Jaringan Terputus!</span>
        <span class="alert-message" id="alertMessage">Tidak ada koneksi internet. Periksa router/modem Anda.</span>
    </div>
    <button class="alert-close" onclick="closeNetworkAlert()">&times;</button>
</div>

<!-- ====================== SIDEBAR ====================== -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="{{ asset('images/Logo.png') }}" 
                 alt="DISKOMINFOTIK" 
                 class="logo-image">
            <div class="logo-text">
                <h2>DISKOMINFOTIK</h2>
                <span class="subtitle">Monitoring System</span>
            </div>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-label">Main Menu</div>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('services') }}" class="{{ request()->routeIs('services*') ? 'active' : '' }}">
            <i class="fas fa-server"></i>
            <span>Monitoring Service</span>
        </a>

        <a href="{{ route('smoke') }}" class="{{ request()->routeIs('smoke*') ? 'active' : '' }}">
            <i class="fas fa-fire-extinguisher"></i>
            <span>Smoke Detector</span>
        </a>

        <a href="{{ route('contacts') }}" class="{{ request()->routeIs('contacts*') ? 'active' : '' }}">
            <i class="fas fa-address-book"></i>
            <span>Contacts</span>
        </a>

        <a href="{{ route('logs') }}" class="{{ request()->routeIs('logs*') ? 'active' : '' }}">
            <i class="fas fa-history"></i>
            <span>Monitoring Logs</span>
        </a>
    </div>
</div>

<!-- ====================== MAIN CONTENT ====================== -->
<div class="main">
    <!-- Top Bar -->
    <div class="topbar">
        <div class="page-title">
            <h1>
                <i class="fas fa-hand-wave welcome-icon"></i>
                Halo, <span class="username">{{ Auth::user()->username ?? 'User' }}</span>! 👋
            </h1>
            <div class="breadcrumb">
                <i class="fas fa-calendar-alt"></i>
                <span id="currentDate">{{ now()->format('l, d F Y') }}</span>
            </div>
        </div>
        <div class="right-section">
            <span class="time" id="currentTime">
                <i class="far fa-clock"></i>
                <span id="timeDisplay">--:-- WIB</span>
            </span>
            <button class="btn-refresh" onclick="refreshPage()" title="Refresh halaman">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 3v6h-6" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 21v-6h6" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18.364 5.636a9 9 0 0 1 0 12.728 9 9 0 0 1-12.728 0 9 9 0 0 1 0-12.728 9 9 0 0 1 12.728 0z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Refresh</span>
            </button>

            <!-- USER DROPDOWN -->
            <div class="user-dropdown">
                <div class="avatar" onclick="toggleDropdown()">
                    <i class="fas fa-user"></i>
                </div>
                <div class="dropdown-menu" id="userDropdown">
                    <div class="user-info">
                        <div class="user-username">{{ Auth::user()->username ?? 'username' }}</div>
                    </div>
                    <hr class="dropdown-divider">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        @yield('content')
    </div>
</div>

<script>
    // ====================== REFRESH ======================
    function refreshPage() {
        const btn = document.querySelector('.btn-refresh svg');
        btn.classList.add('spinning');
        setTimeout(function() {
            location.reload();
        }, 500);
    }

    // ====================== UPDATE TIME ======================
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeString = `${hours}:${minutes} WIB`;
        const timeDisplay = document.getElementById('timeDisplay');
        if (timeDisplay) {
            timeDisplay.textContent = timeString;
        }
    }

    function updateDate() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateString = now.toLocaleDateString('id-ID', options);
        const dateElement = document.getElementById('currentDate');
        if (dateElement) {
            dateElement.textContent = dateString;
        }
    }

    // ====================== NETWORK ALERT ======================
    let networkAlertShown = false;
    let networkAlertTimer = null;

    function showNetworkAlert(isConnected) {
        const alert = document.getElementById('networkAlert');
        const icon = document.getElementById('alertIcon');
        const title = document.getElementById('alertTitle');
        const message = document.getElementById('alertMessage');

        if (!alert) return;

        alert.classList.remove('show', 'hide', 'error', 'success');

        if (isConnected) {
            icon.textContent = '🟢';
            title.textContent = 'Jaringan Normal';
            message.textContent = 'Koneksi internet telah pulih. Semua sistem berjalan normal.';
            alert.className = 'network-alert success';
            networkAlertShown = false;
        } else {
            icon.textContent = '🚨';
            title.textContent = 'Jaringan Terputus!';
            message.textContent = 'Tidak ada koneksi internet. Periksa router/modem dan kabel jaringan.';
            alert.className = 'network-alert error';
            networkAlertShown = true;
        }

        alert.classList.add('show');

        clearTimeout(networkAlertTimer);
        networkAlertTimer = setTimeout(function() {
            closeNetworkAlert();
        }, 10000);
    }

    function closeNetworkAlert() {
        const alert = document.getElementById('networkAlert');
        if (alert) {
            alert.classList.add('hide');
            setTimeout(function() {
                alert.classList.remove('show', 'hide');
            }, 500);
        }
        clearTimeout(networkAlertTimer);
    }

    function checkNetworkStatus() {
        fetch('/api/network/status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const isConnected = data.connected;
                    if (isConnected && networkAlertShown) {
                        showNetworkAlert(true);
                    } else if (!isConnected && !networkAlertShown) {
                        showNetworkAlert(false);
                    }
                }
            })
            .catch(function() {
                if (!networkAlertShown) {
                    showNetworkAlert(false);
                }
            });
    }

    // ====================== USER DROPDOWN ======================
    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const avatar = document.querySelector('.user-dropdown .avatar');
        if (dropdown && avatar && !avatar.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
        }
    });

    // ====================== INITIALIZATION ======================
    document.addEventListener('DOMContentLoaded', function() {
        updateTime();
        updateDate();
        setInterval(updateTime, 10000);
        setInterval(updateDate, 60000);

        // Cek status jaringan pertama kali
        setTimeout(function() {
            checkNetworkStatus();
        }, 1000);

        // Cek setiap 30 detik
        setInterval(checkNetworkStatus, 1000);

        console.log('✅ Monitoring System DISKOMINFOTIK Loaded');
    });
</script>

</body> 
</html>