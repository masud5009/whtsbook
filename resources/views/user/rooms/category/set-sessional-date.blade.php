<div class="modal fade" id="setSeasonal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Seasonal Dates') }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ __('Select Date Range') }}</label>
                    <input type="text" id="seasonalDatePicker" class="form-control mb-2" placeholder=" {{ __('Select dates') }}"
                        readonly>
                    <button type="button" id="addSeasonalRange" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> {{ __('Add to List') }}
                    </button>
                </div>

                <hr>
                <div id="modalSeasonalPendingList" class="mt-2">
                    <p class="text-center mb-0" id="emptySeasonalMessage">{{ __('No dates added yet') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger text-white px-4 mr-2 rounded" data-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary px-4 shadow-sm rounded" id="applySeasonalDates">
                    {{ __('Apply') }}
                </button>
            </div>
        </div>
    </div>
</div>
