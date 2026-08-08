<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Room Amenity') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger pb-1 dis-none" id="blogErrors">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <ul></ul>
                </div>
                <form id="amenitiesForm" class="modal-form"
                    action="{{ route('tenant.rooms_management.store_amenity') }}" method="post" dir="ltr">
                    @csrf

                    @foreach ($langs as $language)
                    <div class="form-group">
                        <label for="" class="d-flex">
                            {{ __('Name') }} ({{ $language->name }})
                            @if ($language->is_default == 1)
                                <span class="text-danger">**</span>
                            @endif
                        </label>
                        <input type="text" class="form-control {{ $language->rtl == 1 ? 'text-right rtl' : 'ltr' }}"
                            name="{{ $language->code }}_name" placeholder="{{ __('Enter Name') }}">
                    </div>
                    @endforeach

                    <div class="form-group">
                        <label for="">{{ __('Status') }}<span class="text-danger"> **</span></label>
                        <select name="status" class="form-control">
                            <option disabled>{{ __('Select a Status') }}</option>
                            <option value="1">
                                {{ __('Active') }}
                            </option>
                            <option value="0">
                                {{ __('Deactive') }}
                            </option>
                        </select>
                        <p id="err_status" class="mt-1 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                        <input type="number" class="form-control ltr" name="serial_number"
                            placeholder="{{ __('Enter Serial Number') }}">
                        <p id="err_serial_number" class="mt-1 mb-0 text-danger em"></p>
                        <p class="text-warning mt-2">
                            <small>{{ __('The higher the serial number, the later it will be displayed.') }}</small>
                        </p>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                <button form="amenitiesForm" class="btn btn-primary">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
</div>
