document.addEventListener('DOMContentLoaded', function () {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(function (toastEl) {
        var toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 3000
        });
        toast.show();
    });
});
