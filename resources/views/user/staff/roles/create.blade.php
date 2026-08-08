<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Role') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ajaxForm" class="modal-form" action="{{ route('tenant.staff_management.role.store') }}"
                    method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="">{{ __('Role Name') }} <span class="text-danger">**</span></label>
                        <input type="text" class="form-control" name="name" value=""
                            placeholder="{{ __('Enter role name') }}" required>
                        <p id="err_name" class="mb-0 text-danger em"></p>
                        <span class="form-text text-info">{{ __('You can configure module permissions after creating the role.') }}</span>
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
