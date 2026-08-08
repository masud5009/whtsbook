@extends('admin.layout')

@php
    use App\Models\Language;
    $setLang = Language::where('code', request()->input('language'))->first();
@endphp
@if (!empty($setLang) && $setLang->rtl == 1)
    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit package') }}</h4>
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
                <a href="#">{{ __('Package Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit package') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block" href="{{ route('admin.package.index') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>

                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-8 m-auto">
                            <form id="ajaxForm" action="{{ route('admin.package.update') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">

                                {{-- Row: Title + Price --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="title">{{ __('Package Title') }} <span
                                                class="text-danger">**</span></label>
                                        <input id="title" type="text" class="form-control" name="title"
                                            value="{{ $package->title }}" placeholder="{{ __('Enter Title') }}">
                                        <p id="err_title" class="mb-0 text-danger em"></p>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="price">{{ __('Price') }} ({{ $bex->base_currency_text }}) <span
                                                class="text-danger">**</span></label>
                                        <input id="price" type="number" class="form-control ltr" name="price"
                                            placeholder="{{ __('Enter Package Price') }}" value="{{ $package->price }}">
                                        <p id="err_price" class="mb-0 text-danger em"></p>
                                        <small class="text-warning d-block mt-1">
                                            {{ __('If price is 0 , than it will appear as free') }}
                                        </small>
                                    </div>
                                </div>

                                {{-- Row: Term + Status (keep only one status field on page) --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="plan_term">{{ __('Package term') }} <span
                                                class="text-danger">**</span></label>
                                        <select id="plan_term" name="term" class="form-control">
                                            <option value="" selected disabled>{{ __('Select a Term') }}</option>
                                            <option value="monthly" {{ $package->term == 'monthly' ? 'selected' : '' }}>
                                                {{ __('monthly') }}
                                            </option>
                                            <option value="yearly" {{ $package->term == 'yearly' ? 'selected' : '' }}>
                                                {{ __('yearly') }}
                                            </option>
                                            <option value="lifetime" {{ $package->term == 'lifetime' ? 'selected' : '' }}>
                                                {{ __('lifetime') }}
                                            </option>
                                        </select>
                                        <p id="err_term" class="mb-0 text-danger em"></p>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="status">{{ __('Status') }} <span
                                                class="text-danger">**</span></label>
                                        <select id="status" class="form-control ltr" name="status">
                                            <option value="" selected disabled>{{ __('Select a status') }}</option>
                                            <option value="1" {{ $package->status == '1' ? 'selected' : '' }}>
                                                {{ __('Active') }}
                                            </option>
                                            <option value="0" {{ $package->status == '0' ? 'selected' : '' }}>
                                                {{ __('Deactive') }}
                                            </option>
                                        </select>
                                        <p id="err_status" class="mb-0 text-danger em"></p>
                                    </div>
                                </div>

                                @php
                                    $permissions = $package->features;
                                    if (!empty($package->features)) {
                                        $permissions = json_decode($permissions, true);
                                    } else {
                                        $permissions = [];
                                    }
                                @endphp

                                {{-- Full: Features --}}
                                <div class="form-group">
                                    <label class="form-label">{{ __('Package Features') }}</label>
                                    <div class="selectgroup selectgroup-pills">
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="features[]" value="QR Builder"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('QR Builder', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('QR Builder') }}</span>
                                        </label>

                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="features[]" value="Support Ticket"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Support Ticket', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Support Ticket') }}</span>
                                        </label>
                                    </div>
                                    <p id="err_features" class="mb-0 text-danger em"></p>
                                </div>

                                {{-- Full: AI Token --}}
                                <div class="form-group">
                                    <label for="total_ai_token">{{ __('AI Credit Limit') }} <span
                                            class="text-danger">**</span></label>

                                    <input id="total_ai_token" type="number" class="form-control ltr"
                                        name="total_ai_token" placeholder="{{ __('Example: 500000') }}" min="100000"
                                        step="10000" value="{{ $package->total_ai_token }}">

                                    <p id="err_total_ai_token" class="mb-0 text-danger em"></p>

                                    <small class="text-info d-block mt-1">
                                        {{ __('Set the maximum number of AI credit available per billing cycle for this package. This limit is consumed by automated replies, room suggestions, booking flow, and booking summary generation. When the limit is reached, AI responses will pause until the next renewal.') }}
                                    </small>
                                </div>

                                {{-- Row: Language + Room Categories --}}
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="whatsapp_limit">
                                            {{ __('WhatsApp Business Number Limit') }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input id="whatsapp_limit" type="number" class="form-control"
                                            name="whatsapp_limit" placeholder="{{ __('Enter number limit') }}"
                                            min="1" value="{{ $package->whatsapp_limit }}">

                                        <p id="err_whatsapp_limit" class="mb-0 text-danger em"></p>

                                        <small class="text-info d-block mt-1">
                                            {{ __('Maximum number of WhatsApp Business numbers a tenant can add.') }}
                                        </small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="language_limit">{{ __('Number Of Language Limit') }} <span
                                                class="text-danger">**</span></label>
                                        <input id="language_limit" type="number" class="form-control ltr"
                                            name="language_limit" placeholder="{{ __('Enter language Limit') }}"
                                            value="{{ $package->language_limit }}">
                                        <p id="err_language_limit" class="mb-0 text-danger em"></p>
                                        <small class="text-warning d-block mt-1">
                                            {{ __('Enter 999999 , than it will appear as unlimited') }}
                                        </small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="room_categories_limit">{{ __('Number Of Room Categories Limit') }}
                                            <span class="text-danger">**</span></label>
                                        <input id="room_categories_limit" type="number" class="form-control ltr"
                                            name="room_categories_limit"
                                            placeholder="{{ __('Enter room categories limit') }}"
                                            value="{{ $package->room_categories_limit }}">
                                        <p id="err_room_categories_limit" class="mb-0 text-danger em"></p>
                                        <small class="text-warning d-block mt-1">
                                            {{ __('Enter 999999 , than it will appear as unlimited') }}
                                        </small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="room_limit">{{ __('Number Of Room Limit') }} <span
                                                class="text-danger">**</span></label>
                                        <input id="room_limit" type="number" class="form-control ltr" name="room_limit"
                                            placeholder="{{ __('Enter room limit') }}"
                                            value="{{ $package->room_limit }}">
                                        <p id="err_room_limit" class="mb-0 text-danger em"></p>
                                        <small class="text-warning d-block mt-1">
                                            {{ __('Enter 999999 , than it will appear as unlimited') }}
                                        </small>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="room_booking_limit">{{ __('Number Of Room Bookings') }} <span
                                                class="text-danger">**</span></label>
                                        <input id="room_booking_limit" type="number" class="form-control ltr"
                                            name="room_booking_limit" placeholder="{{ __('Enter room booking limit') }}"
                                            value="{{ $package->room_booking_limit }}">
                                        <p id="err_room_booking_limit" class="mb-0 text-danger em"></p>
                                        <small class="text-warning d-block mt-1">
                                            {{ __('Enter 999999 , than it will appear as unlimited') }}
                                        </small>
                                    </div>

                                      <div class="form-group col-md-6">
                                        <label>{{ __('Icon') }} <span class="text-danger">**</span></label>
                                        <div class="btn-group d-block">
                                            <button type="button" class="btn btn-primary iconpicker-component">
                                                <i class="{{ $package->icon }}"></i>
                                            </button>
                                            <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle"
                                                data-selected="fa-car" data-toggle="dropdown"></button>
                                            <div class="dropdown-menu"></div>
                                        </div>
                                        <input id="inputIcon" type="hidden" name="icon"
                                            value="{{ $package->icon }}">
                                        @if ($errors->has('icon'))
                                            <p class="mb-0 text-danger">{{ $errors->first('icon') }}</p>
                                        @endif
                                        <p id="err_icon" class="mb-0 text-danger em"></p>
                                        <small class="d-block mt-1">
                                            {{ __('NB: click on the dropdown sign to select an icon') }}
                                        </small>
                                    </div>
                                     <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Featured') }} <span
                                                class="text-danger">**</span></label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="featured" value="1"
                                                    class="selectgroup-input"
                                                    {{ $package->featured == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('Yes') }}</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="featured" value="0"
                                                    class="selectgroup-input"
                                                    {{ $package->featured == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('No') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Recommended') }} <span
                                                class="text-danger">**</span></label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="recommended" value="1"
                                                    class="selectgroup-input"
                                                    {{ $package->recommended == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('Yes') }}</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="recommended" value="0"
                                                    class="selectgroup-input"
                                                    {{ $package->recommended == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('No') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Trial') }} <span
                                                class="text-danger">**</span></label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_trial" value="1"
                                                    class="selectgroup-input"
                                                    {{ $package->is_trial == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('Yes') }}</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_trial" value="0"
                                                    class="selectgroup-input"
                                                    {{ $package->is_trial == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">{{ __('No') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    @if ($package->is_trial == 1)
                                        <div class="form-group col-md-6 dis-block" id="trial_day">
                                            <label for="trial_days_2">{{ __('Trial days') }} <span
                                                    class="text-danger">**</span></label>
                                            <input id="trial_days_2" type="number" class="form-control ltr"
                                                name="trial_days" placeholder="{{ __('Enter trial days') }}"
                                                value="{{ $package->trial_days }}">
                                        </div>
                                    @else
                                        <div class="form-group col-md-6 dis-none" id="trial_day">
                                            <label for="trial_days_1">{{ __('Trial days') }} <span
                                                    class="text-danger">**</span></label>
                                            <input id="trial_days_1" type="number" class="form-control ltr"
                                                name="trial_days" placeholder="{{ __('Enter trial days') }}"
                                                value="{{ $package->trial_days }}">
                                        </div>
                                    @endif
                                </div>

                                {{-- Keep trial error right under trial days --}}
                                <p id="err_trial_days" class="mb-0 text-danger em"></p>

                                {{-- Meta Keywords --}}
                                <div class="form-group mt-3">
                                    <label for="meta_keywords">{{ __('Meta Keywords') }}</label>
                                    <input id="meta_keywords" type="text" class="form-control" name="meta_keywords"
                                        value="{{ $package->meta_keywords }}" data-role="tagsinput">
                                </div>

                                {{-- Meta Description --}}
                                <div class="form-group">
                                    <label for="meta_description">{{ __('Meta Description') }}</label>
                                    <textarea id="meta_description" class="form-control" name="meta_description" rows="5">{{ $package->meta_description }}</textarea>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="submitBtn" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        "use strict";
        var permission = @php echo json_encode($permissions) @endphp;
    </script>
    <script src="{{ asset('assets/admin/js/edit-package.js') }}"></script>
@endsection
