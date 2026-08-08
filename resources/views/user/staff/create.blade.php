<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Staff') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ajaxForm" class="modal-form" action="{{ route('tenant.staff_management.staff.store') }}"
                    method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">{{ __('Name') }} <span class="text-danger">**</span></label>
                                <input type="text" class="form-control" name="name" value=""
                                    placeholder="{{ __('Enter name') }}" required>
                                <p id="err_name" class="mb-0 text-danger em"></p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">{{ __('Username') }} <span class="text-danger">**</span></label>
                                <input type="text" class="form-control" name="username" value=""
                                    placeholder="{{ __('Enter username') }}" required>
                                <p id="err_username" class="mb-0 text-danger em"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">{{ __('Email') }} <span class="text-danger">**</span></label>
                                <input type="email" class="form-control" name="email" value=""
                                    placeholder="{{ __('Enter email') }}" required>
                                <p id="err_email" class="mb-0 text-danger em"></p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">{{ __('Role') }} <span class="text-danger">**</span></label>
                                <select class="form-control" name="role" required>
                                    <option value="" selected disabled>{{ __('Select a Role') }}</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <p id="err_role" class="mb-0 text-danger em"></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">{{ __('Password') }} <span class="text-danger">**</span></label>
                                <input type="password" class="form-control" name="password" value=""
                                    placeholder="{{ __('Enter password') }}" required>
                                <p id="err_password" class="mb-0 text-danger em"></p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">{{ __('Re-type Password') }} <span
                                        class="text-danger">**</span></label>
                                <input type="password" class="form-control" name="password_confirmation" value=""
                                    placeholder="{{ __('Enter your password again') }}" required>
                                <p id="err_password_confirmation" class="mb-0 text-danger em"></p>
                            </div>
                        </div>
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
