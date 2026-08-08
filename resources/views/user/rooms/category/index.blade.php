@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Categories') }}</h4>
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
                <a href="#">{{ __('Rooms Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Categories') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('Categories') }}</div>
                        </div>
                        <div class="col-lg-4">
              @includeIf('user.partials.languages')
            </div>
                        <div class="col-lg-4  mt-2 mt-lg-0">
                            <a href="{{ route('tenant.rooms_management.create_category') }}"
                                class="btn btn-primary  float-right"><i class="fas fa-plus"></i>
                                {{ __('Add Category') }}</a>

                            <button class="btn btn-danger  float-right mr-2 d-none bulk-delete"
                                data-href="{{ route('tenant.rooms_management.bulk_delete_category') }}"><i
                                    class="flaticon-interface-5"></i>
                                {{ __('Delete') }}</button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($roomContents) == 0)
                                <h3 class="text-center">{{ __('NO ROOM FOUND') . '!' }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Regular Price') }}</th>
                                                <th scope="col">{{ __('Weekend Price') }}</th>
                                                <th scope="col">{{ __('Seasonal Price') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($roomContents as $roomContent)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $roomContent->room_id }}">
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('front.room.details', ['userId' => $roomContent->room->user_id, 'slug' => $roomContent->slug]) }}"
                                                            target="_blank">

                                                            {{ truncateString($roomContent->title, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        {{ userPriceFormat($roomContent->room->user_id, $roomContent->room->regular_price) }}
                                                    </td>
                                                    <td>
                                                        @if ($roomContent->room->weekend_price > 0)
                                                            {{ userPriceFormat($roomContent->room->user_id, $roomContent->room->weekend_price) }}
                                                        @else
                                                            {{ __('N/A') }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($roomContent->room->seasonal_price > 0)
                                                            {{ userPriceFormat($roomContent->room->user_id, $roomContent->room->seasonal_price) }}
                                                        @else
                                                            {{ __('N/A') }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($roomContent->room->status == 1)
                                                            <span class="badge badge-success">{{ __('Active') }}</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ __('Inactive') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm mr-1 mb-1"
                                                            href="{{ route('tenant.rooms_management.edit_category', $roomContent->room_id) }}">
                                                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                                                        </a>
                                                        <form class="deleteForm d-inline-block"
                                                            action="{{ route('tenant.rooms_management.delete_category') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="room_id"
                                                                value="{{ $roomContent->room_id }}">

                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm deleteBtn mb-1">
                                                                <i class="fas fa-trash"></i> {{ __('Delete') }}
                                                            </button>
                                                        </form>
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
            </div>
        </div>
    </div>
@endsection
