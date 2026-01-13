# Hướng dẫn Implement Logic cho Import Classes

## Tổng quan

File này hướng dẫn chi tiết cách implement logic mapping dữ liệu cho từng Import Class.

## 1. DegreeImport - Bằng Cử nhân, Thạc sĩ, Tiến sĩ

### Cấu trúc Excel mẫu

| ho_ten       | ma_sinh_vien | ngay_sinh  | noi_sinh | gioi_tinh | nganh               | loai_bang | so_bang | so_vao_so | ngay_cap   | xep_loai | hinh_thuc_dao_tao | ghi_chu |
| ------------ | ------------ | ---------- | -------- | --------- | ------------------- | --------- | ------- | --------- | ---------- | -------- | ----------------- | ------- |
| Nguyễn Văn A | SV001        | 01/01/2000 | Hà Nội   | Nam       | Công nghệ thông tin | bachelor  | B001    | VS001     | 20/06/2024 | Giỏi     | Chính quy         |         |

### Logic Implementation

```php
public function model(array $row)
{
    // Skip empty rows
    if (!$this->validateRequiredFields($row, ['ho_ten', 'ma_sinh_vien', 'nganh', 'so_bang'])) {
        return null;
    }

    try {
        DB::beginTransaction();

        // 1. Tìm hoặc tạo Student
        $student = Student::firstOrCreate(
            ['student_code' => $this->cleanString($row['ma_sinh_vien'])],
            [
                'full_name' => $this->cleanString($row['ho_ten']),
                'date_of_birth' => $this->parseDate($row['ngay_sinh']),
                'place_of_birth' => $this->cleanString($row['noi_sinh']),
                'gender' => $this->parseGender($row['gioi_tinh']),
            ]
        );

        // 2. Tìm Major
        $majorName = $this->normalizeMajorName($row['nganh']);
        $major = Major::where('major_name', 'LIKE', "%{$majorName}%")->first();

        if (!$major) {
            $this->logError('Major not found', ['major_name' => $row['nganh'], 'row' => $row]);
            DB::rollBack();
            return null;
        }

        // 3. Tạo Degree
        $degree = new Degree([
            'degree_type' => $this->parseDegreeType($row['loai_bang']),
            'registration_number' => $this->cleanString($row['so_bang']),
            'record_number' => $this->cleanString($row['so_vao_so']),
            'granting_date' => $this->parseDate($row['ngay_cap']),
            'classification' => $this->cleanString($row['xep_loai']),
            'training_mode' => $this->cleanString($row['hinh_thuc_dao_tao']),
            'notes' => $this->cleanString($row['ghi_chu']),
        ]);

        $degree->student()->associate($student);
        $degree->major()->associate($major);
        $degree->save();

        DB::commit();
        $this->importedCount++;

        return null;

    } catch (\Exception $e) {
        DB::rollBack();
        $this->errorCount++;
        $this->logError($e->getMessage(), ['row' => $row]);
        Log::error('DegreeImport Error: ' . $e->getMessage(), ['row' => $row]);
        return null;
    }
}

private function parseGender($value)
{
    $value = mb_strtolower($this->cleanString($value) ?? '');
    return in_array($value, ['nam', 'male', 'm']) ? 'male' : 'female';
}

private function parseDegreeType($value)
{
    $value = mb_strtolower($this->cleanString($value) ?? '');
    if (in_array($value, ['bachelor', 'cu_nhan', 'cử nhân'])) return 'bachelor';
    if (in_array($value, ['master', 'thac_si', 'thạc sĩ'])) return 'master';
    if (in_array($value, ['doctorate', 'tien_si', 'tiến sĩ'])) return 'doctorate';
    return 'bachelor'; // default
}

public function rules(): array
{
    return [
        'ho_ten' => 'required|string|max:255',
        'ma_sinh_vien' => 'required|string|max:50',
        'ngay_sinh' => 'nullable|date',
        'noi_sinh' => 'nullable|string|max:255',
        'gioi_tinh' => 'nullable|string|max:10',
        'nganh' => 'required|string|max:255',
        'loai_bang' => 'required|string',
        'so_bang' => 'required|string|unique:degrees,registration_number',
        'so_vao_so' => 'nullable|string',
        'ngay_cap' => 'required|date',
        'xep_loai' => 'nullable|string|max:50',
        'hinh_thuc_dao_tao' => 'nullable|string|max:100',
    ];
}
```

## 2. PoliticalTheoryImport - Lý luận chính trị

### Cấu trúc Excel mẫu

| ho_ten     | ma_sinh_vien | ngay_sinh  | chung_chi_so | loai_chung_chi | ngay_cap   | noi_cap    | ghi_chu |
| ---------- | ------------ | ---------- | ------------ | -------------- | ---------- | ---------- | ------- |
| Trần Thị B | SV002        | 15/05/1999 | LLCT001      | Cao cấp        | 10/07/2024 | Trường ABC |         |

