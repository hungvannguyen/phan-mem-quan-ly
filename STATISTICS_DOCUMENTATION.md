# 📊 Tài liệu Trang Thống Kê Phôi Văn Bằng

## Tổng quan

Trang thống kê phôi văn bằng được xây dựng dựa trên **Laravel Easy Metrics** để cung cấp cái nhìn toàn diện về tình trạng và xu hướng sử dụng phôi văn bằng trong hệ thống.

## 🚀 Tính năng chính

### 1. **Thống kê tổng quan**

- **Tổng số phôi**: Hiển thị tổng số phôi trong hệ thống với tỷ lệ tăng trưởng
- **Phôi chưa cấp**: Số lượng phôi còn trong kho (trạng thái InStock)
- **Phôi đã cấp**: Số lượng phôi đã được cấp cho sinh viên (trạng thái Issued)
- **Phôi đã thu hồi**: Số lượng phôi đã bị thu hồi (trạng thái Recalled)
- **Phôi hư hỏng**: Số lượng phôi bị hư hỏng (trạng thái Damaged)

### 2. **Bộ lọc thống kê**

#### **Khoảng thời gian**

- 7 ngày qua
- 30 ngày qua
- 90 ngày qua
- 1 năm qua
- Tất cả thời gian

#### **Loại phôi**

- Lọc theo các loại phôi văn bằng khác nhau
- Tùy chọn "Tất cả loại phôi"

#### **Khóa tốt nghiệp**

- Lọc theo năm tốt nghiệp của sinh viên
- Hiển thị tất cả các khóa có dữ liệu

#### **Khoảng ngày tùy chỉnh**

- Từ ngày - Đến ngày
- Cho phép lọc trong khoảng thời gian cụ thể

### 3. **Biểu đồ thống kê**

#### **📈 Phân bố trạng thái phôi** (Doughnut Chart)

- Hiển thị tỷ lệ phần trăm các trạng thái phôi
- Màu sắc phân biệt:
    - 🟢 Trong kho (Xanh lá)
    - 🔵 Đã cấp (Xanh dương)
    - 🟡 Đã thu hồi (Vàng)
    - 🔴 Hư hỏng (Đỏ)

#### **📊 Phân bố theo loại phôi** (Bar Chart)

- Thống kê số lượng phôi theo từng loại
- Hiển thị tên loại phôi trên trục X

#### **📈 Xu hướng cấp phôi theo thời gian** (Line Chart)

- Theo dõi số lượng phôi được cấp theo tháng
- Hiển thị tỷ lệ tăng trưởng
- Có thể lọc theo loại phôi và khóa tốt nghiệp

#### **📈 Xu hướng thu hồi phôi theo thời gian** (Line Chart)

- Theo dõi số lượng phôi bị thu hồi theo tháng
- Hiển thị tỷ lệ tăng trưởng
- Có thể lọc theo loại phôi và khóa tốt nghiệp

#### **📊 So sánh cấp phôi và thu hồi theo tháng** (Bar Chart)

- So sánh trực quan giữa số phôi cấp và thu hồi
- Dữ liệu 12 tháng gần nhất
- Có thể lọc theo khóa tốt nghiệp

## 🛠️ Hướng dẫn sử dụng

### Bước 1: Truy cập trang thống kê

- Đăng nhập vào hệ thống
- Click vào menu **"Thống kê"** trên thanh navigation

### Bước 2: Sử dụng bộ lọc

1. **Chọn khoảng thời gian**: Dropdown "Khoảng thời gian"
2. **Chọn loại phôi**: Dropdown "Loại phôi" (tùy chọn)
3. **Chọn khóa tốt nghiệp**: Dropdown "Khóa tốt nghiệp" (tùy chọn)
4. **Chọn khoảng ngày**: Input "Từ ngày" và "Đến ngày" (tùy chọn)
5. **Áp dụng bộ lọc**: Click nút "Áp dụng"

### Bước 3: Xem thống kê

