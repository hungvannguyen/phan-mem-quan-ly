<div class="students-table-container">
    <table class="students-data-table">
        <thead>
            <tr class="table-header-row">

                <th class="table-header-cell">Mã sinh viên</th>
                <th class="table-header-cell">Họ và tên</th>
                <th class="table-header-cell">Ngày sinh</th>
                <th class="table-header-cell">Lớp</th>
                <th class="table-header-cell">Ngành đào tạo</th>
                <th class="table-header-cell">Số văn bằng</th>
                <th class="table-header-cell">Trạng thái</th>
                <th class="table-header-cell">Hành động</th>
            </tr>
        </thead>
        <tbody class="table-body">
            <tr id="loading" class="loading-overlay hidden">
                <td colspan="9" class="loading-cell">
                    <div class="spinner"></div>
                    <span class="loading-text">Đang tải dữ liệu...</span>
                </td>
            </tr>
            @forelse($students as $index => $student)
                <tr class="table-row" data-student-id="{{ $student->student_id }}" onclick="toggleRowHighlight(this)">
                    <td class="table-cell">
                        <span class="student-code">{{ $student->student_code }}</span>
                    </td>
                    <td class="table-cell">
                        <div class="student-info">
                            <span class="student-name">{{ $student->full_name }}</span>
                        </div>
                    </td>
                    <td class="table-cell">
                        @if ($student->date_of_birth)
                            <span class="date-birth">{{ $student->date_of_birth->format('d/m/Y') }}</span>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        <span class="class-name">{{ $student->class_name ?? '--' }}</span>
                    </td>
                    <td class="table-cell">
                        @if ($student->major)
                            <div class="major-info">
                                <span class="major-name">{{ $student->major->major_name }}</span>
                                <small class="major-code text-muted">({{ $student->major->major_code }})</small>
                            </div>
                        @else
                            <span class="text-muted">Chưa xác định</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        @if ($student->degrees && $student->degrees->count() > 0)
                            <div class="degree-count">
                                <span class="badge badge-success">{{ $student->degrees->count() }}</span>
                            </div>
                        @else
                            <span class="badge badge-warning">Chưa cấp</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        @if ($student->degrees && $student->degrees->count() > 0)
                            @php
                                $latestDegree = $student->degrees->sortByDesc('created_at')->first();
                            @endphp
                            <span class="badge badge-success">Đã cấp</span>
                        @else
                            <span class="badge badge-warning">Chưa cấp</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        <div class="action-buttons">
                            <a href="{{ route('student', $student->student_id) }}" class="btn btn-table btn-sm"
                                title="Chỉnh sửa thông tin">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            @if ($student->degrees && $student->degrees->count() > 0)
                                <button class="btn btn-table btn-sm btn-info" title="Cấp lại văn bằng">
                                    <i class="fas fa-redo"></i> Cấp lại Văn bằng
                                </button>
                            @else
                                <button class="btn btn-table btn-sm btn-success" title="Cấp văn bằng">
                                    <i class="fas fa-certificate"></i> Cấp Văn Bằng
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-4 text-center">
                        <div class="empty-state">
                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Không tìm thấy sinh viên nào</p>
                            <small class="text-muted">Hãy thử điều chỉnh bộ lọc tìm kiếm hoặc thêm sinh viên mới</small>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Custom Pagination Section -->
<div class="students-pagination-wrapper">
    <x-pagination.custom :paginator="$students" item-name="sinh viên" label="Students Pagination Navigation"
        :per-page-options="[5, 10, 15, 25, 50]" />
</div>
