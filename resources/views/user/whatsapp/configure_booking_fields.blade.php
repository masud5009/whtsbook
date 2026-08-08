@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Custom Booking Fields') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="#"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">{{ __('Whatsapp Numbers') }}</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">{{ __('Custom Booking Fields') }}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- Note -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Automatically Collected Information Section -->
                    <div>
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-robot mr-2"></i>
                            {{ __('Information Automatically Collected by AI') }}
                        </h4>
                        <div class="custom-fields-ai-collecte">
                            <p class="badge badge-pill badge-light border px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-user text-muted mr-1"></i>
                                Customer Name
                            </p>
                            <p class="badge badge-pill badge-light border px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-envelope text-muted mr-1"></i>
                                Customer Email
                            </p>
                            <p class="badge badge-pill badge-light border px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-phone text-muted mr-1"></i>
                                Customer Phone
                            </p>
                            <p class="badge badge-pill badge-light border px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-calendar-check text-muted mr-1"></i>
                                Arrival Date
                            </p>
                            <p class="badge badge-pill badge-light border px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-calendar-times text-muted mr-1"></i>
                                Departure Date
                            </p>
                            <p class="badge badge-pill badge-light border px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-male text-muted mr-1"></i>
                                Adult
                            </p>
                            <p class="badge badge-pill badge-light border px-3 py-2 mb-2">
                                <i class="fas fa-child text-muted mr-1"></i>
                                Child
                            </p>
                        </div>
                    </div>
                    <hr>
                    <!-- Need More Information Section -->
                    <div>
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-plus-circle mr-2"></i>
                            {{ __('Need more information?') }}
                        </h4>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-2 text-muted">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                {{ __('Create custom fields that the WhatsApp bot will use to collect information from customers during booking.') }}
                            </p>
                            <p class="mb-0 text-info bg-white p-2 rounded border-left border-info">
                                <i class="fas fa-lightbulb text-warning mr-2"></i>
                                <em>{{ __('Example: Customer Address, Special Request, etc.') }}</em>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Form Builder Section -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ __('Add New Field') }}</h5>
                        </div>
                        <div class="card-body">
                            <form id="addFieldForm">
                                <!-- Field Label -->
                                <div class="form-group">
                                    <label>{{ __('Field Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fieldLabel"
                                        placeholder=" {{ __('e.g. Room Preference') }}" required>
                                    <p class="text-danger mb-0" id="field-required"></p>
                                </div>

                                <!-- Required/Optional -->
                                <div class="form-group">
                                    <label>{{ __('Field Type') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" id="fieldRequired">
                                        <option value="1">{{ __('Required') }}</option>
                                        <option value="0">{{ __('Optional') }}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-block" id="addFieldBtn">
                                        <i class="fas fa-plus mr-1"></i> {{ __('Add Field') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="card-title mb-0">{{ __('Custom Fields') }}</h5>
                                </div>
                                <div class="col-auto">
                                    <span class="badge badge-info" id="fieldCount">0</span>
                                </div>
                            </div>
                            <p class="mb-0 text-info">
                                {{ __('You can sort the fields by dragging them to your preferred position') }}</p>
                        </div>
                        <div class="card-body">
                            <div id="fieldsContainer" class="fields-preview">
                                <!-- Fields will be added here -->
                            </div>
                            <div id="emptyState" class="text-center text-muted py-5">
                                <p>{{ __('No fields added yet. Add a field to get started.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for saving -->
    <form id="saveForm" method="POST" action="{{ route('user.whatsapp_configure_booking_fields.update') }}"
        style="display: none;">
        @csrf
        <input type="hidden" name="wp_id" value="{{ $wp_id }}">
        <input type="hidden" name="fields_data" id="fieldsData">
    </form>

    <!-- Edit Field Modal -->
    <div class="modal fade" id="editFieldModal" tabindex="-1" role="dialog" aria-labelledby="editFieldModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFieldModalLabel">{{ __('Edit Field') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editFieldId">
                    <div class="form-group">
                        <label>{{ __('Field Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editFieldLabel" placeholder="{{ __('e.g. Room Preference') }}" required>
                        <p class="text-danger mb-0" id="edit-field-required"></p>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Field Type') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="editFieldRequired">
                            <option value="1">{{ __('Required') }}</option>
                            <option value="0">{{ __('Optional') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="updateFieldBtn">{{ __('Update') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        let fieldsData = @json($customFields ?? []);
        const __field_name__ = "{{ __('The field name is required.') }}";
        const __required__ = "{{ __('Required') }}";
        const __optional__ = "{{ __('Optional') }}";
        const __saved_successfully__ = "{{ __('Saved successfully') }}";
        const wpId = {{ $wp_id }};
    </script>
    <script src="{{ asset('assets/tenant/js/custom-fields.js') }}"></script>
@endsection
