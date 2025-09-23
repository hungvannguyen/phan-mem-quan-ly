<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Degree extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'degree_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'degree_type',
        'diploma_blank_id',
        'registration_number',
        'granting_date',
        'graduation_year',
        'ranking',
        'decision_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'degree_type' => 'string',
            'registration_number' => 'string',
            'granting_date' => 'date',
            'graduation_year' => 'integer',
            'ranking' => 'string',
            'decision_number' => 'string',
        ];
    }

    /**
     * Get the student who received this degree.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the diploma blank used for this degree.
     */
    public function diplomaBlank()
    {
        return $this->belongsTo(DiplomaBlank::class, 'diploma_blank_id', 'diploma_blank_id');
    }

    /**
     * Get the full degree information with related data.
     */
    public function getFullDegreeInfo()
    {
        return $this->load(['student.major', 'diplomaBlank.type']);
    }
}
