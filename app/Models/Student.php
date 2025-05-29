<?php

namespace App\Models;

use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
	use HasFactory;

	protected $fillable = [
			'name',
			'date_of_birth',
			'place_of_birth',
			'gender',
			'nation',
			'nationality',
			'training_id',
			'number_in_the_book',
			'status',
	];

	protected $casts = [
			'name' => 'string',
			'date_of_birth' => 'date',
			'place_of_birth' => 'string',
			'gender' => StudentGender::class,
			'nation' => 'string',
			'nationality' => 'string',
			'number_in_the_book' => 'string',
			'status' => StudentStatus::class,
	];

	protected function dateOfBirth(): Attribute
	{
		return Attribute::make(
				get: fn($value) => Carbon::parse($value)->format('d-m-Y'),
		);
	}
}
