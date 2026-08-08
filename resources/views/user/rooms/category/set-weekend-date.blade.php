<div class="modal fade" id="setWeekend" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg rounded">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        {{ __('Select the days when weekend pricing applies') }}
                    </h5>
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
                        <div class="day-item">
                            <input type="checkbox" id="day_{{ $day['full'] }}" name="weekend_days[]"
                                value="{{ $day['full'] }}" class="day-checkbox d-none">
                            <label for="day_{{ $day['full'] }}" class="day-label">
                                <span class="day-name">{{ __($day['full']) }}</span>
                                <i class="fas fa-check-circle check-icon"></i>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger text-white px-4 mr-2 rounded" data-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                <button id="confirmDays" type="button" class="btn btn-primary px-4 shadow-sm rounded">
                    {{ __('Apply') }}
                </button>
            </div>
        </div>
    </div>
</div>