### Logic Implementation

```php
public function model(array $row)
{
    if (!$this->validateRequiredFields($row, ['ho_ten', 'chung_chi_so', 'loai_chung_chi'])) {
        return null;
    }

    try {
        DB::beginTransaction();

        // 1. Tìm hoặc tạo Student
        $student = Student::firstOrCreate(
            ['student_code' => $this->cleanString($row['ma_sinh_vien'])],
            [
                'full_name' => $this->cleanString($row['ho_ten']),
                'date_of_birth' => $this->parseDate($row['ngay_sinh']),
            ]
        );

        // 2. Xác định loại certificate
        $certificateType = $this->parsePoliticalTheoryType($row['loai_chung_chi']);

        // 3. Tìm DiplomaBlankType tương ứng
        $diplomaBlankType = DiplomaBlankType::where('type_name', 'LIKE', "%{$certificateType}%")
            ->where('type_name', 'LIKE', '%lý luận chính trị%')
            ->first();

        if (!$diplomaBlankType) {
            $this->logError('Diploma blank type not found', ['type' => $certificateType, 'row' => $row]);
            DB::rollBack();
            return null;
        }

        // 4. Tạo Degree (certificate)
        $degree = new Degree([
            'degree_type' => 'certificate',
            'certificate_number' => $this->cleanString($row['chung_chi_so']),
            'granting_date' => $this->parseDate($row['ngay_cap']),
            'issuing_place' => $this->cleanString($row['noi_cap']),
            'notes' => $this->cleanString($row['ghi_chu']),
        ]);

        $degree->student()->associate($student);
        // Cần associate với diploma_blank nếu có
        $degree->save();

        DB::commit();
        $this->importedCount++;

        return null;

    } catch (\Exception $e) {
        DB::rollBack();
        $this->errorCount++;
        $this->logError($e->getMessage(), ['row' => $row]);
        Log::error('PoliticalTheoryImport Error: ' . $e->getMessage(), ['row' => $row]);
        return null;
    }
}

private function parsePoliticalTheoryType($value)
{
    $value = mb_strtolower($this->cleanString($value) ?? '');
    if (str_contains($value, 'cao cấp') || str_contains($value, 'cao cap')) {
        return 'Cao cấp lý luận chính trị';
    }
    if (str_contains($value, 'trung cấp') || str_contains($value, 'trung cap')) {
        return 'Trung cấp lý luận chính trị';
    }
    return 'Cao cấp lý luận chính trị'; // default
}

public function rules(): array
{
    return [
        'ho_ten' => 'required|string|max:255',
        'ma_sinh_vien' => 'nullable|string|max:50',
        'chung_chi_so' => 'required|string',
        'loai_chung_chi' => 'required|string',
        'ngay_cap' => 'required|date',
        'noi_cap' => 'nullable|string|max:255',
    ];
}
```

## 3. CertificateImport - Chứng chỉ

### Cấu trúc Excel mẫu

| ho_ten   | ma_sinh_vien | ngay_sinh  | chung_chi_so | ten_chung_chi     | ngay_cap   | noi_cap    | co_quan_cap | ghi_chu |
| -------- | ------------ | ---------- | ------------ | ----------------- | ---------- | ---------- | ----------- | ------- |
| Lê Văn C | SV003        | 20/08/1998 | CC001        | Chứng chỉ Tin học | 15/09/2024 | Trường XYZ | Bộ GD&ĐT    |         |

### Logic Implementation

```php
public function model(array $row)
{
    if (!$this->validateRequiredFields($row, ['ho_ten', 'chung_chi_so', 'ten_chung_chi'])) {
        return null;
    }

    try {
        DB::beginTransaction();

        // 1. Tìm hoặc tạo Student
        $student = Student::firstOrCreate(
            ['student_code' => $this->cleanString($row['ma_sinh_vien'])],
            [
                'full_name' => $this->cleanString($row['ho_ten']),
                'date_of_birth' => $this->parseDate($row['ngay_sinh']),
            ]
        );

        // 2. Tìm hoặc tạo certificate type
        $certificateName = $this->cleanString($row['ten_chung_chi']);

        // TODO: Có thể cần tạo/tìm DiplomaBlankType hoặc Major tùy logic của bạn

        // 3. Tạo Degree (certificate)
        $degree = new Degree([
            'degree_type' => 'certificate',
            'certificate_number' => $this->cleanString($row['chung_chi_so']),
            'certificate_name' => $certificateName,
            'granting_date' => $this->parseDate($row['ngay_cap']),
            'issuing_place' => $this->cleanString($row['noi_cap']),
            'issuing_authority' => $this->cleanString($row['co_quan_cap']),
            'notes' => $this->cleanString($row['ghi_chu']),
        ]);

        $degree->student()->associate($student);
        $degree->save();

        DB::commit();
        $this->importedCount++;

        return null;

    } catch (\Exception $e) {
        DB::rollBack();
        $this->errorCount++;
        $this->logError($e->getMessage(), ['row' => $row]);
        Log::error('CertificateImport Error: ' . $e->getMessage(), ['row' => $row]);
        return null;
    }
}

public function rules(): array
{
    return [
        'ho_ten' => 'required|string|max:255',
        'ma_sinh_vien' => 'nullable|string|max:50',
        'chung_chi_so' => 'required|string',
        'ten_chung_chi' => 'required|string|max:255',
        'ngay_cap' => 'required|date',
        'noi_cap' => 'nullable|string|max:255',
        'co_quan_cap' => 'nullable|string|max:255',
    ];
}
```

