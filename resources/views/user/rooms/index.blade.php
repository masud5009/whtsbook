@extends('user.layout')
@includeIf('user.partials.rtl-style')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Rooms') }}</h4>
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
                <a href="#">{{ __('Rooms') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-lg-2 col-md-6">
                            <div class="card-title d-inline-block">{{ __('Rooms') }}</div>
                        </div>
                        <div class="col-lg-7 col-md-6">
                            <form action="{{ route('tenant.rooms_management.rooms') }}" method="GET">
                                <div class="form-row">
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <select class="form-control" id="language_id" name="language"
                                            onchange="this.form.submit()">
                                            @foreach ($langs as $lang)
                                                <option value="{{ $lang->code }}"
                                                    @selected(($selectedLanguageCode ?? request()->input('language')) == $lang->code)>
                                                    {{ $lang->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <input type="text" class="form-control" id="room_number" name="room_number"
                                            value="{{ request()->input('room_number') }}"
                                            placeholder="{{ __('Search by room number/name') }}"
                                            onkeydown="if (event.key === 'Enter') { this.form.submit(); }"
                                            onchange="this.form.submit()">
                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <select class="form-control" id="room_category_id" name="room_category_id"
                                            onchange="this.form.submit()">
                                            <option value="">{{ __('All Categories') }}</option>
                                            @foreach ($roomCategories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ (string) request()->input('room_category_id') === (string) $category->id ? 'selected' : '' }}>
                                                    {{ $category->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <select class="form-control" id="status" name="status"
                                            onchange="this.form.submit()">
                                            <option value="">{{ __('All') }}</option>
                                            <option value="1"
                                                {{ request()->input('status') === '1' ? 'selected' : '' }}>
                                                {{ __('Active') }}</option>
                                            <option value="0"
                                                {{ request()->input('status') === '0' ? 'selected' : '' }}>
                                                {{ __('Deactive') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-3 col-md-6 text-lg-right text-left">
                            <a href="#" data-toggle="modal" data-target="#createModal" class="btn btn-primary"><i
                                    class="fas fa-plus"></i>
                                {{ __('Add Room') }}</a>

                            <button class="btn btn-danger mr-2 d-none bulk-delete"
                                data-href="{{ route('tenant.rooms_management.room.bulk_delete') }}"><i
                                    class="flaticon-interface-5"></i>
                                {{ __('Delete') }}</button>
                        </div>
                    </div>


                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($rooms) == 0)
                                <h3 class="text-center">{{ __('NO ROOM FOUND!') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Room Number/Name') }}</th>
                                                <th scope="col">{{ __('Category') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rooms as $room)
                                                @php
                                                    $roomNumberContent = $room->contents->firstWhere('language_id', $selectedLanguageId);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $room->id }}">
                                                    </td>
                                                    <td>{{ $roomNumberContent->name ?? 'N/A' }}</td>
                                                    <td>{{ $room->categoryContents->first()->title ?? 'N/A' }}</td>
                                                    <td>
                                                        @if ($room->status)
                                                            <span class="badge badge-success">{{ __('Active') }}</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ __('Deactive') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm mr-1 editBtn" href="#"
                                                            data-toggle="modal" data-target="#editModal"
                                                            data-id="{{ $room->id }}"
                                                            data-room_category_id="{{ $room->room_category_id }}"
                                                            data-status="{{ $room->status }}"
                                                            @foreach ($langs as $lang)
                                                                data-room_number_{{ $lang->code }}="{{ $room->contents->firstWhere('language_id', $lang->id)->name ?? '' }}"
                                                            @endforeach>
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                            {{ __('Edit') }}
                                                        </a>

                                                        <form class="deleteForm d-inline-block"
                                                            action="{{ route('tenant.rooms_management.room.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="room_id"
                                                                value="{{ $room->id }}">

                                                            <button type="submit" class="btn btn-danger btn-sm deleteBtn">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-trash"></i>
                                                                </span>
                                                                {{ __('Delete') }}
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

                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $rooms->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- create modal --}}
    @include('user.rooms.create')

    {{-- edit modal --}}
    @include('user.rooms.edit')
@endsection
@section('script')
    <script>
        let getLangwiseRoomCategoryUrl = "{{ route('tenant.rooms_management.get_langwise_room_category') }}";
    </script>
    <script src="{{ asset('assets/tenant/js/rooms/room.js') }}"></script>
@endsection
