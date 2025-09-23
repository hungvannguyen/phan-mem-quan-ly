<div class="students-table-container">
    <table class="students-data-table">
        <thead>
            <tr class="table-header-row">

                <th class="table-header-cell">Mã sinh viên</th>
                <th class="table-header-cell">Họ và tên</th>
                <th class="table-header-cell">Ngày sinh</th>
                <th class="table-header-cell">Lớp</th>
                <th class="table-header-cell">Ngành đào tạo</th>
                <th class="table-header-cell">Dân tộc</th>
                <th class="table-header-cell">Quốc tịch</th>
                <th class="table-header-cell">Số sổ gốc</th>
                <th class="table-header-cell">Số văn bằng</th>
                <th class="table-header-cell">Trạng thái</th>
                <th class="table-header-cell">Hành động</th>
            </tr>
        </thead>
        <tbody class="table-body">
            <tr id="loading" class="loading-overlay hidden">
                <td colspan="12" class="loading-cell">
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
                            @if ($student->gender)
                                <small class="text-muted d-block">
                                    <i class="fas fa-{{ $student->gender->value === 0 ? 'mars' : 'venus' }}"></i>
                                    {{ $student->gender_label }}
                                    @if ($student->date_of_birth)
                                        • {{ $student->age }} tuổi
                                    @endif
                                </small>
                            @endif
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
                        <span class="nation">{{ $student->nation ?? '--' }}</span>
                    </td>
                    <td class="table-cell">
                        <div class="nationality-info">
                            <span class="nationality">{{ $student->nationality ?? '--' }}</span>
                            @if ($student->nationality && $student->nationality !== 'Việt Nam')
                                <i class="fas fa-globe-americas text-info ml-1" title="Quốc tịch nước ngoài"></i>
                            @endif
                        </div>
                    </td>
                    <td class="table-cell">
                        <div class="book-number">
                            @if ($student->number_in_the_book)
                                <span class="book-number-text">{{ $student->number_in_the_book }}</span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </div>
                    </td>
                    <td class="table-cell">
                        @if ($student->degrees && $student->degrees->count() > 0)
                            <div class="degree-info">
                                <span class="badge badge-success">{{ $student->degrees->count() }} văn bằng</span>
                            </div>
                        @else
                            <span class="badge badge-warning">Chưa cấp văn bằng</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        @if ($student->status)
                            <span
                                class="badge @if ($student->status->value === 0) badge-primary
                                @elseif($student->status->value === 1) badge-success
                                @else badge-danger @endif">
                                {{ $student->status_label }}
                            </span>
                        @else
                            <span class="badge badge-secondary">Không xác định</span>
                        @endif
                    </td>
                    <td class="table-cell">
                        <div class="action-buttons">
                            <a href="{{ route('student', $student->student_id) }}" class="btn btn-table btn-sm"
                                title="Chỉnh sửa thông tin sinh viên">
                                <i class="fas fa-edit"></i> Sửa
                            </a>

                            {{-- Hiển thị nút cấp văn bằng dựa trên trạng thái học sinh --}}
                            @if ($student->status && $student->status->value === 1)
                                {{-- Sinh viên đã tốt nghiệp --}}
                                @if ($student->degrees && $student->degrees->count() > 0)
                                    <button class="btn btn-table btn-sm btn-info" title="Cấp lại văn bằng">
                                        <i class="fas fa-redo"></i> Cấp lại
                                    </button>
                                @else
                                    <button class="btn btn-table btn-sm btn-success" title="Cấp văn bằng">
                                        <i class="fas fa-certificate"></i> Cấp văn bằng
                                    </button>
                                @endif
                            @elseif ($student->status && $student->status->value === 0)
                                {{-- Sinh viên đang học --}}
                                <button class="btn btn-table btn-sm btn-secondary" disabled
                                    title="Sinh viên chưa tốt nghiệp">
                                    <i class="fas fa-clock"></i> Chưa tốt nghiệp
                                </button>
                            @else
                                {{-- Sinh viên bỏ học --}}
                                <button class="btn btn-table btn-sm btn-danger" disabled title="Sinh viên đã bỏ học">
                                    <i class="fas fa-times"></i> Đã bỏ học
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="py-4 text-center">
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
