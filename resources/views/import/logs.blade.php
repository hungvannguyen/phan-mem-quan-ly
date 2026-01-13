@extends('layouts.default')

@section('content')
    <div class="container py-6">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-history text-blue-600 mr-2"></i>
                    Import Logs
                </h1>
                <p class="text-gray-600">
                    Lịch sử các lần import dữ liệu vào hệ thống
                </p>
            </div>
            <a href="{{ route('import.index') }}" class="btn btn-primary">
                <i class="fas fa-upload mr-2"></i>
                Import mới
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <form action="{{ route('import.logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="form-label">Loại</label>
                        <select name="type" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="degree">Bằng</option>
                            <option value="political_theory">Lý luận chính trị</option>
                            <option value="certificate">Chứng chỉ</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="completed_with_errors">Có lỗi</option>
                            <option value="failed">Thất bại</option>
                            <option value="processing">Đang xử lý</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Lọc
                        </button>
                        <a href="{{ route('import.logs') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Người thực hiện</th>
                                <th>Loại dữ liệu</th>
                                <th>File</th>
                                <th>Trạng thái</th>
                                <th>Kết quả</th>
                                <th>Thời gian</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $log->getTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            <div class="font-medium">{{ $log->file_name }}</div>
                                            <div class="text-gray-500">{{ $log->formatted_file_size }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $log->getStatusBadgeColor() }}">
                                            {{ $log->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($log->status !== 'processing')
                                            <div class="text-sm">
                                                <div class="text-green-600">✓ {{ $log->success_rows ?? 0 }} thành công
                                                </div>
                                                @if ($log->error_rows > 0)
                                                    <div class="text-red-600">✗ {{ $log->error_rows }} lỗi</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-sm">
                                            <div>{{ $log->started_at->format('d/m/Y H:i') }}</div>
                                            @if ($log->completed_at)
                                                <div class="text-gray-500">{{ $log->duration }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('import.logs.show', $log->id) }}" class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-gray-500 py-8">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>Chưa có log nào</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($logs->hasPages())
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
