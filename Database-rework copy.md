Dựa trên thông tin từ các tài liệu bạn cung cấp, tôi đã thiết kế một cấu trúc cơ sở dữ liệu (database) hoàn chỉnh bằng tiếng Anh để đáp ứng các yêu cầu của phần mềm quản lý văn bằng, chứng chỉ (VBCC).

Dưới đây là cấu trúc chi tiết cho từng bảng:

---

### **1\. User and Permission Management (Quản lý Người dùng và Phân quyền)**

Hệ thống này quản lý tài khoản người dùng và vai trò của họ, tương ứng với các nhóm quyền hạn được mô tả.

#### **Table: Roles**

Lưu trữ các vai trò (quyền hạn) trong hệ thống.  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| RoleID | INT | PRIMARY KEY, AUTO\_INCREMENT | Khóa chính, định danh duy nhất cho mỗi vai trò. |  
| RoleName | VARCHAR(50) | NOT NULL, UNIQUE | Tên vai trò (ví dụ: 'Admin', 'DiplomaManager', 'CertificateManager'). |  
| Description| TEXT | NULL | Mô tả chi tiết về quyền hạn của vai trò. |

#### **Table: Users**

Lưu trữ thông tin tài khoản của người dùng.  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| UserID | INT | PRIMARY KEY, AUTO\_INCREMENT | Khóa chính, định danh duy nhất cho mỗi người dùng. |  
| Username | VARCHAR(50) | NOT NULL, UNIQUE | Tên đăng nhập của người dùng. |  
| PasswordHash| VARCHAR(255)| NOT NULL | Mật khẩu đã được mã hóa. |  
| FullName | NVARCHAR(100)| NOT NULL | Họ và tên đầy đủ của người dùng. |  
| Email | VARCHAR(100)| UNIQUE | Địa chỉ email của người dùng. |  
| IsActive | BOOLEAN | NOT NULL, DEFAULT 1 | Trạng thái hoạt động của tài khoản (1: Active, 0: Inactive). |  
| CreatedAt | DATETIME | NOT NULL, DEFAULT CURRENT\_TIMESTAMP | Thời gian tạo tài khoản. |

#### **Table: UserRoles**

Bảng trung gian để gán vai trò cho người dùng (mối quan hệ nhiều-nhiều).  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| UserID | INT | FOREIGN KEY (Users.UserID) | Khóa ngoại, liên kết đến bảng Users. |  
| RoleID | INT | FOREIGN KEY (Roles.RoleID) | Khóa ngoại, liên kết đến bảng Roles. |

---

### **2\. Diploma Blank Management (Quản lý Phôi)**

Đây là phần lõi, quản lý vòng đời của từng phôi, từ lúc nhập kho đến khi cấp phát và thu hồi.

#### **Table: DiplomaBlankTypes**

Phân loại các loại phôi (ví dụ: Phôi văn bằng, phôi chứng chỉ).  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| TypeID | INT | PRIMARY KEY, AUTO\_INCREMENT | Khóa chính, định danh duy nhất cho loại phôi. |  
| TypeName | NVARCHAR(100)| NOT NULL, UNIQUE | Tên loại phôi (ví dụ: 'Bằng tốt nghiệp Đại học', 'Chứng chỉ Tin học'). |  
| Prefix | VARCHAR(20) | NULL | Ký hiệu tiền tố của loại phôi (nếu có). |

#### **Table: DiplomaBlanks**

