@extends('user.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Fees') }}</h4>
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
        <a href="#">{{ __('Fees') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-4">
              <div class="card-title">{{ __('Fees') }}</div>
            </div>
          </div>
        </div>

        <div class="card-body pt-5 pb-5">
          <div class="row">
            <div class="col-lg-6 mx-auto">
              <form id="ajaxForm" action="{{ route('tenant.rooms_management.update_tax_fee') }}" method="post">
                @csrf
                <div class="form-group">
                  <label>{{ __('Tax Status') }} <span class="text-danger">**</span></label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="room_tax_status" value="1" class="selectgroup-input"
                        {{ $data->room_tax_status == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Active') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="radio" name="room_tax_status" value="0" class="selectgroup-input"
                        {{ $data->room_tax_status == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Deactive') }}</span>
                    </label>
                  </div>
                  <p id="err_room_tax_status" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group {{ $data->room_tax_status == 0 ? 'd-none' : 'd-block' }}" id="taxInput">
                  <label>{{ __('Tax Amount') }}(%) <span class="text-danger">**</span> </label>
                  <input type="text" class="form-control" name="room_tax" value="{{ $data->room_tax }}">
                  <p id="err_room_tax" class="mb-0 text-danger em"></p>
                </div>

                <div class="form-group">
                  <label>{{ __('Fee Status') }} <span class="text-danger">**</span></label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="room_fee_status" value="1" class="selectgroup-input"
                        {{ $data->room_fee_status == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Active') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="radio" name="room_fee_status" value="0" class="selectgroup-input"
                        {{ $data->room_fee_status == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">{{ __('Deactive') }}</span>
                    </label>
                  </div>
                  <p id="err_room_fee_status" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group {{ $data->room_fee_status == 0 ? 'd-none' : 'd-block' }}" id="feeInput">
                  <label>{{ __('Fee') }}({{ $data->base_currency_symbol }}) <span class="text-danger">** </span>
                    </label>
                  <input type="text" class="form-control" name="room_fee" value="{{ $data->room_fee }}">
                  <p id="err_room_fee" class="mb-0 text-danger em"></p>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="row">
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
@endsection
@section('script')
  <script src="{{ asset('assets/tenant/js/rooms/room.js') }}"></script>
@endsection
