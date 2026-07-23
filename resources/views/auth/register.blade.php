<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Monitoring System</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d3b66 0%, #1a4d7a 40%, #2563eb 100%);
            padding: 20px;
            position: relative;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .network-bg {
            position: fixed;
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

        body::before {
            content: '';
            position: fixed;
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

        .register-container {
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.8s ease;
            position: relative;
            z-index: 1;
            margin: 20px auto;
            padding: 10px 0;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .register-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 32px 28px 24px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
            z-index: 1;
            width: 100%;
        }

        .register-card:hover {
            transform: translateY(-2px);
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #25D366, #6366f1, #8b5cf6, #25D366);
            background-size: 300% 100%;
            animation: gradientMove 4s linear infinite;
            z-index: 2;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 0%; }
            100% { background-position: 300% 0%; }
        }

        .register-card::after {
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

        .register-logo {
            text-align: center;
            margin-bottom: 18px;
            position: relative;
            z-index: 2;
        }

        .register-logo .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 6px;
        }

        .register-logo .logo-wrapper::before {
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

        .register-logo .logo-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 18px;
            box-shadow: 0 16px 48px rgba(99, 102, 241, 0.35);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: block;
            position: relative;
            z-index: 1;
            background: white;
            padding: 10px;
            margin: 0 auto;
        }

        .register-logo .logo-image:hover {
            transform: scale(1.05) rotate(-3deg);
            box-shadow: 0 24px 64px rgba(99, 102, 241, 0.5);
        }

        .register-logo .tagline {
            font-size: 12px;
            color: #0f172a;
            font-weight: 600;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-family: 'Poppins', 'Inter', sans-serif;
            margin-top: 4px;
            position: relative;
            display: inline-block;
        }

        .register-logo .tagline::before,
        .register-logo .tagline::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 18px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #6366f1);
        }

        .register-logo .tagline::before {
            right: calc(100% + 10px);
            transform: translateY(-50%);
        }

        .register-logo .tagline::after {
            left: calc(100% + 10px);
            transform: translateY(-50%);
            background: linear-gradient(90deg, #6366f1, transparent);
        }

        .register-logo .tagline span {
            color: #6366f1;
        }

        .register-title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            position: relative;
            z-index: 2;
        }

        .register-title span {
            color: #6366f1;
        }

        .form-group {
            margin-bottom: 13px;
            position: relative;
            z-index: 2;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group .input-wrapper .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .form-group .input-wrapper .input-icon.focused {
            color: #6366f1;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px 10px 36px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13px;
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

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 14px;
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
            font-size: 11px;
            margin-top: 3px;
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

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12px;
            animation: shake 0.5s ease;
            position: relative;
            z-index: 2;
        }

        .alert-error .alert-icon {
            font-size: 16px;
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

        .btn-register {
            width: 100%;
            padding: 11px 20px;
            background: linear-gradient(135deg, #25D366, #1da851);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            z-index: 2;
            overflow: hidden;
            margin-top: 4px;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.45);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-register .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-link {
            text-align: center;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
            position: relative;
            z-index: 2;
        }

        .login-link p {
            font-size: 13px;
            color: #475569;
            margin: 0;
        }

        .login-link .btn-login-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #6366f1;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 4px 10px;
            border-radius: 6px;
            position: relative;
        }

        .login-link .btn-login-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .login-link .btn-login-link:hover {
            color: #4f46e5;
            transform: translateX(4px);
        }

        .login-link .btn-login-link:hover::after {
            width: 80%;
        }

        .login-link .btn-login-link i {
            font-size: 13px;
            transition: transform 0.3s ease;
        }

        .login-link .btn-login-link:hover i {
            transform: translateX(4px);
        }

        .register-footer {
            text-align: center;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #f1f5f9;
            position: relative;
            z-index: 2;
        }

        .register-footer p {
            font-size: 11px;
            color: #94a3b8;
        }

        .register-footer .version {
            font-size: 10px;
            color: #cbd5e1;
            margin-top: 2px;
        }

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

        .password-strength {
            margin-top: 4px;
            height: 3px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        .password-strength .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.3s ease, background 0.3s ease;
        }

        .password-strength .strength-bar.weak {
            width: 25%;
            background: #ef4444;
        }

        .password-strength .strength-bar.medium {
            width: 50%;
            background: #f59e0b;
        }

        .password-strength .strength-bar.strong {
            width: 75%;
            background: #3b82f6;
        }

        .password-strength .strength-bar.very-strong {
            width: 100%;
            background: #22c55e;
        }

        .password-strength-text {
            font-size: 10px;
            margin-top: 3px;
            color: #94a3b8;
            display: flex;
            justify-content: flex-end;
        }

        .password-strength-text .weak { color: #ef4444; }
        .password-strength-text .medium { color: #f59e0b; }
        .password-strength-text .strong { color: #3b82f6; }
        .password-strength-text .very-strong { color: #22c55e; }

        /* ====================== SCROLLBAR ====================== */
        body::-webkit-scrollbar {
            width: 6px;
        }

        body::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 10px;
        }

        body::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap');

        @media (max-width: 480px) {
            body {
                padding: 12px;
                align-items: flex-start;
                padding-top: 30px;
                padding-bottom: 30px;
            }

            .register-container {
                margin: 0 auto;
                padding: 0;
            }

            .register-card {
                padding: 20px 16px 18px;
                border-radius: 20px;
            }

            .register-logo .logo-image {
                width: 65px;
                height: 65px;
                padding: 8px;
            }

            .register-logo .tagline {
                font-size: 10px;
                letter-spacing: 3px;
            }

            .register-logo .tagline::before,
            .register-logo .tagline::after {
                width: 10px;
            }

            .register-title {
                font-size: 13px;
                margin-bottom: 14px;
            }

            .btn-register {
                font-size: 13px;
                padding: 10px 14px;
            }

            .toggle-password {
                right: 8px;
                font-size: 13px;
            }

            .form-control {
                padding: 9px 12px 9px 32px;
                font-size: 12px;
            }

            .form-group .input-wrapper .input-icon {
                left: 10px;
                font-size: 12px;
            }

            .form-group {
                margin-bottom: 11px;
            }

            .network-bg .node { display: none; }
            .network-bg .line { display: none; }

            .login-link p {
                font-size: 12px;
            }

            .login-link .btn-login-link {
                font-size: 12px;
                padding: 4px 6px;
            }

            .alert-error {
                padding: 8px 12px;
                font-size: 11px;
                margin-bottom: 12px;
            }

            .register-footer p {
                font-size: 10px;
            }
        }

        @media (max-width: 380px) {
            body {
                padding: 8px;
                padding-top: 20px;
                padding-bottom: 20px;
            }

            .register-card {
                padding: 16px 12px 14px;
                border-radius: 16px;
            }

            .register-logo .logo-image {
                width: 55px;
                height: 55px;
                padding: 6px;
            }

            .register-logo .tagline {
                font-size: 9px;
                letter-spacing: 2px;
            }

            .register-logo .tagline::before,
            .register-logo .tagline::after {
                width: 8px;
            }

            .form-control {
                padding: 8px 10px 8px 28px;
                font-size: 11px;
            }

            .btn-register {
                font-size: 12px;
                padding: 8px 12px;
            }

            .form-group .input-wrapper .input-icon {
                left: 8px;
                font-size: 11px;
            }

            .register-title {
                font-size: 12px;
            }
        }

        @media (min-height: 800px) {
            .register-container {
                margin: 30px auto;
            }
        }

        @media (max-height: 700px) {
            body {
                align-items: flex-start;
                padding-top: 20px;
                padding-bottom: 20px;
            }

            .register-logo .logo-image {
                width: 65px;
                height: 65px;
                padding: 8px;
            }

            .register-logo {
                margin-bottom: 12px;
            }

            .register-card {
                padding: 20px 24px 16px;
            }

            .form-group {
                margin-bottom: 10px;
            }

            .register-title {
                margin-bottom: 12px;
                font-size: 14px;
            }

            .btn-register {
                padding: 9px 16px;
                font-size: 13px;
                margin-top: 2px;
            }

            .register-footer {
                margin-top: 10px;
                padding-top: 8px;
            }
        }

        @media (max-height: 600px) {
            .register-logo .logo-image {
                width: 55px;
                height: 55px;
                padding: 6px;
            }

            .register-logo {
                margin-bottom: 8px;
            }

            .register-card {
                padding: 14px 18px 12px;
            }

            .form-group {
                margin-bottom: 8px;
            }

            .form-control {
                padding: 7px 10px 7px 28px;
                font-size: 11px;
            }

            .register-title {
                margin-bottom: 8px;
                font-size: 12px;
            }

            .btn-register {
                padding: 7px 12px;
                font-size: 12px;
            }

            .login-link {
                margin-top: 10px;
                padding-top: 8px;
            }

            .register-footer {
                margin-top: 8px;
                padding-top: 6px;
            }

            .password-strength {
                margin-top: 2px;
                height: 2px;
            }

            .password-strength-text {
                font-size: 9px;
                margin-top: 2px;
            }

            .alert-error {
                padding: 6px 10px;
                font-size: 11px;
                margin-bottom: 10px;
            }

            .login-link p {
                font-size: 11px;
            }
        }
    </style>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="network-bg">
    <svg viewBox="0 0 800 600" preserveAspectRatio="xMidYMid slice">
        <circle class="node" cx="100" cy="100" r="4"/>
        <circle class="node" cx="250" cy="50" r="4"/>
        <circle class="node" cx="500" cy="80" r="4"/>
        <circle class="node" cx="700" cy="120" r="4"/>
        <circle class="node" cx="150" cy="300" r="4"/>
        <circle class="node" cx="650" cy="280" r="4"/>
        <circle class="node" cx="80" cy="480" r="4"/>
        <circle class="node" cx="350" cy="400" r="4"/>
        <circle class="node" cx="550" cy="450" r="4"/>
        <circle class="node" cx="720" cy="500" r="4"/>
        <circle class="node" cx="300" cy="550" r="4"/>
        <circle class="node" cx="600" cy="550" r="4"/>

        <line class="line" x1="100" y1="100" x2="250" y2="50"/>
        <line class="line" x1="250" y1="50" x2="500" y2="80"/>
        <line class="line" x1="500" y1="80" x2="700" y2="120"/>
        <line class="line" x1="100" y1="100" x2="150" y2="300"/>
        <line class="line" x1="700" y1="120" x2="650" y2="280"/>
        <line class="line" x1="150" y1="300" x2="350" y2="400"/>
        <line class="line" x1="650" y1="280" x2="550" y2="450"/>
        <line class="line" x1="80" y1="480" x2="350" y2="400"/>
        <line class="line" x1="350" y1="400" x2="550" y2="450"/>
        <line class="line" x1="550" y1="450" x2="720" y2="500"/>
        <line class="line" x1="300" y1="550" x2="600" y2="550"/>
        <line class="line" x1="80" y1="480" x2="300" y2="550"/>
        <line class="line" x1="720" y1="500" x2="600" y2="550"/>
        <line class="line" x1="250" y1="50" x2="150" y2="300"/>
        <line class="line" x1="500" y1="80" x2="650" y2="280"/>
        <line class="line" x1="100" y1="100" x2="350" y2="400"/>
        <line class="line" x1="700" y1="120" x2="550" y2="450"/>
    </svg>
</div>

<div class="register-container">
    <div class="register-card">

        <div class="register-logo">
            <div class="logo-wrapper">
                <img src="{{ asset('images/download.jpg') }}" alt="Logo" class="logo-image">
            </div>
            <div class="brand-text">
                <p class="tagline">Monitoring <span>System</span></p>
            </div>
        </div>

        <div class="register-title">
            <i class="fas fa-user-plus" style="color: #6366f1; margin-right: 6px;"></i>
            Daftar <span>Akun</span>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <span class="alert-icon">⚠️</span>
                <div>
                    <strong>Registrasi Gagal!</strong>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <!-- Nama Lengkap -->
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <div class="input-wrapper">
                    <span class="input-icon" id="nameIcon"><i class="fas fa-user"></i></span>
                    <input type="text" name="name" id="name"
                        class="form-control @error('name') error @enderror"
                        placeholder="Masukkan nama lengkap" value="{{ old('name') }}" required autofocus
                        onfocus="document.getElementById('nameIcon').classList.add('focused')"
                        onblur="document.getElementById('nameIcon').classList.remove('focused')">
                    <span class="input-highlight"></span>
                </div>
                @error('name') <div class="error-message">⚠️ {{ $message }}</div> @enderror
            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <span class="input-icon" id="usernameIcon"><i class="fas fa-user-tag"></i></span>
                    <input type="text" name="username" id="username"
                        class="form-control @error('username') error @enderror"
                        placeholder="Masukkan username" value="{{ old('username') }}" required
                        onfocus="document.getElementById('usernameIcon').classList.add('focused')"
                        onblur="document.getElementById('usernameIcon').classList.remove('focused')">
                    <span class="input-highlight"></span>
                </div>
                @error('username') <div class="error-message">⚠️ {{ $message }}</div> @enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <span class="input-icon" id="emailIcon"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" id="email"
                        class="form-control @error('email') error @enderror"
                        placeholder="Masukkan email" value="{{ old('email') }}" required
                        onfocus="document.getElementById('emailIcon').classList.add('focused')"
                        onblur="document.getElementById('emailIcon').classList.remove('focused')">
                    <span class="input-highlight"></span>
                </div>
                @error('email') <div class="error-message">⚠️ {{ $message }}</div> @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon" id="passwordIcon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') error @enderror"
                        placeholder="Minimal 8 karakter" required
                        onfocus="document.getElementById('passwordIcon').classList.add('focused')"
                        onblur="document.getElementById('passwordIcon').classList.remove('focused')"
                        onkeyup="checkPasswordStrength(this.value)">
                    
                    <button type="button" class="toggle-password" 
                            onclick="togglePasswordVisibility('password', 'eyeIcon')" 
                            title="Tampilkan/Sembunyikan password">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                    
                    <span class="input-highlight"></span>
                </div>
                @error('password') <div class="error-message">⚠️ {{ $message }}</div> @enderror
                
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="password-strength-text">
                    <span id="strengthText">Kekuatan password</span>
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <span class="input-icon" id="passwordConfirmationIcon"><i class="fas fa-check-circle"></i></span>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control @error('password_confirmation') error @enderror"
                        placeholder="Ulangi password" required
                        onfocus="document.getElementById('passwordConfirmationIcon').classList.add('focused')"
                        onblur="document.getElementById('passwordConfirmationIcon').classList.remove('focused')">
                    
                    <button type="button" class="toggle-password" 
                            onclick="togglePasswordVisibility('password_confirmation', 'eyeIconConfirm')" 
                            title="Tampilkan/Sembunyikan password">
                        <i class="fas fa-eye" id="eyeIconConfirm"></i>
                    </button>
                    
                    <span class="input-highlight"></span>
                </div>
                @error('password_confirmation') <div class="error-message">⚠️ {{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-register" id="btnRegister">
                <span id="btnText">Daftar Sekarang</span>
                <i class="fas fa-arrow-right" style="font-size: 13px; transition: transform 0.3s ease;"></i>
            </button>
        </form>

        <div class="login-link">
            <p>
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="btn-login-link">
                    Login <i class="fas fa-arrow-right"></i>
                </a>
            </p>
        </div>

        <div class="register-footer">
            <p>© {{ date('Y') }} Monitoring System</p>
            <div class="version">Version 1.0.0</div>
        </div>

    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, eyeIconId) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(eyeIconId);
        
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

    function checkPasswordStrength(password) {
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        let message = '';
        let className = '';

        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.className = 'strength-bar';
            strengthText.textContent = 'Kekuatan password';
            strengthText.className = '';
            return;
        }

        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;

        if (strength <= 2) {
            message = 'Lemah';
            className = 'weak';
        } else if (strength <= 3) {
            message = 'Sedang';
            className = 'medium';
        } else if (strength <= 4) {
            message = 'Kuat';
            className = 'strong';
        } else {
            message = 'Sangat Kuat';
            className = 'very-strong';
        }

        strengthBar.className = 'strength-bar ' + className;
        strengthText.textContent = message;
        strengthText.className = className;
    }

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const btnRegister = document.getElementById('btnRegister');
        const btnText = document.getElementById('btnText');
        const arrowIcon = btnRegister.querySelector('.fa-arrow-right');
        
        btnRegister.disabled = true;
        btnText.innerHTML = '<span class="spinner"></span> Memproses...';
        if (arrowIcon) arrowIcon.style.display = 'none';
    });

    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        if (nameInput) nameInput.focus();
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

    document.querySelector('.btn-register').addEventListener('mouseenter', function() {
        const arrow = this.querySelector('.fa-arrow-right');
        if (arrow) arrow.style.transform = 'translateX(4px)';
    });

    document.querySelector('.btn-register').addEventListener('mouseleave', function() {
        const arrow = this.querySelector('.fa-arrow-right');
        if (arrow) arrow.style.transform = 'translateX(0)';
    });
</script>

</body>
</html>