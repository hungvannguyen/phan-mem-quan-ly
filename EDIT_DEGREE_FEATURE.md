# ✏️ Tính năng Sửa Văn Bằng (Edit Degree)

## ✅ **Đã thực hiện:**

### **1. UI Enhancement:**

```html
<!-- Thêm nút "Sửa" vào mỗi degree card -->
<div class="degree-actions">
    <button
        type="button"
        onclick="openEditDegreeModal({{ $degree->degree_id }})"
        class="inline-flex items-center rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50"
    >
        <i class="fas fa-edit mr-1"></i>
        Sửa
    </button>
</div>
```

### **2. CSS Updates:**

```css
/* Degree Header Layout - Flex với space-between */
.degree-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.degree-actions {
    flex-shrink: 0;
}
```

### **3. Edit Degree Modal:**

- **Design**: Similar to Add Degree Modal but for editing
- **Fields**: All degree fields except diploma blank (read-only)
- **Validation**: Same as add degree + unique registration number exception
- **Diploma Blank Info**: Display current blank info (non-editable)

### **4. JavaScript Functions:**

```javascript
function openEditDegreeModal(degreeId) {
    // Find degree data from PHP array
    // Populate all form fields
    // Show diploma blank info
    // Set form action to update endpoint
}

function closeEditDegreeModal() {
    // Hide modal and reset form
}
```

### **5. Backend Implementation:**

```php
// Route
Route::put("degrees/{degree}/update", [DiplomaManagementController::class, 'updateDegree']);

// Controller Method
public function updateDegree(Request $request, Degree $degree) {
    // Validate (same as store but with unique exception)
    // Check student graduation status
    // Update degree (preserve diploma_blank_id)
    // Redirect with success message
}
```

## 🎨 **UI Layout:**

### **Before (Add Button Only):**

```
┌─────────────────────────────────────┐
│ Văn bằng #1 [Cử nhân]               │
│ Cấp ngày: 19/10/2024                │
├─────────────────────────────────────┤
│ • Số đăng ký: VB001                 │
│ • Năm tốt nghiệp: 2024              │
│ • Xếp loại: Giỏi                    │
│ • ...                               │
└─────────────────────────────────────┘
```

### **After (With Edit Button):**

```
┌─────────────────────────────────────┐
│ Văn bằng #1 [Cử nhân]    [🖊️ Sửa]   │
│ Cấp ngày: 19/10/2024                │
├─────────────────────────────────────┤
│ • Số đăng ký: VB001                 │
│ • Năm tốt nghiệp: 2024              │
│ • Xếp loại: Giỏi                    │
│ • Mã phôi: GP76929906               │
│ • Loại phôi: Chứng chỉ Tin học      │
└─────────────────────────────────────┘
```

## 📋 **Edit Modal Features:**

### **Editable Fields:**

- ✅ Loại văn bằng (bachelor, master, doctor, certificate)
- ✅ Số đăng ký (with unique validation exception)
- ✅ Năm tốt nghiệp
- ✅ Ngày cấp
- ✅ Xếp loại
- ✅ Số quyết định
- ✅ Chuyên ngành (dropdown from majors)
- ✅ Ghi chú

### **Read-only Information:**

- 🔒 **Mã phôi**: Hiển thị nhưng không thể sửa
- 🔒 **Loại phôi**: Hiển thị nhưng không thể sửa
- ℹ️ **Note**: "Không thể thay đổi phôi văn bằng sau khi đã cấp"

### **Form Validation:**

```php
'registration_number' => 'required|string|max:255|unique:degrees,registration_number,' . $degree->degree_id . ',degree_id',
// ^ Exception for current degree's registration number
```

## 🔄 **Workflow:**

### **User Experience:**

1. **View Degrees**: User sees list of degrees with "Sửa" button
2. **Click Edit**: Modal opens with pre-filled data
3. **Edit Fields**: User modifies allowed fields
4. **View Diploma Info**: Current diploma blank info shown (read-only)
5. **Submit**: Form validates and updates
6. **Success**: Redirect back with success message

### **Technical Flow:**

```
User clicks "Sửa" button
    ↓
JavaScript finds degree data from PHP array
    ↓
Populate modal fields with current values
    ↓
Show diploma blank info (read-only)
    ↓
User edits and submits form
    ↓
PUT /degrees/{id}/update
    ↓
Controller validates and updates
    ↓
Redirect back with success message
```

## 🚫 **Business Rules:**

### **What can be edited:**

- ✅ Administrative information (registration number, dates, etc.)
- ✅ Academic information (ranking, major, etc.)
- ✅ Notes and additional info

### **What cannot be edited:**

- ❌ **Diploma blank assignment**: Once assigned, cannot be changed
- ❌ **Student**: Degree always belongs to the same student
- ❌ **Diploma blank serial**: Physical document cannot be changed

### **Validation Rules:**

- ✅ Student must still be graduated (status = 1)
- ✅ Registration number unique (except for current degree)
- ✅ Date validations (granting_date <= today)
- ✅ Year validations (1990 <= graduation_year <= current year)

## 🧪 **Testing:**

### **Test Cases:**

1. **Happy Path**: Edit degree with valid data
2. **Duplicate Registration**: Try to use existing registration number
3. **Invalid Dates**: Future granting date, invalid graduation year
4. **Student Status**: Try to edit degree for non-graduated student
5. **Modal Functionality**: Open/close, data population

### **Test URL:**

```
http://127.0.0.1:8000/student/01k5trx0bk6pee5yvcpmxw1kev
```

### **Test Steps:**

1. Login with `testuser/password`
2. Go to student page
3. Click "Sửa" button on any degree
4. Verify:
    - ✅ Modal opens with pre-filled data
    - ✅ Diploma blank info shown (read-only)
    - ✅ All fields editable except diploma info
5. Make changes and submit
6. Verify success redirect and updated data

## 💡 **Benefits:**

### **✅ User Experience:**

- Easy to edit degree information
- Clear visual separation of editable vs read-only fields
- Consistent with add degree workflow

### **✅ Data Integrity:**

- Diploma blank assignment preserved
- Validation prevents invalid updates
- Audit trail maintained

### **✅ Business Logic:**

- Cannot change physical diploma blank
- Administrative corrections allowed
- Student status validation enforced

---

**🎉 Tính năng sửa văn bằng hoàn thành! Users có thể chỉnh sửa thông tin văn bằng mà không ảnh hưởng đến phôi đã cấp.**
