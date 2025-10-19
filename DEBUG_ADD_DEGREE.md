# 🛠️ Debug Guide: Lỗi khi thêm văn bằng

## 🐛 **Các lỗi đã phát hiện và sửa:**

### **1. Authentication Issue:**

- **Vấn đề**: User chưa login → redirect về /login
- **Giải pháp**: Tạo test user
    ```
    Username: testuser
    Password: password
    ```

### **2. Enum Comparison Bug:**

```php
// ❌ SAI - So sánh enum với integer
if ($student->status !== 1) {

// ✅ ĐÚNG - So sánh enum value
if ($student->status->value !== 1) {
```

### **3. User Model Issue:**

- **Vấn đề**: User model cần `username` field (required)
- **Đã sửa**: Tạo user với đầy đủ thông tin

## 📋 **Testing Workflow:**

### **Step 1: Login**

1. Truy cập: `http://127.0.0.1:8000/login`
2. Login với:
    - Username: `testuser`
    - Password: `password`

### **Step 2: Test Add Degree**

1. Truy cập: `http://127.0.0.1:8000/student/01k5trx0bk6pee5yvcpmxw1kev`
2. Click "Thêm văn bằng"
3. Fill form:
    ```
    Loại văn bằng: Cử nhân
    Loại phôi văn bằng: [Chọn từ dropdown]
    Số đăng ký: TEST789 (unique)
    Năm tốt nghiệp: 2024
    Ngày cấp: [Today]
    Xếp loại: Giỏi
    ```
4. Submit form

### **Step 3: Check Logs**

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Check server output
# Terminal sẽ show requests và responses
```

## 🔍 **Debug Commands:**

### **Check Available Data:**

```bash
php artisan tinker --execute="
// Check student status
\$student = App\\Models\\Student::find('01k5trx0bk6pee5yvcpmxw1kev');
echo 'Student status: ' . \$student->status->value . PHP_EOL;

// Check available blanks
\$blanks = App\\Models\\DiplomaBlank::where('status', App\\Enums\\DiplomaBlankStatus::IN_STOCK)
    ->whereDoesntHave('degree')->count();
echo 'Available blanks: ' . \$blanks . PHP_EOL;

// Check diploma blank types
\$types = App\\Models\\DiplomaBlankType::count();
echo 'Diploma blank types: ' . \$types . PHP_EOL;
"
```

### **Test API Endpoint:**

```bash
# Test auto diploma blank selection API
curl -X GET "http://127.0.0.1:8000/api/diploma-blanks/available/1" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=xxx"
```

### **Manual Form Test:**

```bash
# Test form submission với curl (sau khi login)
curl -X POST "http://127.0.0.1:8000/degrees/store" \
  -d "student_id=01k5trx0bk6pee5yvcpmxw1kev" \
  -d "degree_type=bachelor" \
  -d "diploma_blank_id=1" \
  -d "registration_number=CURL_TEST_123" \
  -d "graduation_year=2024" \
  -d "granting_date=2024-10-19" \
  -d "_token=xxx"
```

## 🚨 **Common Issues & Solutions:**

### **Issue 1: "Field doesn't have default value"**

- **Cause**: Database field required nhưng không có default
- **Solution**: Check migration và model fillable

### **Issue 2: "SQLSTATE[23000]: Integrity constraint violation"**

- **Cause**: Foreign key constraint hoặc unique constraint
- **Solution**: Check diploma_blank_id exists và registration_number unique

### **Issue 3: "Unauthenticated"**

- **Cause**: User chưa login
- **Solution**: Login trước khi test

### **Issue 4: "Validation failed"**

- **Cause**: Form data không đúng format
- **Solution**: Check validation rules trong controller

## 📊 **Expected Behavior:**

### **Success Case:**

```
1. User submits form
2. Controller validates data ✅
3. Check student status = 1 ✅
4. Check diploma blank available ✅
5. Create degree record ✅
6. Update diploma blank status ✅
7. Redirect with success message ✅
```

### **Error Cases:**

```
- Student status ≠ 1 → "Chỉ có thể cấp văn bằng cho sinh viên đã tốt nghiệp"
- Diploma blank not available → "Phôi văn bằng không khả dụng"
- Duplicate registration_number → "Số đăng ký đã tồn tại"
- Validation errors → Show in modal
```

## 🎯 **Current Status:**

- ✅ Server running: `http://127.0.0.1:8000`
- ✅ Test user created: `testuser/password`
- ✅ Enum comparison bug fixed
- ✅ Available test data confirmed
- 🔄 Ready to test form submission

---

**Next: Login và test thêm văn bằng để xem có lỗi gì khác không!**
