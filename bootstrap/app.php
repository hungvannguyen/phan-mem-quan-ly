<?php

use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
		->withRouting(
				web: __DIR__.'/../routes/web.php',
				commands: __DIR__.'/../routes/console.php',
				health: '/up',
		)
		->withMiddleware(function (Middleware $middleware) {
			$middleware->append(RedirectIfAuthenticated::class);
		})
		->withExceptions(function (Exceptions $exceptions) {
			$exceptions->render(function (HttpExceptionInterface $e, Request $request) {
				$status = $e->getStatusCode();

				$messages = [
						403 => 'Bạn không có quyền truy cập trang này.',
						404 => 'Không tìm thấy trang bạn yêu cầu.',
						405 => 'Phương thức không được phép.',
						500 => 'Lỗi máy chủ nội bộ. Vui lòng thử lại sau.',
				];

				$message = $messages[$status]
						?? ($e->getMessage() ?: 'Đã xảy ra lỗi không xác định.');

				return response()->view('error', compact('status', 'message'), $status);
			});
		})->create();
