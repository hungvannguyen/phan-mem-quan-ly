@extends('layouts.default')

@section('content')
    <main class="management-page">
        <div class="form-section">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1 class="page-title">Thêm ngành đào tạo mới</h1>
                    <p class="page-subtitle">Tạo mới ngành đào tạo trong hệ thống</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('settings.index') }}" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form action="{{ route('settings.majors.store') }}" method="POST" class="custom-form">
                    @csrf

                    <div class="form-grid">
                        <!-- Major Name -->
                        <div class="form-field full-width">
                            <label for="major_name" class="field-label required">Tên ngành đào tạo</label>
                            <input type="text" id="major_name" name="major_name"
                                class="field-input @error('major_name') is-invalid @enderror"
                                placeholder="VD: Công nghệ thông tin, Kế toán, Quản trị kinh doanh..."
                                value="{{ old('major_name') }}" required>
                            @error('major_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="field-hint">
                                <i class="fas fa-info-circle"></i>
                                Nhập tên đầy đủ của ngành đào tạo
                            </small>
                        </div>

                        <!-- Major Code -->
                        <div class="form-field">
                            <label for="major_code" class="field-label required">Mã ngành</label>
                            <input type="text" id="major_code" name="major_code"
                                class="field-input @error('major_code') is-invalid @enderror"
                                placeholder="VD: IT01, ACC01, BUS01..." value="{{ old('major_code') }}" maxlength="20"
                                style="text-transform: uppercase;" required>
                            @error('major_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="field-hint">
                                <i class="fas fa-info-circle"></i>
                                Mã định danh duy nhất (tối đa 20 ký tự)
                            </small>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Lưu thông tin
                        </button>
                        <a href="{{ route('settings.index') }}" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>

            <!-- Information Card -->
            <div class="info-card">
                <h3>
                    <i class="fas fa-lightbulb"></i>
                    Hướng dẫn
                </h3>
                <ul>
                    <li>
                        <strong>Tên ngành đào tạo:</strong> Nhập tên đầy đủ, rõ ràng của ngành.
                        <br>Ví dụ: "Công nghệ thông tin", "Kế toán", "Quản trị kinh doanh"
                    </li>
                    <li>
                        <strong>Mã ngành:</strong> Mã định danh ngắn gọn để phân biệt các ngành đào tạo.
                        <br>Ví dụ: "IT01" cho Công nghệ thông tin, "ACC01" cho Kế toán
                    </li>
                    <li>Mã ngành phải là duy nhất trong hệ thống</li>
                    <li>Mã ngành sẽ được sử dụng trong việc quản lý sinh viên và văn bằng</li>
                </ul>
            </div>
        </div>
    </main>

    <style>
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .info-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .info-card h3 {
            color: #15803d;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-card li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #dcfce7;
            color: #166534;
            line-height: 1.6;
        }

        .info-card li:last-child {
            border-bottom: none;
        }

        .info-card strong {
            color: #15803d;
        }

        .field-label.required::after {
            content: " *";
            color: #ef4444;
        }
    </style>

    <script>
        // Auto uppercase major code input
        document.getElementById('major_code').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
@endsection
