@extends('layouts.blank')

@section('content')
	<div class="not-found">
		<div class="not-found-code">{{ $status ?? 'Lỗi' }}</div>
		<div class="not-found-message">{{ $message ?? 'Đã xảy ra lỗi không xác định.' }}</div>
		<a href="{{ url('/') }}" class="not-found-button">Về Trang Chủ</a>
	</div>
@endsection
