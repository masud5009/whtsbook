<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Room') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="ajaxForm" class="modal-form"
                    action="{{ route('tenant.rooms_management.room.store', ['language_id' => request()->input('language_id')]) }}"
                    method="post">
                    @csrf

                    <div class="form-group">
                        <label for="language">{{ __('Room Category') }} <span class="text-danger">**</span></label>
                        <select name="room_category_id" class="form-control">
                            <option selected disabled>{{ __('Select a Category') }}</option>
                            @foreach ($roomCategories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->title }}
                                </option>
                            @endforeach
                        </select>
                        <p id="err_room_category_id" class="mt-1 mb-0 text-danger em"></p>
                    </div>

                    @foreach ($langs as $lang)
                        <div class="form-group">
                            <label for="">{{ __('Room Number/Name') }} ({{ $lang->name }})
                                @if ($webDefaultLang == $lang->code)
                                    <span class="text-danger">**</span>
                                @endif
                            </label>

                            <input type="text" class="form-control" name="room_number_{{ $lang->code }}"
                                placeholder="{{ __('Enter Room Number/Name') }}">
                            <p id="err_room_number_{{ $lang->code }}" class="mt-1 mb-0 text-danger em"></p>
                        </div>
                    @endforeach

                    <div class="form-group">
                        <label for="status">{{ __('Status') }} <span class="text-danger">**</span></label>
                        <select id="status" name="status" class="form-control">
                            <option selected disabled>{{ __('Select Status') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Inactive') }}</option>
                        </select>
                        <p id="err_status" class="mt-1 mb-0 text-danger em"></p>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    {{ __('Close') }}
                </button>
                <button id="submitBtn" type="button" class="btn btn-primary">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
</div>
