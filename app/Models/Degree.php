<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Degree extends Model
{
    use HasFactory, SoftDeletes;

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
        'council_decision_number',
        'council_decision_date',
        'graduation_decision_number',
        'graduation_decision_date',
        'major_id',
        'major_name',
        'defense_date',
        'training_start_date',
        'training_end_date',
        'notes',
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
            'defense_date' => 'date',
            'training_start_date' => 'date',
            'training_end_date' => 'date',
            'council_decision_date' => 'date',
            'graduation_decision_date' => 'date',
            'graduation_year' => 'integer',
            'ranking' => 'string',
            'decision_number' => 'string',
            'council_decision_number' => 'string',
            'graduation_decision_number' => 'string',
            'major_name' => 'string',
            'notes' => 'string',
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
     * Get the major for this degree.
     */
    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    /**
     * Get all adjustments for this degree.
     */
    public function adjustments()
    {
        return $this->hasMany(DegreeAdjustment::class, 'degree_id', 'degree_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the full degree information with related data.
     */
    public function getFullDegreeInfo()
    {
        return $this->load(['student.major', 'diplomaBlank.type']);
    }
}
