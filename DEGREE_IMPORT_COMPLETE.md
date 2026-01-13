# Hệ thống Import Bằng Cấp - Hoàn Thành

## Tổng quan

Đã hoàn thành xây dựng hệ thống import dữ liệu bằng cấp (Cử nhân, Thạc sĩ, Tiến sĩ) từ file Excel.

## Cấu trúc File Excel

Template: `/resources/templates/Import/[Mau TT01] Thong tin cap bang cu nhan, thac si, tien si.xls`

### Mapping Cột Excel:

- **A** - Số TT (STT)
- **B** - Loại văn bằng → `degree_type` (bachelor/master/doctorate)
- **C** - Họ và tên → `students.full_name`
- **D** - Ngày sinh → `students.date_of_birth`
- **E** - Nơi sinh → `students.place_of_birth`
- **F** - Quê quán → `students.hometown`
- **G** - Nguyên quán → `students.place_of_origin`
- **H** - Giới tính → `students.gender`
- **I** - Dân tộc → `students.nation`
- **J** - Quốc tịch → `students.nationality`
- **K** - Khoá → `students.course`
- **L** - Lớp → `students.class_name`
- **M** - Niên khoá → `students.academic_year`
- **N** - Ngành đào tạo → `majors.major_name` + `degrees.major_id`
- **O** - Hình thức đào tạo → `students.training_type`
- **P** - Số QĐ (Hội đồng) → `degrees.council_decision_number`
- **Q** - Ngày QĐ (Hội đồng) → `degrees.council_decision_date`
- **R** - Ngày bảo vệ → `degrees.defense_date`
- **S** - Số QĐ (Công nhận) → `degrees.graduation_decision_number`
- **T** - Ngày QĐ (Công nhận) → `degrees.graduation_decision_date`
- **U** - Năm tốt nghiệp → `degrees.graduation_year`
- **V** - Xếp loại → `degrees.ranking`
- **W** - Số hiệu văn bằng → `diploma_blanks.serial_number` (via `diploma_blank_import`)
- **X** - Số vào sổ gốc → `degrees.registration_number`
- **Y** - Ngày cấp → `degrees.granting_date`
- **Z** - Nội dung điều chỉnh → `change_logs.change_description`
- **AA** - QĐ điều chỉnh → `change_logs.decision_number`
- **AB** - Ngày QĐ điều chỉnh → `change_logs.decision_date`
- **AC** - Số hiệu văn bằng (Cấp lại) → Lưu vào `change_logs.additional_data`
- **AD** - Nội dung chỉnh sửa (Cấp lại) → `change_logs.change_description`
- **AE** - QĐ thu hồi/cấp lại → `change_logs.decision_number`
- **AF** - Ngày QĐ (Cấp lại) → `change_logs.decision_date`
- **AG** - Ghi chú → `degrees.notes`

## Luồng xử lý

### 1. Import Request

- User chọn file Excel và loại dữ liệu "degree"
- Controller tạo `ImportLog` record
- File được gửi vào queue hoặc xử lý ngay

### 2. DegreeImport Processing

```php
DegreeImport::__construct($documentReference)
  ↓
collection($rows)
  ↓
Tạo DiplomaBlankImport với document_reference
  ↓
foreach row:
  ↓
  processRow()
    ↓
    1. Parse dữ liệu từ Excel columns
    2. Tìm/Tạo Major (từ column N)
    3. Tìm/Tạo Student (match: full_name + date_of_birth)
    4. Tạo DiplomaBlank (nếu có column W)
    5. Tạo Degree
    6. Tạo ChangeLog (nếu có columns Z-AB hoặc AC-AF)
  ↓
Update DiplomaBlankImport status
  ↓
DB::commit()
```

### 3. Database Records Created

**Mỗi row Excel tạo:**

1. **Student** (nếu chưa tồn tại)

    - Match điều kiện: `full_name` + `date_of_birth`
    - Lưu thông tin: họ tên, ngày sinh, nơi sinh, giới tính, dân tộc, quốc tịch, khoá, lớp, niên khoá, ngành, hình thức đào tạo

2. **Major** (nếu chưa tồn tại)

    - Match điều kiện: `major_name`
    - Auto-generate `major_code` từ tên ngành

3. **DiplomaBlank** (nếu có số hiệu văn bằng - column W)

    - Lưu `serial_number` từ column W
    - Liên kết với `diploma_blank_import` record
    - Status: 'issued'

4. **Degree** (luôn tạo mới)

    - Liên kết với Student, Major, DiplomaBlank
    - Lưu tất cả thông tin về bằng cấp

