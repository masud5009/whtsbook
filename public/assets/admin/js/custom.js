"use strict";

WebFont.load({
    google: { "families": ["Lato:300,400,700,900"] },
    custom: { "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: [mainurl + '/assets/admin/css/fonts.min.css'] },
    active: function () {
        sessionStorage.fonts = true;
    }
});

/***********************************************
   * Clone Input
   */

function cloneInput(fromId, toId, event) {

    let $target = $(event.target);
    let $formId = $('#' + fromId);

    if ($target.is(':checked')) {
        $('#' + fromId + ' .form-control').each(function (i) {
            let index = i;
            let val = $(this).val();
            let $toInput = $('#' + toId + ' .form-control').eq(index);
            if ($toInput.hasClass('summernote')) {
                let val = tinyMCE.activeEditor.getContent();
                let tmcId = $toInput.attr('id');
                tinyMCE.get(tmcId).setContent(val);

            } else if ($(this).data('role') == 'tagsinput') {
                if (val.length > 0) {
                    let tags = val.split(',');
                    tags.forEach(tag => {
                        $toInput.tagsinput('add', tag);
                    });
                } else {
                    $toInput.tagsinput('removeAll');
                }
            } else if ($(this).data('role') == 'checkbox') {
                if ($(this).is(':checked')) {
                    $toInput.prop('checked', true);
                }
            } else {
                $toInput.val(val);
            }
        });
    } else {
        $('#' + toId + ' .form-control').each(function (i) {
            let $toInput = $('#' + toId + ' .form-control').eq(i);

            if ($(this).hasClass('summernote')) {
                let tmcId = $toInput.attr('id');
                tinyMCE.get(tmcId).setContent('');
            } else if ($(this).data('role') == 'tagsinput') {
                $toInput.tagsinput('removeAll');
            } else {
                $toInput.val('');
            }
        });
    }
}



/*****************************************************
  ==========Bootstrap Notify start==========
  ******************************************************/
function bootnotify(message, title, type) {
    var content = {};

    content.message = message;
    content.title = title;
    content.icon = 'fa fa-bell';

    $.notify(content, {
        type: type,
        placement: {
            from: 'top',
            align: 'right'
        },
        showProgressbar: true,
        time: 1000,
        allow_dismiss: true,
        delay: 4000
    });
}
/*****************************************************
==========Bootstrap Notify end==========
******************************************************/


/*****************************************************
==========Demo code ==========
******************************************************/
if (demo_mode == 'active') {
    $.ajaxSetup({
        beforeSend: function (jqXHR, settings, event) {
            if (settings.type == 'POST') {
                if ($(".request-loader").length > 0) {
                    $(".request-loader").removeClass('show');
                }
                if ($(".modal").length > 0) {
                    $(".modal").modal('hide');
                }
                if ($("button[disabled='disabled']").length > 0) {
                    $("button[disabled='disabled']").removeAttr('disabled');
                }
                bootnotify('This is demo version. You cannot change anything here!', 'Demo Version', 'warning')
                jqXHR.abort(event);
            }
        },
        complete: function () {
            // hide progress spinner
        }
    });
}
/*****************************************************
==========Demo code end==========
******************************************************/

$(function ($) {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* ***************************************************************
    ==========disabling default behave of form submits start==========
    *****************************************************************/
    $("#ajaxEditForm").attr('onsubmit', 'return false');
    $("#ajaxForm").attr('onsubmit', 'return false');
    /* *************************************************************
    ==========disabling default behave of form submits end==========
    ***************************************************************/

    // Sidebar Search

    $(".sidebar-search").on('input', function () {
        let term = $(this).val().toLowerCase();

        if (term.length > 0) {
            $(".sidebar ul li.nav-item").each(function (i) {
                let menuName = $(this).find("p").text().toLowerCase();
                let $mainMenu = $(this);

                // if any main menu is matched
                if (menuName.indexOf(term) > -1) {
                    $mainMenu.removeClass('d-none');
                    $mainMenu.addClass('d-block');
                } else {
                    let matched = 0;
                    let count = 0;
                    // search sub-items of the current main menu (which is not matched)
                    $mainMenu.find('span.sub-item').each(function (i) {
                        // if any sub-item is matched  of the current main menu, set the flag
                        if ($(this).text().toLowerCase().indexOf(term) > -1) {
                            count++;
                            matched = 1;
                        }
                    });


                    // if any sub-item is matched  of the current main menu (which is not matched)
                    if (matched == 1) {
                        $mainMenu.removeClass('d-none');
                        $mainMenu.addClass('d-block');
                    } else {
                        $mainMenu.removeClass('d-block');
                        $mainMenu.addClass('d-none');
                    }
                }
            });
        } else {
            $(".sidebar ul li.nav-item").addClass('d-block');
        }
    });




    /* ***************************************************
    ==========flatpickr start==========
    ******************************************************/
    $('.datepicker').flatpickr({
        enableTime: false,
        noCalendar: false,
        minDate: "today",
        allowInput: true,
    });
    /* ***************************************************
    ==========flatpickr end==========
    ******************************************************/



    /* ***************************************************
    ==========fontawesome icon picker start==========
    ******************************************************/
    // fontawesome icon picker start
    $('.icp-dd').iconpicker();
    // fontawesome icon picker end
    $('.icp').on('iconpickerSelected', function (event) {
        $("#inputIcon").val($(".iconpicker-component").find('i').attr('class'));
    });
    /* ***************************************************
    ==========fontawesome icon picker upload end==========
    ******************************************************/


    /* ***************************************************
    ==========Summernote initialization start==========
    ******************************************************/
    $(".summernote").each(function (i) {

        var isDark = $('body').attr('data-background-color') === 'dark';

        tinymce.init({
            selector: '.summernote',
            plugins: 'autolink charmap emoticons image link lists media searchreplace table visualblocks wordcount directionality',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat | ltr rtl',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'Author name',
            promotion: false,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? 'dark' : 'default',
            mergetags_list: [
                { value: 'First.Name', title: 'First Name' },
                { value: 'Email', title: 'Email' },
            ]
        });

    });



    $(document).on('click', ".note-video-btn", function () {
        let i = $(this).index();
        if ($(".summernote").eq(i).parents(".modal").length > 0) {
            setTimeout(() => {
                $("body").addClass('modal-open');
            }, 500);
        }
    });


    /* ***************************************************
    ==========Summernote initialization end==========
    ******************************************************/

    $('.icp-dd').iconpicker();
    $('.icp').on('iconpickerSelected', function (event) {
        $("#inputIcon").val($(".iconpicker-component").find('i').attr('class'));
    });


    /* ***************************************************
    ==========Summernote initialization end==========
    ******************************************************/



    /* ***************************************************
    ==========Bootstrap Notify start==========
    ******************************************************/
    function bootnotify(message, title, type) {
        var content = {};

        content.message = message;
        content.title = title;
        content.icon = 'fa fa-bell';

        $.notify(content, {
            type: type,
            placement: {
                from: 'top',
                align: 'right'
            },
            showProgressbar: true,
            time: 1000,
            allow_dismiss: true,
            delay: 4000,
        });
    }
    /* ***************************************************
    ==========Bootstrap Notify end==========
    ******************************************************/



    /* ***************************************************
    ==========Form Submit with AJAX Request Start==========
    ******************************************************/
    $("#submitBtn").on('click', function (e) {
        $(e.target).attr('disabled', true);
        $(".request-loader").addClass("show");

        // Clear previous validation styles
        $('.form-control').removeClass('valid-field invalid-field');
        $('.form-control').css('border', '');

        let ajaxForm = document.getElementById('ajaxForm');
        let fd = new FormData(ajaxForm);
        let url = $("#ajaxForm").attr('action');
        let method = $("#ajaxForm").attr('method');

        $('.form-control').each(function (i) {
            let index = i;
            let $toInput = $('.form-control').eq(index);

            if ($(this).hasClass('summernote')) {
                let tmcId = $toInput.attr('id');
                let content = tinyMCE.get(tmcId).getContent();
                fd.delete($(this).attr('name'));
                fd.append($(this).attr('name'), content);
            }
        });

        $.ajax({
            url: url,
            method: method,
            data: fd,
            contentType: false,
            processData: false,
            success: function (data) {
                $(e.target).attr('disabled', false);
                $(".request-loader").removeClass("show");

                $(".em").each(function () {
                    $(this).html('');
                });

                // Mark all fields as valid on success
                $('.form-control').each(function () {
                    if ($(this).prop('required')) {
                        $(this).addClass('valid-field').removeClass('invalid-field');
                    }
                });

                if (data == "success") {
                    location.reload();
                }
            },
            error: function (error) {
                $('.em').each(function () {
                    $(this).html('');
                });

                // Clear previous validation styles
                $('.form-control').removeClass('valid-field invalid-field');

                // Mark invalid fields with red border
                for (let x in error.responseJSON.errors) {
                    document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];

                    // Add red border to invalid fields
                    let $field = $('[name="' + x + '"]');
                    if ($field.length) {
                        $field.addClass('invalid-field').removeClass('valid-field');
                    }
                }

                // Mark valid required fields with green border
                $('.form-control').each(function () {
                    if ($(this).prop('required') && !$(this).hasClass('invalid-field')) {
                        let fieldValue = $(this).val();
                        if (fieldValue && fieldValue.trim() !== '') {
                            $(this).addClass('valid-field');
                        }
                    }
                });

                $('.request-loader').removeClass('show');
                $(e.target).attr('disabled', false);
            }
        });
    });


    $("#permissionBtn").on('click', function () {
        $("#permissionsForm").trigger("submit");
    });
    /* ***************************************************
    ==========Form Submit with AJAX Request End==========
    ******************************************************/

    /* ***************************************************
    ==========datatables start==========
    ******************************************************/
    $('#basic-datatables').DataTable({
        responsive: true,
        ordering: false,
        "language": {
            "search": Search + ':',
            "lengthMenu": `${showText} _MENU_ ${entriesText}`,
            "info": `${Showing} _START_ ${to} _END_ ${ofText} _TOTAL_ ${entriesText}`,
            "paginate": {
                "next": nextText,
                "previous": previousText
            }
        }
    });
    /* ***************************************************
    ==========datatables end==========
    ******************************************************/


    /* ***************************************************
    ==========Form Prepopulate After Clicking Edit Button Start==========
    ******************************************************/
    $(".editbtn").on('click', function () {
        let datas = $(this).data();
        delete datas['toggle'];
        for (let x in datas) {
            if ($("#in_" + x).hasClass('summernote')) {
                tinymce.get("in_" + x).setContent(datas[x]);
            } else if ($("#in_" + x).hasClass('image')) {
                $("#in_" + x).attr('src', datas[x]);
            } else if ($("#in_" + x).data('role') == 'tagsinput') {
                if (datas[x].length > 0) {
                    let arr = datas[x].split(" ");
                    for (let i = 0; i < arr.length; i++) {
                        $("#in_" + x).tagsinput('add', arr[i]);
                    }
                } else {
                    $("#in_" + x).tagsinput('removeAll');
                }
            } else if ($("input[name='" + x + "']").attr('type') == 'radio') {
                $("input[name='" + x + "']").each(function (i) {
                    if ($(this).val() == datas[x]) {
                        $(this).prop('checked', true);
                    }
                });
            } else if ($("#in_" + x).hasClass('select2')) {
                $("#in_" + x).val(datas[x]);
                $("#in_" + x).trigger('change');
            } else {
                $("#in_" + x).val(datas[x]);
                if ($('#inicon').length > 0) {
                    $('#inicon').attr('class', datas['icon']);
                }
                $('.brand-img').attr('src', datas['brand_img']);
                $('.gallery-img').attr('src', datas['gallery_img']);
            }
        }


        // focus & blur colorpicker inputs
        setTimeout(() => {
            $(".jscolor").each(function () {
                $(this).focus();
                $(this).blur();
            });
        }, 300);
    });

    /* ***************************************************
    ==========Form Prepopulate After Clicking Edit Button End==========
    ******************************************************/


    /* ***************************************************
    ==========Form Update with AJAX Request Start==========
    ******************************************************/
    $("#updateBtn").on('click', function (e) {
        $(".request-loader").addClass("show");

        // Clear previous validation styles
        $('.form-control').removeClass('valid-field invalid-field');
        $('.form-control').css('border', '');

        if ($(".edit-iconpicker-component").length > 0) {
            $("#editInputIcon").val($(".edit-iconpicker-component").find('i').attr('class'));
        }
        let ajaxEditForm = document.getElementById('ajaxEditForm');
        let fd = new FormData(ajaxEditForm);
        let url = $("#ajaxEditForm").attr('action');
        let method = $("#ajaxEditForm").attr('method');

        $('#ajaxEditForm .form-control').each(function (i) {
            let index = i;
            let $toInput = $('#ajaxEditForm .form-control').eq(index);
            if ($(this).hasClass('summernote')) {
                let tmcId = $toInput.attr('id');
                let content = tinyMCE.get(tmcId).getContent();
                fd.delete($(this).attr('name'));
                fd.append($(this).attr('name'), content);
            }
        });

        $.ajax({
            url: url,
            method: method,
            data: fd,
            contentType: false,
            processData: false,
            success: function (data) {
                $(".request-loader").removeClass("show");
                $(".em").each(function () {
                    $(this).html('');
                })

                // Mark all fields as valid on success
                $('.form-control').each(function () {
                    if ($(this).prop('required')) {
                        $(this).addClass('valid-field').removeClass('invalid-field');
                    }
                });

                if (data == "success") {
                    location.reload();
                }
            },
            error: function (error) {
                $(".em").each(function () {
                    $(this).html('');
                })

                // Clear previous validation styles
                $('.form-control').removeClass('valid-field invalid-field');

                // Mark invalid fields with red border
                for (let x in error.responseJSON.errors) {
                    document.getElementById('editErr_' + x).innerHTML = error.responseJSON.errors[x][0];

                    // Add red border to invalid fields
                    let $field = $('[name="' + x + '"]');
                    if ($field.length) {
                        $field.addClass('invalid-field').removeClass('valid-field');
                    }
                }

                // Mark valid required fields with green border
                $('.form-control').each(function () {
                    if ($(this).prop('required') && !$(this).hasClass('invalid-field')) {
                        let fieldValue = $(this).val();
                        if (fieldValue && fieldValue.trim() !== '') {
                            $(this).addClass('valid-field');
                        }
                    }
                });

                $('.request-loader').removeClass('show');
                $(e.target).attr('disabled', false);
            }
        });
    });

    $(".update-btn").each(function () {
        $(this).on('click', function (e) {
            let $this = $(this);

            $(".request-loader").addClass("show");

            let formId = $(this).data('form_id');
            let ajaxEditForm = document.getElementById(formId);
            let fd = new FormData(ajaxEditForm);
            let url = $("#" + formId).attr('action');
            let method = $("#" + formId).attr('method');

            if ($("#" + formId + " .summernote").length > 0) {
                $("#" + formId + " .summernote").each(function (i) {
                    let content = $(this).summernote('code');
                    fd.delete($(this).attr('name'));
                    fd.append($(this).attr('name'), content);
                })
            }

            $.ajax({
                url: url,
                method: method,
                data: fd,
                contentType: false,
                processData: false,
                success: function (data) {
                    let parentCount = $this.parents('.modal').length;
                    let parentId;
                    // if the form is in modal
                    if (parentCount > 0) {
                        parentId = $this.parents('.modal').attr('id');
                    }
                    // if the form is not in modal
                    else {
                        parentId = formId;
                    }
                    $(".request-loader").removeClass("show");

                    $("#" + parentId).children(".em").each(function () {
                        $(this).html('');
                    })

                    if (data == "success") {
                        location.reload();
                    }

                    // if error occurs
                    else if (typeof data.error != 'undefined') {
                        for (let x in data) {
                            if (x == 'error') {
                                continue;
                            }
                            $("#" + parentId + " .eerr" + x).html(data[x][0]);
                        }
                    }
                }
            });
        });
    });
    /* ***************************************************
    ==========Form Update with AJAX Request End==========
    ******************************************************/



    /* ***************************************************
    ==========Delete Using AJAX Request Start==========
    ******************************************************/
    $('.deletebtn').on('click', function (e) {
        e.preventDefault();
        $(".request-loader").addClass("show");

        swal({
            title: are_you_sure,
            text: wont_revert_text,
            type: 'warning',
            buttons: {
                confirm: {
                    text: yes_delete_it,
                    className: 'btn btn-success'
                },
                cancel: {
                    text: cancel,
                    visible: true,
                    className: 'btn btn-danger'
                }
            }
        }).then((Delete) => {
            if (Delete) {
                $(this).parent(".deleteform").trigger('submit');
            } else {
                swal.close();
                $(".request-loader").removeClass("show");
            }
        });
    });
    /* ***************************************************
    ==========Delete Using AJAX Request End==========
    ******************************************************/


    /* ***************************************************
    ==========Close Ticket Using AJAX Request Start==========
    ******************************************************/
    $('.close-ticket').on('click', function (e) {
        e.preventDefault();
        $(".request-loader").addClass("show");
        swal({
            title: are_you_sure,
            text: close_this_ticket,
            type: 'warning',
            buttons: {
                confirm: {
                    text: Yes_close_it,
                    className: 'btn btn-success'
                },
                cancel: {
                    text: cancel,
                    visible: true,
                    className: 'btn btn-danger'
                }
            }
        }).then((Delete) => {
            if (Delete) {
                swal.close();
                $(".request-loader").removeClass("show");
            } else {
                swal.close();
                $(".request-loader").removeClass("show");
            }
        });
    });
    /* ***************************************************
    ==========Delete Using AJAX Request End==========
    ******************************************************/


    /* ***************************************************
    ==========Delete Using AJAX Request Start==========
    ******************************************************/
    $(document).on('change', '.bulk-check', function () {
        let val = $(this).data('val');
        let checked = $(this).prop('checked');

        // if selected checkbox is 'all' then check all the checkboxes
        if (val == 'all') {
            if (checked) {
                $(".bulk-check").each(function () {
                    $(this).prop('checked', true);
                });
            } else {
                $(".bulk-check").each(function () {
                    $(this).prop('checked', false);
                });
            }
        }


        // if any checkbox is checked then flag = 1, otherwise flag = 0
        let flag = 0;
        $(".bulk-check").each(function () {
            let status = $(this).prop('checked');
            if (status) {
                flag = 1;
            }
        });

        // if any checkbox is checked then show the delete button
        if (flag == 1) {
            $(".bulk-delete").addClass('d-inline-block');
            $(".bulk-delete").removeClass('d-none');
        }
        // if no checkbox is checked then hide the delete button
        else {
            $(".bulk-delete").removeClass('d-inline-block');
            $(".bulk-delete").addClass('d-none');
        }
    });

    $('.bulk-delete').on('click', function () {
        swal({
            title: are_you_sure,
            text: wont_revert_text,
            type: 'warning',
            buttons: {
                confirm: {
                    text: yes_delete_it,
                    className: 'btn btn-success'
                },
                cancel: {
                    text: cancel,
                    visible: true,
                    className: 'btn btn-danger'
                }
            }
        }).then((Delete) => {
            if (Delete) {
                $(".request-loader").addClass('show');
                let href = $(this).data('href');
                let ids = [];

                // take ids of checked one's
                $(".bulk-check:checked").each(function () {
                    if ($(this).data('val') != 'all') {
                        ids.push($(this).data('val'));
                    }
                });

                let fd = new FormData();
                for (let i = 0; i < ids.length; i++) {
                    fd.append('ids[]', ids[i]);
                }

                $.ajax({
                    url: href,
                    method: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $(".request-loader").removeClass('show');
                        if (data == "success") {
                            location.reload();
                        }
                    }
                });
            } else {
                swal.close();
            }
        });

    });
    /* ***************************************************
    ==========Delete Using AJAX Request End==========
    ******************************************************/


    //  image (id) preview js/
    $(document).on('change', '#image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });

    $(document).on('change', '#image2', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage2 img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })

    $(document).on('change', '#image3', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage3 img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })

    $(document).on('change', '#image4', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage4 img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    })

    // datepicker & timepicker
    $("input.datepicker").flatpickr({
        enableTime: false,
        noCalendar: false,
        minDate: "today",
        allowInput: true,
    });

    $('input.timepicker').flatpickr({
        enableTime: true,
        noCalendar: true,
        allowInput: true,
        dateFormat: 'h:i K',
        minuteIncrement: 1
    });

    // select2
    $('.select2').select2();

    $("#langBtn").on('click', function () {
        $("#langForm").trigger("submit");
    });
});

