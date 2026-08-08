@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Today\'s Booked') }}</h4>

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
                <a href="#">{{ __('Rooms Bookings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Today\'s Booked') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title">
                                {{ __('Today\'s Booked') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pb-0">

                    @if ($bookedRoomNumbers->isEmpty())
                        <h3 class="text-center mt-2">{{ __('No Room Booked Yet') }}</h3>
                    @else
                        <div class="row">
                            @foreach ($bookedRoomNumbers as $room)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-3 col-xl-2">
                                    <div class="card rome-card">
                                        <div class="card-body">
                                            <div class="content">
                                                <h5 class="card-title text-dark">{{ $room->room_number }}</h5>
                                                <p class="card-text mb-0">{{ $room->room_name }}</p>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title">
                                {{ __('Today\'s Available Rooms') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body pb-0">

                    @if ($avaiableroomNumbers->isEmpty())
                        <h3 class="text-center mt-2">{{ __('No Room Booked Yet') }}</h3>
                    @else
                        <div class="row">
                            @foreach ($avaiableroomNumbers as $room)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-3 col-xl-2">
                                    <div class="card rome-card">
                                        <div class="card-body">
                                            <div class="content">
                                                <h5 class="card-title">{{ $room->room_number }}</h5>
                                                <p class="card-text mb-0">{{ $room->room_name }}</p>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var currency = "{{ $currencyInfo->base_currency_text }}";
    </script>
    <script type="text/javascript" src="{{ asset('assets/tenant/js/admin-room.js') }}"></script>
@endsection
