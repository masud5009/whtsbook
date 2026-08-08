@extends('user.layout')
@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/tenant/css/custom_dropzone.css') }}">
@endsection

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Category') }}</h4>
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
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit Category') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Update Category') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('tenant.rooms_management.categories', ['language' => 'en']) }}">
                        <span class="btn-label">
                            <i class="fas fa-backward" style="font-size: 12px;"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>

                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-10 offset-lg-1">
                            <div class="alert alert-danger pb-1" id="roomErrors" style="display: none;">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <ul></ul>
                            </div>

                            <div style="margin-left: 10px;">
                                <label for=""><strong>{{ __('Gallery') }} <span
                                            class="text-danger">**</span></strong></label>

                                @php $sliderImages = json_decode($room->slider_imgs); @endphp

                                @if (count($sliderImages) > 0)
                                    <div id="reload-slider-div">
                                        <div class="row mt-2">
                                            <div class="col">

                                                <table class="table table-striped" id="imgtable">
                                                    @foreach ($sliderImages as $key => $sliderImage)
                                                        @php
                                                            $ext = strtolower(
                                                                pathinfo($sliderImage, PATHINFO_EXTENSION),
                                                            );
                                                            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
                                                            $mediaPath = $isVideo
                                                                ? Constant::WEBSITE_ROOM_VIDEO . '/' . $sliderImage
                                                                : Constant::WEBSITE_ROOM_SLIDER_IMAGE .
                                                                    '/' .
                                                                    $sliderImage;
                                                        @endphp
                                                        <tr class="trdb" id="trdb{{ $key }}">
                                                            <td>
                                                                <div class="thumbnail">
                                                                    @if ($isVideo)
                                                                        <video class="width-150" controls>
                                                                            <source src="{{ asset($mediaPath) }}">
                                                                        </video>
                                                                    @else
                                                                        <img class="width-150"
                                                                            src="{{ asset($mediaPath) }}" alt="">
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-danger pull-right rmvbtndb"
                                                                    onclick="rmvStoredImg({{ $room->id }}, {{ $key }})">
                                                                    <i class="fa fa-times"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <form id="slider-dropzone" enctype="multipart/form-data" class="dropzone mt-2 mb-0">
                                    @csrf
                                    <div class="dz-message">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <br>
                                        {{ __('Drag and drop image and video files here to upload') }}
                                    </div>
                                    <div class="fallback"></div>
                                </form>
                                <p class="em text-info mb-0">
                                    <strong>{{ __('Recommended') . ':' }}</strong>{{ __('Upload 770X600 images and MP4/WebM/OGG videos for best quality.') }}
                                </p>
                            </div>

                            <form id="roomForm"
                                action="{{ route('tenant.rooms_management.update_category', ['id' => $room->id]) }}"
                                method="POST">
                                @csrf

                                <input type="hidden" name="seasonal_dates" id="seasonal_dates_input"
                                    value="{{ $room->seasonal_dates }}">
                                <input type="hidden" name="selected_days" id="selectedDaysInput"
                                    value="{{ $room->weekend }}">
                                <input type="hidden" name="selected_seasonal_days" id="selectedSeasonalDaysInput"
                                    value="{{ $room->seasonal_weekend }}">

                                <div id="slider-image-id"></div>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Status') }} <span class="text-danger">**</span></label>
                                            <select name="status" class="form-control">
                                                <option disabled selected>{{ __('Select a Status') }}</option>
                                                <option value="1" {{ $room->status == 1 ? 'selected' : '' }}>
                                                    {{ __('Show') }}
                                                </option>
                                                <option value="0" {{ $room->status == 0 ? 'selected' : '' }}>
                                                    {{ __('Hide') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Regular Price') }} ({{ $userBs->base_currency_text }}) <span
                                                    class="text-danger">**</span></label>
                                            <input type="number" step="0.01" class="form-control" name="regular_price"
                                                value="{{ $room->regular_price }}"
                                                placeholder="{{ __('Enter Regular Price') }}">
                                        </div>
                                    </div>

                                    <!-- Weekend Price -->
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Weekend Price') }} ({{ $userBs->base_currency_text }}) </label>
                                            <input type="number" step="0.01" class="form-control"
                                                value="{{ $room->weekend_price }}" name="weekend_price"
                                                placeholder="{{ __('Enter Weekend Price') }}">
                                            <a href="javascript:void(0)" data-toggle="modal" data-target="#setWeekend"
                                                data-value='@json($weekendArray)'>
                                                {{ __('Set Weekend Dates') }}
                                            </a>
                                            <div class="selected-dates-container mt-2 {{ is_null($room->weekend) ? 'd-none' : '' }}"
                                                id="selectedDatesContainer">
                                                <h6 class="mb-2"> {{ __('Selected Weekend Days') }}:</h6>
                                                <div id="selectedDatesList">
                                                    @foreach ($weekendArray as $day)
                                                        <span class="badge px-3 py-2 m-1 badge-info">
                                                            {{ $day }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Seasonal Price -->
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Seasonal Price') }} ({{ $userBs->base_currency_text }})</label>
                                            <input type="number" step="0.01" class="form-control"
                                                value="{{ $room->seasonal_price }}" name="seasonal_price"
                                                placeholder="{{ __('Enter Seasonal Price') }}">
                                            <a href="javascript:void(0)" data-toggle="modal" data-target="#setSeasonal">
                                                {{ __('Set Seasonal Dates') }}
                                            </a>
                                            <div class="selected-dates-container mt-2 {{ is_null($room->seasonal_dates) ? 'd-none' : '' }} "
                                                id="selectedSeasonalDatesContainer">
                                                <h6 class="mb-2"> {{ __('Selected Seasonal Dates') }}:
                                                    <span id="selectedSeasonalDatesList">
                                                        @if (!is_null($room->seasonal_dates))
                                                            @foreach ($seasonalDates as $key => $seasonal_date)
                                                                <span class="badge badge-info p-2 mb-1 mr-1">
                                                                    {{ $seasonal_date['start'] }} {{ __('to') }}
                                                                    {{ $seasonal_date['end'] }}
                                                                    <i class="fas fa-times ml-2 text-white"
                                                                        onclick="removeSeasonalRange({{ $key + 1 }})"
                                                                        title="Remove"></i>
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                    </span>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Seasonal Weekend Price -->
                                    <div
                                        class="col-lg-4 seasonal-weekend-price {{ is_null($room->seasonal_price) ? 'd-none' : '' }}">
                                        <div class="form-group">
                                            <label>{{ __('Seasonal Weekend Price') }} ({{ $userBs->base_currency_text }})
                                            </label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="seasonal_weekend_price" value="{{ $room->seasonal_weekend_price }}"
                                                placeholder="{{ __('Enter Seasonal Weekend Price') }}">
                                            <a href="javascript:void(0)" data-toggle="modal"
                                                data-target="#seasonalWeekendModal"
                                                data-value='@json($dayNames)'>
                                                {{ __('Set Weekend Days') }}
                                            </a>
                                            <div class="selected-dates-container mt-2 {{ empty($dayNames) ? 'd-none' : '' }}"
                                                id="selectedSeasonalWeekendDatesContainer">
                                                <h6 class="mb-2"> {{ __('Selected Seasonal Weekend Days') }}:</h6>
                                                <div id="selectedSeasonalWeekendDatesList">
                                                    @foreach ($dayNames as $day)
                                                        <span class="badge px-3 py-2 m-1 badge-info">
                                                            {{ $day }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Beds') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control" name="bed"
                                                placeholder="{{ __('Enter No. Of Bed') }}" value="{{ $room->bed }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Baths') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control" name="bath"
                                                placeholder="{{ __('Enter No. Of Bath') }}" value="{{ $room->bath }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Adult') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control" name="adult"
                                                placeholder="Enter Adult" value="{{ $room->adult }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Child') }}</label>
                                            <input type="number" class="form-control" name="child"
                                                placeholder="Enter Child" value="{{ $room->child }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{ __('Payment System') }} <span class="text-danger">**</span></label>
                                            <select name="payment_system" id="payment_system" class="form-control">
                                                <option selected disabled>{{ __('Select a Payment System') }}</option>
                                                <option value="full"
                                                    {{ $room->payment_system == 'full' ? 'selected' : '' }}>
                                                    {{ __('Full Payment') }}
                                                </option>
                                                <option value="advance"
                                                    {{ $room->payment_system == 'advance' ? 'selected' : '' }}>
                                                    {{ __('Advance Payment') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-4" id="amount_field" style="display: none;">
                                        <div class="form-group">
                                            <label>{{ __('Amount') }} ({{ __('in') }}
                                                {{ $userBs->base_currency_text }})
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="number" class="form-control" name="advance_amount"
                                                value="{{ $room->advance_amount }}"
                                                placeholder="{{ __('Enter amount') }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label class="form-label">
                                                {{ __('Room Details Link') }} <span class="text-danger">**</span>
                                            </label>

                                            <div class="selectgroup w-100">
                                                <label class="selectgroup-item">
                                                    <input type="radio" name="room_details_page" value="1"
                                                        class="selectgroup-input" @checked($room->room_details_page == 1)>
                                                    <span class="selectgroup-button">
                                                        {{ __('Use Default URL') }}
                                                    </span>
                                                </label>

                                                <label class="selectgroup-item">
                                                    <input type="radio" name="room_details_page" value="0"
                                                        class="selectgroup-input" @checked($room->room_details_page == 0)>
                                                    <span class="selectgroup-button">
                                                        {{ __('Use Custom URL') }}
                                                    </span>
                                                </label>
                                            </div>

                                            <small class="form-text text-info mt-1">
                                                {{ __("Choose how customers will view room details when they click the link sent via WhatsApp. You can use your website's default room page or provide a custom URL.") }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-lg-8" id="detailsLinkWrapper">
                                        <div class="form-group">
                                            <label>{{ __('Custom Details Link') }}</label>
                                            <input type="url" class="form-control" name="details_link"
                                                placeholder="{{ __('Enter your room details page URL') }}"
                                                value="{{ $room->details_link }}">

                                            <small class="form-text text-info">
                                                {{ __("If you select Use Custom URL, enter the link where customers can view room images and details. This link will be shared with customers on WhatsApp.") }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group {{ $defaultLang->rtl == 1 ? 'rtl text-right' : '' }}">
                                            @php
                                                $amenities = App\Models\User\RoomAmenity::active()
                                                    ->where('language_id', $defaultLang->id)
                                                    ->where('user_id', Auth::guard('web')->user()->id)
                                                    ->orderBy('serial_number', 'asc')
                                                    ->get();
                                            @endphp
                                            <label class="d-block mb-2"><strong>{{ __('Amenities') }} <span
                                                        class="text-danger">**</span></strong></label>
                                            <div class="amenities-container">
                                                @php
                                                    $roomAmenities = json_decode($room->amenities_index, true);
                                                @endphp
                                                @foreach ($amenities as $amenity)
                                                    <div class="amenity-wrapper">
                                                        <input type="checkbox" class="amenity-checkbox"
                                                            name="amenities[]" id="amenity_{{ $amenity->indx }}"
                                                            value="{{ $amenity->indx }}" @checked(in_array($amenity->indx, $roomAmenities))>
                                                        <label class="amenity-label" for="amenity_{{ $amenity->indx }}">
                                                            <span class="amenity-icon"><i class="fas fa-check"></i></span>
                                                            <span class="amenity-text">{{ $amenity->name }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="accordion" class="mt-5">
                                    @foreach ($languages as $language)
                                        @php
                                            $roomContent = $language
                                                ->roomDetails()
                                                ->where('room_id', $room->id)
                                                ->first();
                                            $title = !empty($roomContent) ? $roomContent->title : '';
                                            $categoryId = !empty($roomContent) ? $roomContent->room_category_id : '';
                                            $summary = !empty($roomContent) ? $roomContent->summary : '';
                                            $description = !empty($roomContent) ? $roomContent->description : '';
                                            $meta_keywords = !empty($roomContent) ? $roomContent->meta_keywords : '';
                                            $meta_description = !empty($roomContent)
                                                ? $roomContent->meta_description
                                                : '';
                                        @endphp

                                        <div class="version">
                                            <div class="version-header" id="heading{{ $language->id }}">
                                                <input type="hidden" value="{{ $language->id }}" name="lang">
                                                <h5 class="mb-0">
                                                    <button type="button" class="btn btn-link" data-toggle="collapse"
                                                        data-target="#collapse{{ $language->id }}"
                                                        aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}"
                                                        aria-controls="collapse{{ $language->id }}">
                                                        {{ $language->name . __(' Language') }}
                                                        {{ $language->is_default == 1 ? __('(Default)') : '' }}
                                                    </button>
                                                </h5>
                                            </div>

                                            <div id="collapse{{ $language->id }}"
                                                class="collapse {{ $language->is_default == 1 ? 'show' : '' }}"
                                                aria-labelledby="heading{{ $language->id }}" data-parent="#accordion">
                                                <div class="version-body">

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->rtl == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Title') }} <span
                                                                        class="text-danger">**</span></label>
                                                                <input type="text" class="form-control"
                                                                    name="{{ $language->code }}_title"
                                                                    placeholder="{{ __('Enter Title') }}"
                                                                    value="{{ !empty($roomContent->title) ? $roomContent->title : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->rtl == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Summary') }} <span
                                                                        class="text-danger">**</span></label>
                                                                <textarea class="form-control" name="{{ $language->code }}_summary" placeholder="{{ __('Enter Summary') }}"
                                                                    rows="3">{{ $summary }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->rtl == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Description') }} <span
                                                                        class="text-danger">**</span></label>
                                                                <textarea class="form-control summernote" name="{{ $language->code }}_description"
                                                                    placeholder="{{ __('Enter Description') }}" data-height="300" id="{{ $language->code }}RoomDesc">{{ replaceBaseUrl($description, 'summernote') }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->rtl == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Meta Keywords') }}</label>
                                                                <input class="form-control"
                                                                    name="{{ $language->code }}_meta_keywords"
                                                                    placeholder="{{ __('Enter Meta Keywords') }}"
                                                                    data-role="tagsinput" value="{{ $meta_keywords }}">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->rtl == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Meta Description') }}</label>
                                                                <textarea class="form-control" name="{{ $language->code }}_meta_description" rows="5"
                                                                    placeholder="{{ __('Enter Meta Description') }}">{{ $meta_description }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-12">
                                                            @php
                                                                $currLang = $language;
                                                            @endphp
                                                            @foreach ($languages as $language)
                                                                @continue($currLang->id == $language->id)

                                                                <div class="form-check py-0">
                                                                    <label class="form-check-label">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            value=""
                                                                            onchange="cloneContent('collapse{{ $currLang->id }}', 'collapse{{ $language->id }}', event)">
                                                                        <span
                                                                            class="form-check-sign">{{ __('Clone for') }}
                                                                            <strong
                                                                                class="text-capitalize text-secondary">{{ $language->name }}</strong>
                                                                            {{ __('Language') }}</span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" form="roomForm" class="btn btn-success">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @includeIf('user.rooms.category.set-weekend-date')
    @includeIf('user.rooms.category.set-sessional-date')
    @includeIf('user.rooms.category.set-seasonal-weekend')
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        "use strict"
        const uploadSliderImage = "{{ route('tenant.rooms_management.sliderImage') }}";
        const imgRmvUrl = "{{ route('tenant.rooms_management.remove_slider_image') }}";
        const imgDetachUrl = "{{ route('tenant.rooms_management.detach_slider_image') }}";
    </script>

    <script src="{{ asset('assets/tenant/js/rooms/dropzone-slider.js') }}"></script>
    <script src="{{ asset('assets/tenant/js/rooms/room.js') }}"></script>
    <script src="{{ asset('assets/tenant/js/rooms/seasonal-date-picker.js') }}"></script>
@endsection
