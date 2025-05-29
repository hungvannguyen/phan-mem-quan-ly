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