$(document).on('click', '#earnTypeC', function () {
    $('.commisionPrice').removeClass('d-none')
})

$(document).on('click', '#earnTypeS', function () {
    $('.commisionPrice').addClass('d-none')
})

/* ******************** *******************************
==========Form Submit with AJAX Request Start==========
******************************************************/
$("#submitFeatureSectionBtn").on('click', function (e) {
    $(e.target).attr('disabled', true);
    $(".request-loader").addClass("show");
    let ajaxForm = document.getElementById('featureSectionForm');
    let fd = new FormData(ajaxForm);
    let url = $("#featureSectionForm").attr('action');
    let method = $("#featureSectionForm").attr('method');

    $.ajax({
        url: url,
        method: method,
        data: fd,
        contentType: false,
        processData: false,
        success: function (data) {
            $(e.target).attr('disabled', false);
            $(".request-loader").removeClass("show");

            $(".em").each(function () {
                $(this).html('');
            })

            if (data == "success") {
                location.reload();
            }
            // if error occurs
            else if (typeof data.error != 'undefined') {
                for (let x in data) {
                    if (x == 'error') {
                        continue;
                    }
                    document.getElementById('err' + x).innerHTML = data[x][0];
                }
            } else if (data?.errors?.error) {
                const errors = data?.errors;
                Object.keys(errors).map(function (key) {
                    if (key !== 'error')
                        document.getElementById('err' + key).innerHTML = errors[key][0];
                });
            }
        },
        error: function (error) {
            $(".em").each(function () {
                $(this).html('');
            })
            for (let x in error.responseJSON.errors) {
                document.getElementById('err' + x).innerHTML = error.responseJSON.errors[x][0];
            }
            $(".request-loader").removeClass("show");
            $(e.target).attr('disabled', false);
            if (error?.responseJSON?.exception) {
                bootnotify(error?.responseJSON?.exception, "Warning", "warning");
            }
        }
    });
});


//admin languages
$('.adminLangBtn').on('change', function () {
    let $this = $(this);
    var $code = $this.val();
    var $url = $this.data('url');
    $.ajax({
        url: $url,
        method: 'get',
        data: {
            code: $code
        },
        success: function (data) {
            var curr_url = $(location).attr('href');
            var new_url;
            if (curr_url.includes('?language=')) {
                new_url = curr_url.replace(/([?&])language=[^&]*/, '$1language=' + $code);
            } else {
                new_url = curr_url + (curr_url.includes('?') ? '&' : '?') + 'language=' + $code;
            }
            new_url = new_url.replace(/([?&])$/, '');
            window.location = new_url;
        }
    });
});

//submitBtn languages
$("#submitBtn2").on('click', function (e) {
    $(e.target).attr('disabled', true);
    $(".request-loader").addClass("show");

    // Clear previous validation styles
    $('.form-control').removeClass('valid-field invalid-field');
    $('.form-control').css('border', '');

    let ajaxForm2 = document.getElementById('ajaxForm2');
    let fd = new FormData(ajaxForm2);
    let url = $("#ajaxForm2").attr('action');
    let method = $("#ajaxForm2").attr('method');

    $('.form-control').each(function (i) {
        let index = i;
        let $toInput = $('.form-control').eq(index);

        if ($(this).hasClass('summernote')) {
            let tmcId = $toInput.attr('id');
            let content = tinyMCE.get(tmcId).getContent();
            fd.delete($(this).attr('name'));
            fd.append($(this).attr('name'), content);
        }
    });

    $.ajax({
        url: url,
        method: method,
        data: fd,
        contentType: false,
        processData: false,
        success: function (data) {
            $(e.target).attr('disabled', false);
            $(".request-loader").removeClass("show");

            $(".em").each(function () {
                $(this).html('');
            });

            // Mark all fields as valid on success
            $('.form-control').each(function () {
                if ($(this).prop('required')) {
                    $(this).addClass('valid-field').removeClass('invalid-field');
                }
            });

            if (data == "success") {
                location.reload();
            }
        },
        error: function (error) {
            $('.em').each(function () {
                $(this).html('');
            });

            // Clear previous validation styles
            $('.form-control').removeClass('valid-field invalid-field');

            // Mark invalid fields with red border
            for (let x in error.responseJSON.errors) {
                document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];

                // Add red border to invalid fields
                let $field = $('[name="' + x + '"]');
                if ($field.length) {
                    $field.addClass('invalid-field').removeClass('valid-field');
                }
            }

            // Mark valid required fields with green border
            $('.form-control').each(function () {
                if ($(this).prop('required') && !$(this).hasClass('invalid-field')) {
                    let fieldValue = $(this).val();
                    if (fieldValue && fieldValue.trim() !== '') {
                        $(this).addClass('valid-field');
                    }
                }
            });

            $('.request-loader').removeClass('show');
            $(e.target).attr('disabled', false);
        }
    });
});

//submitBtn languages
$("#submitBtn3").on('click', function (e) {
    $(e.target).attr('disabled', true);
    $(".request-loader").addClass("show");

    // Clear previous validation styles
    $('.form-control').removeClass('valid-field invalid-field');
    $('.form-control').css('border', '');

    let ajaxForm = document.getElementById('ajaxForm3');
    let fd = new FormData(ajaxForm);
    let url = $("#ajaxForm3").attr('action');
    let method = $("#ajaxForm3").attr('method');

    $('.form-control').each(function (i) {
        let index = i;
        let $toInput = $('.form-control').eq(index);

        if ($(this).hasClass('summernote')) {
            let tmcId = $toInput.attr('id');
            let content = tinyMCE.get(tmcId).getContent();
            fd.delete($(this).attr('name'));
            fd.append($(this).attr('name'), content);
        }
    });

    $.ajax({
        url: url,
        method: method,
        data: fd,
        contentType: false,
        processData: false,
        success: function (data) {
            $(e.target).attr('disabled', false);
            $(".request-loader").removeClass("show");

            $(".em").each(function () {
                $(this).html('');
            });

            // Mark all fields as valid on success
            $('.form-control').each(function () {
                if ($(this).prop('required')) {
                    $(this).addClass('valid-field').removeClass('invalid-field');
                }
            });

            if (data == "success") {
                location.reload();
            }
        },
        error: function (error) {
            $('.em').each(function () {
                $(this).html('');
            });

            // Clear previous validation styles
            $('.form-control').removeClass('valid-field invalid-field');

            // Mark invalid fields with red border
            for (let x in error.responseJSON.errors) {
                document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];

                // Add red border to invalid fields
                let $field = $('[name="' + x + '"]');
                if ($field.length) {
                    $field.addClass('invalid-field').removeClass('valid-field');
                }
            }

            // Mark valid required fields with green border
            $('.form-control').each(function () {
                if ($(this).prop('required') && !$(this).hasClass('invalid-field')) {
                    let fieldValue = $(this).val();
                    if (fieldValue && fieldValue.trim() !== '') {
                        $(this).addClass('valid-field');
                    }
                }
            });

            $('.request-loader').removeClass('show');
            $(e.target).attr('disabled', false);
        }
    });
});

