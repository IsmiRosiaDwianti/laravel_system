@extends('layouts.app')

@section('content')
<style>
    .service-create-container {
        padding: 24px;
        max-width: 800px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .service-create-card {
        background: white;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #eef2f6;
    }

    .service-create-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .service-create-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .service-create-header h1 .icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .btn-back {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-back:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .form-group label .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .form-group .helper-text {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        color: #0f172a;
        transition: all 0.2s ease;
        background: #fafbfc;
        outline: none;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        background: white;
    }

    .form-control.error {
        border-color: #ef4444;
    }

    .form-control.error:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }

    .error-message {
        color: #ef4444;
        font-size: 13px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }

    .btn-submit {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        padding: 10px 24px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        transform: translateY(-1px);
    }

    /* Alert Error */
    .alert-error {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 20px;
    }

    .alert-error ul li {
        list-style: disc;
    }

    @media (max-width: 640px) {
        .service-create-container {
            padding: 12px;
        }

        .service-create-card {
            padding: 20px;
        }

        .service-create-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .service-create-header h1 {
            font-size: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-submit,
        .btn-cancel {
            justify-content: center;
        }
    }
</style>

<div class="service-create-container">
    <div class="service-create-card">
        <!-- Header -->
        <div class="service-create-header">
            <h1>
                <span class="icon">➕</span>
                Tambah Service
            </h1>
            <a href="{{ route('services') }}" class="btn-back">
                ← Kembali
            </a>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert-error">
                <span>⚠️</span>
                <div>
                    <strong>Ada kesalahan:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('services.store') }}">
            @csrf

            <!-- Nama Service -->
            <div class="form-group">
                <label for="name">
                    Nama Service
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name"
                    class="form-control @error('name') error @enderror"
                    placeholder="Contoh: Website Utama, API Gateway, dll"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">Nama yang mudah diingat untuk service ini</div>
            </div>

            <!-- Target URL / IP -->
            <div class="form-group">
                <label for="target">
                    Target URL / IP
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="target" 
                    id="target"
                    class="form-control @error('target') error @enderror"
                    placeholder="Contoh: https://example.com atau 192.168.1.1"
                    value="{{ old('target') }}"
                    required
                >
                @error('target')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">URL lengkap dengan protocol (http:// atau https://) atau alamat IP</div>
            </div>

            <!-- Type -->
            <div class="form-group">
                <label for="type">
                    Type
                    <span class="required">*</span>
                </label>
                <select name="type" id="type" class="form-control @error('type') error @enderror" required>
                    <option value="http" {{ old('type') == 'http' ? 'selected' : '' }}>HTTP</option>
                    <option value="https" {{ old('type') == 'https' ? 'selected' : '' }}>HTTPS</option>
                    <option value="ping" {{ old('type') == 'ping' ? 'selected' : '' }}>PING</option>
                    <option value="port" {{ old('type') == 'port' ? 'selected' : '' }}>PORT</option>
                </select>
                @error('type')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">Jenis monitoring yang akan digunakan</div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    💾 Simpan Service
                </button>
                <a href="{{ route('services') }}" class="btn-cancel">
                    ✕ Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection