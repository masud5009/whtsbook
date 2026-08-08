<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Package') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
                    action="{{ route('admin.package.store') }}" method="POST">
                    @csrf

                    <!-- Row: Title + Price -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="title">{{ __('Package title') }} <span class="text-danger">**</span></label>
                            <input id="title" type="text" class="form-control" name="title"
                                placeholder="{{ __('Enter Package title') }}" value="">
                            <p id="err_title" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="price">{{ __('Price') }} ({{ $bex->base_currency_text }}) <span
                                    class="text-danger">**</span></label>
                            <input id="price" type="number" class="form-control ltr" name="price"
                                placeholder="{{ __('Enter Package price') }}" value="">
                            <p id="err_price" class="mb-0 text-danger em"></p>
                            <small class="text-warning d-block mt-1">
                                {{ __('If price is 0 , than it will appear as free') }}
                            </small>
                        </div>
                    </div>

                    <!-- Row: Term + Status -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="term">{{ __('Package term') }} <span class="text-danger">**</span></label>
                            <select id="term" name="term" class="form-control" required>
                                <option value="" selected disabled>{{ __('Choose a Package term') }}</option>
                                <option value="monthly">{{ __('monthly') }}</option>
                                <option value="yearly">{{ __('yearly') }}</option>
                                <option value="lifetime">{{ __('lifetime') }}</option>
                            </select>
                            <p id="err_term" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="status">{{ __('Status') }}<span class="text-danger">**</span></label>
                            <select id="status" class="form-control ltr" name="status">
                                <option value="" selected disabled>{{ __('Select a status') }}</option>
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Deactive') }}</option>
                            </select>
                            <p id="err_status" class="mb-0 text-danger em"></p>
                        </div>
                    </div>

                    <!-- Full: Features -->
                    <div class="form-group">
                        <label class="form-label">{{ __('Package Features') }}</label>
                        <div class="selectgroup selectgroup-pills">
                            <label class="selectgroup-item">
                                <input type="checkbox" name="features[]" value="QR Builder" class="selectgroup-input">
                                <span class="selectgroup-button">{{ __('QR Builder') }}</span>
                            </label>
                            <label class="selectgroup-item">
                                <input type="checkbox" name="features[]" value="Support Ticket"
                                    class="selectgroup-input">
                                <span class="selectgroup-button">{{ __('Support Ticket') }}</span>
                            </label>
                        </div>
                        <p id="err_features" class="mb-0 text-danger em"></p>
                    </div>

                    <!-- Full: AI Credit Limit -->
                    <div class="form-group">
                        <label for="total_ai_token">
                            {{ __('AI Credit Limit') }} <span class="text-danger">**</span>
                        </label>

                        <input id="total_ai_token" type="number" class="form-control ltr" name="total_ai_token"
                            placeholder="{{ __('Example: 500000') }}">

                        <p id="err_total_ai_token" class="mb-0 text-danger em"></p>

                        <small class="text-info d-block mt-1">
                            {{ __('Set the maximum number of AI credit available per billing cycle for this package. This limit is consumed by automated replies, room suggestions, booking flow, and booking summary generation. When the limit is reached, AI responses will pause until the next renewal.') }}
                        </small>
                    </div>

                    <!-- Row: WhatsApp + Language -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="whatsapp_limit">
                                {{ __('WhatsApp Business Number Limit') }}
                                <span class="text-danger">*</span>
                            </label>

                            <input id="whatsapp_limit" type="number" class="form-control" name="whatsapp_limit"
                                placeholder="{{ __('Enter number limit') }}" min="1">

                            <p id="err_whatsapp_limit" class="mb-0 text-danger em"></p>

                            <small class="text-info d-block mt-1">
                                {{ __('Maximum number of WhatsApp Business numbers a tenant can add.') }}
                            </small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="language_limit">{{ __('Number Of Language Limit') }}<span
                                    class="text-danger">**</span></label>
                            <input id="language_limit" type="number" class="form-control ltr" name="language_limit"
                                placeholder="{{ __('Enter Language Limit') }}" min="0">
                            <p id="err_language_limit" class="mb-0 text-danger em"></p>
                            <small class="text-warning d-block mt-1">
                                {{ __('Enter 999999 , than it will appear as unlimited') }}
                            </small>
                        </div>
                    </div>

                    <!-- Row: Room Categories + Rooms -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="room_categories_limit">{{ __('Number Of Room Categories Limit') }} <span
                                    class="text-danger">**</span></label>
                            <input id="room_categories_limit" type="number" class="form-control ltr"
                                name="room_categories_limit" placeholder="{{ __('Enter room categories limit') }}"
                                min="0">
                            <p id="err_room_categories_limit" class="mb-0 text-danger em"></p>
                            <small class="text-warning d-block mt-1">
                                {{ __('Enter 999999 , than it will appear as unlimited') }}
                            </small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="room_limit">{{ __('Number Of Room Limit') }} <span
                                    class="text-danger">**</span></label>
                            <input id="room_limit" type="number" class="form-control ltr" name="room_limit"
                                placeholder="{{ __('Enter room limit') }}" value="" min="0">
                            <p id="err_room_limit" class="mb-0 text-danger em"></p>
                            <small class="text-warning d-block mt-1">
                                {{ __('Enter 999999 , than it will appear as unlimited') }}
                            </small>
                        </div>
                    </div>

                    <!-- Row: Bookings + Coupon -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="room_booking_limit">{{ __('Number Of Room Bookings Limit') }}<span
                                    class="text-danger">**</span></label>
                            <input id="room_booking_limit" type="number" class="form-control ltr"
                                name="room_booking_limit" placeholder="{{ __('Enter room booking limit') }}"
                                value="" min="0">
                            <p id="err_room_booking_limit" class="mb-0 text-danger em"></p>
                            <small class="text-warning d-block mt-1">
                                {{ __('Enter 999999 , than it will appear as unlimited') }}
                            </small>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Featured') }} <span
                                    class="text-danger">**</span></label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="featured" value="1" class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Yes') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="featured" value="0" class="selectgroup-input"
                                        checked>
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
                                        class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Yes') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="recommended" value="0"
                                        class="selectgroup-input" checked>
                                    <span class="selectgroup-button">{{ __('No') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Icon') }} <span class="text-danger">**</span></label>
                            <div class="btn-group d-block">
                                <button type="button" class="btn btn-primary iconpicker-component">
                                    <i class="fa fa-fw fa-heart"></i>
                                </button>
                                <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle"
                                    data-selected="fa-car" data-toggle="dropdown"></button>
                                <div class="dropdown-menu"></div>
                            </div>

                            <input id="inputIcon" type="hidden" name="icon" value="fas fa-heart">

                            @if ($errors->has('icon'))
                                <p class="mb-0 text-danger">{{ $errors->first('icon') }}</p>
                            @endif

                            <p id="err_icon" class="mb-0 text-danger em"></p>

                            <small class="d-block mt-1">
                                {{ __('NB: click on the dropdown sign to select a icon') }}
                            </small>
                        </div>
                    </div>


                    <!-- Row: Trial + Trial Days -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Trial') }} <span class="text-danger">**</span></label>
                            <div class="selectgroup w-100">
                                <label class="selectgroup-item">
                                    <input type="radio" name="is_trial" value="1" class="selectgroup-input">
                                    <span class="selectgroup-button">{{ __('Yes') }}</span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="is_trial" value="0" class="selectgroup-input"
                                        checked>
                                    <span class="selectgroup-button">{{ __('No') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group col-md-6 dis-none" id="trial_day">
                            <label for="trial_days">{{ __('Trial days') }}<span class="text-danger">**</span></label>
                            <input id="trial_days" type="number" class="form-control ltr" name="trial_days"
                                placeholder="{{ __('Enter trial days') }}" value="">
                            <p id="err_trial_days" class="mb-0 text-danger em"></p>
                        </div>
                    </div>

                    <!-- Meta Keywords -->
                    <div class="form-group">
                        <label for="">{{ __('Meta Keywords') }}</label>
                        <input type="text" class="form-control" name="meta_keywords" value=""
                            data-role="tagsinput">
                    </div>

                    <!-- Meta Description -->
                    <div class="form-group">
                        <label for="meta_description">{{ __('Meta Description') }}</label>
                        <textarea id="meta_description" type="text" class="form-control" name="meta_description" rows="5"></textarea>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                <button id="submitBtn" type="button" class="btn btn-primary">{{ __('Submit') }}</button>
            </div>

        </div>
    </div>
</div>
