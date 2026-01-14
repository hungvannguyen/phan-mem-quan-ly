<?php

namespace App\Models\Traits;

use App\Models\ChangeLog;

/**
 * Trait để quản lý quan hệ với ChangeLog
 * Auto-logging đã được chuyển sang Observer (DegreeObserver, StudentObserver)
 */
trait LogsChanges
{
    /**
     * Quan hệ với change logs
     * Dùng hasMany thay vì morphMany vì entity_type lưu class basename hoặc full class name
     */
    public function changeLogs()
    {
        return $this->hasMany(ChangeLog::class, 'entity_id', $this->getKeyName())
            ->where(function($query) {
                $query->where('entity_type', class_basename($this))
                      ->orWhere('entity_type', get_class($this));
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * Lấy lịch sử thay đổi
     */
    public function getChangeHistory()
    {
        return $this->changeLogs()->with('changedBy')->get();
    }
}
