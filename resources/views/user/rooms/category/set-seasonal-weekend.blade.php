<div class="modal fade" id="seasonalWeekendModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg rounded">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        {{ __('Select the days when weekend pricing applies') }}
                    </h5>
                    <small class="text-muted" id="seasonalDaysStatus"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body px-4 pb-4">
                <div class="day-selector-grid">
                    @php
                        $days = [
                            ['full' => 'Saturday', 'short' => 'Sat'],
                            ['full' => 'Sunday', 'short' => 'Sun'],
                            ['full' => 'Monday', 'short' => 'Mon'],
                            ['full' => 'Tuesday', 'short' => 'Tue'],
                            ['full' => 'Wednesday', 'short' => 'Wed'],
                            ['full' => 'Thursday', 'short' => 'Thu'],
                            ['full' => 'Friday', 'short' => 'Fri'],
                        ];
                    @endphp

                    @foreach ($days as $day)
                        <div class="day-item seasonal-day-item d-none" data-day="{{ $day['full'] }}">
                            <input type="checkbox" id="seasonal_weekend_day_{{ $day['full'] }}"
                                name="seasonal_weekend_days[]" value="{{ $day['full'] }}"
                                class="seasonal-day-checkbox d-none">
                            <label for="seasonal_weekend_day_{{ $day['full'] }}" class="day-label">
                                <span class="day-name">{{ __($day['full']) }}</span>
                                <i class="fas fa-check-circle check-icon"></i>
                            </label>
                        </div>
                    @endforeach

                    <div id="noDaysMessage" class="text-center p-3 w-100">
                        <p class="text-danger">{{ __('Please select date ranges first to see available days.') }}</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger text-white px-4 mr-2 rounded" data-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                <button id="confirmSeasonalDays" type="button" class="btn btn-primary px-4 shadow-sm rounded">
                    {{ __('Apply') }}
                </button>
            </div>
        </div>
    </div>
</div>
