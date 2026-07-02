@extends('layouts.app')

@section('content')
<style>
    .contact-create-container {
        padding: 24px;
        max-width: 800px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .contact-create-card {
        background: white;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #eef2f6;
    }

    .contact-create-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .contact-create-header h1 {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .contact-create-header h1 .icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #25D366, #128C7E);
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
        border-color: #25D366;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
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
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
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

    /* Info Box */
    .info-box {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1e40af;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .info-box .info-icon {
        font-size: 18px;
        margin-top: 1px;
    }

    .info-box .info-content {
        font-size: 13px;
        line-height: 1.5;
    }

    .info-box .info-content strong {
        display: block;
        margin-bottom: 2px;
    }

    @media (max-width: 640px) {
        .contact-create-container {
            padding: 12px;
        }

        .contact-create-card {
            padding: 20px;
        }

        .contact-create-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .contact-create-header h1 {
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

<div class="contact-create-container">
    <div class="contact-create-card">
        <!-- Header -->
        <div class="contact-create-header">
            <h1>
                <span class="icon">💬</span>
                Tambah Kontak WhatsApp
            </h1>
            <a href="{{ route('contacts') }}" class="btn-back">
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

        <!-- Info Box -->
        <div class="info-box">
            <span class="info-icon">ℹ️</span>
            <div class="info-content">
                <strong>Format Nomor WhatsApp:</strong>
                Gunakan format internasional tanpa tanda +, spasi, atau tanda hubung.<br>
                Contoh: <code>6281234567890</code> (Indonesia) atau <code>60123456789</code> (Malaysia)
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('contacts.store') }}">
            @csrf

            <!-- Nama -->
            <div class="form-group">
                <label for="name">
                    Nama Kontak
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name"
                    class="form-control @error('name') error @enderror"
                    placeholder="Contoh: Budi Santoso"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">Nama lengkap atau nama panggilan kontak</div>
            </div>

            <!-- Nomor WhatsApp -->
            <div class="form-group">
                <label for="phone">
                    Nomor WhatsApp
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="phone" 
                    id="phone"
                    class="form-control @error('phone') error @enderror"
                    placeholder="Contoh: 6281234567890"
                    value="{{ old('phone') }}"
                    required
                >
                @error('phone')
                    <div class="error-message">⚠️ {{ $message }}</div>
                @enderror
                <div class="helper-text">Masukkan nomor dengan format internasional (tanpa +, spasi, atau tanda hubung)</div>
            </div>

            <!-- Status Aktif (Hidden, default true) -->
            <input type="hidden" name="is_active" value="1">

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    💾 Simpan Kontak
                </button>
                <a href="{{ route('contacts') }}" class="btn-cancel">
                    ✕ Batal
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Debug: Tampilkan URL form --}}
<p style="background: #f0f0f0; padding: 10px; border-radius: 4px; font-size: 12px; margin-top: 20px; text-align: center; color: #64748b;">
    🔍 Form akan mengirim ke: <code>{{ route('contacts.store') }}</code>
</p>

@endsection