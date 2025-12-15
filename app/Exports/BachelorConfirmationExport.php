<?php

namespace App\Exports;

use App\Models\Student;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BachelorConfirmationExport
{
    protected $student;

    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    /**
     * Generate bachelor confirmation document from template
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
        $templatePath = resource_path('templates/[Mau XN01] Giay xac nhan cu nhan.docx');

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $templatePath);
        }

        // Load template
        $templateProcessor = new TemplateProcessor($templatePath);

        // Prepare data
        $fullName = $this->student->full_name ?? '';
        $dateOfBirth = $this->student->date_of_birth ? $this->student->date_of_birth->format('d/m/Y') : '';
        $placeOfBirth = $this->student->place_of_birth ?? '';
        $course = $this->student->course ?? '';
        $academicYear = $this->student->academic_year ?? '';
        $decisionNumber = $degree->decision_number ?? '';
        $decisionDate = $degree->granting_date ? $degree->granting_date->format('d/m/Y') : ''; // Using granting_date as decision_date
        $majorName = $degree->major->major_name ?? $this->student->major->major_name ?? '';
        $grantingDate = $degree->granting_date ? $degree->granting_date->format('d/m/Y') : '';
        $registrationNumber = $degree->registration_number ?? '';
        $numberInBook = $this->student->number_in_the_book ?? '';
        $ranking = $degree->ranking ?? '';
        $trainingType = $this->student->training_type ?? 'Chính quy';

        // Set values for template placeholders
        $templateProcessor->setValue('ho_ten_sv', $fullName);
        $templateProcessor->setValue('ngay_sinh', $dateOfBirth);
        $templateProcessor->setValue('noi_sinh', $placeOfBirth);
        $templateProcessor->setValue('khoa', $course);
        $templateProcessor->setValue('nien_khoa', $academicYear);
        $templateProcessor->setValue('so_quyet_dinh', $decisionNumber);
        $templateProcessor->setValue('ngay_quyet_dinh', $decisionDate);
        $templateProcessor->setValue('nganh_dao_tao', $majorName);
        $templateProcessor->setValue('ngay_cap_bang', $grantingDate);
        $templateProcessor->setValue('so_hieu_bang', $registrationNumber);
        $templateProcessor->setValue('so_vao_so', $numberInBook);
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

        return "Giay_xac_nhan_cu_nhan_{$studentCode}_{$timestamp}.docx";
    }
}