- **Thẻ tổng quan**: Xem các số liệu chính và tỷ lệ tăng trưởng
- **Biểu đồ**: Phân tích xu hướng và phân bố dữ liệu
- **Làm mới**: Click nút "Làm mới" để cập nhật dữ liệu mới nhất

### Bước 4: Tương tác với biểu đồ

- **Hover**: Di chuột lên biểu đồ để xem chi tiết
- **Legend**: Click vào chú thích để ẩn/hiện dữ liệu
- **Refresh**: Click nút refresh từng biểu đồ riêng lẻ

## 📱 Responsive Design

Trang thống kê được thiết kế responsive, hoạt động tốt trên:

- **Desktop**: Layout dạng grid 2 cột cho biểu đồ
- **Tablet**: Layout linh hoạt điều chỉnh theo màn hình
- **Mobile**: Layout 1 cột, điều hướng dễ dàng

## 🔧 Tính năng kỹ thuật

### **Frontend**

- **Chart.js**: Thư viện vẽ biểu đồ interactive
- **Bootstrap 5**: CSS framework responsive
- **Font Awesome**: Icon set
- **Vanilla JavaScript**: Xử lý tương tác

### **Backend**

- **Laravel Easy Metrics**: Thư viện metrics chuyên nghiệp
- **MySQL**: Database lưu trữ
- **Eloquent ORM**: Truy vấn dữ liệu
- **Carbon**: Xử lý ngày tháng

### **Các loại Metrics được sử dụng**

1. **Value Metrics**: Thống kê tổng quan với growth rate

    ```php
    Value::make(DiplomaBlank::class)
        ->range($timeRange)
        ->withGrowthRate()
        ->count();
    ```

2. **Doughnut Metrics**: Phân bố trạng thái

    ```php
    Doughnut::make(DiplomaBlank::class)
        ->count('status');
    ```

3. **Trend Metrics**: Xu hướng theo thời gian

    ```php
    Trend::make(DiplomaBlank::class)
        ->range($timeRange)
        ->withGrowthRate()
        ->countByMonths();
    ```

4. **Bar Metrics**: Phân bố theo loại
    ```php
    Bar::make(DiplomaBlank::class)
        ->range($timeRange)
        ->count('type_id');
    ```

## 📈 Ứng dụng thực tế

### **Dành cho Ban Giám hiệu**

- Theo dõi tình hình sử dụng phôi văn bằng
- Đánh giá hiệu quả quản lý kho phôi
- Lập kế hoạch nhập phôi mới

### **Dành cho Phòng Đào tạo**

- Thống kê số lượng văn bằng đã cấp theo khóa
- Theo dõi xu hướng tốt nghiệp
- Dự báo nhu cầu phôi văn bằng

### **Dành cho Phòng Kế hoạch-Tài chính**

- Theo dõi chi phí liên quan đến phôi văn bằng
- Thống kê phôi hư hỏng để tính toán thiệt hại
- Lập báo cáo định kỳ

## 🚀 Tính năng mở rộng (Roadmap)

- **📄 Xuất báo cáo**: PDF, Excel export
- **📧 Thông báo**: Email alerts cho các chỉ số bất thường
- **📊 Dashboard**: Trang dashboard tổng hợp
- **🔔 Real-time**: Cập nhật dữ liệu thời gian thực
- **📱 Mobile App**: Ứng dụng di động
- **🤖 AI Insights**: Phân tích và dự báo thông minh

## 🐛 Troubleshooting

### **Biểu đồ không hiển thị**

- Kiểm tra JavaScript console để xem lỗi
- Đảm bảo Chart.js CDN load thành công
- Refresh trang và thử lại

### **Dữ liệu không chính xác**

- Click nút "Làm mới" để reload dữ liệu
- Kiểm tra bộ lọc đã chọn đúng chưa
- Xác nhận dữ liệu trong database

### **Performance chậm**

- Giảm phạm vi thời gian lọc
- Tối ưu database indexes
- Cache kết quả truy vấn

---

**Phiên bản**: 1.0  
**Cập nhật**: October 21, 2025  
**Framework**: Laravel + Easy Metrics  
**Tác giả**: Development Team
