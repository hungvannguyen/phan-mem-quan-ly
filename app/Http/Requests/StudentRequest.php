<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $studentId = $this->route('student')?->student_id;

        return [
            'student_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_code')->ignore($studentId, 'student_id'),
            ],
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'class_name' => 'nullable|string|max:100',
            'major_id' => 'required|exists:majors,major_id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_code.required' => 'Vui lòng nhập mã sinh viên.',
            'student_code.string' => 'Mã sinh viên phải là một chuỗi ký tự.',
            'student_code.max' => 'Mã sinh viên không được vượt quá :max ký tự.',
            'student_code.unique' => 'Mã sinh viên đã tồn tại.',

            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.string' => 'Họ và tên phải là một chuỗi ký tự.',
            'full_name.max' => 'Họ và tên không được vượt quá :max ký tự.',

            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',

            'class_name.string' => 'Tên lớp phải là một chuỗi ký tự.',
            'class_name.max' => 'Tên lớp không được vượt quá :max ký tự.',

            'major_id.required' => 'Vui lòng chọn ngành đào tạo.',
            'major_id.exists' => 'Ngành đào tạo không tồn tại.',

            // Các thông báo cho trường tạm thời comment
            // 'place_of_birth.string' => 'Nơi sinh phải là một chuỗi ký tự.',
            // 'place_of_birth.max' => 'Nơi sinh không được vượt quá :max ký tự.',
            // 'gender.integer' => 'Giới tính không hợp lệ.',
            // 'nation.string' => 'Dân tộc phải là một chuỗi ký tự.',
            // 'nation.max' => 'Dân tộc không được vượt quá :max ký tự.',
            // 'nationality.string' => 'Quốc tịch phải là một chuỗi ký tự.',
            // 'nationality.max' => 'Quốc tịch không được vượt quá :max ký tự.',
            // 'number_in_the_book.string' => 'Số vào sổ phải là một chuỗi ký tự.',
            // 'number_in_the_book.max' => 'Số vào sổ không được vượt quá :max ký tự.',
            // 'status.integer' => 'Trạng thái không hợp lệ.',
        ];
    }
}