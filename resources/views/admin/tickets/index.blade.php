@extends('admin.layout')

@section('content')
    @php
        $staffs = App\Models\Admin::select('id', 'username')->get();
    @endphp
    <div class="page-header">
        <h4 class="page-title">
            @if (request()->path() == 'admin/all/tickets')
                {{ __('All') }}
            @elseif (request()->path() == 'admin/pending/tickets')
                {{ __('Pending') }}
            @elseif (request()->path() == 'admin/open/tickets')
                {{ __('Open') }}
            @elseif (request()->path() == 'admin/closed/tickets')
                {{ __('Closed') }}
            @endif
            {{ __('Tickets') }}
        </h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
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
                <a href="#">
                    @if (request()->path() == 'admin/all/tickets')
                        {{ __('All') }}
                    @elseif (request()->path() == 'admin/pending/tickets')
                        {{ __('Pending') }}
                    @elseif (request()->path() == 'admin/open/tickets')
                        {{ __('Open') }}
                    @elseif (request()->path() == 'admin/closed/tickets')
                        {{ __('Closed') }}
                    @endif
                    {{ __('Tickets') }}
                </a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-9 mb-3 mb-lg-0">
                            <div class="card-title d-inline-block">
                                @if (request()->path() == 'admin/all/tickets')
                                    {{ __('All') }}
                                @elseif (request()->path() == 'admin/pending/tickets')
                                    {{ __('Pending') }}
                                @elseif (request()->path() == 'admin/open/tickets')
                                    {{ __('Open') }}
                                @elseif (request()->path() == 'admin/closed/tickets')
                                    {{ __('Closed') }}
                                @endif
                                {{ __('Tickets') }}
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <form action="{{ url()->current() }}" method="GET">
                                <input class="form-control" type="text" name="search"
                                    value="{{ request()->input('search') ? request()->input('search') : '' }}"
                                    placeholder="{{ __('Enter ticket number / subject to seach') }}">
                            </form>
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
                                                <th scope="col">{{ __('Username') }}</th>
                                                <th scope="col">{{ __('Email') }}</th>
                                                <th scope="col">{{ __('Subject') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($tickets as $ticket)
                                                <tr>
                                                    <td>
                                                        #{{ $ticket->ticket_number }}
                                                    </td>

                                                    <td>
                                                        <a class="badge badge-primary"
                                                            href="{{ route('register.user.view', $ticket->id) }}">{{ $ticket?->user?->username }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $ticket?->user?->email }}</td>
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

                                                        <div class="btn-group">
                                                            <button type="button"
                                                                class="btn btn-info dropdown-toggle btn btn-sm"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                {{ __('Actions') }}
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.ticket.messages', $ticket->id) }}">{{ __('Messages') }}</a>
                                                            </div>
                                                        </div>
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

    <!-- Create Ticket Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Assign Staff') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxForm" class="modal-form" action="{{ route('ticket.assign.staff') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ticket_id" class="ticket_id_get" value="">
                        <div class="form-group">
                            <label for="">{{ __('Staff') }} <span class="text-danger">**</span></label>
                            <select id="staff" name="staff" class="form-control">
                                <option value="1" selected disabled>{{ __('Select Staff') }}</option>
                                @foreach ($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->username }}</option>
                                @endforeach
                            </select>
                            <p id="err_staff" class="mb-0 text-danger em"></p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                    <button id="submitBtn" type="button" class="btn btn-primary">{{ __('Assign') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
 <script src="{{ asset('assets/admin/js/ticket.js') }}"></script>
@endsection
