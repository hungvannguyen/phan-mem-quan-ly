<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiplomaBatche extends Model
{
	use HasFactory;

	protected $fillable = [
			'import_date',
			'quality',
			'remaining',
			'error',
			'description',
	];

	protected $casts = [
			'import_date' => 'date',
			'quality' => 'integer',
			'remaining' => 'integer',
			'error' => 'integer',
			'description' => 'string',
	];
}
