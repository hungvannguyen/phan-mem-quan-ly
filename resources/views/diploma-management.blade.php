@extends('layouts.default')

@section('content')
	<main class="diploma-management">
		<div class="form-section">
			<form class="form">
				<div class="form-grid">
					<div class="form-group">
						<label for="diploma-name">Họ và tên</label>
						<input type="text" id="diploma-name" name="diploma-name" placeholder="Nhập tên học viên" required>
					</div>

					<div class="form-group">
						<label for="diploma-year">Năm tốt nghiệp</label>
						<input type="text" id="diploma-year" name="diploma-year" placeholder="Nhập năm tốt nghiệp" required>
					</div>

					<div class="form-group">
						<label for="diploma-id">Số hiệu</label>
						<input type="text" id="diploma-id" name="diploma-id" placeholder="Nhập số hiệu của sinh viên" required>
					</div>

					<div class="form-group">
						<label for="diploma-course">Khoá</label>
						<input type="text" id="diploma-course" name="diploma-course" placeholder="Nhập Khoá" required>
					</div>

					<div class="form-group">
						<label for="diploma-branch">Ngành</label>
						<input type="text" id="diploma-branch" name="diploma-branch" placeholder="Nhập Ngành của học viên" required>
					</div>

					<div class="form-group">
						<label for="diploma-number">Số vào sổ</label>
						<input type="text" id="diploma-number" name="diploma-number" placeholder="Nhập ngành của học viên" required>
					</div>
				</div>

				<div class="form-search-row">
					<div class="form-group form-group-select">
						<label for="diploma-select">Loại văn bằng</label>
						<div class="form-select">
							<select id="diploma-select" name="diploma-select">
								<option value="1">Văn bằng 2</option>
								<option value="2">Chứng chỉ</option>
								<option value="3">Chứng nhận</option>
								<option value="4">Giấy chứng nhận</option>
								<option value="5">Giấy chứng nhận tốt nghiệp</option>
							</select>
							<button type="submit" class="btn btn-primary btn-search">Tìm kiếm</button>
						</div>
					</div>
				</div>
			</form>

			<div class="action-container">
				<a href="{{route('student.create')}}" class="btn btn-secondary">Thêm mới</a>
				<button class="btn btn-secondary">In xác nhận</button>
				<button class="btn btn-secondary">In xác minh</button>
				<button class="btn btn-secondary">Xuất dữ liệu</button>
			</div>
		</div>
		<div class="table-section">
			<div class="table-wrapper" id="table-data">
				@include('components.students.table')
			</div>
		</div>
	</main>
@endsection
