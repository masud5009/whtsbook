<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Number') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="ajaxEditForm" class="modal-form" action="{{ route('user.whatsapp_list_update') }}" method="post">
                    @csrf
                    <input type="hidden" name="id" id="in_id">
                    <div class="form-group">
                        <label for="">{{ __('Whatsapp From Number') }} <span
                                class="text-danger">**</span></label>
                        <input type="text" class="form-control" name="whatsapp_from_number"
                            id="in_whatsapp_from_number" placeholder="{{ __('Enter from number') }}">
                        <p id="editErr_whatsapp_from_number" class="mt-1 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('Whatsapp Number Id') }} <span class="text-danger">**</span></label>
                        <input type="text" class="form-control" name="whatsapp_number_id" id="in_whatsapp_number_id"
                            placeholder="{{ __('Enter number id') }}">
                        <p id="editErr_whatsapp_number_id" class="mt-1 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('Whatsapp Business Account Number') }} <span
                                class="text-danger">**</span></label>
                        <input type="text" class="form-control" name="whatsapp_business_account_number"
                            id="in_whatsapp_business_account_number"
                            placeholder="{{ __('Enter business account number') }}">
                        <p id="editErr_whatsapp_business_account_number" class="mt-1 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('Whatsapp Verify token') }} <span
                                class="text-danger">**</span></label>
                        <input type="text" class="form-control" name="whatsapp_verify_token"
                            id="in_whatsapp_verify_token" placeholder="{{ __('Enter verify token') }}">
                            <p id="editErr_whatsapp_verify_token" class="mt-1 mb-0 text-danger em"></p>
                            <small class="form-text text-muted">
                            {{ __('This token is used to verify the webhook URL when setting up the WhatsApp Business API. It should be a random string that you will also enter in the Facebook Developer Console.') }}
                            <a href="{{ asset('assets/admin/img/verify-token-guide.png') }}" target="_blank">
                                {{ __('See Example') }}
                            </a>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('Whatsapp Access token') }} <span
                                class="text-danger">**</span></label>
                        <textarea class="form-control" id="in_whatsapp_access_token" name="whatsapp_access_token"rows="7"
                            placeholder="{{ __('Enter access token') }}"></textarea>
                        <p id="editErr_whatsapp_access_token" class="mt-1 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label for="status">{{ __('Status') }}<span class="text-danger">**</span></label>
                        <select id="status" name="status" class="form-control" id="in_status">
                            <option disabled>{{ __('Select Status') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Dective') }}</option>
                        </select>
                        <p id="editErr_status" class="mt-1 mb-0 text-danger em"></p>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                <button id="updateBtn" type="button" class="btn btn-primary">
                    {{ __('Update') }}
                </button>
            </div>
        </div>
    </div>
</div>
