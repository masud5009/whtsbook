@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Share Information') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">{{ __('Whatsapp Numbers') }}</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">{{ __('Share Information') }}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">

            <!-- Note -->
            <div class="alert alert-info py-3">
                <strong>{{ __('Note').':' }}</strong>
                <span class="text-muted">
                    {{ __('The information you save here will be sent to customers when they request contact, or business details. If left blank, the system will use the default settings from your website') }}
                </span>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h5 class="card-title mb-0">{{ __('Share Information') }}</h5>
                        </div>
                        <div class="col-lg-4 text-lg-right">
                            <a href="{{ route('user.whatsapp_list') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-backward mr-1"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <form action="{{ route('user.whatsapp_share_information.update') }}" method="POST"
                                id="updateInformationForm">
                                @csrf
                            <input type="hidden" name="wp_id" value="{{ $wp_id }}">
                                <!-- Hotel Name -->
                                <div class="form-group">
                                    <label>{{ __('Hotel Name') }}</label>
                                    <input type="text" class="form-control" name="hotel_name"
                                        value="{{ old('hotel_name', $share_info->hotel_name) }}">
                                </div>

                                <!-- Email Address -->
                                <div class="form-group">
                                    <label>{{ __('Email Address') }}</label>
                                    <input type="text" class="form-control" name="email_address" data-role="tagsinput"
                                        value="{{ old('email_address', $share_info->email_address ? implode(',', $share_info->email_address) : '') }}">
                                </div>

                                <!-- Phone Numbers -->
                                <div class="form-group">
                                    <label>{{ __('Phone Numbers') }}</label>
                                    <input type="phone" class="form-control" name="phone_numbers" data-role="tagsinput"
                                        value="{{ old('phone_numbers', $share_info->phone_numbers ? implode(',', $share_info->phone_numbers) : '') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <button type="submit" form="updateInformationForm" class="btn btn-success">
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