//submitBtn languages
$("#submitBtn4").on('click', function (e) {
    $(e.target).attr('disabled', true);
    $(".request-loader").addClass("show");

    // Clear previous validation styles
    $('.form-control').removeClass('valid-field invalid-field');
    $('.form-control').css('border', '');

    let ajaxForm4 = document.getElementById('ajaxForm4');
    let fd = new FormData(ajaxForm4);
    let url = $("#ajaxForm4").attr('action');
    let method = $("#ajaxForm4").attr('method');

    $('.form-control').each(function (i) {
        let index = i;
        let $toInput = $('.form-control').eq(index);

        if ($(this).hasClass('summernote')) {
            let tmcId = $toInput.attr('id');
            let content = tinyMCE.get(tmcId).getContent();
            fd.delete($(this).attr('name'));
            fd.append($(this).attr('name'), content);
        }
    });

    $.ajax({
        url: url,
        method: method,
        data: fd,
        contentType: false,
        processData: false,
        success: function (data) {
            $(e.target).attr('disabled', false);
            $(".request-loader").removeClass("show");

            $(".em").each(function () {
                $(this).html('');
            });

            // Mark all fields as valid on success
            $('.form-control').each(function () {
                if ($(this).prop('required')) {
                    $(this).addClass('valid-field').removeClass('invalid-field');
                }
            });

            if (data == "success") {
                location.reload();
            }
        },
        error: function (error) {
            $('.em').each(function () {
                $(this).html('');
            });

            // Clear previous validation styles
            $('.form-control').removeClass('valid-field invalid-field');

            // Mark invalid fields with red border
            for (let x in error.responseJSON.errors) {
                document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];

                // Add red border to invalid fields
                let $field = $('[name="' + x + '"]');
                if ($field.length) {
                    $field.addClass('invalid-field').removeClass('valid-field');
                }
            }

            // Mark valid required fields with green border
            $('.form-control').each(function () {
                if ($(this).prop('required') && !$(this).hasClass('invalid-field')) {
                    let fieldValue = $(this).val();
                    if (fieldValue && fieldValue.trim() !== '') {
                        $(this).addClass('valid-field');
                    }
                }
            });

            $('.request-loader').removeClass('show');
            $(e.target).attr('disabled', false);
        }
    });
});

