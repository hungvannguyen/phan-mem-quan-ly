document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('create-student-form');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Xóa các thông báo lỗi cũ
        const errorElements = form.querySelectorAll('.error-message');
        errorElements.forEach(el => el.remove());

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else if (response.status === 422) {
                    return response.json().then(data => {
                        throw data.errors;
                    });
                } else {
                    throw {general: ['Đã xảy ra lỗi không xác định.']};
                }
            })
            .then(data => {
                // Đóng modal
                closeModal();

                // Hiển thị thông báo thành công
                alert(data.message);

                // Cập nhật danh sách sinh viên nếu cần
                // Ví dụ: loadStudentList();
            })
            .catch(errors => {
                // Hiển thị lỗi
                for (const [field, messages] of Object.entries(errors)) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        const errorDiv = document.createElement('div');
                        errorDiv.classList.add('error-message');
                        errorDiv.style.color = 'red';
                        errorDiv.textContent = messages.join(', ');
                        input.parentNode.appendChild(errorDiv);
                    }
                }
            });
    });
});
