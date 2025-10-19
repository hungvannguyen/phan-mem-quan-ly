# Test Nút "Lưu văn bằng" - Add Degree Modal

## 🎯 **Mục tiêu test:**

Kiểm tra xem nút "Lưu văn bằng" trong Add Degree Modal có hoạt động chính xác không.

## 📋 **Điều kiện tiên quyết:**

- ✅ Server đang chạy: `http://127.0.0.1:8000`
- ✅ Database có sinh viên đã tốt nghiệp (status = 1)
- ✅ Database có phôi văn bằng khả dụng (status = IN_STOCK)
- ✅ Database có major records

## 🔍 **Test Data Available:**

### Sinh viên đã tốt nghiệp:

- ID: `01k5trx0bk6pee5yvcpmxw1kev` - Aurelie Adams (EH538031)
- ID: `01k5trx0bpmmrwswjbq15sz9ph` - Omari Berge (RU631863)
- ID: `01k5trx0bqq9mj4jwf5bj5yh8m` - Ms. Katrine Kuvalis (WP140656)

### Phôi văn bằng khả dụng:

- ID: 1 - Serial: UM16711519 (Type: 3)
- ID: 2 - Serial: UI92825301 (Type: 3)
- ID: 4 - Serial: WP21000659 (Type: 1)
- ID: 5 - Serial: SV75696445 (Type: 1)

## 📝 **Các bước test:**

### **Bước 1: Truy cập trang sinh viên**

1. Mở browser đến: `http://127.0.0.1:8000/student/01k5trx0bk6pee5yvcpmxw1kev`
2. Kiểm tra trang load thành công
3. Kiểm tra thông tin sinh viên hiển thị đúng

### **Bước 2: Mở Add Degree Modal**

1. Click nút "Thêm văn bằng" (màu xanh lá)
2. Modal "Thêm văn bằng mới" phải hiện ra
3. Kiểm tra các field bắt buộc có dấu sao đỏ (\*)

### **Bước 3: Test Auto Diploma Blank Selection**

1. **Chọn "Loại văn bằng"**: Chọn bất kỳ (ví dụ: "Cử nhân")
2. **Chọn "Loại phôi văn bằng"**: Chọn 1 trong các type có sẵn
3. **Kiểm tra auto-selection**:
    - ✅ Phần "Phôi văn bằng được chọn" tự động hiển thị
    - ✅ Hiện serial number (màu xanh, bold)
    - ✅ Hiện ngày nhập
    - ✅ Có icon check màu xanh
    - ✅ Hidden input `diploma_blank_id` được set giá trị

### **Bước 4: Điền thông tin văn bằng**

```
- Số đăng ký: TEST001 (unique)
- Năm tốt nghiệp: 2024
- Ngày cấp: Ngày hôm nay hoặc trước đó
- Xếp loại: Giỏi (tùy chọn)
- Số quyết định: QD/TEST/001 (tùy chọn)
- Chuyên ngành: Sẽ default chuyên ngành của sinh viên
- Ghi chú: Test add degree (tùy chọn)
```

### **Bước 5: Test Submit**

1. **Click "Lưu văn bằng"**
2. **Kiểm tra loading state**:
    - ✅ Button hiện spinner "Đang lưu..."
    - ✅ Button bị disable
3. **Kiểm tra response**:
    - ✅ Success message hiện ra
    - ✅ Modal đóng lại
    - ✅ Page reload để hiện degree mới
    - ✅ Degree mới xuất hiện trong danh sách

### **Bước 6: Verification**

1. **Check database**:
    - Degree record được tạo trong bảng `degrees`
    - DiplomaBlank status chuyển từ `IN_STOCK` → `ISSUED`
    - DiplomaBlank có `issue_date` và `issue_reason`

## 🚨 **Test Cases cho Error Handling:**

### **Test 1: Không có phôi khả dụng**

- Chọn loại phôi không có trong kho
- Expect: Message "Không có phôi khả dụng cho loại này"

### **Test 2: Sinh viên chưa tốt nghiệp**

- Thử với sinh viên status ≠ 1
- Expect: Button "Thêm văn bằng" bị disable và có tooltip

### **Test 3: Số đăng ký trùng**

- Nhập registration_number đã tồn tại
- Expect: Error message về trùng số đăng ký

### **Test 4: Network error**

- Disconnect internet rồi submit
- Expect: Error message "Có lỗi xảy ra khi thêm văn bằng!"

## ✅ **Expected Results:**

### **Happy Path:**

1. ✅ Modal mở/đóng smooth
2. ✅ Auto-selection phôi cũ nhất hoạt động
3. ✅ Validation hoạt động đúng
4. ✅ Submit thành công
5. ✅ Database cập nhật chính xác
6. ✅ UI feedback tốt (loading, success message)
7. ✅ Page refresh hiển thị degree mới

### **Error Cases:**

1. ✅ Proper error messages
2. ✅ Form không submit khi thiếu thông tin
3. ✅ Network error handling
4. ✅ Validation messages rõ ràng

## 🔧 **Debug nếu có lỗi:**

### **Console Errors:**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check browser console (F12)
# Look for JavaScript errors
```

### **Network Issues:**

```bash
# Check routes
php artisan route:list | grep degrees

# Test API manually
curl -X GET http://127.0.0.1:8000/api/diploma-blanks/available/1
```

### **Database Issues:**

```bash
# Check database records
php artisan tinker
>>> App\Models\Degree::latest()->first()
>>> App\Models\DiplomaBlank::where('status', 'ISSUED')->latest()->first()
```

## 🎉 **Success Criteria:**

Nút "Lưu văn bằng" được coi là hoạt động thành công khi:

1. ✅ Form validation hoạt động
2. ✅ Auto diploma blank selection hoạt động
3. ✅ Submit request thành công
4. ✅ Database transaction complete
5. ✅ UI feedback appropriate
6. ✅ Error handling robust

---

**📅 Test Date:** [Điền khi test]  
**👤 Tester:** [Tên người test]  
**✅ Status:** [PASS/FAIL + notes]
