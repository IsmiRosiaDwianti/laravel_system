<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Monitoring System</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d3b66 0%, #1a4d7a 40%, #2563eb 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* ====================== LATAR BELAKANG NETWORK ====================== */
        .network-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .network-bg svg {
            width: 100%;
            height: 100%;
        }

        .network-bg .node {
            fill: rgba(255, 255, 255, 0.2);
            animation: pulseNode 4s ease-in-out infinite;
        }

        .network-bg .node:nth-child(1) { animation-delay: 0s; }
        .network-bg .node:nth-child(2) { animation-delay: 0.5s; }
        .network-bg .node:nth-child(3) { animation-delay: 1s; }
        .network-bg .node:nth-child(4) { animation-delay: 1.5s; }
        .network-bg .node:nth-child(5) { animation-delay: 2s; }
        .network-bg .node:nth-child(6) { animation-delay: 2.5s; }
        .network-bg .node:nth-child(7) { animation-delay: 3s; }
        .network-bg .node:nth-child(8) { animation-delay: 3.5s; }

        .network-bg .line {
            stroke: rgba(255, 255, 255, 0.06);
            stroke-width: 1.5;
            animation: drawLine 6s ease-in-out infinite;
        }

        @keyframes pulseNode {
            0%, 100% { opacity: 0.2; r: 4; }
            50% { opacity: 0.8; r: 6; }
        }

        @keyframes drawLine {
            0%, 100% { stroke-dashoffset: 1000; opacity: 0.2; }
            50% { stroke-dashoffset: 0; opacity: 0.5; }
        }

        /* ====================== ANIMASI ROTASI BG ====================== */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 30% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 60%),
                        radial-gradient(ellipse at 70% 50%, rgba(37, 211, 102, 0.08) 0%, transparent 60%);
            animation: rotateBg 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes rotateBg {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            animation: fadeInUp 0.8s ease;
            position: relative;
            z-index: 1;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 36px 32px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
            z-index: 1;
        }

        .login-card:hover {
            transform: translateY(-2px);
        }

        /* Decorative gradient border */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #25D366, #6366f1);
            background-size: 300% 100%;
            animation: gradientMove 4s linear infinite;
            z-index: 2;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 0%; }
            100% { background-position: 300% 0%; }
        }

        /* Decorative circle di belakang */
        .login-card::after {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.06), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* ====================== LOGO ====================== */
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
            position: relative;
            z-index: 2;
        }

        .login-logo .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
        }

        .login-logo .logo-wrapper::before {
            content: '';
            position: absolute;
            inset: -20px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.05) 50%, transparent 70%);
            border-radius: 50%;
            animation: pulseGlow 3s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 1; }
        }

        .login-logo .logo-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 24px;
            box-shadow: 0 16px 48px rgba(99, 102, 241, 0.35);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: block;
            position: relative;
            z-index: 1;
            background: white;
            padding: 14px;
        }

        .login-logo .logo-image:hover {
            transform: scale(1.05) rotate(-3deg);
            box-shadow: 0 24px 64px rgba(99, 102, 241, 0.5);
        }

        .login-logo .brand-text {
            margin-top: 4px;
        }

        .login-logo h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-family: 'Poppins', 'Inter', sans-serif;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        /* ====================== TAGLINE DENGAN GARIS ====================== */
        .login-logo .tagline {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 400;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-family: 'Poppins', 'Inter', sans-serif;
            margin-top: 6px;
            position: relative;
            display: inline-block;
        }

        .login-logo .tagline::before,
        .login-logo .tagline::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 25px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #94a3b8);
        }

        .login-logo .tagline::before {
            right: calc(100% + 12px);
            transform: translateY(-50%);
        }

        .login-logo .tagline::after {
            left: calc(100% + 12px);
            transform: translateY(-50%);
            background: linear-gradient(90deg, #94a3b8, transparent);
        }

        .login-logo .tagline span {
            color: #6366f1;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
            z-index: 2;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            pointer-events: none;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .form-group .input-wrapper .input-icon.focused {
            color: #6366f1;
        }

        .form-control {
            width: 100%;
            padding: 13px 16px 13px 44px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            color: #0f172a;
            transition: all 0.3s ease;
            background: #f8fafc;
            outline: none;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: white;
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .form-control.error:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        /* ====================== TOGGLE PASSWORD ====================== */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: all 0.3s ease;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: #6366f1;
        }

        .toggle-password:active {
            transform: translateY(-50%) scale(0.9);
        }

        .error-message {
            color: #ef4444;
            font-size: 13px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            position: relative;
            z-index: 2;
        }

        .form-options .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #475569;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .form-options .remember-me:hover {
            color: #0f172a;
        }

        .form-options .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-login .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            animation: shake 0.5s ease;
            position: relative;
            z-index: 2;
        }

        .alert-error .alert-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-error strong {
            display: block;
            font-weight: 600;
        }

        .alert-error div div {
            font-weight: 400;
        }

        .login-footer {
            text-align: center;
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
            position: relative;
            z-index: 2;
        }

        .login-footer p {
            font-size: 12px;
            color: #94a3b8;
        }

        .login-footer .version {
            font-size: 11px;
            color: #cbd5e1;
            margin-top: 2px;
        }

        /* Animasi input */
        .input-wrapper .input-highlight {
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            z-index: 1;
        }

        .input-wrapper .input-highlight.active {
            width: 80%;
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap');

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 20px 24px;
            }

            .login-logo .logo-image {
                width: 100px;
                height: 100px;
                padding: 10px;
            }

            .login-logo h1 {
                font-size: 22px;
                letter-spacing: 2px;
            }

            .login-logo .tagline {
                font-size: 10px;
                letter-spacing: 2px;
            }

            .login-logo .tagline::before,
            .login-logo .tagline::after {
                width: 12px;
            }

            .form-options {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .btn-login {
                font-size: 14px;
                padding: 13px 16px;
            }

            .toggle-password {
                right: 10px;
                font-size: 14px;
            }

            .form-control {
                padding: 12px 16px 12px 40px;
                font-size: 13px;
            }

            .form-group .input-wrapper .input-icon {
                left: 12px;
                font-size: 14px;
            }

            .network-bg .node { display: none; }
            .network-bg .line { display: none; }
        }
    </style>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ====================== LATAR BELAKANG NETWORK ====================== -->
