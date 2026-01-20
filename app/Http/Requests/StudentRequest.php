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
            'class_name' => 'required|string|max:100',
            'course' => 'nullable|string|max:20',
            'academic_year' => 'nullable|string|max:20',
            'major_id' => 'required|exists:majors,major_id',
            'place_of_birth' => 'required|string|max:255',
            'hometown' => 'nullable|string|max:255',
            'place_of_origin' => 'nullable|string|max:255',
            'gender' => 'nullable|integer|in:0,1',
            'nation' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'status' => 'required|integer|in:0,1,2',
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

            'class_name.required' => 'Vui lòng nhập tên lớp.',
            'class_name.string' => 'Tên lớp phải là một chuỗi ký tự.',
            'class_name.max' => 'Tên lớp không được vượt quá :max ký tự.',

            'course.string' => 'Khóa phải là một chuỗi ký tự.',
            'course.max' => 'Khóa không được vượt quá :max ký tự.',

            'academic_year.string' => 'Niên khóa phải là một chuỗi ký tự.',
            'academic_year.max' => 'Niên khóa không được vượt quá :max ký tự.',

            'major_id.required' => 'Vui lòng chọn ngành đào tạo.',
            'major_id.exists' => 'Ngành đào tạo không tồn tại.',

            'place_of_birth.required' => 'Vui lòng nhập nơi sinh.',
            'place_of_birth.string' => 'Nơi sinh phải là một chuỗi ký tự.',
            'place_of_birth.max' => 'Nơi sinh không được vượt quá :max ký tự.',

            'hometown.string' => 'Quê quán phải là một chuỗi ký tự.',
            'hometown.max' => 'Quê quán không được vượt quá :max ký tự.',

            'place_of_origin.string' => 'Nguyên quán phải là một chuỗi ký tự.',
            'place_of_origin.max' => 'Nguyên quán không được vượt quá :max ký tự.',

            'gender.integer' => 'Giới tính không hợp lệ.',
            'gender.in' => 'Giới tính phải là Nam hoặc Nữ.',

            'nation.required' => 'Vui lòng nhập dân tộc.',
            'nation.string' => 'Dân tộc phải là một chuỗi ký tự.',
            'nation.max' => 'Dân tộc không được vượt quá :max ký tự.',

            'nationality.required' => 'Vui lòng nhập quốc tịch.',
            'nationality.string' => 'Quốc tịch phải là một chuỗi ký tự.',
            'nationality.max' => 'Quốc tịch không được vượt quá :max ký tự.',

            'number_in_the_book.required' => 'Vui lòng nhập số sổ gốc.',
            'number_in_the_book.string' => 'Số sổ gốc phải là một chuỗi ký tự.',
            'number_in_the_book.max' => 'Số sổ gốc không được vượt quá :max ký tự.',

            'status.required' => 'Vui lòng chọn trạng thái học tập.',
            'status.integer' => 'Trạng thái không hợp lệ.',
            'status.in' => 'Trạng thái phải là Đang học, Đã tốt nghiệp hoặc Bỏ học.',
        ];
    }
}
