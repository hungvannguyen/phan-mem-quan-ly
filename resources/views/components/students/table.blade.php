<table class="data-table">
	<thead>
	<tr>
		<th class="w-10">#</th>
		<th class="w-40">Họ và tên</th>
		<th class="w-32">Ngày sinh</th>
		<th class="w-32">Nơi sinh</th>
		<th class="w-24">Giới tính</th>
		<th class="w-24">Dân tộc</th>
		<th class="w-32">Quốc tịch</th>
		<th class="w-40">Ngành đào tạo</th>
		<th class="w-28">Số hiệu</th>
		<th class="w-28">Số vào sổ</th>
		<th class="w-32">Tình trạng</th>
		<th class="w-28">Hành động</th>
	</tr>
	</thead>
	<tbody class="relative">
	<tr id="loading" class="loading-overlay hidden">
		<td>
			<div class="spinner"></div>
			<span class="loading-text">Đang tải dữ liệu...</span>
		</td>
	</tr>
	@foreach( $students as $student)
		<tr>
			<td>{{$student->id}}</td>
			<td>{{$student->name}}</td>
			<td>{{$student->date_of_birth}}</td>
			<td>{{$student->place_of_birth}}</td>
			<td>{{$student->gender->label()}}</td>
			<td>{{$student->nation}}</td>
			<td>{{$student->nationality}}</td>
			<td>CNTT</td>
			<td>123456</td>
			<td>{{$student->number_in_the_book}}</td>
			<td>{{$student->status->label()}}</td>
			<td>
				<button class="btn btn-table">Sửa</button>
				<button class="btn btn-table">Cấp lại</button>
				<button class="btn btn-table">Chi tiết</button>
			</td>
		</tr>
	@endforeach
	</tbody>
</table>

<div class="pagination">
	{{$students->links()}}
</div>
