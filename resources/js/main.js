$(document).on("click", ".pagination a", function (event) {
    event.preventDefault();
    var url = $(this).attr("href");

    $.ajax({
        url: url,
        type: "GET",
        beforeSend: function () {
            $("#loading").removeClass("hidden");
        },
        success: function (data) {
            $("#table-data").html(data);
        },
        error: function () {
            alert("Đã xảy ra lỗi khi tải dữ liệu.");
        },
        complete: function () {
            $("#loading").addClass("hidden");
        },
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const submitBtn = document.getElementById("submitBtn");
    const requiredFields = form.querySelectorAll(".required");

    function validateField(field) {
        if (field.type === "date") {
            return field.valueAsDate !== null;
        }
        return field.value.trim() !== "";
    }

    function validateForm() {
        let isValid = true;
        requiredFields.forEach((field) => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        submitBtn.disabled = !isValid;
    }

    requiredFields.forEach((field) => {
        field.addEventListener("input", validateForm);
    });

    validateForm();
});
