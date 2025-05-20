@extends('layouts.blank')

@section('content')

	<div class="login">
		<div class="header">
			<span class="title_top">Học viện an ninh nhân dân</span>
			<span class="title_bottom">Phòng quản lý đào tạo và bồi dưỡng nâng cao</span>
		</div>
		<div class="login-body">
			<div class="title">Hệ thống quản lý văn bằng, chứng chỉ</div>

			<form class="login-form">
				<div class="form-group">
					<label for="username">Tên đăng nhập</label>
					<div class="col-span-2">
						<input type="text" class="form-control" id="username" placeholder="Nhập tên đăng nhập">
					</div>
				</div>
				<div class="form-group">
					<label for="password">Mật khẩu</label>
					<div class="col-span-2">
						<input type="password" class="form-control" id="password" placeholder="Nhập mật khẩu">
					</div>
				</div>

				<div>
					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="remember">
						<label class="form-check" for="remember">Ghi nhớ đăng nhập</label>
					</div>
					<div class="form-submit">
						<button type="submit">Đăng nhập
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
@endsection