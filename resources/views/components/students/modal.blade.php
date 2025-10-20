@php
    use App\Enums\StudentGender;
    use App\Enums\StudentStatus;
@endphp
<div class="modal__dialog">
    <div class="modal__overlay" aria-hidden="true"></div>
    <div class="modal__container">
        <div class="modal__content">
            <div class="modal__header">
                <div class="modal__title-wrapper">
                    <h3 class="modal__title" id="modal-title">
                        Thêm Sinh Viên Mới </h3>
                </div>
            </div>
            <form class="form" method="POST" action="{{ route('students.create') }}">
                <div class="modal__body">
                    @csrf
                    <div class="form__grid">
                        <div class="form-group">
                            <label for="name">Tên</label>
                            <input type="text" id="name"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="name" required>
                        </div>
                        <div class="form-group">
                            <x-vietnamese-date-input id="dob" name="date_of_birth" label="Ngày sinh"
                                :required="true" value=""
                                inputClass="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div class="form-group">
                            <label for="pob">Nơi sinh</label>
                            <input type="text" id="pob"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="place_of_birth" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Giới tính</label>
                            <select id="gender"
                                class="w-auto rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="gender" required>
                                @foreach (StudentGender::cases() as $gender)
                                    <option value="{{ $gender->value }}"
                                        {{ old('gender', '') == $gender->value ? 'selected' : '' }}>
                                        {{ $gender->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nation">Dân tộc</label>
                            <input type="text" id="nation"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="nation" required>
                        </div>
                        <div class="form-group">
                            <label for="nationality">Quốc tịch</label>
                            <input type="text" id="nationality"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="nationality" required>
                        </div>
                        <div class="form-group">
                            <label for="number_in_the_book">Số vào sổ</label>
                            <input type="text" id="number_in_the_book"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="number_in_the_book" required>
                        </div>
                        <div class="form-group">
                            <label for="training_id">Ngành đào tạo</label>
                            <select id="training_id"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="training_id" required>
                                @foreach ($trainings as $training)
                                    <option value="{{ $training->id }}"
                                        {{ old('$training_id', '') == $training->id ? 'selected' : '' }}>
                                        {{ $training->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status"
                                class="w-full rounded-md border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                name="status" required>
                                @foreach (StudentStatus::cases() as $status)
                                    <option value="{{ $status->value }}"
                                        {{ old('status', '') == $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal__footer">
                    <button type="submit" class="modal__footer--action-primary" id="submitBtn" disabled>
                        Lưu
                    </button>
                    <button type="button" class="modal__footer--action-secondary" onclick="closeModal()">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
