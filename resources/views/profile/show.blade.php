@extends('layouts.default')

@section('content')
    <style>
        .field-description {
            margin-top: 0.5rem;
        }

        .alert-info,
        .alert-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .alert-info {
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
        }

        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
        }

        .password-toggle:hover {
            color: #059669;
        }

        .password-field-wrapper {
            position: relative;
        }

        .status-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .status-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .status-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .status-value {
            font-weight: 600;
        }
    </style>

    <div class="student-edit-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex w-full items-center justify-between">
                <div>
                    <h1>Thông Tin Cá Nhân</h1>
                    <p>Email: {{ $user->email }}</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>

        <!-- Profile Information Form -->
        <form method="POST" action="{{ route('profile.update') }}" class="mb-8">
            @csrf
            @method('PATCH')

            <!-- Profile Information Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-user-edit text-blue-600"></i>
                    Thông tin tài khoản
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field">
                        <label for="email" class="field-label">Email</label>
                        <input type="email" id="email" class="field-input bg-gray-100" value="{{ $user->email }}"
                            disabled readonly>
                        <div class="field-description">
                            <small class="text-gray-600">Email không thể thay đổi</small>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="full_name" class="field-label">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="field-input"
                            value="{{ old('full_name', $user->full_name) }}" placeholder="Nhập họ và tên đầy đủ" required>
                        @error('full_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field md:col-span-2">
                        <label class="field-label">Vai trò</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <span class="status-badge status-completed">
                                    <i class="fas fa-user-tag"></i>
                                    {{ $role->role_name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-section mt-6">
                    <div class="action-buttons">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save mr-2"></i>Lưu thay đổi
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Password Change Form -->
        <form method="POST" action="{{ route('profile.password') }}" class="mb-8">
            @csrf
            @method('PATCH')

            <!-- Password Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-key text-yellow-600"></i>
                    Đổi mật khẩu
                </h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="current_password" class="field-label">Mật khẩu hiện tại <span
                                class="text-red-500">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" id="current_password" name="current_password" class="field-input pr-10"
                                placeholder="Nhập mật khẩu hiện tại" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('current_password')"></i>
                        </div>
                        @error('current_password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="password" class="field-label">Mật khẩu mới <span class="text-red-500">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password" name="password" class="field-input pr-10"
                                placeholder="Nhập mật khẩu mới" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                        </div>
                        <div class="field-description">
                            <small class="text-gray-600">Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="password_confirmation" class="field-label">Xác nhận mật khẩu mới <span
                                class="text-red-500">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="field-input pr-10" placeholder="Nhập lại mật khẩu mới" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password_confirmation')"></i>
                        </div>
                    </div>
                </div>

                <div class="alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Lưu ý:</strong> Bạn cần nhập mật khẩu hiện tại để xác thực trước khi thay đổi mật khẩu mới.
                        Mật khẩu mới phải có ít nhất 6 ký tự.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-section mt-6">
                    <div class="action-buttons">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-key mr-2"></i>Đổi mật khẩu
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Status Information Section -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-info-circle text-blue-600"></i>
                Thông tin trạng thái
            </h2>
            <div class="status-info-grid">
                <div class="status-card">
                    <div class="status-header">
                        <i class="fas fa-toggle-on"></i>
                        <span>Trạng thái hiện tại</span>
                    </div>
                    <div class="status-value">
                        @if ($user->is_active)
                            <span class="text-green-600"><i class="fas fa-check-circle"></i> Hoạt động</span>
                        @else
                            <span class="text-red-600"><i class="fas fa-times-circle"></i> Vô hiệu hóa</span>
                        @endif
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-header">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Ngày tạo</span>
                    </div>
                    <div class="status-value">
                        <span class="text-sm">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-header">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Ngày cập nhật</span>
                    </div>
                    <div class="status-value">
                        <span class="text-sm">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-header">
                        <i class="fas fa-id-card"></i>
                        <span>ID tài khoản</span>
                    </div>
                    <div class="status-value">
                        <span class="font-mono text-sm">{{ $user->user_id }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="flash-message success" id="success-message">
                <div class="flash-content">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                    <button onclick="document.getElementById('success-message').remove()" class="flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="flash-message error" id="error-message">
                <div class="flash-content">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                    <button onclick="document.getElementById('error-message').remove()" class="flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const successMsg = document.getElementById('success-message');
            const errorMsg = document.getElementById('error-message');
            if (successMsg) successMsg.remove();
            if (errorMsg) errorMsg.remove();
        }, 5000);

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.querySelector('form[action="{{ route('profile.password') }}"]');
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');

            // Real-time password match validation
            if (passwordConfirmation) {
                passwordConfirmation.addEventListener('input', function() {
                    if (password.value && password.value !== this.value) {
                        this.classList.add('border-yellow-500');
                    } else if (password.value) {
                        this.classList.remove('border-yellow-500');
                        this.classList.add('border-green-500');
                    }
                });
            }

            // Form validation on submit
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    if (password.value && password.value !== passwordConfirmation.value) {
                        e.preventDefault();
                        alert('Mật khẩu xác nhận không khớp!');
                        passwordConfirmation.classList.add('border-red-500');
                    }
                });
            }
        });
    </script>
@endsection
