@extends('layouts.default')

@section('content')
    <div class="container py-6">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('import.logs') }}" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại danh sách
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                Chi tiết Import Log #{{ $log->id }}
            </h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Summary Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold">Thông tin tổng quan</h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Loại dữ liệu</label>
                                <p class="font-medium">{{ $log->getTypeLabel() }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Trạng thái</label>
                                <p>
                                    <span class="badge {{ $log->getStatusBadgeColor() }}">
                                        {{ $log->getStatusLabel() }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Người thực hiện</label>
                                <p class="font-medium">{{ $log->user->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Email</label>
                                <p class="font-medium">{{ $log->user->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Tên file</label>
                                <p class="font-medium">{{ $log->file_name }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Kích thước</label>
                                <p class="font-medium">{{ $log->formatted_file_size }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Bắt đầu</label>
                                <p class="font-medium">{{ $log->started_at?->format('d/m/Y H:i:s') }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Kết thúc</label>
                                <p class="font-medium">
                                    {{ $log->completed_at?->format('d/m/Y H:i:s') ?? 'Chưa hoàn thành' }}</p>
                            </div>
                            @if ($log->duration)
                                <div class="col-span-2">
                                    <label class="text-sm text-gray-600">Thời gian xử lý</label>
                                    <p class="font-medium">{{ $log->duration }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Results Card -->
                @if ($log->status !== 'processing')
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">Kết quả Import</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <div class="text-3xl font-bold text-blue-600">{{ $log->total_rows ?? 0 }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Tổng số dòng</div>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <div class="text-3xl font-bold text-green-600">{{ $log->success_rows ?? 0 }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Thành công</div>
                                </div>
                                <div class="p-4 bg-red-50 rounded-lg">
                                    <div class="text-3xl font-bold text-red-600">{{ $log->error_rows ?? 0 }}</div>
                                    <div class="text-sm text-gray-600 mt-1">Lỗi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Error Details -->
                @if ($errorDetails && count($errorDetails) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold text-red-600">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Chi tiết lỗi ({{ count($errorDetails) }} lỗi)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                @foreach ($errorDetails as $index => $error)
                                    <div class="border border-red-200 bg-red-50 rounded p-4">
                                        <div class="flex items-start">
                                            <span
                                                class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 flex-shrink-0">
                                                {{ $index + 1 }}
                                            </span>
                                            <div class="flex-1">
                                                @if (isset($error['row']))
                                                    <p class="font-semibold text-red-900 mb-1">
                                                        Dòng {{ $error['row'] }}
                                                    </p>
                                                @endif

                                                @if (isset($error['message']))
                                                    <p class="text-red-700 mb-2">{{ $error['message'] }}</p>
                                                @endif

                                                @if (isset($error['error']))
                                                    <p class="text-red-700 mb-2">{{ $error['error'] }}</p>
                                                @endif

                                                @if (isset($error['errors']))
                                                    <ul class="list-disc ml-4 text-sm text-red-600">
                                                        @foreach ((array) $error['errors'] as $err)
                                                            <li>{{ is_array($err) ? implode(', ', $err) : $err }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif

                                                @if (isset($error['values']) && is_array($error['values']))
                                                    <details class="mt-2">
                                                        <summary
                                                            class="cursor-pointer text-sm text-red-600 hover:text-red-800">
                                                            Xem dữ liệu dòng
                                                        </summary>
                                                        <pre class="mt-2 p-2 bg-red-100 rounded text-xs overflow-x-auto">{{ json_encode($error['values'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </details>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Side Info -->
            <div class="space-y-6">
                <!-- Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold">
                            <i class="fas fa-clock text-blue-600 mr-2"></i>
                            Timeline
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                                <div>
                                    <p class="font-medium text-sm">Import được tạo</p>
                                    <p class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                            @if ($log->started_at)
                                <div class="flex items-start">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                                    <div>
                                        <p class="font-medium text-sm">Bắt đầu xử lý</p>
                                        <p class="text-xs text-gray-500">{{ $log->started_at->format('d/m/Y H:i:s') }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($log->completed_at)
                                <div class="flex items-start">
                                    <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                                    <div>
                                        <p class="font-medium text-sm">Hoàn thành</p>
                                        <p class="text-xs text-gray-500">{{ $log->completed_at->format('d/m/Y H:i:s') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold">Thao tác</h3>
                    </div>
                    <div class="card-body space-y-2">
                        @if ($log->status === 'processing')
                            <button class="btn btn-outline w-full" onclick="location.reload()">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Làm mới
                            </button>
                        @endif

                        <a href="{{ route('import.logs') }}" class="btn btn-outline w-full">
                            <i class="fas fa-list mr-2"></i>
                            Xem tất cả logs
                        </a>

                        <a href="{{ route('import.index') }}" class="btn btn-primary w-full">
                            <i class="fas fa-upload mr-2"></i>
                            Import mới
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
