@extends('layouts.default')

@section('content')
    <style>
        .field-description {
            margin-top: 0.5rem;
        }

        .alert-info,
        .alert-warning,
        .alert-danger {
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

        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
        }

        .info-sidebar-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-sidebar-card h6 {
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: #1e293b;
            font-weight: 500;
        }

        .roles-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .roles-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .roles-list li:last-child {
            border-bottom: none;
        }
    </style>

    <div class="student-edit-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex w-full items-center justify-between">
                <div>
                    <h1>Chỉnh Sửa Permission</h1>
                    <p>Permission: <code class="text-blue-600">{{ $permission->name }}</code></p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <!-- Current Info Card -->
                <div class="section-card mb-6">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        Thông tin hiện tại
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="mb-1 text-sm text-gray-600">Tên Permission:</p>
                            <code class="text-blue-600">{{ $permission->name }}</code>
                        </div>
                        <div>
                            <p class="mb-1 text-sm text-gray-600">Tên Hiển Thị:</p>
                            <p class="font-medium">{{ $permission->display_name }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-sm text-gray-600">Danh Mục:</p>
                            <span class="badge badge-info">{{ ucfirst($permission->category) }}</span>
                        </div>
                        <div>
                            <p class="mb-1 text-sm text-gray-600">Số Roles Sử Dụng:</p>
                            <span class="badge badge-secondary">{{ $permission->roles()->count() }} role(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <form method="POST" action="{{ route('permissions.update', $permission->permission_id) }}">
                    @csrf
                    @method('PUT')

                    <div class="section-card">
                        <h2 class="section-title">
                            <i class="fas fa-edit text-blue-600"></i>
                            Chỉnh sửa thông tin
                        </h2>
                        <div class="grid grid-cols-1 gap-6">
                            <div class="form-field">
                                <label for="name" class="field-label">Tên Permission <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" class="field-input"
                                    value="{{ old('name', $permission->name) }}" required>
                                @error('name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="display_name" class="field-label">Tên Hiển Thị <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="display_name" name="display_name" class="field-input"
                                    value="{{ old('display_name', $permission->display_name) }}" required>
                                @error('display_name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="category" class="field-label">Danh Mục <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="category" name="category" class="field-input"
                                    value="{{ old('category', $permission->category) }}" list="categories" required>
                                <datalist id="categories">
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                                @error('category')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="description" class="field-label">Mô Tả</label>
                                <textarea id="description" name="description" class="field-input" rows="3">{{ old('description', $permission->description) }}</textarea>
                                @error('description')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-section">
                        <div class="last-updated">
                            <i class="fas fa-info-circle"></i>
                            Cập nhật lần cuối: {{ $permission->updated_at?->format('d/m/Y H:i') ?? 'Chưa cập nhật' }}
                        </div>
                        <div class="action-buttons">
                            <button type="button" onclick="history.back()" class="btn-cancel">
                                <i class="fas fa-times mr-2"></i>Hủy
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save mr-2"></i>Cập nhật Permission
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Warning/Delete Cards -->
                @if ($permission->roles()->count() > 0)
                    <div class="section-card mt-6">
                        <div class="alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Cảnh Báo:</strong> Permission này đang được gán cho
                                <strong>{{ $permission->roles()->count() }} role(s)</strong>.
                                Bạn cần gỡ bỏ permission khỏi các roles trước khi xóa.
                                <div class="mt-2">
                                    <strong>Các roles đang sử dụng:</strong>
                                    @foreach ($permission->roles as $role)
                                        <span class="badge badge-secondary ms-1">{{ $role->role_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="section-card mt-6">
                        <h2 class="section-title">
                            <i class="fas fa-trash text-red-600"></i>
                            Xóa Permission
                        </h2>
                        <div class="alert-danger mb-0">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <p class="mb-3">Permission này chưa được gán cho role nào. Bạn có thể xóa nó.</p>
                                <form action="{{ route('permissions.destroy', $permission->permission_id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa permission này? Hành động này không thể hoàn tác!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash mr-2"></i>Xóa Permission
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Info Sidebar -->
            <div class="lg:col-span-1">
                <div class="info-sidebar-card">
                    <h6>
                        <i class="fas fa-clock text-blue-600"></i>
                        Thông tin thời gian
                    </h6>
                    <div class="info-item">
                        <p class="info-label">Ngày tạo:</p>
                        <p class="info-value">{{ $permission->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="info-item">
                        <p class="info-label">Cập nhật lần cuối:</p>
                        <p class="info-value">{{ $permission->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="info-sidebar-card">
                    <h6>
                        <i class="fas fa-users text-purple-600"></i>
                        Roles sử dụng
                    </h6>
                    @if ($permission->roles()->count() > 0)
                        <ul class="roles-list">
                            @foreach ($permission->roles as $role)
                                <li>
                                    <span class="badge badge-primary">{{ $role->role_name }}</span>
                                    <br>
                                    <small class="text-gray-600">{{ $role->description }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mb-0 text-sm text-gray-600">
                            <i class="fas fa-info-circle"></i>
                            Chưa có role nào sử dụng permission này
                        </p>
                    @endif
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

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (!form) return;

            const requiredFields = form.querySelectorAll('input[required]');

            form.addEventListener('submit', function(e) {
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('border-red-500');

                        field.addEventListener('input', function() {
                            this.classList.remove('border-red-500');
                        });
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ các trường bắt buộc!');
                }
            });
        });
    </script>
@endsection
