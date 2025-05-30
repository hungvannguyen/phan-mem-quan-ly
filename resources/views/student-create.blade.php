@extends('layouts.default')

@section('content')
	@php use App\Enums\StudentGender;use App\Enums\StudentStatus; @endphp

	<div class="mx-auto max-w-[1140px] px-4 py-8">

		<div class="form-section">
			<h1 class="section-title">Tạo mới Sinh Viên</h1>
			<form class="form" method="POST" action="{{route('student.save')}}"> @csrf

				<div class="form-grid">
					<div class="form-group">
						<label for="name">Tên</label>
						<input type="text" id="name" name="name" value="{{old('name',"")}}" required>
						@error('name')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="dob">Ngày sinh</label>
						<input type="date" id="dob" name="date_of_birth" value="{{ old('date_of_birth',"") }}" required>
						@error('date_of_birth')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="pob">Nơi sinh</label>
						<input type="text" id="pob" name="place_of_birth" value="{{old('place_of_birth',"")}}" required>
						@error('place_of_birth')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="gender">Giới tính</label>
						<select id="gender" name="gender" required>
							@foreach(StudentGender::cases() as $gender)
								<option value="{{ $gender->value }}" @selected(old('gender',"") == $gender->value)>
									{{ $gender->label() }}
								</option>
							@endforeach
						</select>
						@error('gender')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="nation">Dân tộc</label>
						<input type="text" id="nation" name="nation" value="{{old('nation',"")}}" required>
						@error('nation')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="nationality">Quốc tịch</label>
						<input type="text" id="nationality" name="nationality" value="{{old('nationality',"")}}" required>
						@error('nationality')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="number_in_the_book">Số vào sổ</label>
						<input type="text" id="number_in_the_book" name="number_in_the_book"
										value="{{old('number_in_the_book',"")}}" required>
						@error('number_in_the_book')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="training_id">Ngành đào tạo</label>
						<select id="training_id" name="training_id" required>
							@foreach($trainings as $training)
								<option value="{{ $training->id }}" @selected(old('training_id', "") == $training->id)>
									{{ $training->name }}
								</option>
							@endforeach
						</select>
						@error('training_id')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
					<div class="form-group">
						<label for="status">Trạng thái</label>
						<select id="status" name="status" required>
							@foreach(StudentStatus::cases() as $status)
								<option value="{{ $status->value }}" @selected(old('status', "") == $status->value)>
									{{ $status->label() }}
								</option>
							@endforeach
						</select>
						@error('status')
						<div class="text-danger">{{ $message }}</div>
						@enderror
					</div>
				</div>

				<div class="action-container">
					<button type="submit" class="btn btn-primary" id="submitBtn">
						Tạo mới
					</button>
					<button type="button" class="btn btn-secondary" onclick="history.back()">
						Hủy
					</button>
				</div>
			</form>
		</div>
	</div>
@endsection