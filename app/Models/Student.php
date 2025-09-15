<?php

namespace App\Models;

use App\Enums\StudentGender;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, HasUlids;

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
        'major_id',
        // Commented preserved fields - uncomment when needed
        // 'place_of_birth',
        // 'gender',
        // 'nation',
        // 'nationality',
        // 'number_in_the_book',
        // 'status',
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
            // Commented preserved casts - uncomment when needed
            // 'place_of_birth' => 'string',
            // 'gender' => StudentGender::class,
            // 'nation' => 'string',
            // 'nationality' => 'string',
            // 'number_in_the_book' => 'string',
            // 'status' => StudentStatus::class,
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

    // Legacy relationship - keep commented for reference
    // public function training()
    // {
    //     return $this->belongsTo(Training::class);
    // }
}