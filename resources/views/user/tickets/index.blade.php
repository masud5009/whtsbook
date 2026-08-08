@extends('user.layout')

@section('content')

    <div class="page-header">
        <h4 class="page-title">{{ __('My Tickets') }}</h4>
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
                <a href="#">{{ __('Support Tickets') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('My Tickets') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-5">
                            <div class="card-title mb-0">{{ __('All Tickets') }}</div>
                        </div>
                        <div class="col-lg-8 col-md-7 mt-2 mt-md-0">
                            <div class="d-flex flex-column flex-md-row justify-content-md-end align-items-md-center">
                                <form action="{{ url()->current() }}" method="GET" class="mb-2 mb-md-0 mr-md-2">
                                    <input class="form-control" type="text" name="search"
                                        value="{{ request()->input('search') ? request()->input('search') : '' }}"
                                        placeholder="{{ __('Enter Ticket Number') }}">
                                </form>
                                <button class="btn btn-primary btn-sm" type="button" data-toggle="modal"
                                    data-target="#createTicketModal">
                                    <i class="fas fa-plus mr-2"></i> {{ __('Create Ticket') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">

                            @if ($tickets->count() == 0)
                                <h3 class="text-center">{{ __('NO TICKET FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Ticket Number') }}</th>
                                                <th scope="col">{{ __('Subject') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Message') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tickets as $ticket)
                                                <tr>
                                                    <td>
                                                        #{{ $ticket->ticket_number }}
                                                    </td>
                                                    <td>{{ $ticket->subject }}</td>
                                                    <td>
                                                        @if ($ticket->status == 'pending')
                                                            <span class="badge badge-warning">{{ __('Pending') }}</span>
                                                        @elseif($ticket->status == 'open')
                                                            <span class="badge badge-primary">{{ __('Open') }}</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ __('Closed') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('tenant.ticket.messages', $ticket->ticket_number) }}"
                                                            class="btn btn-primary   border-0 btn-sm">
                                                            <i class="fas fa-comments"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $tickets->appends(['search' => request()->input('search')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createTicketModal" tabindex="-1" role="dialog" aria-labelledby="createTicketModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTicketModalLabel">{{ __('Create Ticket') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tenant.ticket.store') }}" method="POST" id="ajaxForm"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label>{{ __('Email') }}</label>
                            <input type="email" class="form-control"
                                value="{{ Auth::guard('web')->user()->email }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="ticket_subject">{{ __('Subject') }} <span class="text-danger">**</span></label>
                            <input type="text" class="form-control" id="ticket_subject" name="subject"
                                value="{{ old('subject') }}" required>
                            <p class="em text-danger mb-0" id="err_subject"></p>
                        </div>

                        <div class="form-group">
                            <label for="ticket_message">{{ __('Message') }} <span class="text-danger">**</span></label>
                            <textarea name="message" id="ticket_message" class="summernote form-control" data-height="220" required>{{ old('message') }}</textarea>
                            <p class="em text-danger mb-0" id="err_message"></p>
                        </div>

                        <div class="form-group">
                            <label for="ticket_zip_file">{{ __('Attachment') }}</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input form-control" name="zip_file"
                                    id="ticket_zip_file" accept=".zip">
                                <label class="custom-file-label" for="ticket_zip_file">{{ __('Choose file') }}</label>
                            </div>
                            <p class="text-warning mb-0 mt-1">{{ __('Upload only ZIP Files, Max File Size is 5 MB') }}</p>
                            <p class="em text-danger mb-0" id="err_zip_file"></p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-success" id="submitBtn">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>


@endsection
@section('script')
    <script>
        $(document).on('change', '#ticket_zip_file', function() {
            const fileName = this.files && this.files.length > 0 ? this.files[0].name : "{{ __('Choose file') }}";
            $(this).next('.custom-file-label').text(fileName);
        });

        $('#createTicketModal').on('hidden.bs.modal', function() {
            const form = document.getElementById('ajaxForm');
            if (form) {
                form.reset();
            }

            $('.em').html('');
            $('#ticket_zip_file').next('.custom-file-label').text("{{ __('Choose file') }}");
            $('.form-control').removeClass('valid-field invalid-field');

            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('ticket_message')) {
                tinyMCE.get('ticket_message').setContent('');
            }
        });
    </script>
    <script src="{{ asset('assets/tenant/js/ticket.js') }}"></script>
@endsection
