# DiplomaBlank Import System

## 📋 Tổng Quan

Hệ thống tự động xử lý việc nhập phôi văn bằng vào database. Khi tạo DiplomaBlankImport với trạng thái PENDING, hệ thống sẽ tự động tạo các bản ghi DiplomaBlank tương ứng.

## 🔄 Quy Trình Hoạt Động

### 1. **Tạo Import Request**

- User điền form tại `/diploma-blank-management/create`
- Dữ liệu được lưu vào bảng `diploma_blank_import` với status `PENDING`
- Form include: type_id, document_reference, issue_date, prefix, from_number, to_number, suffix

### 2. **Automatic Processing**

- **Scheduled Job** chạy mỗi phút: `imports:process-pending`
- Scan bảng `diploma_blank_import` tìm records với status `PENDING`
- Dispatch `ProcessDiplomaBlankImportJob` cho từng import

### 3. **Job Execution**

- **ProcessDiplomaBlankImportJob** xử lý từng import:
    - Đánh dấu status = `PROCESSING`
    - Generate serial numbers từ `prefix + (from_number → to_number) + suffix`
    - Batch insert vào bảng `diploma_blanks` (100 records/batch)
    - Cập nhật `processed_count` và `last_processed_serial`
    - Đánh dấu status = `COMPLETED` khi xong

## 🗄️ Database Schema

### DiplomaBlankImport Table

```sql
- import_id (PK)
- type_id (FK to diploma_blank_types)
- document_reference
- issue_date
- import_date
- total_quantity (auto-calculated)
- prefix, suffix, from_number, to_number
- status (PENDING/PROCESSING/COMPLETED/FAILED)
- processed_count, last_processed_serial
- error_message, started_at, completed_at
```

### DiplomaBlank Table

```sql
- diploma_blank_id (PK)
- serial_number (generated from import)
- type_id (FK)
- status ('available')
- import_date, issue_date, recall_date
- issue_reason, recall_reason
```

## 🎯 Key Components

### 1. **ProcessDiplomaBlankImportJob**

```php
// Located: app/Jobs/ProcessDiplomaBlankImportJob.php
// Purpose: Tạo các DiplomaBlank records từ import specs
// Features:
// - Batch processing (100 records per batch)
// - Transaction safety với rollback
// - Progress tracking
// - Duplicate serial number detection
// - Error handling và retry logic
```

### 2. **ProcessPendingImports Command**

```php
// Located: app/Console/Commands/ProcessPendingImports.php
// Signature: imports:process-pending --limit=5
// Purpose: Scan và dispatch jobs cho pending imports
// Schedule: Every minute via routes/console.php
```

### 3. **DiplomaBlankImportController**

```php
// Located: app/Http/Controllers/DiplomaBlankImportController.php
// Key Methods:
// - store(): Tạo import record với validation
// - start(): Manual trigger processing
// - pause/retry(): Job control
```

## 🚀 Deployment & Usage

### 1. **Start Queue Worker**

```bash
php artisan queue:work --sleep=3 --tries=3
```

### 2. **Start Scheduler** (Production)

```bash
# Add to crontab:
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. **Manual Commands**

```bash
# Process pending imports manually
php artisan imports:process-pending --limit=10

# Check schedule list
php artisan schedule:list

# Monitor queue jobs
php artisan queue:monitor
```

## 📊 Monitoring & Logs

### 1. **Command Output**

- Scheduled command logs to: `storage/logs/pending-imports.log`
- Command provides statistics: Pending/Processing/Completed/Failed counts

### 2. **Job Monitoring**

- Job failures logged with full stack trace
- Progress tracking trong database
- Database transactions ensure data integrity

### 3. **Web Interface**

- Management page: `/diploma-blank-management`
- Real-time status updates
- Manual job control buttons

## ⚠️ Important Notes

### 1. **Performance**

- Batch size: 100 records per insert để tránh timeout
- Job timeout: 5 phút
- Max concurrent jobs: Sử dụng `withoutOverlapping()`

### 2. **Error Handling**

- Job retries: 3 lần
- Transaction rollback on failure
- Duplicate serial detection
- Comprehensive error logging

### 3. **Data Integrity**

- Kiểm tra overlap serial ranges
- Foreign key constraints
- Status flow validation (PENDING → PROCESSING → COMPLETED/FAILED)

## 🔧 Configuration

### Schedule Settings (routes/console.php)

```php
Schedule::command('imports:process-pending --limit=5')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/pending-imports.log'));
```

### Job Settings

```php
public $timeout = 300; // 5 minutes
public $tries = 3;     // 3 retry attempts
```

## 📝 Example Usage

### 1. **Create Import via Form**

```
Type: "Bằng tốt nghiệp đại học"
Document: "Số 123/QĐ-X02 ngày 15/09/2025"
Issue Date: "2025-09-15"
Prefix: "A."
From: "00001"
To: "00100"
Suffix: "/X02CN"
```

### 2. **Expected Output**

- 100 DiplomaBlank records với serial: A.00001/X02CN → A.00100/X02CN
- Status progression: PENDING → PROCESSING → COMPLETED
- Logs trong pending-imports.log

Hệ thống hoạt động hoàn toàn tự động sau khi setup!
