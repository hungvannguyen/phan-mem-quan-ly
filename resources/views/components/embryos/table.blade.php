<h3 class="section-subtitle">Danh sách Lô Phôi</h3>
<div class="table-wrapper">
	<table class="data-table">
		<thead>
		<tr>
			<th class="w-10">ID</th>
			<th class="w-32">Ngày nhập</th>
			<th class="w-32">SL Ban đầu</th>
			<th class="w-32">SL Còn lại</th>
			<th class="w-28">SL Lỗi</th>
			<th class="w-64">Ghi chú</th>
			<th class="w-40">Hành động</th>
		</tr>
		</thead>
		<tbody>
		@foreach($diplomaBatches as $diplomaBatche)
			<tr>
				<td>{{$diplomaBatche->id}}</td>
				<td>{{$diplomaBatche->import_date->format('d-m-Y')}}</td>
				<td>{{$diplomaBatche->quality}}</td>
				<td>{{$diplomaBatche->remaining}}</td>
				<td>{{$diplomaBatche->error}}</td>
				<td>{{$diplomaBatche->description}}</td>
				<td>
					<button class="btn btn-table">Sửa</button>
					<button class="btn btn-table">Chi tiết</button>
					<button class="btn btn-table">Xóa</button>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>

<div class="pagination">
	{{$diplomaBatches->links()}}
</div>