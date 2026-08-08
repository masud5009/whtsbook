"use strict";

WebFont.load({
    google: { "families": ["Lato:300,400,700,900"] },
    custom: { "families": ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: [mainURL + '/assets/admin/css/fonts.min.css'] },
    active: function () {
        sessionStorage.fonts = true;
    }
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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

function cloneContent(fromId, toId, event) {
    "use strict";
    let $target = $(event.target);

    if ($target.is(":checked")) {
        $("#" + fromId + " .form-control").each(function (i) {
            let index = i;
            let val = $(this).val();
            let $toInput = $("#" + toId + " .form-control").eq(index);

            if ($(this).hasClass('summernote')) {
                let tmcFId = $toInput.attr('id');
                let tmcId = $(this).attr('id');
                let val = tinyMCE.get(tmcId).getContent();
                tinyMCE.get(tmcFId).setContent(val);
            } else if ($(this).data('role') == 'tagsinput') {
                if (val.length > 0) {
                    let tags = val.split(',');
                    tags.forEach(tag => {
                        $toInput.tagsinput('add', tag);
                    });
                } else {
                    $toInput.tagsinput('removeAll');
                }
            } else {
                $toInput.val(val);
            }
        });
    } else {
        $("#" + toId + " .form-control").each(function (i) {
            let $toInput = $("#" + toId + " .form-control").eq(i);
            if ($(this).hasClass('summernote')) {
                tinyMCE.get(toId).setContent('');
            } else if ($(this).data('role') == 'tagsinput') {
                $toInput.tagsinput('removeAll');
            } else {
                $toInput.val('');
            }
        });
    }
}

function base64ToBlob(base64, mime) {
    mime = mime || '';
    var sliceSize = 1024;
    var byteChars = window.atob(base64);
    var byteArrays = [];

    for (var offset = 0, len = byteChars.length; offset < len; offset += sliceSize) {
        var slice = byteChars.slice(offset, offset + sliceSize);

        var byteNumbers = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        var byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
    }

    return new Blob(byteArrays, { type: mime });
}


$(function ($) {
    //  image (id) preview js/
    $(document).on('change', '#image', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })

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
    $(document).on('change', '#image5', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage5 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image6', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage6 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image7', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage7 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image10', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage10 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })

    $(document).on('change', '#image12', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage12 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })

    $(document).on('change', '#image13', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage13 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })

    $(document).on('change', '#image14', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage14 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image15', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage15 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image16', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage16 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image20', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage20 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image21', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage21 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image22', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage22 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })
    $(document).on('change', '#image23', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.showImage23 img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    })

    $(document).on('change', '#vaultFile', function (event) {
        var file = event.target.files[0];
        var label = document.querySelector('label[for="vaultFile"]');

        if (label) {
            label.textContent = file ? file.name : 'Choose file';
        }
    })

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

    /*****************************************************************
    ==========disabling default behave of form submits start==========
    *****************************************************************/
    $("#ajaxEditForm").attr('onsubmit', 'return false');
    $("#ajaxForm").attr('onsubmit', 'return false');
    /***************************************************************
    ==========disabling default behave of form submits end==========
    ***************************************************************/


    /******************************************************
    ==========flatpickr datepicker start==========
    ******************************************************/
    $('.datepicker').flatpickr({
        enableTime: false,
        noCalendar: false,
        minDate: "today",
        allowInput: true,
    });
    $('.datepicker-2').flatpickr({
        enableTime: false,
        noCalendar: false,
        allowInput: true,
    });


    $('.timepicker').each(function () {
        let interval = $(this).data('interval') ? $(this).data('interval') : 1;

        $(this).flatpickr({
            enableTime: true,
            noCalendar: true,
            allowInput: true,
            dateFormat: 'h:i K',
            minuteIncrement: interval
        });
    });
    /*****************************************************
    ==========flatpickr datepicker end==========
    ******************************************************/


    /******************************************************
    ==========dm uploader single file upload start=========
    ******************************************************/
    function ui_single_update_active(element, active) {
        element.find('div.progress').toggleClass('d-none', !active);
        element.find('.progressbar').toggleClass('d-none', active);

        element.find('input[type="file"]').prop('disabled', active);
        element.find('.btn').toggleClass('disabled', active);

        element.find('.btn i').toggleClass('fa-circle-o-notch fa-spin', active);
        element.find('.btn i').toggleClass('fa-folder-o', !active);
    }

    function ui_single_update_progress(element, percent, active) {
        active = (typeof active === 'undefined' ? true : active);

        var bar = element.find('div.progress-bar');

        bar.width(percent + '%').attr('aria-valuenow', percent);
        bar.toggleClass('progress-bar-striped progress-bar-animated', active);

        if (percent === 0) {
            bar.html('');
        } else {
            bar.html(percent + '%');
        }
    }

    function ui_single_update_status(element, message, color) {
        color = (typeof color === 'undefined' ? 'muted' : color);

        element.find('small.status').prop('class', 'status text-' + color).html(message);
    }


    $('.drag-and-drop-zone').each(function (i) {
        let $this = $(this);

        $this.dmUploader({
            url: $this.attr('action'),
            multiple: false,
            allowedTypes: 'image/*',
            extFilter: ['jpg', 'jpeg', 'png'],
            onDragEnter: function () {
                // Happens when dragging something over the DnD area
                this.addClass('active');
            },
            onDragLeave: function () {
                // Happens when dragging something OUT of the DnD area
                this.removeClass('active');
            },
            onInit: function () {
                // Plugin is ready to use
                this.find('.progressbar').val('');
            },
            onComplete: function () {
                // All files in the queue are processed (success or error)
            },
            onNewFile: function (id, file) {
                // When a new file is added using the file selector or the DnD area
                if (typeof FileReader !== "undefined") {
                    var reader = new FileReader();
                    var img = this.find('img');

                    reader.onload = function (e) {
                        img.attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            },
            onBeforeUpload: function (id) {
                // about to start uploading a file
                ui_single_update_progress(this, 0, true);
                ui_single_update_active(this, true);
                ui_single_update_status(this, 'Uploading...');
            },
            onUploadProgress: function (id, percent) {
                // Updating file progress
                ui_single_update_progress(this, percent);
            },
            onUploadSuccess: function (id, data) {
                var response = JSON.stringify(data);

                let ems = document.getElementsByClassName('em');
                for (let i = 0; i < ems.length; i++) {
                    ems[i].innerHTML = '';
                }

                // if only the image is being stored
                if (data.status == "success") {
                    bootnotify(data.image + " updated successfully!", 'Success!', 'success');
                    ui_single_update_active(this, false);
                    // You should probably do something with the response data, we just show it
                    this.find('.progressbar').val("Uploaded Successfully");
                    this.find('.form-control[readonly]').attr('style', 'background-color: #28a745 !important; text-alignment: center !important; opacity: 1 !important;border: none !important;');
                    ui_single_update_status(this, 'Upload Completed.', 'success');
                }


                // if the image is being stored along with other form fields
                else if (data.status == "session_image") {
                    $("#image").attr('name', data.image);
                    $("#image").val(data.filename);

                    $("#editImage").attr('name', data.image);
                    $("#editImage").val(data.filename);
                    ui_single_update_active(this, false);

                    // You should probably do something with the response data, we just show it
                    this.find('.progressbar').val("Uploaded Successfully");
                    this.find('.form-control[readonly]').attr('style', 'background-color: #28a745 !important; text-alignment: center !important; opacity: 1 !important;border: none !important;');
                    ui_single_update_status(this, 'Upload Completed.', 'success');
                }

                // if you need a reload after image store
                else if (data.status == "reload") {
                    ui_single_update_active(this, false);
                    // You should probably do something with the response data, we just show it
                    this.find('.progressbar').val("Uploaded Successfully");
                    this.find('.form-control[readonly]').attr('style', 'background-color: #28a745 !important; text-alignment: center !important; opacity: 1 !important;border: none !important;');
                    ui_single_update_status(this, 'Upload Completed.', 'success');
                    location.reload();
                }

                // if error is returned while storing image
                else if (typeof data.errors.error != 'undefined') {
                    if (typeof data.errors.file != 'undefined') {
                        document.getElementById('err_' + data.id).innerHTML = data.errors.file[0];
                    }
                }
            },
            onUploadError: function (id, xhr, status, message) {
                // Happens when an upload error happens
                ui_single_update_active(this, false);
                ui_single_update_status(this, 'Error: ' + message, 'danger');
            },
            onFallbackMode: function () {
                // When the browser doesn't support this plugin :(
            },
            onFileSizeError: function (file) {
                ui_single_update_status(this, 'File excess the size limit', 'danger');
            },
            onFileTypeError: function (file) {
                ui_single_update_status(this, 'File type is not an image', 'danger');
            },
            onFileExtError: function (file) {
                ui_single_update_status(this, 'File extension not allowed', 'danger');
            }
        });
    })
    /*****************************************************
    ==========dm uploader single file upload end==========
    ******************************************************/


    /*****************************************************
    ==========fontawesome icon picker start==========
    ******************************************************/
    $('.icp-dd').iconpicker();
    /* ***************************************************
    ==========fontawesome icon picker end============
    ******************************************************/

    /*****************************************************
    ==========lfm image icon for summernote start=========
    ******************************************************/
    var ImageButton = function (context) {
        var ui = $.summernote.ui;
        var button = ui.button({
            contents: '<i class="far fa-images"></i>',
            tooltip: 'File Manager',
            click: function () {
                let id = context.$note[0].id;
                $('#lfmModalSummernote').find('iframe').attr('src', '');
                $('#lfmModalSummernote').find('iframe').attr('src', mainURL + '/laravel-filemanager?summernote=' + id);
                $('#lfmModalSummernote').modal('show');
            }
        });

        return button.render();
    }
    /*****************************************************
    ==========lfm image icon for summernote end=========
    ******************************************************/


    /*****************************************************
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
    /*****************************************************
    ==========Summernote initialization end==========
    ******************************************************/





    // select2 start
    $('.select2').select2();
    // select2 end


    /******************************************************
    ==========Form Submit with AJAX Request Start==========
    ******************************************************/
    $("#submitBtn").on('click', function (e) {
        $(e.target).attr('disabled', true);
        $(".request-loader").addClass("show");

        // Clear previous validation styles
        $('.form-control').removeClass('valid-field invalid-field');
        $('.form-control').css('border', '');

        if ($(".iconpicker-component").length > 0) {
            $("#inputIcon").val($(".iconpicker-component").find('i').attr('class'));
        }

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

        let blob_image_url = $('#blob_image').text().trim();
        if (blob_image_url.length > 0) {
            var base64ImageContent = blob_image_url.replace(/^data:image\/(png|jpg);base64,/, "");
            var blob = base64ToBlob(base64ImageContent, 'image/png');
            fd.append('thumbnail_image', blob);
        }

        $.ajax({
            url: url,
            method: method,
            data: fd,
            contentType: false,
            processData: false,
            success: function (data) {
                $(e.target).attr('disabled', false);
                $('.request-loader').removeClass('show');

                $('.em').each(function () {
                    $(this).html('');
                });

                // Mark all fields as valid on success
                $('.form-control').each(function () {
                    if ($(this).prop('required')) {
                        $(this).addClass('valid-field').removeClass('invalid-field');
                    }
                });

                if (data == 'success') {
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

    $("#refundBtn").on('click', function (e) {
        $(e.target).attr('disabled', true);
        $(".request-loader").addClass("show");

        // Clear previous validation styles
        $('.form-control').removeClass('valid-field invalid-field');
        $('.form-control').css('border', '');

        let refundForm = document.getElementById('refundForm');
        let fd = new FormData(refundForm);
        let url = $("#refundForm").attr('action');
        let method = $("#refundForm").attr('method');

        $.ajax({
            url: url,
            method: method,
            data: fd,
            contentType: false,
            processData: false,
            success: function (data) {
                $(e.target).attr('disabled', false);
                $('.request-loader').removeClass('show');

                $('.em').each(function () {
                    $(this).html('');
                });

                // Mark all fields as valid on success
                $('.form-control').each(function () {
                    if ($(this).prop('required')) {
                        $(this).addClass('valid-field').removeClass('invalid-field');
                    }
                });

                if (data == 'success') {
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
    /******************************************************
    ==========Form Submit with AJAX Request End==========
    ******************************************************/



    /********************************************************************
    ==========Form Prepopulate After Clicking Edit Button Start=========
    ********************************************************************/
    $(".editBtn").on('click', function () {
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
                if ($('#in_icon').length > 0) {
                    $('#in_icon').attr('class', datas['icon']);
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
    /******************************************************************
    ==========Form Prepopulate After Clicking Edit Button End==========
    ******************************************************************/


    /******************************************************************
    ==========Form Prepopulate After Clicking Location Button Start====
    ******************************************************************/
    $('.locationBtn').on('click', function () {
        let info = $(this).data();

        $('#package-id-location').val(info.id);
    });
    /******************************************************************
    ==========Form Prepopulate After Clicking Location Button End======
    ******************************************************************/


    /******************************************************************
    ==========Form Prepopulate After Clicking Plan Button Start========
    ******************************************************************/
    $('.planBtn').on('click', function () {
        let info = $(this).data();

        if (info.plan_type == 'daywise') {
            $('#addDaywisePlanModal').modal('show');
            $('#package-id-daywise-plan').val(info.id);
        } else if (info.plan_type == 'timewise') {
            $('#addTimewisePlanModal').modal('show');
            $('#package-id-timewise-plan').val(info.id);
        }
    });
    /******************************************************************
    ==========Form Prepopulate After Clicking Plan Button End==========
    ******************************************************************/


    /**************************************************************
    ==========Form Prepopulate After Clicking Mail Button Start====
    **************************************************************/
    $('.mailBtn').on('click', function () {
        let info = $(this).data();

        $('#mail-id').val(info.customer_email);
    });
    /**************************************************************
    ==========Form Prepopulate After Clicking Mail Button End======
    **************************************************************/


    /***********************************************************************
    ==========Form Submit with AJAX Request For Daywise Plan Start==========
    ***********************************************************************/
    $('#daywise-plan-submit-btn').on('click', function (e) {
        $(e.target).attr('disabled', true);
        $('.request-loader').addClass('show');

        let ajaxForm = document.getElementById('daywise-plan-ajax-form');
        let fd = new FormData(ajaxForm);
        let url = $('#daywise-plan-ajax-form').attr('action');
        let method = $('#daywise-plan-ajax-form').attr('method');

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
                $('.request-loader').removeClass('show');

                $('.em').each(function () {
                    $(this).html('');
                })

                location.reload();
            },
            error: function (error) {
                $(e.target).attr('disabled', false);
                $('.request-loader').removeClass('show');

                $('.em').each(function () {
                    $(this).html('');
                });

                for (let x in error.responseJSON.errors) {
                    document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];
                }
            }
        });
    });
    /*********************************************************************
    ==========Form Submit with AJAX Request For Daywise Plan End==========
    *********************************************************************/


    /***********************************************************************
    ==========Form Submit with AJAX Request For Timewise Plan Start=========
    ***********************************************************************/
    $('#timewise-plan-submit-btn').on('click', function (e) {
        $(e.target).attr('disabled', true);
        $('.request-loader').addClass('show');

        let ajaxForm = document.getElementById('timewise-plan-ajax-form');
        let fd = new FormData(ajaxForm);
        let url = $('#timewise-plan-ajax-form').attr('action');
        let method = $('#timewise-plan-ajax-form').attr('method');

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
                $('.request-loader').removeClass('show');

                $('.em').each(function () {
                    $(this).html('');
                })

                location.reload();
            },
            error: function (error) {
                $(e.target).attr('disabled', false);
                $('.request-loader').removeClass('show');

                $('.em').each(function () {
                    $(this).html('');
                });

                for (let x in error.responseJSON.errors) {
                    document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];
                }
            }
        });
    });
    /***********************************************************************
    ==========Form Submit with AJAX Request For Timewise Plan End===========
    ***********************************************************************/


    /******************************************************
    ==========Form Update with AJAX Request Start==========
    ******************************************************/
    $("#updateBtn").on('click', function (e) {
        $(e.target).attr('disabled', true);
        $(".request-loader").addClass("show");

        // Clear previous validation styles
        $('#ajaxEditForm .form-control').removeClass('valid-field invalid-field');
        $('#ajaxEditForm .form-control').css('border', '');

        if ($("#ajaxEditForm .iconpicker-component").length > 0) {
            $("#editInputIcon").val($("#ajaxEditForm .iconpicker-component").find('i').attr('class'));
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
                $(e.target).attr('disabled', false);
                $('.request-loader').removeClass('show');

                $('.em').each(function () {
                    $(this).html('');
                });

                // Mark all fields as valid on success
                $('#ajaxEditForm .form-control').each(function () {
                    if ($(this).prop('required')) {
                        $(this).addClass('valid-field').removeClass('invalid-field');
                    }
                });

                if (data == 'success') {
                    location.reload();
                }

                if (data == "downgrade") {
                    $('.modal').modal('hide');
                    "use strict";
                    var content = {};
                    content.message = 'Your feature limit is over or down graded!';
                    content.title = "Warning";
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
                $('.em').each(function () {
                    $(this).html('');
                });

                // Clear previous validation styles
                $('#ajaxEditForm .form-control').removeClass('valid-field invalid-field');

                // Mark invalid fields with red border
                for (let x in error.responseJSON.errors) {
                    document.getElementById('editErr_' + x).innerHTML = error.responseJSON.errors[x][0];

                    // Add red border to invalid fields
                    let $field = $('#ajaxEditForm [name="' + x + '"]');
                    if ($field.length) {
                        $field.addClass('invalid-field').removeClass('valid-field');
                    }
                }

                // Mark valid required fields with green border
                $('#ajaxEditForm .form-control').each(function () {
                    if ($(this).prop('required') && !$(this).hasClass('invalid-field')) {
                        let fieldValue = $(this).val();

                        // Special handling for summernote fields
                        if ($(this).hasClass('summernote')) {
                            let editorId = $(this).attr('id');
                            if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId)) {
                                fieldValue = tinyMCE.get(editorId).getContent();
                            }
                        }

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

    //amenities Form
    $('#amenitiesForm').on('submit', function (e) {
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


    /******************************************************
    ==========Delete Using AJAX Request Start==========
    ******************************************************/
    $('.deleteBtn').on('click', function (e) {
        e.preventDefault();
        let $button = $(this);
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
                let formId = $button.attr('form');

                if (formId && $('#' + formId).length) {
                    $('#' + formId).trigger('submit');
                } else {
                    $button.closest('form.deleteForm').trigger('submit');
                }
            } else {
                swal.close();
                $(".request-loader").removeClass("show");
            }
        });
    });
    /******************************************************
    ==========Delete Using AJAX Request End==========
    ******************************************************/


    /*****************************************************
    ==========Close Ticket Using AJAX Request Start======
    ******************************************************/
    $('.close-ticket').on('click', function (e) {
        e.preventDefault();
        $(".request-loader").addClass("show");

        swal({
            title: are_you_sure,
            text: you_want_to_close_this_ticket,
            type: 'warning',
            buttons: {
                confirm: {
                    text: yes_close_it,
                    className: 'btn btn-success'
                },
                cancel: {
                    text: Cancel,
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
    /******************************************************
    ==========Close Ticket Using AJAX Request End==========
    ******************************************************/


    /*****************************************************
    ==========Bulk Delete Using AJAX Request Start========
    ******************************************************/
    $(".bulk-check").on('change', function () {
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
        } else {
            // if no checkbox is checked then hide the delete button
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
    /*****************************************************
    ==========Bulk Delete Using AJAX Request End==========
    *****************************************************/


    // LFM scripts START
    window.lfmSliders = [];
    window.closeLfmModal = function (serial) {
        $('#lfmModal' + serial).modal('hide');
        // if any modal is open, then add 'modal-open' class to body
        if ($(".modal.show").length > 0) {
            setTimeout(function () {
                $('body').addClass('modal-open');
            }, 500);
        }
    };
    window.closeLfmModalSummernote = function () {
        $('#lfmModalSummernote').modal('hide');
        // if any modal is open, then add 'modal-open' class to body
        setTimeout(function () {
            if ($(".modal.show").length > 0) {
                $('body').addClass('modal-open');
            }
        }, 500);
    };

    $(`.lfm-modal .fas.fa-times-circle`).on('click', function () {
        $(this).parents('.lfm-modal').modal('hide');
        // if any modal is open, then add 'modal-open' class to body
        setTimeout(function () {
            if ($(".modal.show", parent.document).length > 0) {
                $('body', parent.document).addClass('modal-open');
            }
        }, 500);
    });

    $(`.lfm-modal`).on('click', function (e) {
        if (!$(e.target).hasClass('modal-dialog') && !$(e.target).parents('.modal-dialog').length) {
            // if any modal is open, then add 'modal-open' class to body
            setTimeout(function () {
                if ($(".modal.show", parent.document).length > 0) {
                    $('body', parent.document).addClass('modal-open');
                }
            }, 500);
        }
    });

    window.insertImage = function (id, items) {
        items.forEach(function (item) {
            $("#" + id).summernote('insertImage', item);
        });
    };

    $(document).on('click', ".rmvLfmSliderImgs", function () {
        let index = $(this).data('index');
        let serial = $(this).data('serial');

        window.lfmSliders.splice(index, 1);
        window.prevLfmSliderImgs(serial);
    });


    window.prevLfmSliderImgs = function (serial) {
        let imagesDiv = ``;
        let sliderValues = [];

        if (window.lfmSliders.length > 0) {
            window.lfmSliders.forEach(function (slider, index) {

                imagesDiv += `<div class="thumb-preview mr-2 mb-2">
                <i class="fas fa-times-circle rmvLfmSliderImgs" data-index="${index}" data-serial="${serial}"></i>
                <img src="${slider}" alt="Slider Image">
            </div>`;

                sliderValues.push(slider.replace(mainURL + '/', ""));

            });
        }

        $("#sliderThumbs" + serial).html(imagesDiv);

        $("#fileInput" + serial).val(sliderValues);
    };
    // LFM scripts END


    // Uploaded Image Preview Start
    $('.img-input').on('change', function (event) {
        let file = event.target.files[0];
        let reader = new FileReader();

        reader.onload = function (e) {
            $('.uploaded-img').attr('src', e.target.result);
        };

        reader.readAsDataURL(file);
    });

    $('#socialForm').on('submit', function (e) {
        e.preventDefault();

        $('#inputIcon').val($('.iconpicker-component').find('i').attr('class'));
        document.getElementById('socialForm').submit();
    });

    // make input fields RTL
    $("select[name='language_id']").on('change', function () {
        $(".request-loader").addClass("show");

        // product category load according to language selection
        $("#category").removeAttr('disabled');

        let langid = $(this).val();

        $("#bcategory").removeAttr('disabled');
        $.get(mainURL + "/user/blog/" + langid + "/getcats", function (data) {
            let options = `<option value="" disabled selected>Select a category</option>`;
            for (let i = 0; i < data.length; i++) {
                options += `<option value="${data[i].id}">${data[i].name}</option>`;
            }
            $("#bcategory").html(options);

        });

        if ($(this).parents('form').hasClass('create')) {
            $.get(mainURL + "/user/rtlcheck/" + $(this).val(), function (data) {
                $(".request-loader").removeClass("show");
                if (data == 1) {
                    $("form.create input").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.create select").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.create textarea").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });

                    $("form.create .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    });

                } else {
                    $("form.create input, form.create select, form.create textarea").removeClass('rtl');
                    $("form.create .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    });
                }
            });
        } else if ($(this).parents('form').hasClass('modal-form')) {
            $.get(mainURL + "/user/rtlcheck/" + $(this).val(), function (data) {
                $(".request-loader").removeClass("show");
                if (data == 1) {
                    $("form.modal-form input").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form select").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form textarea").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    });

                } else {
                    $("form.modal-form input, form.modal-form select, form.modal-form textarea").removeClass('rtl');
                    $("form.modal-form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    });
                }
            });
        } else {
            // make input fields RTL
            $.get(mainURL + "/user/rtlcheck/" + $(this).val(), function (data) {
                $(".request-loader").removeClass("show");
                if (data == 1) {
                    $("form input").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form select").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form textarea").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    });

                } else {
                    $("form input, form select, form textarea").removeClass('rtl');
                    $("form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    });
                }
            });
        }
    });

    $("select[name='user_language_id']").on('change', function () {
        const langid = $(this).val();
        $(".request-loader").addClass("show");
        if ($(this).parents('form').hasClass('create')) {

            $.get(mainURL + "/user/rtlcheck/" + langid, function (data) {
                $(".request-loader").removeClass("show");
                if (data == 1) {
                    $("form.create input").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.create select").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.create textarea").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.create .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    });

                } else {
                    $("form.create input, form.create select, form.create textarea").removeClass('rtl');

                    $("form.create .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    });
                }
            });
        } else if ($(this).parents('form').hasClass('modal-form')) {
            $.get(mainURL + "/user/rtlcheck/" + $(this).val(), function (data) {
                $(".request-loader").removeClass("show");
                if (data == 1) {
                    $("form.modal-form input").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form select").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form textarea").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form.modal-form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    });

                } else {
                    $("form.modal-form input, form.modal-form select, form.modal-form textarea").removeClass('rtl');

                    $("form.modal-form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    });
                }
            });
        } else {
            // make input fields RTL
            $.get(mainURL + "/user/rtlcheck/" + $(this).val(), function (data) {
                $(".request-loader").removeClass("show");
                if (data == 1) {
                    $("form input").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form select").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form textarea").each(function () {
                        if (!$(this).hasClass('ltr')) {
                            $(this).addClass('rtl');
                        }
                    });
                    $("form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                    });

                } else {
                    $("form input, form select, form textarea").removeClass('rtl');
                    $("form .summernote").each(function () {
                        $(this).siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    });
                }
            });
        }

    });

    /******************************************************
==========Form Submit with AJAX Request Start Profile==========
******************************************************/



    // $("#submitBtnProfile").on('click', function (e) {
    //     $(e.target).attr('disabled', true);
    //     $(".request-loader").addClass("show");

    //     if ($(".iconpicker-component").length > 0) {
    //         $("#inputIcon").val($(".iconpicker-component").find('i').attr('class'));
    //     }

    //     let ajaxForm = document.getElementById('ajaxForm');
    //     let fd = new FormData(ajaxForm);
    //     let url = $("#ajaxForm").attr('action');
    //     let method = $("#ajaxForm").attr('method');

    //     $('.form-control').each(function (i) {
    //         let index = i;
    //         let $toInput = $('.form-control').eq(index);
    //         if ($(this).hasClass('summernote')) {
    //             let tmcId = $toInput.attr('id');
    //             let content = tinyMCE.get(tmcId).getContent();
    //             fd.delete($(this).attr('name'));
    //             fd.append($(this).attr('name'), content);
    //         }
    //     });

    //     let blob_image_url = $('#blob_image').text().trim();
    //     if (blob_image_url.length > 0) {
    //         var base64ImageContent = blob_image_url.replace(/^data:image\/(png|jpg);base64,/, "");
    //         var blob = base64ToBlob(base64ImageContent, 'image/png');
    //         fd.append('photo', blob);
    //     }

    //     $.ajax({
    //         url: url,
    //         method: method,
    //         data: fd,
    //         contentType: false,
    //         processData: false,
    //         success: function (data) {
    //             $(e.target).attr('disabled', false);
    //             $('.request-loader').removeClass('show');

    //             $('.em').each(function () {
    //                 $(this).html('');
    //             })

    //             if (data == 'success') {
    //                 location.reload();
    //             }

    //             if (data == "downgrade") {
    //                 $('.modal').modal('hide');
    //                 "use strict";
    //                 var content = {};
    //                 content.message = your_feature_limit_is_over_or_down_graded;
    //                 content.title = warning;
    //                 content.icon = 'fa fa-bell';
    //                 $.notify(content, {
    //                     type: 'warning',
    //                     placement: {
    //                         from: 'top',
    //                         align: 'right'
    //                     },
    //                     showProgressbar: true,
    //                     time: 1000,
    //                     delay: 4000,
    //                 });
    //                 $("#limitModal").modal('show');
    //             }

    //         },
    //         error: function (error) {
    //             $('.em').each(function () {
    //                 $(this).html('');
    //             });

    //             for (let x in error.responseJSON.errors) {
    //                 document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];
    //             }

    //             $('.request-loader').removeClass('show');
    //             $(e.target).attr('disabled', false);
    //         }
    //     });
    // });

    /******************************************************
==========Form Submit with AJAX Request Start Blog==========
******************************************************/
    $('#blogForm').on('submit', function (e) {
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

                if (data == 'success') {
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

    // custom page form
    $('#pageForm').on('submit', function (e) {

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

                if (data == 'success') {
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

                $('#pageErrors ul').html(errors);
                $('#pageErrors').show();

                $('.request-loader').removeClass('show');

                $('html, body').animate({
                    scrollTop: $('#pageErrors').offset().top - 100
                }, 1000);
            }
        });
    });
    // ad type
    $('#ad-type').on('change', function () {
        let adType = $(this).val();
        if (adType == 'banner') {
            if (!$('#slot-input').hasClass('d-none')) {
                $('#slot-input').addClass('d-none');
            }

            $('#image-input').removeClass('d-none');
            $('#url-input').removeClass('d-none');
        } else {
            if (!$('#image-input').hasClass('d-none') && !$('#url-input').hasClass('d-none')) {
                $('#image-input').addClass('d-none');
                $('#url-input').addClass('d-none');
            }

            $('#slot-input').removeClass('d-none');
        }
    });
    // edit type
    $('.edit-ad-type').on('change', function () {
        let adType = $(this).val();

        if (adType == 'banner') {
            if (!$('#edit-slot-input').hasClass('d-none')) {
                $('#edit-slot-input').addClass('d-none');
            }
            $('#edit-image-input').removeClass('d-none');
            $('#edit-url-input').removeClass('d-none');
        } else {
            if (!$('#edit-image-input').hasClass('d-none') && !$('#edit-url-input').hasClass('d-none')) {
                $('#edit-image-input').addClass('d-none');
                $('#edit-url-input').addClass('d-none');
            }
            $('#edit-slot-input').removeClass('d-none');
        }
    });
    /* ***************************************************
==========Form Prepopulate After Clicking Edit Button Start==========
******************************************************/
    $(".editbtn").on('click', function () {
        let datas = $(this).data();
        delete datas['toggle'];
        for (let x in datas) {
            if ($("#in" + x).hasClass('summernote')) {
                $("#in" + x).summernote('code', datas[x]);
            } else if ($("#in" + x).hasClass('image')) {
                $("#in" + x).attr('src', datas[x]);
            } else if ($("#in" + x).data('role') == 'tagsinput') {
                if (datas[x].length > 0) {
                    let arr = datas[x].split(" ");
                    for (let i = 0; i < arr.length; i++) {
                        $("#in" + x).tagsinput('add', arr[i]);
                    }
                } else {
                    $("#in" + x).tagsinput('removeAll');
                }
            } else if ($("#in_" + x).hasClass('select2')) {
                $("#in_" + x).val(datas[x]);
                $("#in_" + x).trigger('change');
            }
            else if ($("input[name='" + x + "']").attr('type') == 'radio') {
                $("input[name='" + x + "']").each(function (i) {
                    if ($(this).val() == datas[x]) {
                        $(this).prop('checked', true);
                    }
                });
            }
            else {
                $("#in" + x).val(datas[x]);
                if ($('#in_image').length > 0) {
                    $('#in_image').attr('src', datas['image']);
                }
                if ($('#in_icon').length > 0) {
                    $('#in_icon').attr('class', datas['icon']);
                }
            }
        }
        if ('edit' in datas && datas.edit === 'editAdvertisement') {
            if (datas.ad_type === 'banner') {
                if (!$('#edit-slot-input').hasClass('d-none')) {
                    $('#edit-slot-input').addClass('d-none');
                }

                $('#edit-image-input').removeClass('d-none');
                $('#edit-url-input').removeClass('d-none');
            } else {
                if (!$('#edit-image-input').hasClass('d-none') && !$('#edit-url-input').hasClass('d-none')) {
                    $('#edit-image-input').addClass('d-none');
                    $('#edit-url-input').addClass('d-none');
                }

                $('#edit-slot-input').removeClass('d-none');
            }
        }

        // focus & blur color picker inputs
        setTimeout(() => {
            $(".jscolor").each(function () {
                $(this).focus();
                $(this).blur();
            });
        }, 300);

    });
    $('#booking_no').on('keypress', function (e) {
        if (e.which == 13) {
            $(this).closest('form').submit();
        }
    })
    $('#keyword').on('keypress', function (e) {
        if (e.which == 13) {
            $(this).closest('form').submit();
        }
    })
});

/*======================= whatsapp template message script =======================*/

(function () {
    if ($.fn && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    function getActiveMessageField(triggerEl) {
        var activeModal = triggerEl ? triggerEl.closest('.modal') : null;

        if (activeModal) {
            var modalTextarea = activeModal.querySelector('textarea[name="message"]');
            if (modalTextarea) {
                return modalTextarea;
            }
        }

        var visibleModalTextarea = document.querySelector('.modal.show textarea[name="message"]');
        if (visibleModalTextarea) {
            return visibleModalTextarea;
        }

        return document.querySelector('textarea[name="message"]');
    }

    function insertAtCursor(myField, myValue) {
        if (!myField) return;
        if (document.selection) {
            myField.focus();
            var sel = document.selection.createRange();
            sel.text = myValue;
        } else if (myField.selectionStart || myField.selectionStart === 0) {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            var before = myField.value.substring(0, startPos);
            var after = myField.value.substring(endPos, myField.value.length);
            myField.value = before + myValue + after;
            myField.selectionStart = myField.selectionEnd = startPos + myValue.length;
            myField.focus();
        } else {
            myField.value += myValue;
            myField.focus();
        }
    }

    function fallbackCopyToClipboard(code, button) {
        var tmp = document.createElement('textarea');
        tmp.value = code;
        tmp.style.position = 'fixed';
        tmp.style.left = '-9999px';
        document.body.appendChild(tmp);
        tmp.focus();
        tmp.select();
        tmp.setSelectionRange(0, tmp.value.length);

        try {
            document.execCommand('copy');
            flashCopy(button);
        } catch (e) {
            /* ignore */
        }

        document.body.removeChild(tmp);
    }

    function copyToClipboard(code, button) {
        if (!code) return;
        if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(function () {
                flashCopy(button);
            }).catch(function () {
                fallbackCopyToClipboard(code, button);
            });
        } else {
            fallbackCopyToClipboard(code, button);
        }
    }

    function flashCopy(btn) {
        if (!btn) return;
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        setTimeout(function () {
            btn.innerHTML = original;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 900);
    }

    document.addEventListener('click', function (e) {
        var el = e.target;
        var btn = el.closest('button');

        // insert via code click or insert button
        if (el.classList.contains('bb-item') || (btn && btn.classList.contains('insert-code')) || el
            .classList.contains('insert-code')) {
            var ta = getActiveMessageField(el);
            var code = el.getAttribute('data-code') || (btn && btn.getAttribute('data-code')) || el
                .textContent.trim();
            e.preventDefault();
            insertAtCursor(ta, code);
            return;
        }

        // copy
        if (btn && btn.classList.contains('copy-code') || el.classList.contains('copy-code')) {
            var copyBtn = btn && btn.classList.contains('copy-code') ? btn : el;
            var code = copyBtn.getAttribute('data-code');
            e.preventDefault();
            copyToClipboard(code, copyBtn);
            return;
        }
    }, false);

    // init bootstrap tooltips if available
    if (typeof $ !== 'undefined' && $.fn && $.fn.tooltip) {
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    }
})();
