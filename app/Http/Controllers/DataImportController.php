<?php

namespace App\Http\Controllers;

use App\Imports\DegreeImport;
use App\Imports\PoliticalTheoryImport;
use App\Imports\CertificateImport;
use App\Models\ImportLog;
use App\Jobs\ProcessImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataImportController extends Controller
{
    /**
     * Hiển thị form upload
     */
    public function index()
    {
        // Lấy danh sách template files
        $templatesPath = resource_path('templates/Import');
        $templates = [];

        if (is_dir($templatesPath)) {
            $files = scandir($templatesPath);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $templates[] = $file;
                }
            }
        }

        return view('import.index', compact('templates'));
    }

    /**
     * Xử lý import với logic điều hướng
     */
    public function handleImport(Request $request)
    {
        // Validate request
        $request->validate([
            'import_type' => 'required|in:degree,political_theory,certificate',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            'use_queue' => 'boolean',
        ], [
            'import_type.required' => 'Vui lòng chọn loại dữ liệu cần import',
            'import_type.in' => 'Loại dữ liệu không hợp lệ',
            'excel_file.required' => 'Vui lòng chọn file Excel',
            'excel_file.mimes' => 'File phải có định dạng xlsx, xls hoặc csv',
            'excel_file.max' => 'File không được vượt quá 10MB',
        ]);

        $type = $request->input('import_type');
        $file = $request->file('excel_file');
        $useQueue = $request->boolean('use_queue', false);

        // Log import start
        $importLog = ImportLog::create([
            'user_id' => Auth::id(),
            'import_type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            // Nếu dùng queue
            if ($useQueue) {
                // Lưu file vào storage
                $filePath = $file->store('imports', 'local');

                // Dispatch job
                ProcessImportJob::dispatch($importLog->id, $filePath, $type);

                return back()->with('success', 'Import đã được thêm vào hàng đợi xử lý. Bạn sẽ nhận được thông báo khi hoàn thành!');
            }

            // Xử lý ngay lập tức (không dùng queue)
            $documentReference = 'IMPORT_' . $importLog->id . '_' . date('YmdHis');
            $import = $this->getImportInstance($type, $documentReference);

            Excel::import($import, $file);

            // Get statistics
            $stats = $import->getStatistics();

            // Update import log
            $importLog->update([
                'status' => $stats['errors'] > 0 ? 'completed_with_errors' : 'completed',
                'total_rows' => $stats['imported'] + $stats['errors'],
                'success_rows' => $stats['imported'],
                'error_rows' => $stats['errors'],
                'error_details' => $stats['errors'] > 0 ? json_encode($stats['error_details']) : null,
                'completed_at' => now(),
            ]);

            // Return response
            if ($stats['errors'] > 0) {
                return back()->with('warning', "Import hoàn thành với {$stats['imported']} dòng thành công và {$stats['errors']} dòng lỗi. Xem chi tiết trong phần logs.");
            }

            return back()->with('success', "Import thành công {$stats['imported']} dòng dữ liệu!");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errorDetails = [];
            foreach ($failures as $failure) {
                $errorDetails[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }

            // Update import log
            $importLog->update([
                'status' => 'failed',
                'error_rows' => count($failures),
                'error_details' => json_encode($errorDetails),
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Validation failed! Có ' . count($failures) . ' dòng không hợp lệ. Vui lòng kiểm tra lại file.')
                ->withErrors(['import' => 'Xem chi tiết lỗi trong phần logs.']);

        } catch (\Exception $e) {
            // Update import log
            $importLog->update([
                'status' => 'failed',
                'error_details' => json_encode(['error' => $e->getMessage()]),
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Get import instance theo type
     */
    private function getImportInstance(string $type, string $documentReference = null)
    {
        $documentReference = $documentReference ?? 'IMPORT_' . date('YmdHis');

        switch ($type) {
            case 'degree':
                return new DegreeImport($documentReference);
            case 'political_theory':
                return new PoliticalTheoryImport();
            case 'certificate':
                return new CertificateImport();
            default:
                throw new \InvalidArgumentException('Loại dữ liệu không hợp lệ anh Hùng ơi!');
        }
    }

    /**
     * Hiển thị danh sách import logs
     */
    public function logs()
    {
        $logs = ImportLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('import.logs', compact('logs'));
    }

    /**
     * Xem chi tiết một import log
     */
    public function showLog($id)
    {
        $log = ImportLog::with('user')->findOrFail($id);

        $errorDetails = null;
        if ($log->error_details) {
            $errorDetails = json_decode($log->error_details, true);
        }

        return view('import.log-detail', compact('log', 'errorDetails'));
    }

    /**
     * Download template file
     */
    public function downloadTemplate($index)
    {
        // Lấy danh sách template files
        $templatesPath = resource_path('templates/Import');
        $templates = [];

        if (is_dir($templatesPath)) {
            $files = scandir($templatesPath);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $templates[] = $file;
                }
            }
        }

        // Kiểm tra index hợp lệ
        if (!isset($templates[$index])) {
            return back()->with('error', 'Template không tồn tại.');
        }

        $filePath = resource_path('templates/Import/' . $templates[$index]);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File template không tồn tại.');
        }

        return response()->download($filePath);
    }
}
