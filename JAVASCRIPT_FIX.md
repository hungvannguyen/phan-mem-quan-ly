# 🛠️ FIX: Lỗi JavaScript FormData.append

## 🐛 **Lỗi đã sửa:**

```
Uncaught TypeError: FormData.append: 1 is not a valid argument count for any overload.
handleAddDegree http://localhost/student/01k6y9dvrctz4m9mk32y6ebbah:815
```

## 🔍 **Nguyên nhân:**

- **Trước**: `formData.append('student_id', {{ $student->id }});`
- **Vấn đề**: `$student->id` bị `null` hoặc không tồn tại
- **Thực tế**: Student model dùng `student_id` làm primary key, không phải `id`

## ✅ **Giải pháp:**

```javascript
// ❌ SAI:
formData.append('student_id', {{ $student->id }});

// ✅ ĐÚNG:
formData.append('student_id', '{{ $student->student_id }}');
```

## 📋 **Test sau khi sửa:**

### **Bước 1: Kiểm tra cơ bản**

1. Truy cập: `http://127.0.0.1:8000/student/01k5trx0bk6pee5yvcpmxw1kev`
2. Mở Developer Tools (F12)
3. Check Console - không còn lỗi JavaScript

### **Bước 2: Test Add Degree Modal**

1. Click "Thêm văn bằng"
2. Modal mở ra bình thường
3. Chọn loại văn bằng và loại phôi
4. Kiểm tra auto-selection phôi hoạt động

### **Bước 3: Test Form Submit**

1. Điền đầy đủ thông tin:
    - Loại văn bằng: Cử nhân
    - Loại phôi văn bằng: (chọn bất kỳ)
    - Số đăng ký: TEST123 (unique)
    - Năm tốt nghiệp: 2024
    - Ngày cấp: Hôm nay
2. Click "Lưu văn bằng"
3. Kiểm tra:
    - ✅ Button chuyển thành "Đang lưu..."
    - ✅ No console errors
    - ✅ AJAX request được gửi đi

### **Bước 4: Verification**

```bash
# Check request trong Network tab (F12)
# Method: POST
# URL: /degrees/store
# FormData chứa:
# - student_id: "01k5trx0bk6pee5yvcpmxw1kev"
# - degree_type: "bachelor"
# - diploma_blank_id: [auto selected]
# - registration_number: "TEST123"
# ... và các field khác
```

## 🔧 **Debug commands nếu cần:**

### **Check Student data:**

```bash
php artisan tinker --execute="
\$student = App\Models\Student::where('student_id', '01k5trx0bk6pee5yvcpmxw1kev')->first();
echo 'Student ID: ' . \$student->student_id . PHP_EOL;
echo 'Student Code: ' . \$student->student_code . PHP_EOL;
echo 'Status: ' . \$student->status . PHP_EOL;
"
```

### **Check available blanks:**

```bash
php artisan tinker --execute="
\$blanks = App\Models\DiplomaBlank::where('status', App\Enums\DiplomaBlankStatus::IN_STOCK)
    ->whereDoesntHave('degree')->limit(3)->get();
foreach(\$blanks as \$blank) {
    echo 'ID: ' . \$blank->diploma_blank_id . ' - Serial: ' . \$blank->serial_number . ' (Type: ' . \$blank->type_id . ')' . PHP_EOL;
}
"
```

### **Test API endpoint:**

```bash
curl -X GET "http://127.0.0.1:8000/api/diploma-blanks/available/1" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=xxx"
```

## ✅ **Expected Behavior:**

1. ✅ No JavaScript errors in console
2. ✅ Modal opens and closes smoothly
3. ✅ Auto diploma blank selection works
4. ✅ Form validation works
5. ✅ Submit sends proper FormData with student_id
6. ✅ Success/error handling works properly

## 🎯 **Technical Details:**

### **Student Model Primary Key:**

```php
// app/Models/Student.php
protected $primaryKey = 'student_id';  // Uses ULID
```

### **Proper FormData append:**

```javascript
// student_id is ULID string like: "01k5trx0bk6pee5yvcpmxw1kev"
formData.append("student_id", "{{ $student->student_id }}");
```

### **Controller expects:**

```php
// DiplomaManagementController@storeDegree
$validated = $request->validate([
    'student_id' => 'required|exists:students,student_id', // Checks student_id field
    // ... other fields
]);
```

---

**🎉 Lỗi JavaScript đã được sửa! Form submission bây giờ sẽ hoạt động bình thường.**
