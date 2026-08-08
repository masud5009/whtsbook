@extends('user.layout')

{{-- this style will be applied when the direction of language is right-to-left --}}
@includeIf('backend.partials.rtl_style')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Amenities') }}</h4>
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
                <a href="#">{{ __('Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Amenities') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Amenities') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('tenant.rooms_management.amenities') . '?language=' . request()->input('language') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <div class="alert alert-danger pb-1 dis-none" id="blogErrors">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <ul></ul>
                            </div>
                            <form id="amenitiesForm" action="{{ route('tenant.rooms_management.update_amenity') }}"
                                method="post" class="ltr">
                                @csrf
                                <input type="hidden" name="amenity_indx" value="{{ $data->indx }}">
                                @foreach ($languages as $language)
                                    @php
                                        $amenity = App\Models\User\RoomAmenity::where('indx', $data->indx)
                                            ->where('language_id', $language->id)
                                            ->first();
                                    @endphp
                                    <div class="form-group">
                                        <label for=""
                                            class="d-flex">{{ __('Name') }}
                                            ({{ $language->name }})
                                            @if ($language->is_default == 1)
                                                <span class="text-danger">**</span>
                                            @endif
                                        </label>
                                        <input type="text" value="{{ $amenity?->name }}"
                                            class="form-control {{ $language->rtl == 1 ? 'text-right rtl' : '' }}"
                                            name="{{ $language->code }}_name" placeholder="{{ __('Enter Name') }}">

                                    </div>
                                @endforeach

                                <div class="form-group">
                                    <label for="">{{ __('Status') }}<span class="text-danger">**</span></label>
                                    <select name="status" id="in_status" class="form-control">
                                        <option disabled>{{ __('Select a Status') }}</option>
                                        <option value="1" {{ $data->status == 1 ? 'selected' : '' }}>
                                            {{ __('Active') }}
                                        </option>
                                        <option value="0" {{ $data->status == 0 ? 'selected' : '' }}>
                                            {{ __('Deactive') }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Serial Number') }} <span
                                            class="text-danger">**</span></label>
                                    <input type="number" id="in_serial_number" class="form-control ltr"
                                        value="{{ $data->serial_number }}" name="serial_number"
                                        placeholder="{{ __('Enter Serial Number') }}">
                                    <p class="text-warning mt-2">
                                        <small>{{ __('The higher the serial number, the later it will be displayed.') }}</small>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" form="amenitiesForm"
                                        class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
