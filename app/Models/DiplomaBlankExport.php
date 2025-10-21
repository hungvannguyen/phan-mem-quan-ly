<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiplomaBlankExport extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'export_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_id',
        'course',
        'graduation_year',
        'decision_number',
        'issue_date',
        'quantity_requested',
        'quantity_exported',
        'export_date',
        'export_ranges',
        'notes',
        'exported_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'export_date' => 'datetime',
            'export_ranges' => 'array',
            'quantity_requested' => 'integer',
            'quantity_exported' => 'integer',
        ];
    }

    /**
     * Get the type of this diploma blank export.
     */
    public function type()
    {
        return $this->belongsTo(DiplomaBlankType::class, 'type_id', 'type_id');
    }

    /**
     * Get the user who exported the diploma blanks.
     */
    public function exportedBy()
    {
        return $this->belongsTo(User::class, 'exported_by', 'user_id');
    }

    /**
     * Get the diploma blanks that were exported.
     */
    public function diplomaBlanks()
    {
        return $this->hasMany(DiplomaBlank::class, 'export_id', 'export_id');
    }

    /**
     * Get the formatted export ID.
     */
    public function getFormattedExportIdAttribute()
    {
        return 'EX' . str_pad($this->export_id, 6, '0', STR_PAD_LEFT);
    }
}
