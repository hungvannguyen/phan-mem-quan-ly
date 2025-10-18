<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cập nhật import_id cho các diploma_blanks đã tồn tại
        // Khớp dựa trên type_id, import_date và pattern của serial number

        $imports = DB::table('diploma_blank_import')->get();

        foreach ($imports as $import) {
            $prefix = $import->prefix ?? '';
            $suffix = $import->suffix ?? '';

            // Tìm các diploma_blanks có thể thuộc về import này
            $query = DB::table('diploma_blanks')
                ->where('type_id', $import->type_id)
                ->whereDate('import_date', $import->import_date)
                ->whereNull('import_id'); // Chỉ cập nhật những record chưa có import_id

            // Nếu có prefix hoặc suffix, filter theo pattern
            if ($prefix || $suffix) {
                $pattern = $prefix . '%' . $suffix;
                $query->where('serial_number', 'like', $pattern);
            }

            // Cập nhật import_id cho những records phù hợp
            $query->update(['import_id' => $import->id]);

            echo "Updated diploma_blanks for import ID: {$import->id}\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa import_id khỏi tất cả diploma_blanks
        DB::table('diploma_blanks')->update(['import_id' => null]);
    }
};