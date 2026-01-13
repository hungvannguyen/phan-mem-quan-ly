<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'import_type',
        'file_name',
        'file_size',
        'status',
        'total_rows',
        'success_rows',
        'error_rows',
        'error_details',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'error_rows' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relationship với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human readable file size
     */
    public function getFormattedFileSizeAttribute()
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Get duration
     */
    public function getDurationAttribute()
    {
        if (!$this->completed_at || !$this->started_at) {
            return null;
        }

        return $this->started_at->diffForHumans($this->completed_at, true);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('import_type', $type);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get import type label
     */
    public function getTypeLabel()
    {
        return match($this->import_type) {
            'degree' => 'Bằng Cử nhân, Thạc sĩ, Tiến sĩ',
            'political_theory' => 'Lý luận chính trị',
            'certificate' => 'Chứng chỉ',
            default => $this->import_type,
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor()
    {
        return match($this->status) {
            'processing' => 'bg-blue-500',
            'completed' => 'bg-green-500',
            'completed_with_errors' => 'bg-yellow-500',
            'failed' => 'bg-red-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return match($this->status) {
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'completed_with_errors' => 'Hoàn thành có lỗi',
            'failed' => 'Thất bại',
            default => $this->status,
        };
    }
}
