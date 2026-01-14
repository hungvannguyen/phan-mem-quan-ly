<?php

namespace App\Models;

use App\Enums\DegreeStatus;
use App\Models\Traits\LogsChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Degree extends Model
{
    use HasFactory, SoftDeletes, LogsChanges;

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
        'council_decision_number',
        'council_decision_date',
        'graduation_decision_number',
        'graduation_decision_date',
        'major_id',
        'major_name',
        'defense_date',
        'training_start_date',
        'training_end_date',
        'status',
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
            'status' => DegreeStatus::class,
            'notes' => 'string',
        ];
    }

    /**
     * Field labels for logging (tiếng Việt)
     *
     * @var array
     */
    protected $fieldLabels = [
        'registration_number' => 'số đăng ký',
        'degree_type' => 'loại văn bằng',
        'major_name' => 'ngành/chuyên ngành',
        'ranking' => 'xếp loại',
        'granting_date' => 'ngày cấp',
        'graduation_year' => 'năm tốt nghiệp',
        'decision_number' => 'số quyết định',
        'council_decision_number' => 'số QĐ thành lập hội đồng',
        'council_decision_date' => 'ngày QĐ thành lập hội đồng',
        'graduation_decision_number' => 'số QĐ công nhận tốt nghiệp',
        'graduation_decision_date' => 'ngày QĐ công nhận tốt nghiệp',
        'defense_date' => 'ngày bảo vệ',
        'training_start_date' => 'ngày bắt đầu đào tạo',
        'training_end_date' => 'ngày kết thúc đào tạo',
        'diploma_blank_id' => 'số hiệu văn bằng',
        'major_id' => 'mã ngành',
        'status' => 'trạng thái',
        'notes' => 'ghi chú',
    ];

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
     * Sử dụng ChangeLog với hasMany vì entity_type lưu class basename
     */
    public function adjustments()
    {
        return $this->hasMany(ChangeLog::class, 'entity_id', 'degree_id')
            ->where('entity_type', 'Degree')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all reissues for this degree.
     */
    public function reissues()
    {
        return $this->hasMany(DegreeReissue::class, 'degree_id', 'degree_id')
            ->orderBy('decision_date', 'desc');
    }

    /**
     * Get the full degree information with related data.
     */
    public function getFullDegreeInfo()
    {
        return $this->load(['student.major', 'diplomaBlank.type']);
    }
}
