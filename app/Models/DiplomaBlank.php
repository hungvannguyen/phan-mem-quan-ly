<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiplomaBlank extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'diploma_blank_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serial_number',
        'type_id',
        'import_id',
        'status',
        'import_date',
        'issue_date',
        'recall_date',
        'issue_reason',
        'recall_reason',
        'damage_reason_id',
        'damage_description',
        'damage_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'serial_number' => 'string',
            'status' => \App\Enums\DiplomaBlankStatus::class,
            'import_date' => 'datetime',
            'issue_date' => 'datetime',
            'recall_date' => 'datetime',
            'issue_reason' => 'string',
            'recall_reason' => 'string',
            'damage_date' => 'datetime',
            'damage_description' => 'string',
        ];
    }

    /**
     * Status constants
     */
    public const STATUS_IN_STOCK = 'InStock';
    public const STATUS_ISSUED = 'Issued';
    public const STATUS_RECALLED = 'Recalled';
    public const STATUS_DAMAGED = 'Damaged';

    /**
     * Get the type of this diploma blank.
     */
    public function type()
    {
        return $this->belongsTo(DiplomaBlankType::class, 'type_id', 'type_id');
    }

    /**
     * Get the import record that created this diploma blank.
     */
    public function import()
    {
        return $this->belongsTo(DiplomaBlankImport::class, 'import_id', 'id');
    }

    /**
     * Get the degree that uses this diploma blank.
     */
    public function degree()
    {
        return $this->hasOne(Degree::class, 'diploma_blank_id', 'diploma_blank_id');
    }

    /**
     * Get the damage reason for this diploma blank.
     */
    public function damageReason()
    {
        return $this->belongsTo(DamageReason::class, 'damage_reason_id', 'id');
    }

    /**
     * Check if the diploma blank is available for use.
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_IN_STOCK;
    }

    /**
     * Check if the diploma blank is issued.
     */
    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    /**
     * Scope for available diploma blanks.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_IN_STOCK);
    }
}