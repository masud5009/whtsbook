
$(document).on('click', '.close-ticket', function() {
    $('.swal-button--confirm').attr('data-href', $(this).attr('data-href'));
});
$(document).on('click', '.swal-button--confirm', function() {
    $.get($(this).attr('data-href'), function(res) {
        $('.message-type').remove();
        location.reload();
    });
});