<div class="network-bg">
    <svg viewBox="0 0 800 600" preserveAspectRatio="xMidYMid slice">
        <!-- Node / Titik -->
        <circle class="node" cx="100" cy="100" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="250" cy="50" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="500" cy="80" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="700" cy="120" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="150" cy="300" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="650" cy="280" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="80" cy="480" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="350" cy="400" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="550" cy="450" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="720" cy="500" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="300" cy="550" r="4" fill="rgba(255,255,255,0.3)"/>
        <circle class="node" cx="600" cy="550" r="4" fill="rgba(255,255,255,0.3)"/>

        <!-- Garis koneksi -->
        <line class="line" x1="100" y1="100" x2="250" y2="50" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="250" y1="50" x2="500" y2="80" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="500" y1="80" x2="700" y2="120" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="100" y1="100" x2="150" y2="300" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="700" y1="120" x2="650" y2="280" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="150" y1="300" x2="350" y2="400" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="650" y1="280" x2="550" y2="450" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="80" y1="480" x2="350" y2="400" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="350" y1="400" x2="550" y2="450" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="550" y1="450" x2="720" y2="500" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="300" y1="550" x2="600" y2="550" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="80" y1="480" x2="300" y2="550" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="720" y1="500" x2="600" y2="550" stroke="rgba(255,255,255,0.08)" stroke-width="1.5" stroke-dasharray="200" stroke-dashoffset="200"/>
        <line class="line" x1="250" y1="50" x2="150" y2="300" stroke="rgba(255,255,255,0.06)" stroke-width="1" stroke-dasharray="150" stroke-dashoffset="150"/>
        <line class="line" x1="500" y1="80" x2="650" y2="280" stroke="rgba(255,255,255,0.06)" stroke-width="1" stroke-dasharray="150" stroke-dashoffset="150"/>
        <line class="line" x1="100" y1="100" x2="350" y2="400" stroke="rgba(255,255,255,0.05)" stroke-width="1" stroke-dasharray="100" stroke-dashoffset="100"/>
        <line class="line" x1="700" y1="120" x2="550" y2="450" stroke="rgba(255,255,255,0.05)" stroke-width="1" stroke-dasharray="100" stroke-dashoffset="100"/>
    </svg>
