<?php

namespace App\Models;

use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use App\Models\Traits\LogsChanges;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUlids, SoftDeletes, LogsChanges;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'student_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_code',
        'full_name',
        'date_of_birth',
        'class_name',
        'course',
        'academic_year',
        'major_id',
        'place_of_birth',
        'hometown',
        'place_of_origin',
        'gender',
        'nation',
        'nationality',
        'number_in_the_book',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'full_name' => 'string',
            'date_of_birth' => 'date',
            'class_name' => 'string',
            'student_code' => 'string',
            'place_of_birth' => 'string',
            'hometown' => 'string',
            'place_of_origin' => 'string',
            'gender' => StudentGender::class,
            'nation' => 'string',
            'nationality' => 'string',
            'number_in_the_book' => 'string',
            'status' => StudentStatus::class,
        ];
    }

    /**
     * Get the major that the student belongs to.
     */
    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    /**
     * Get the degrees issued to this student.
     */
    public function degrees()
    {
        return $this->hasMany(Degree::class, 'student_id', 'student_id');
    }

    /**
     * Get the full name with gender prefix.
     */
    public function getFullNameWithGenderAttribute(): string
    {
        $prefix = $this->gender === StudentGender::Male ? 'Anh' : 'Chị';
        return "{$prefix} {$this->full_name}";
    }

    /**
     * Get the age of the student.
     */
    public function getAgeAttribute(): int
    {
        return \Carbon\Carbon::parse($this->date_of_birth)->diffInYears(now());
    }

    /**
     * Check if the student has graduated.
     */
    public function hasGraduated(): bool
    {
        return $this->status === StudentStatus::Graduate;
    }

    /**
     * Check if the student is currently studying.
     */
    public function isStudying(): bool
    {
        return $this->status === StudentStatus::Studying;
    }

    /**
     * Get the student's status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Get the student's gender label.
     */
    public function getGenderLabelAttribute(): string
    {
        return $this->gender->label();
    }

    // Legacy relationship - keep commented for reference
    // public function training()
    // {
    //     return $this->belongsTo(Training::class);
    // }
}
