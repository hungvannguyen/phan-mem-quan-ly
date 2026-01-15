<?php

namespace App\Exports;

use App\Models\Student;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DiplomaVerificationExport
{
    protected $student;
    protected $params = [];

    public function __construct(Student $student, array $params = [])
    {
        $this->student = $student;
        $this->params = $params;
    }

    /**
     * Generate diploma verification document from template
     *
     * @return string Path to the generated file
     */
    public function generate()
    {
        // Load student with all relationships
        $this->student->load(['major', 'degrees.major', 'degrees.diplomaBlank.type']);

        // Get the first degree
        $degree = $this->student->degrees->first();

        if (!$degree) {
            throw new \Exception('Sinh viên chưa được cấp văn bằng');
        }

        // Template path
        $templatePath = resource_path('templates/[Mau XM01] Cong van tra loi xac minh van bang.docx');

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $templatePath);
        }

        // Load template
        $templateProcessor = new TemplateProcessor($templatePath);

        // Prepare data
        $majorName = $degree->major->major_name ?? $this->student->major->major_name ?? '';
        $registrationNumber = $degree->registration_number ?? '';
        $numberInBook = $this->student->number_in_the_book ?? '';
        $grantingDate = $degree->granting_date ? $degree->granting_date->format('d/m/Y') : '';
        $fullName = $this->student->full_name ?? '';
        $dateOfBirth = $this->student->date_of_birth ? $this->student->date_of_birth->format('d/m/Y') : '';
        $ranking = $degree->ranking ?? '';
        $trainingType = $degree->training_type ?? 'Chính quy';

        // Set values for template placeholders
        $donVi = $this->params['don_vi_yeu_cau'] ?? '';
        $soCv = $this->params['so_cv_den'] ?? '';
        $ngayCv = $this->params['ngay_cv_den'] ?? '';

        // If date provided in ISO format, convert to dd/mm/YYYY for template
        if (!empty($ngayCv)) {
            try {
                $d = new \DateTime($ngayCv);
                $ngayCv = $d->format('d/m/Y');
            } catch (\Exception $e) {
                // keep original if parsing fails
            }
        }

        $templateProcessor->setValue('don_vi_yeu_cau', $donVi);
        $templateProcessor->setValue('so_cv_den', $soCv);
        $templateProcessor->setValue('ngay_cv_den', $ngayCv);
        $templateProcessor->setValue('noi_dung_yeu_cau', 'xác minh văn bằng');
        $templateProcessor->setValue('nganh_dao_tao', $majorName);
        $templateProcessor->setValue('so_hieu_bang', $registrationNumber);
        $templateProcessor->setValue('so_vao_so', $numberInBook);
        $templateProcessor->setValue('ngay_cap_bang', $grantingDate);
        $templateProcessor->setValue('ho_ten_sv', $fullName);
        $templateProcessor->setValue('ngay_sinh', $dateOfBirth);
        $templateProcessor->setValue('xep_loai', $ranking);
        $templateProcessor->setValue('hinh_thuc_dt', $trainingType);

        // Generate filename and output path
        $filename = $this->generateFilename();
        $outputPath = storage_path('app/temp/' . $filename);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Save the document
        $templateProcessor->saveAs($outputPath);

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \Exception('Không thể tạo file xuất');
        }

        return $outputPath;
    }

    /**
     * Generate filename for the export
     *
     * @return string
     */
    protected function generateFilename()
    {
        $studentCode = $this->student->student_code ?? 'unknown';
        $timestamp = now()->format('YmdHis');

        return "Xac_minh_van_bang_{$studentCode}_{$timestamp}.docx";
    }

    /**
     * Generate and download the document
     *
     * @return BinaryFileResponse
     */
    public function download()
    {
        $filePath = $this->generate();
        $filename = basename($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }
}