</div>

<div class="login-container">
    <div class="login-card">

        <!-- ====================== LOGO ====================== -->
        <div class="login-logo">
            <div class="logo-wrapper">
                <img src="{{ asset('images/download.jpg') }}" 
                     alt="WASKITA" 
                     class="logo-image">
            </div>
            <div class="brand-text">
                <h1>WASKITA</h1>
                <p class="tagline">Monitoring <span>System</span></p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <span class="alert-icon">⚠️</span>
                <div>
                    <strong>Login Gagal!</strong>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <span class="input-icon" id="usernameIcon"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" id="username"
                        class="form-control @error('username') error @enderror"
                        placeholder="Masukkan username" value="{{ old('username') }}" required autofocus
                        onfocus="document.getElementById('usernameIcon').classList.add('focused')"
                        onblur="document.getElementById('usernameIcon').classList.remove('focused')">
                    <span class="input-highlight" id="usernameHighlight"></span>
                </div>
                @error('username') <div class="error-message">⚠️ {{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon" id="passwordIcon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') error @enderror"
                        placeholder="Masukkan password" required
                        onfocus="document.getElementById('passwordIcon').classList.add('focused')"
                        onblur="document.getElementById('passwordIcon').classList.remove('focused')">
                    
                    <button type="button" class="toggle-password" id="togglePassword" 
                            onclick="togglePasswordVisibility()" 
                            title="Tampilkan/Sembunyikan password">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                    
                    <span class="input-highlight" id="passwordHighlight"></span>
                </div>
                @error('password') <div class="error-message">⚠️ {{ $message }}</div> @enderror
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <span id="btnText">Masuk</span>
                <i class="fas fa-arrow-right" style="font-size: 14px; transition: transform 0.3s ease;"></i>
            </button>
        </form>

        <div class="login-footer">
            <p>© {{ date('Y') }} WASKITA Monitoring System</p>
            <div class="version">Version 1.0.0</div>
        </div>

    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btnLogin = document.getElementById('btnLogin');
        const btnText = document.getElementById('btnText');
        const arrowIcon = btnLogin.querySelector('.fa-arrow-right');
        
        btnLogin.disabled = true;
        btnText.innerHTML = '<span class="spinner"></span> Memproses...';
        if (arrowIcon) arrowIcon.style.display = 'none';
    });

    document.addEventListener('DOMContentLoaded', function() {
        const usernameInput = document.getElementById('username');
        if (usernameInput) usernameInput.focus();
    });

    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() {
            const highlight = this.parentElement.querySelector('.input-highlight');
            if (highlight) highlight.classList.add('active');
        });
        input.addEventListener('blur', function() {
            const highlight = this.parentElement.querySelector('.input-highlight');
            if (highlight) highlight.classList.remove('active');
        });
    });

    document.querySelector('.btn-login').addEventListener('mouseenter', function() {
        const arrow = this.querySelector('.fa-arrow-right');
        if (arrow) arrow.style.transform = 'translateX(4px)';
    });

    document.querySelector('.btn-login').addEventListener('mouseleave', function() {
        const arrow = this.querySelector('.fa-arrow-right');
        if (arrow) arrow.style.transform = 'translateX(0)';
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            const form = document.getElementById('loginForm');
            if (form) form.submit();
        }
    });
</script>

</body>
</html>