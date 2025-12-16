<?php

namespace App\Services;

use App\Contracts\ExportServiceContract;
use App\Models\Student;
use App\Exports\DiplomaVerificationExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DiplomaVerificationService implements ExportServiceContract
{
    /**
     * Export diploma verification document
     *
     * @param array $data Must contain 'student' key with Student instance
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function export(array $data): BinaryFileResponse
    {
        if (!isset($data['student']) || !($data['student'] instanceof Student)) {
            throw new \Exception('Dữ liệu sinh viên không hợp lệ');
        }

        $student = $data['student'];

        if ($student->degrees->count() === 0) {
            throw new \Exception('Sinh viên chưa được cấp văn bằng nào!');
        }

        $export = new DiplomaVerificationExport($student);
        $filePath = $export->generate();

        return response()->download($filePath, basename($filePath), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}