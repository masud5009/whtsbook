<div class="modal fade" id="editTemplateModal{{ $template->event_type }}" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Template') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="ajaxEditForm_{{ $template->id }}" class="modal-form"
                    action="{{ route('user.whatsapp_template.update', ['id' => $template->id]) }}" method="post">
                    @csrf
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="in_message" class="font-weight-bold">{{ __('Message') }} <span
                                            class="text-danger">**</span></label>
                                    <textarea class="form-control shadow-sm" name="message" rows="10"
                                        placeholder="{{ __('Write your message here. Use BB codes from the panel on the right.') }}">{{ $template->message }}</textarea>
                                    <p id="editErr_message" class="mt-2 mb-0 text-danger em"></p>
                                    <small class="text-warning d-block mt-2">
                                        <strong>{{ __('Tip') . ':' }}</strong>{{ __('Click a BB code on the right to insert it at the cursor position') }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm whatsapp-bb-card">
                                    <div class="card-body">
                                        <h6 class="mb-3">{{ __('BB Code') }}</h6>
                                        <div class="bb-panel list-group">
                                            @include('user.whatsapp.message-templates.bbcode')
                                        </div>
                                    </div>
                                </div>
                                <div class="small text-warning">
                                    {{ __('BB codes are placeholders replaced with dynamic values when the message is sent') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                <button form="ajaxEditForm_{{ $template->id }}" type="submit" class="btn btn-primary">
                    {{ __('Update') }}
                </button>
            </div>
        </div>
    </div>
</div>
