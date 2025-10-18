// Handle custom pagination navigation
$(document).on("click", ".pagination-btn:not(.pagination-btn-disabled):not(.pagination-btn-current)", function (event) {
    event.preventDefault();
    var url = $(this).data("url");

    if (url) {
        loadTableData(url);
    }
});

// Handle per-page selector change
$(document).on("change", ".per-page-select", function (event) {
    var perPage = $(this).val();
    var currentUrl = window.location.href;
    var url = new URL(currentUrl);

    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1'); // Reset to first page

    loadTableData(url.toString());
});

// Function to view diploma blanks from specific import
function viewDiplomaBlanks(importId) {
    if (importId) {
        window.location.href = '/diploma-blanks-list/' + importId;
    }
}

// Function to load table data via AJAX
function loadTableData(url) {
    $.ajax({
        url: url,
        type: "GET",
        beforeSend: function () {
            $("#loading").removeClass("hidden");
            // Add loading state to pagination
            $(".students-pagination-wrapper").addClass("opacity-50 pointer-events-none");
        },
        success: function (data) {
            $("#table-data").html(data);
            // Update URL without refreshing page
            window.history.pushState({}, '', url);
        },
        error: function () {
            alert("Đã xảy ra lỗi khi tải dữ liệu.");
        },
        complete: function () {
            $("#loading").addClass("hidden");
            $(".students-pagination-wrapper").removeClass("opacity-50 pointer-events-none");
        },
    });
}

// Legacy pagination support (for fallback)
$(document).on("click", ".pagination a", function (event) {
    event.preventDefault();
    var url = $(this).attr("href");
    loadTableData(url);
});

// Table row interaction functions
function toggleRowHighlight(element) {
    const row = element.closest('tr');
    const isHighlighted = row.classList.contains('bg-blue-50');

    // Remove highlight from all rows
    document.querySelectorAll('.students-data-table tbody tr').forEach(tr => {
        tr.classList.remove('bg-blue-50', 'border-blue-200');
    });

    // Toggle highlight on clicked row
    if (!isHighlighted) {
        row.classList.add('bg-blue-50', 'border-blue-200');
    }
}

// Form validation
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");
    const submitBtn = document.getElementById("submitBtn");
    const requiredFields = form.querySelectorAll(".required");

    function validateField(field) {
        if (!field || !field.value) {
            return false;
        }
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

    // Add click handlers for table rows
    $(document).on('click', '.students-data-table tbody tr', function () {
        toggleRowHighlight(this);
    });
});
