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
		$studentId = $this->route('student')?->id;

		return [
				'name' => 'required|string|max:255',
				'training_id' => 'required|integer',
				'date_of_birth' => 'required|date',
				'place_of_birth' => 'required|string|max:255',
				'gender' => 'required|integer',
				'nation' => 'required|string|max:255',
				'nationality' => 'required|string|max:255',
				'number_in_the_book' => [
						'required',
						'string',
						'max:255',
						Rule::unique('students', 'number_in_the_book')->ignore($studentId),
				],
				'status' => 'required|integer',
		];
	}

	public function messages(): array
	{
		return [
				'name.required' => 'Vui lòng nhập tên.',
				'name.string' => 'Tên phải là một chuỗi ký tự.',
				'name.max' => 'Tên không được vượt quá :max ký tự.',

				'training_id.required' => 'Vui lòng chọn ngành đào tạo.',
				'training_id.integer' => 'Ngành đào tạo không hợp lệ.',

				'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
				'date_of_birth.date' => 'Ngày sinh không hợp lệ.',

				'place_of_birth.required' => 'Vui lòng nhập nơi sinh.',
				'place_of_birth.string' => 'Nơi sinh phải là một chuỗi ký tự.',
				'place_of_birth.max' => 'Nơi sinh không được vượt quá :max ký tự.',

				'gender.required' => 'Vui lòng chọn giới tính.',
				'gender.integer' => 'Giới tính không hợp lệ.',

				'nation.required' => 'Vui lòng nhập dân tộc.',
				'nation.string' => 'Dân tộc phải là một chuỗi ký tự.',
				'nation.max' => 'Dân tộc không được vượt quá :max ký tự.',

				'nationality.required' => 'Vui lòng nhập quốc tịch.',
				'nationality.string' => 'Quốc tịch phải là một chuỗi ký tự.',
				'nationality.max' => 'Quốc tịch không được vượt quá :max ký tự.',

				'number_in_the_book.required' => 'Vui lòng nhập số vào sổ.',
				'number_in_the_book.string' => 'Số vào sổ phải là một chuỗi ký tự.',
				'number_in_the_book.max' => 'Số vào sổ không được vượt quá :max ký tự.',
				'number_in_the_book.unique' => 'Số vào sổ đã tồn tại.',

				'status.required' => 'Vui lòng chọn trạng thái.',
				'status.integer' => 'Trạng thái không hợp lệ.',
		];
	}

}
