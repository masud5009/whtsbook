{{-- room modal --}}
<div class="modal fade" id="roomModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('All Room Categories') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="roomSelectForm" action="{{ route('tenant.room_bookings.get_booked_dates') }}" method="GET">
                    <div class="row">
                        <div class="col-lg-12">
                             <div class="form-group">
                        <label for="language">{{ __('Language') }} <span class="text-danger">**</span></label>
                        <select name="language_id" class="form-control get_langwise_room_category" data-user_id="{{ Auth::guard('web')->user()->id }}">
                            <option disabled>{{ __('Select a Language') }}</option>
                            @foreach ($langs as $lang)
                                <option value="{{ $lang->id }}"
                                    {{ (string) request()->input('language_id', optional($language)->id) === (string) $lang->id ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                        <p id="err_language_id" class="mt-1 mb-0 text-danger em"></p>
                    </div>
                    
                            <div class="form-group">
                                <label>{{ __('Room Category') }} <span class="text-danger">**</span></label>
                                <select name="room_category_id" class="form-control" id="selected-room">
                                    <option selected disabled>{{ __('Select a Room Category') }}</option>
                                    @foreach ($roomInfos as $roomInfo)
                                        <option value="{{ $roomInfo->room_id }}">{{ $roomInfo->title }}</option>
                                    @endforeach
                                </select>
                                <p id="err_room_category_id" class="mt-1 mb-0 ml-1 text-danger em"></p>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>{{ __('Check In / Out Date') }} <span class="text-danger">**</span></label>
                                <input type="text" class="form-control" placeholder="{{ __('Select Dates') }}"
                                    id="date-range" name="dates" value="{{ old('dates') }}">
                                <p id="err_dates" class="mt-1 mb-0 ml-1 text-danger em"></p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" id="roomBookingNextBtn" class="btn btn-primary">
                    {{ __('Next') }}
                </button>
            </div>
        </div>
    </div>
</div>