## 4. Tips và Best Practices

### 4.1. Xử lý duplicate

```php
// Option 1: Skip if exists
$existing = Degree::where('registration_number', $registrationNumber)->first();
if ($existing) {
    $this->logError('Duplicate registration number', ['number' => $registrationNumber]);
    return null;
}

// Option 2: Update if exists
$degree = Degree::updateOrCreate(
    ['registration_number' => $registrationNumber],
    [/* data */]
);
```

### 4.2. Xử lý relationships

```php
// Tìm bằng ID
$major = Major::find($majorId);

// Tìm bằng tên (fuzzy)
$major = Major::where('major_name', 'LIKE', "%{$majorName}%")->first();

// Tìm bằng code
$major = Major::where('major_code', $majorCode)->first();

// Associate
$degree->major()->associate($major);
$degree->student()->associate($student);
```

### 4.3. Transaction handling

```php
DB::beginTransaction();
try {
    // Your logic
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### 4.4. Logging errors

```php
// Log to file
Log::error('Import error', ['context' => $data]);

// Log to internal array
$this->logError('Error message', ['row' => $row]);

// Both
$this->errorCount++;
$this->logError($e->getMessage(), ['row' => $row]);
Log::error('DegreeImport Error: ' . $e->getMessage(), ['row' => $row]);
```

### 4.5. Validate data format

```php
// Sử dụng các helper từ ImportHelper trait
$date = $this->parseDate($row['ngay_cap']);
$phone = $this->parsePhoneNumber($row['dien_thoai']);
$email = $this->parseEmail($row['email']);
$idCard = $this->parseIdentityCard($row['cccd']);
```

## 5. Testing

### 5.1. Tạo file Excel test

Tạo file với:

- 5-10 dòng data hợp lệ
- 2-3 dòng có lỗi validation
- 1-2 dòng trùng lặp

### 5.2. Test script

```php
// routes/web.php hoặc routes/console.php
Route::get('/test-import', function () {
    $import = new \App\Imports\DegreeImport();
    $file = storage_path('app/test_import.xlsx');

    Excel::import($import, $file);

    $stats = $import->getStatistics();
    dd($stats);
});
```

### 5.3. PHPUnit Test

```php
public function test_degree_import()
{
    $import = new DegreeImport();
    $file = base_path('tests/fixtures/degrees.xlsx');

    Excel::import($import, $file);

    $stats = $import->getStatistics();

    $this->assertEquals(10, $stats['imported']);
    $this->assertEquals(0, $stats['errors']);
}
```

## 6. Performance Tips

### 6.1. Batch Insert

```php
public function batchSize(): int
{
    return 500; // Tùy chỉnh theo server
}
```

### 6.2. Chunk Reading

```php
public function chunkSize(): int
{
    return 500; // Tùy chỉnh theo RAM
}
```

### 6.3. Disable timestamps temporarily

```php
// Trong model method
Model::unguard();
Model::insert($data); // Faster than create
Model::reguard();
```

### 6.4. Eager loading

```php
// Load trước các relationships
$majors = Major::all()->keyBy('major_code');
$major = $majors->get($row['ma_nganh']);
```

## 7. Troubleshooting Common Issues

### Issue 1: Memory Limit

```php
ini_set('memory_limit', '512M');
// hoặc trong php.ini: memory_limit = 512M
```

### Issue 2: Timeout

```php
set_time_limit(300); // 5 minutes
// hoặc trong Job: public $timeout = 3600;
```

### Issue 3: Encoding issues

```php
// Trong Excel import, thêm:
public function getCsvSettings(): array
{
    return [
        'input_encoding' => 'UTF-8',
    ];
}
```

### Issue 4: Date format

```php
// Sử dụng helper
$date = $this->parseDate($value);

// Hoặc custom
$date = \Carbon\Carbon::createFromFormat('d/m/Y', $value);
```

## Kết luận

Sau khi implement logic:

1. Test với data nhỏ trước
2. Kiểm tra logs
3. Verify data trong database
4. Test với file lớn
5. Optimize nếu cần
