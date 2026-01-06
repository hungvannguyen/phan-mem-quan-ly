<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeAdjustment extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'adjustment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'degree_id',
        'adjusted_field',
        'old_value',
        'new_value',
        'adjustment_content',
        'decision_number',
        'decision_date',
        'adjusted_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'adjustment_content' => 'string',
            'decision_number' => 'string',
            'decision_date' => 'date',
        ];
    }

    /**
     * Get the degree that this adjustment belongs to.
     */
    public function degree()
    {
        return $this->belongsTo(Degree::class, 'degree_id', 'degree_id');
    }

    /**
     * Get the user who made this adjustment.
     */
    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by', 'user_id');
    }
}
