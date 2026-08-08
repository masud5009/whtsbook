@extends('user.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Contact Page') }}</h4>
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
        <a href="#">{{ __('Pages') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Contact Page') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{ route('user.basic_settings.update_info') }}" method="post">
          @csrf
          <div class="card-header">
            <div class="row">
              <div class="col-lg-10">
                <div class="card-title">{{ __('Update Contact Page') }}</div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-lg-6 mx-auto">
                <div class="form-group">
                  <label>{{ __('Support Email Address') }} <span class="text-danger"></span></label>
                  <input type="email" class="form-control ltr" name="support_email"
                    value="{{ $data->support_email != null ? $data->support_email : '' }}"
                    placeholder="{{ __('Enter Email Address') }}">
                  @if ($errors->has('support_email'))
                    <p class="mt-2 mb-0 text-danger">{{ $errors->first('support_email') }}</p>
                  @endif
                </div>

                <div class="form-group">
                  <label>{{ __('Support Contact Number') }} <span class="text-danger"></span></label>
                  <input type="text" class="form-control" name="support_contact"
                    value="{{ $data->support_contact != null ? $data->support_contact : '' }}"
                    placeholder="{{ __('Enter Contact Number') }}">
                  @if ($errors->has('support_contact'))
                    <p class="mt-2 mb-0 text-danger">{{ $errors->first('support_contact') }}</p>
                  @endif
                </div>

                <div class="form-group">
                  <label>{{ __('Address') }} <span class="text-danger"></span></label>
                  <input type="text" class="form-control" name="address"
                    value="{{ $data->address != null ? $data->address : '' }}" placeholder="{{ __('Enter Address') }}">
                  @if ($errors->has('address'))
                    <p class="mt-2 mb-0 text-danger">{{ $errors->first('address') }}</p>
                  @endif
                </div>

                <div class="form-group">
                  <label>{{ __('Latitude') }} <span class="text-danger"></span></label>
                  <input type="text" class="form-control" name="latitude"
                    value="{{ $data->latitude != null ? $data->latitude : '' }}"
                    placeholder="{{ __('Enter Latitude') }}">
                  @if ($errors->has('latitude'))
                    <p class="mt-2 mb-0 text-danger">{{ $errors->first('latitude') }}</p>
                  @endif
                  <p class="mt-2 mb-0 text-warning">
                    {{ __('The value of the latitude will be helpful to show your location in the map') }}
                  </p>
                </div>

                <div class="form-group">
                  <label>{{ __('Longitude') }} <span class="text-danger"></span></label>
                  <input type="text" class="form-control" name="longitude"
                    value="{{ $data->longitude != null ? $data->longitude : '' }}"
                    placeholder="{{ __('Enter Longitude') }}">
                  @if ($errors->has('longitude'))
                    <p class="mt-2 mb-0 text-danger">{{ $errors->first('longitude') }}</p>
                  @endif
                  <p class="mt-2 mb-0 text-warning">
                    {{ __('The value of the longitude will be helpful to show your location in the map') }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                <button type="submit" class="btn btn-success">
                  {{ __('Update') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
