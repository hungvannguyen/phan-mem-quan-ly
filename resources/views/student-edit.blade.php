@extends('layouts.default')

@section('content')
	@php use App\Enums\StudentGender;use App\Enums\StudentStatus; @endphp
	<div class="mx-auto max-w-[1140px] px-4 py-8">

		<div class="form-section">
			<h1 class="section-title">Chỉnh Sửa Thông Tin Sinh Viên: {{$student->id}}</h1>
			<form class="form" method="POST" action="{{route('student.update' , $student->id)}}"> @csrf

				<div class="form-grid">
					<div class="form-group">
						<label for="name">Tên</label>
						<input type="text" id="name" name="name" value=" {{ old('name',$student->name)}}" required>
					</div>
					<div class="form-group">
						<label for="dob">Ngày sinh</label>
						<input type="date" id="dob" name="date_of_birth"
										value="{{ old('date_of_birth', date('Y-m-d', strtotime($student->date_of_birth))) }}" required>
					</div>
					<div class="form-group">
						<label for="pob">Nơi sinh</label>
						<input type="text" id="pob" name="place_of_birth" value="{{$student->place_of_birth}}" required>
					</div>
					<div class="form-group">
						<label for="gender">Giới tính</label>
						<select id="gender" name="gender" required>
							@foreach(StudentGender::cases() as $gender)
								<option value="{{ $gender->value }}" @selected(old('gender', $student->gender->value) == $gender->value)>
									{{ $gender->label() }}
								</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="nation">Dân tộc</label>
						<input type="text" id="nation" name="nation" value="{{$student->nation}}" required>
					</div>
					<div class="form-group">
						<label for="nationality">Quốc tịch</label>
						<input type="text" id="nationality" name="nationality" value="{{$student->nationality}}" required>
					</div>
					<div class="form-group">
						<label for="number_in_the_book">Số vào sổ</label>
						<input type="text" id="number_in_the_book" name="number_in_the_book"
										value="{{$student->number_in_the_book}}" required>
					</div>
					<div class="form-group">
						<label for="training_id">Ngành đào tạo</label>
						<select id="training_id" name="training_id" required>
							@foreach($trainings as $training)
								<option value="{{ $training->id }}" @selected(old('training_id', $student->training_id) == $training->id)>
									{{ $training->name }}
								</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="status">Trạng thái</label>
						<select id="status" name="status" required>
							@foreach(StudentStatus::cases() as $status)
								<option value="{{ $status->value }}" @selected(old('status', $student->status->value) == $status->value)>
									{{ $status->label() }}
								</option>

							@endforeach
						</select>

					</div>
				</div>

				<div class="action-container">
					<button type="submit" class="btn btn-primary" id="submitBtn">
						Cập nhật
					</button>
					<button type="button" class="btn btn-secondary" onclick="history.back()">
						Hủy
					</button>
				</div>
			</form>
		</div>
	</div>
@endsection