$(document).on('change', '.image_preview33', function (event) {
    let $this = $(this);
    var file = event.target.files[0];
    var reader = new FileReader();
    reader.onload = function (e) {
        $this.prev('.showImage').children('img').attr('src', e.target.result);
    };

    reader.readAsDataURL(file);
});


/******************************************************
==========Form Update with AJAX Request End==========
******************************************************/
// blog form
$('#categoryForm').on('submit', function (e) {

    $('.request-loader').addClass('show');
    e.preventDefault();

    let action = $(this).attr('action');
    let fd = new FormData($(this)[0]);
    $('.form-control').each(function (i) {
        let index = i;

        let $toInput = $('.form-control').eq(index);

        if ($(this).hasClass('summernote')) {
            let tmcId = $toInput.attr('id');
            let content = tinyMCE.get(tmcId).getContent();
            fd.delete($(this).attr('name'));
            fd.append($(this).attr('name'), content);
        }
    });

    $.ajax({
        url: action,
        method: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        success: function (data) {
            $('.request-loader').removeClass('show');
            if (data === 'success') {
                location.reload();
            }

            if (data == "downgrade") {
                $('.modal').modal('hide');
                "use strict";
                var content = {};
                content.message = your_feature_limit_is_over_or_down_graded;
                content.title = warning;
                content.icon = 'fa fa-bell';
                $.notify(content, {
                    type: 'warning',
                    placement: {
                        from: 'top',
                        align: 'right'
                    },
                    showProgressbar: true,
                    time: 1000,
                    delay: 4000,
                });
                $("#limitModal").modal('show');
            }
        },
        error: function (error) {
            let errors = ``;
            for (let x in error.responseJSON.errors) {
                errors += `<li>
                                <p class="text-danger mb-0">${error.responseJSON.errors[x][0]}</p>
                              </li>`;
            }


            if (error?.responseJSON?.exception) {
                errors += `<li>
                                  <p class="text-danger mb-0">${error?.responseJSON?.exception}</p>
                              </li>`;
            }

            $('#blogErrors ul').html(errors);
            $('#blogErrors').show();

            $('.request-loader').removeClass('show');

            $('html, body').animate({
                scrollTop: $('#blogErrors').offset().top - 100
            }, 1000);

        }
    });
});