Quản lý chi tiết từng phôi văn bằng, chứng chỉ.  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| DiplomaBlankID| INT | PRIMARY KEY, AUTO\_INCREMENT | Khóa chính, định danh duy nhất cho mỗi phôi. |  
| SerialNumber | VARCHAR(50) | NOT NULL, UNIQUE | Số hiệu (serial) duy nhất của phôi. |  
| TypeID | INT | NOT NULL, FOREIGN KEY (DiplomaBlankTypes.TypeID) | Khóa ngoại, xác định loại phôi. |  
| Status | VARCHAR(20) | NOT NULL | Trạng thái của phôi ('InStock', 'Issued', 'Recalled', 'Damaged'). |  
| ImportDate | DATETIME | NOT NULL, DEFAULT CURRENT\_TIMESTAMP | Ngày nhập phôi vào kho. |  
| IssueDate | DATETIME | NULL | Ngày xuất phôi để cấp cho sinh viên. |  
| RecallDate | DATETIME | NULL | Ngày thu hồi phôi. |  
| IssueReason | NVARCHAR(255)| NULL | Lý do cấp phôi. |  
| RecallReason | NVARCHAR(255)| NULL | Lý do thu hồi phôi. |

---

### **3\. Student and Degree/Certificate Issuance (Quản lý Sinh viên và Cấp phát Văn bằng/Chứng chỉ)**

Quản lý thông tin sinh viên và việc cấp phát các văn bằng, chứng chỉ cụ thể cho họ.

#### **Table: Majors**

Lưu trữ thông tin các ngành đào tạo.  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| MajorID | INT | PRIMARY KEY, AUTO\_INCREMENT | Khóa chính, định danh duy nhất cho ngành học. |  
| MajorName | NVARCHAR(150)| NOT NULL, UNIQUE | Tên ngành học. |  
| MajorCode | VARCHAR(20) | NOT NULL, UNIQUE | Mã ngành học. |

#### **Table: Students**

Lưu trữ thông tin cơ bản của sinh viên.  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| StudentID | INT | PRIMARY KEY, AUTO\_INCREMENT | Khóa chính, định danh duy nhất cho sinh viên. |  
| StudentCode | VARCHAR(20) | NOT NULL, UNIQUE | Mã số sinh viên. |  
| FullName | NVARCHAR(100)| NOT NULL | Họ và tên đầy đủ của sinh viên. |  
| DateOfBirth| DATE | NOT NULL | Ngày sinh. |  
| ClassName | VARCHAR(50) | NOT NULL | Tên lớp học. |  
| MajorID | INT | NOT NULL, FOREIGN KEY (Majors.MajorID) | Khóa ngoại, liên kết đến ngành học. |

#### **Table: Degrees**

Lưu trữ thông tin về văn bằng đã được cấp cho sinh viên, liên kết một sinh viên cụ thể với một phôi cụ thể.  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| DegreeID | INT | PRIMARY KEY, AUTO\_INCREMENT | Khóa chính, định danh duy nhất cho văn bằng. |  
| StudentID | INT | NOT NULL, FOREIGN KEY (Students.StudentID) | Sinh viên được cấp bằng. |  
| DiplomaBlankID| INT | NOT NULL, UNIQUE, FOREIGN KEY (DiplomaBlanks.DiplomaBlankID) | Phôi được sử dụng cho văn bằng này. |  
| RegistrationNumber| VARCHAR(50) | NOT NULL, UNIQUE | Số vào sổ cấp văn bằng. |  
| GrantingDate | DATE | NOT NULL | Ngày cấp bằng. |  
| GraduationYear| INT | NOT NULL | Năm tốt nghiệp. |  
| Ranking | NVARCHAR(50)| NULL | Xếp loại tốt nghiệp (Giỏi, Khá, v.v.). |  
| DecisionNumber| VARCHAR(50)| NULL | Số quyết định tốt nghiệp. |

---

### **4\. System Configuration (Cài đặt Hệ thống)**

#### **Table: SystemSettings**

Lưu trữ các tham số, cấu hình chung cho toàn hệ thống.  
| Column Name | Data Type | Constraints | Description (Mô tả) |  
| :--- | :--- | :--- | :--- |  
| SettingKey | VARCHAR(50) | PRIMARY KEY | Khóa định danh cho cài đặt (ví dụ: 'SchoolName', 'Address'). |  
| SettingValue| NVARCHAR(255)| NOT NULL | Giá trị của cài đặt. |