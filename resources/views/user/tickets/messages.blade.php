@extends('user.layout')
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Support Ticket') }}</h4>
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
                <a href="#">{{ __('Ticket Details') }}</a>
            </li>
        </ul>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('Ticket Details') }} - #{{ $ticket->id }}</div>
                        </div>
                        <div class="col-lg-3 offset-lg-5 mt-2 mt-lg-0 text-right">
                            <a href="{{ route('tenant.tickets') }}" class="btn btn-sm btn-info btn-md">
                                <span class="btn-label">
                                    <i class="fas fa-backward"></i> {{ __('Back') }}
                                </span></a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h3 class="text-white">{{ convertUtf8($ticket->subject) }}</h3>
                                </div>
                                <div class="col-lg-12 ">
                                    @if ($ticket->status == 'pending')
                                        <h6 class="d-inline-block badge badge-warning">{{ convertUtf8($ticket->status) }}
                                        </h6>
                                    @elseif($ticket->status == 'open')
                                        <h6 class="d-inline-block badge badge-primary">{{ convertUtf8($ticket->status) }}
                                        </h6>
                                    @else
                                        <h6 class="d-inline-block badge badge-success">{{ convertUtf8($ticket->status) }}
                                        </h6>
                                    @endif
                                    <h6><span class="badge badge-light">{{ $ticket->created_at->format('d-m-Y') }}
                                            {{ date('h.i A', strtotime($ticket->created_at)) }}</span></h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-8 mx-auto">

                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row text-center">
                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col-lg-8 mx-auto">
                                                                <div class="summernote-content">
                                                                    {!! replaceBaseUrl(convertUtf8($ticket->message)) !!}
                                                                </div>
                                                                @if ($ticket->zip_file)
                                                                    <a href="{{ asset('assets/front/user-suppor-file/' . $ticket->zip_file) }}"
                                                                        download="support.zip"
                                                                        class="btn btn-primary btn-sm"><i
                                                                            class="fas fa-download"></i>
                                                                        {{ __('Download Attachment') }}</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-round">
                <div class="card-body">
                    <div class="card-title fw-mediumbold">{{ __('Replies') }}</div>
                    <div class="card-list">
                        @if (count($ticket->messages) > 0)
                            <div class="messages-container">
                                @foreach ($ticket->messages as $reply)
                                    @if (!$reply->user_id)
                                        @php
                                            $admin = App\Models\Admin::find($ticket->admin_id);
                                        @endphp
                                        <div class="item-list">
                                            <div class="avatar">
                                                <img src="{{ asset('assets/admin/img/propics/' . $admin->image) }}"
                                                    alt="..." class="avatar-img rounded-circle">
                                            </div>
                                            <div class="info-user ml-3">
                                                <div class="username">{{ convertUtf8($admin->username) }}</div>
                                                <div class="status">
                                                    {{ $admin->id == 1 ? 'Super Admin' : convertUtf8($admin->name) }}</div>
                                                {{ $reply->created_at->format('d-m-Y') }}
                                                {{ date('h.i A', strtotime($reply->created_at)) }}

                                                <div class="item-list">
                                                    <div class="info-user ml-3">
                                                        <div class="summernote-content">
                                                            {!! replaceBaseUrl(convertUtf8($reply->reply)) !!}
                                                        </div>
                                                        @if ($reply->file)
                                                            <a href="{{ asset('assets/front/user-suppor-file/' . $reply->file) }}"
                                                                download="support.zip" class="reply-download-btn"><i
                                                                    class="fas fa-download"></i>
                                                                {{ __('Download') }}</a>
                                                        @endif
                                                    </div>

                                                </div>
                                                <p></p>
                                            </div>

                                        </div>
                                    @else
                                        @php
                                            $user = Auth::guard('web')->user();
                                        @endphp
                                        <div class="item-list">
                                            <div class="avatar">
                                                <img class="avatar-img rounded-circle"
                                                    src="{{ $user->photo ? asset('assets/tenant/img/users/' . $user->photo) : asset('assets/avater.png') }}"
                                                    alt="user-photo">
                                            </div>
                                            <div class="info-user ml-3">
                                                <div class="username">{{ convertUtf8($user->username) }}</div>
                                                <div class="status">{{ __('Customer') }}</div>
                                                <p>{{ $reply->created_at->format('d-m-Y') }}
                                                    {{ date('h.i A', strtotime($reply->created_at)) }}</p>
                                                @if ($reply->file)
                                                    <a href="{{ asset('assets/front/user-suppor-file/' . $reply->file) }}"
                                                        download="support.zip" class="reply-download-btn"><i
                                                            class="fas fa-download"></i> {{ __('Download') }}</a>
                                                @endif
                                                <div class="summernote-content">
                                                    <p><strong>{!! replaceBaseUrl(convertUtf8($reply->reply)) !!}</strong></p>
                                                </div>
                                                <p></p>
                                            </div>

                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            {{ __('NO Conversations') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="summary">
                        <div class="subject">
                            <h3>{{ __('Reply to Ticket') }}</h3>
                        </div>
                    </div>

                    @if ($ticket->status != 'close')
                        <div class="reply-section py-2">
                            <form action="{{ route('tenant.ticket.reply', $ticket->id) }}" method="POST"
                                enctype="multipart/form-data" class="reply-form">
                                @csrf
                                <label for="reply" class="py-2">{{ __('Reply') }} <span
                                        class="text-danger">**</span></label>
                                <div class="form-element">
                                    <textarea name="reply" class="summernote" id="reply" data-height="220"></textarea>
                                    @error('reply')
                                        <p class="text-danger mb-2">{!! convertUtf8($message) !!}</p>
                                    @enderror
                                </div>
                                <label for="" class="py-2">{{ __('Attachment') }}</label>
                                <div class="form-element">
                                    <div class="input-group mb-3">
                                        <div class="custom-file">
                                            <input type="file" data-href="{{ route('zip.upload') }}"
                                                class="custom-file-input" name="file" id="zip_file">
                                            <label class="custom-file-label"
                                                for="zip_file">{{ __('Choose file') }}</label>
                                        </div>

                                    </div>
                                    <div class="progress mt-3 d-none">
                                        <div class="progress-bar " role="progressbar" aria-valuenow="25"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small
                                        class="show-name">{{ __('Upload only ZIP Files, Max File Size is 5 MB') }}</small>
                                    @error('file')
                                        <p class="text-danger mb-2">{!! convertUtf8($message) !!}</p>
                                    @enderror
                                    <p class="text-danger mb-2 file-error d-none"></p>
                                </div>
                                <div class="form-element">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-retweet"></i> {{ __('Reply') }}</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- footer section start -->
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
