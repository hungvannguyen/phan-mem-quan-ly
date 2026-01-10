<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DegreeReissue extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'reissue_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'degree_id',
        'old_diploma_blank_id',
        'new_diploma_blank_id',
        'edit_content',
        'recall_decision',
        'decision_date',
        'is_recalled',
        'is_destroyed',
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
            'decision_date' => 'date',
            'edit_content' => 'string',
            'recall_decision' => 'string',
            'notes' => 'string',
            'is_recalled' => 'boolean',
            'is_destroyed' => 'boolean',
        ];
    }

    /**
     * Get the degree that this reissue belongs to.
     */
    public function degree()
    {
        return $this->belongsTo(Degree::class, 'degree_id', 'degree_id');
    }

    /**
     * Get the old diploma blank.
     */
    public function oldDiplomaBlank()
    {
        return $this->belongsTo(DiplomaBlank::class, 'old_diploma_blank_id', 'diploma_blank_id');
    }

    /**
     * Get the new diploma blank.
     */
    public function newDiplomaBlank()
    {
        return $this->belongsTo(DiplomaBlank::class, 'new_diploma_blank_id', 'diploma_blank_id');
    }
}
