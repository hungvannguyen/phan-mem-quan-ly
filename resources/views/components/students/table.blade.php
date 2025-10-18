<div class="data-table-container">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th">Mã sinh viên</th>
                    <th class="th">Họ và tên</th>
                    <th class="th">Ngày sinh</th>
                    <th class="th">Lớp</th>
                    <th class="th">Ngành đào tạo</th>
                    <th class="th">Dân tộc</th>
                    <th class="th">Quốc tịch</th>
                    <th class="th">Số sổ gốc</th>
                    <th class="th">Số văn bằng</th>
                    <th class="th">Trạng thái</th>
                    <th class="th">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr id="loading" class="loading-overlay hidden">
                    <td colspan="11" class="loading-cell">
                        <div class="spinner"></div>
                        <span class="loading-text">Đang tải dữ liệu...</span>
                    </td>
                </tr>
                @forelse($students as $index => $student)
                    <tr class="table-row" data-student-id="{{ $student->student_id }}"
                        onclick="toggleRowHighlight(this)">
                        <td class="td">
                            <span class="student-code">{{ $student->student_code }}</span>
                        </td>
                        <td class="td">
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
                        <td class="td">
                            @if ($student->date_of_birth)
                                <span class="date-text">{{ $student->date_of_birth->format('d/m/Y') }}</span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td class="td">
                            <span class="class-name">{{ $student->class_name ?? '--' }}</span>
                        </td>
                        <td class="td">
                            @if ($student->major)
                                <div class="major-info">
                                    <span class="major-name">{{ $student->major->major_name }}</span>
                                    <small class="major-code text-muted">({{ $student->major->major_code }})</small>
                                </div>
                            @else
                                <span class="text-muted">Chưa xác định</span>
                            @endif
                        </td>
                        <td class="td">
                            <span class="nation">{{ $student->nation ?? '--' }}</span>
                        </td>
                        <td class="td">
                            <div class="nationality-info">
                                <span class="nationality">{{ $student->nationality ?? '--' }}</span>
                                @if ($student->nationality && $student->nationality !== 'Việt Nam')
                                    <i class="fas fa-globe-americas text-info ml-1" title="Quốc tịch nước ngoài"></i>
                                @endif
                            </div>
                        </td>
                        <td class="td">
                            <div class="book-number">
                                @if ($student->number_in_the_book)
                                    <span class="book-number-text">{{ $student->number_in_the_book }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </div>
                        </td>
                        <td class="td">
                            @if ($student->degrees && $student->degrees->count() > 0)
                                <div class="degree-info">
                                    <span class="status-badge status-completed">{{ $student->degrees->count() }} văn
                                        bằng</span>
                                </div>
                            @else
                                <span class="status-badge status-pending">Chưa cấp văn bằng</span>
                            @endif
                        </td>
                        <td class="td">
                            @if ($student->status)
                                <span
                                    class="status-badge @if ($student->status->value === 0) status-processing
                                    @elseif($student->status->value === 1) status-completed
                                    @else status-failed @endif">
                                    {{ $student->status_label }}
                                </span>
                            @else
                                <span class="status-badge status-unknown">Không xác định</span>
                            @endif
                        </td>
                        <td class="td">
                            <div class="action-buttons">
                                <a href="{{ route('student', $student->student_id) }}" class="btn-action btn-view"
                                    title="Chỉnh sửa thông tin sinh viên">
                                    Sửa
                                </a>

                                {{-- Hiển thị nút cấp văn bằng dựa trên trạng thái học sinh --}}
                                @if ($student->status && $student->status->value === 1)
                                    {{-- Sinh viên đã tốt nghiệp --}}
                                    @if ($student->degrees && $student->degrees->count() > 0)
                                        <button class="btn-action btn-retry" title="Cấp lại văn bằng">
                                            Cấp lại
                                        </button>
                                    @else
                                        <button class="btn-action btn-start" title="Cấp văn bằng">
                                            Cấp văn bằng
                                        </button>
                                    @endif
                                @elseif ($student->status && $student->status->value === 0)
                                    {{-- Sinh viên đang học --}}
                                    <button class="btn-action btn-pause" disabled title="Sinh viên chưa tốt nghiệp">
                                        Chưa tốt nghiệp
                                    </button>
                                @else
                                    {{-- Sinh viên bỏ học --}}
                                    <button class="btn-action btn-delete" disabled title="Sinh viên đã bỏ học">
                                        Đã bỏ học
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="td">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <h3 class="empty-title">Không có dữ liệu</h3>
                                <p class="empty-message">Không tìm thấy sinh viên nào phù hợp với bộ lọc hiện tại.</p>
                                <div class="empty-actions">
                                    <button class="btn-secondary">Thêm sinh viên mới</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Custom Pagination Section -->
<div class="table-pagination-wrapper">
    <x-pagination.custom :paginator="$students" item-name="sinh viên" label="Students Pagination Navigation"
        :per-page-options="[5, 10, 15, 25, 50]" />
</div>
