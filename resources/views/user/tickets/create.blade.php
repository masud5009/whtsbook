@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Ticket Create') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Support Ticket') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('tenant.tickets') }}">{{ __('My Tickets') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Ticket Create') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <a class="btn btn-info btn-sm float-right d-inline-block " href="{{ route('tenant.tickets') }}">
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="account-info">
                        <div class="reply-section">
                            <form action="{{ route('tenant.ticket.store') }}" method="POST" class="reply-form"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="email">{{ __('Email') }} <span class="text-danger">**</span></label>
                                    <div class="input-group">
                                        <input type="email" class="form-control"
                                            value="{{ Auth::guard('web')->user()->email }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="subject">{{ __('Subject') }} <span class="text-danger">**</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="subject"
                                            value="{{ old('subject') }}" name="subject">
                                    </div>
                                    @error('subject')
                                        <p class="text-danger mb-2">{{ convertUtf8($message) }}</p>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">{{ __('Message') }} <span
                                            class="text-danger">**</span></label>
                                    <div class="input-group">
                                        <textarea name="message" id="message" class="summernote" data-height="220">{{ old('message') }}</textarea>
                                    </div>
                                    @error('message')
                                        <p class="text-danger mb-2">{{ convertUtf8($message) }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Attachment') }}</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input"
                                                data-href="{{ route('admin.zip.upload') }}" name="zip_file" id="zip_file">
                                            <label class="custom-file-label" for="zip_file">{{ __('Choose file') }}</label>
                                        </div>
                                    </div>
                                    <p class="em text-danger mb-0" id="errfile"></p>
                                    <p class="mb-0 show-name"><small></small></p>
                                    <div class="progress progress-md d-none mt-2">
                                        <div class="progress-bar bg-success " role="progressbar" aria-valuenow=""
                                            aria-valuemin="0" aria-valuemax=""></div>
                                    </div>
                                    <p class="text-warning">{{ __('Upload only ZIP Files, Max File Size is 5 MB') }}</p>
                                    @error('zip_file')
                                        <p class="text-danger mb-2">{{ convertUtf8($message) }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <div class="form-element">
                                        <button type="submit" class="btn btn-success btn-inline"><i
                                                class="fas fa-retweet"></i>
                                            {{ __('Submit') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).on('change', '#zip_file', function() {
            var formdata = new FormData();
            var file = event.target.files[0];
            var name = event.target.files[0].name;
            formdata.append('file', file);
            $.ajax({
                url: $(this).attr('data-href'),
                type: 'post',
                data: formdata,
                xhr: function() {
                    var appXhr = $.ajaxSettings.xhr();
                    if (appXhr.upload) {
                        if ('#zip_file') {
                            appXhr.upload.addEventListener('progress', function(e) {
                                if (e.lengthComputable) {
                                    currentMainProgress = (e.loaded / e.total) * 100;
                                    currentMainProgress = parseInt(currentMainProgress);
                                    $(".progress").removeClass('d-none');
                                    $(".progress-bar").html(currentMainProgress + '%');
                                    $(".progress-bar").width(currentMainProgress + '%');
                                    if (currentMainProgress == 100)
                                        $(".progress-bar").addClass('bg-success');
                                }
                                $('show-name').text(name);
                            }, false);
                        }
                    }
                    return appXhr;
                },
                success: function(data) {
                    if (data.errors) {
                        $(".progress").addClass('d-none');
                        $('.file-error').text(data.errors.file[0]).removeClass('d-none');
                    } else {
                        $('.file-error').text('').addClass('d-none');
                    }
                },
                contentType: false,
                processData: false
            });

        });
    </script>
    <script src="{{ asset('assets/tenant/js/ticket.js') }}"></script>
@endsection