5. **ChangeLog** (nếu có thông tin điều chỉnh hoặc cấp lại)

    - **Điều chỉnh** (columns Z, AA, AB): action_type = 'adjustment'
    - **Cấp lại** (columns AC, AD, AE, AF): action_type = 'reissue'

6. **DiplomaBlankImport** (1 record cho cả lần import)
    - document*reference: 'IMPORT*{id}\_{timestamp}'
    - Tracking: total_quantity, processed_count, status

## Helper Methods Sử Dụng

### ImportHelper Trait

- `parseDate()` - Parse nhiều format ngày tháng (Excel serial, d/m/Y, Y-m-d...)
- `cleanString()` - Trim, remove whitespace, special characters
- `parseGender()` - Parse giới tính (nam/nữ → male/female)
- `removeVietnameseTones()` - Bỏ dấu tiếng Việt

### DegreeImport Methods

- `parseDegreeType()` - Parse loại bằng từ text tiếng Việt
- `generateMajorCode()` - Tạo mã ngành từ tên ngành

## Error Handling

### Transaction Safety

- Toàn bộ import trong 1 transaction
- Rollback nếu có lỗi fatal
- Continue nếu lỗi ở từng row

### Error Logging

```php
$this->errors[] = [
    'row' => $rowNumber,
    'error' => $errorMessage,
    'data' => $rowData
];
```

### Statistics Tracking

```php
getStatistics() => [
    'imported' => $successCount,
    'errors' => $errorCount,
    'error_details' => $errorArray
]
```

## Validation

### Required Fields

- Họ tên (column C)

### Data Type Validation

- Dates được parse với multiple formats
- Gender được normalize
- Degree type được convert

### Business Logic

- Student matching: full_name + date_of_birth
- Major auto-creation nếu chưa tồn tại
- DiplomaBlank unique theo serial_number

## Configuration

### Type ID

⚠️ **Cần cập nhật:**

```php
// Line 56 & 177 trong DegreeImport.php
'type_id' => 1, // Cần xác định type_id phù hợp từ diploma_blank_types table
```

Kiểm tra `diploma_blank_types` table để lấy đúng `type_id` cho từng loại bằng (bachelor/master/doctorate).

## Testing

### Checklist

- [ ] Upload file Excel với 1 row
- [ ] Kiểm tra tạo Student record
- [ ] Kiểm tra tạo Major record
- [ ] Kiểm tra tạo Degree record
- [ ] Kiểm tra tạo DiplomaBlank record
- [ ] Kiểm tra tạo DiplomaBlankImport record
- [ ] Kiểm tra ChangeLog nếu có điều chỉnh
- [ ] Kiểm tra ChangeLog nếu có cấp lại
- [ ] Test với nhiều rows
- [ ] Test với duplicate students
- [ ] Test với missing data
- [ ] Test error handling

### Sample Data Test

Tạo file Excel test với:

1. Row hoàn chỉnh (có đầy đủ columns A-AG)
2. Row tối thiểu (chỉ có C, D, N, W, X, Y)
3. Row có điều chỉnh (Z, AA, AB)
4. Row có cấp lại (AC, AD, AE, AF)
5. Row duplicate student

## Usage

### Route

```php
POST /import/handle
```

### Form Data

```
import_type: 'degree'
excel_file: <file>
use_queue: 1 (hidden)
```

### Response

- Success: "Import thành công {n} dòng dữ liệu!"
- With errors: "Import hoàn thành với {n} dòng thành công và {m} dòng lỗi"
- Failed: Error message

## Files Modified/Created

### Created

- `app/Imports/DegreeImport.php` - Main import logic

### Modified

- `app/Traits/ImportHelper.php` - Added `parseGender()`
- `app/Http/Controllers/DataImportController.php` - Added document_reference param
- `app/Jobs/ProcessImportJob.php` - Added document_reference param

## Next Steps

1. **Cập nhật type_id:**

    - Xác định type_id cho bachelor, master, doctorate
    - Có thể thêm logic dynamic dựa vào degree_type

2. **Testing:**

    - Tạo file Excel test
    - Chạy import với test data
    - Verify database records

3. **Optimization (nếu cần):**

    - Batch inserts cho performance
    - Caching Major lookups
    - Progress tracking cho large files

4. **Notification:**
    - Implement notification khi import hoàn thành
    - Email/SMS cho user

## Notes

- Import sử dụng `ToCollection` interface thay vì `ToModel` để có control tốt hơn
- Mỗi lần import tạo 1 `DiplomaBlankImport` record để track
- Student matching dựa trên `full_name + date_of_birth` để tránh duplicate
- ChangeLog support 2 loại: 'adjustment' và 'reissue'
- Transaction safety đảm bảo data consistency
