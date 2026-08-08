// Seasonal Date Picker Manager
(function () {
    'use strict';

    let selectedSeasonalRanges = [];
    let seasonalPicker;

    document.addEventListener('DOMContentLoaded', function () {
        initSeasonalDatePicker();
        loadExistingSeasonalDates();
        $('#seasonalWeekendModal').on('show.bs.modal', function () {
            updateDaySelectorUI();
        });
    });

    // Load existing seasonal dates on edit page
    function loadExistingSeasonalDates() {
        const seasonalDatesInput = document.getElementById('seasonal_dates_input');
        if (seasonalDatesInput && seasonalDatesInput.value) {
            try {
                const existingRanges = JSON.parse(seasonalDatesInput.value);
                if (Array.isArray(existingRanges) && existingRanges.length > 0) {
                    selectedSeasonalRanges = existingRanges;
                    updateSeasonalUI();
                    $('.seasonal-weekend-price').removeClass('d-none');
                }
            } catch (e) {
                console.error('Error loading existing seasonal dates:', e);
            }
        }
    }

    function initSeasonalDatePicker() {
        seasonalPicker = flatpickr("#seasonalDatePicker", {
            mode: "range",
            dateFormat: "Y-m-d",
            minDate: "today"
        });

        const addButton = document.getElementById('addSeasonalRange');
        if (addButton) {
            addButton.addEventListener('click', addSeasonalRange);
        }

        const applyButton = document.getElementById('applySeasonalDates');
        if (applyButton) {
            applyButton.addEventListener('click', applySeasonalDates);
        }

        $('#setSeasonal').on('hidden.bs.modal', function () {
            seasonalPicker.clear();
        });
    }

    function addSeasonalRange() {
        const selectedDates = seasonalPicker.selectedDates;

        if (selectedDates.length === 2) {
            const start = seasonalPicker.formatDate(selectedDates[0], 'Y-m-d');
            const end = seasonalPicker.formatDate(selectedDates[1], 'Y-m-d');

            const isDuplicate = selectedSeasonalRanges.some(range =>
                range.start === start && range.end === end
            );

            if (isDuplicate) {
                alert("This date range is already added.");
                return;
            }

            selectedSeasonalRanges.push({ start: start, end: end });
            updateSeasonalUI();
            seasonalPicker.clear();
        } else {
            alert("Please select both start and end date.");
        }
    }

    function updateSeasonalUI() {
        const mainContainer = document.getElementById('selectedSeasonalDatesContainer');
        const mainList = document.getElementById('selectedSeasonalDatesList');
        const modalList = document.getElementById('modalSeasonalPendingList');
        const emptyMessage = document.getElementById('emptySeasonalMessage');

        if (mainList) mainList.innerHTML = '';
        if (modalList) modalList.innerHTML = '';

        if (selectedSeasonalRanges.length > 0) {
            if (emptyMessage) emptyMessage.style.display = 'none';
            if (mainContainer) mainContainer.classList.remove('d-none');

            selectedSeasonalRanges.forEach((range, index) => {
                const badgeHtml = `
                    <span class="badge badge-info p-2 mb-1 mr-1">
                        ${range.start} to ${range.end}
                        <i class="fas fa-times ml-2 text-white"
                           style="cursor:pointer"
                           onclick="removeSeasonalRange(${index})"
                           title="Remove"></i>
                    </span>
                `;
                if (mainList) mainList.innerHTML += badgeHtml;
                if (modalList) modalList.innerHTML += badgeHtml;
            });
        } else {
            if (emptyMessage) emptyMessage.style.display = 'block';
            if (mainContainer) mainContainer.classList.add('d-none');
        }
    }

    function updateDaySelectorUI() {
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        let availableDays = new Set();

        selectedSeasonalRanges.forEach(range => {
            let start = new Date(range.start);
            let end = new Date(range.end);
            let current = new Date(start);

            while (current <= end) {
                availableDays.add(dayNames[current.getDay()]);
                current.setDate(current.getDate() + 1);
            }
        });

        const dayItems = document.querySelectorAll('.seasonal-day-item');
        const noDaysMessage = document.getElementById('noDaysMessage');

        if (availableDays.size > 0) {
            noDaysMessage.classList.add('d-none');
            dayItems.forEach(item => {
                const day = item.getAttribute('data-day');
                if (availableDays.has(day)) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                    item.querySelector('input').checked = false;
                }
            });
        } else {
            noDaysMessage.classList.remove('d-none');
            dayItems.forEach(item => item.classList.add('d-none'));
        }
    }

    function applySeasonalDates() {
        const hiddenInput = document.getElementById('seasonal_dates_input');
        if (hiddenInput) {
            if (selectedSeasonalRanges.length > 0) {
                hiddenInput.value = JSON.stringify(selectedSeasonalRanges);
                $('.seasonal-weekend-price').removeClass('d-none');
            } else {
                hiddenInput.value = '';
                $('.seasonal-weekend-price').addClass('d-none');
            }
        }
        $('#setSeasonal').modal('hide');
    }

    // ============ Helper Function: Get Available Days from Remaining Ranges ============
    function getAvailableDaysFromRanges() {
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        let availableDays = new Set();

        selectedSeasonalRanges.forEach(range => {
            let start = new Date(range.start);
            let end = new Date(range.end);
            let current = new Date(start);

            while (current <= end) {
                availableDays.add(dayNames[current.getDay()]);
                current.setDate(current.getDate() + 1);
            }
        });

        return availableDays;
    }

    // ============ Helper Function: Filter Selected Days ============
    function filterSelectedSeasonalWeekendDays() {
        const hiddenInput = document.getElementById('selectedSeasonalDaysInput');
        if (!hiddenInput || !hiddenInput.value) return;

        let selectedDays = hiddenInput.value.split(',').map(d => d.trim()).filter(d => d);
        const availableDays = getAvailableDaysFromRanges();
        const filteredDays = selectedDays.filter(day => availableDays.has(day));

        updateSeasonalWeekendDaysUI(filteredDays);
    }

    // ============ Helper Function: Update Seasonal Weekend Days UI ============
    function updateSeasonalWeekendDaysUI(daysArray) {
        const displayContainer = document.getElementById('selectedSeasonalWeekendDatesContainer');
        const listContainer = document.getElementById('selectedSeasonalWeekendDatesList');
        const hiddenInput = document.getElementById('selectedSeasonalDaysInput');

        const checkboxes = document.querySelectorAll('.seasonal-day-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = daysArray.includes(cb.value);
        });

        if (daysArray.length > 0) {
            listContainer.innerHTML = daysArray.map(day =>
                `<span class="badge px-3 py-2 m-1 badge-info">${day}</span>`
            ).join('');
            displayContainer.classList.remove('d-none');
            hiddenInput.value = daysArray.join(',');
        } else {
            listContainer.innerHTML = '';
            displayContainer.classList.add('d-none');
            hiddenInput.value = '';
        }
    }

    // ============ UPDATED removeSeasonalRange Function ============
    window.removeSeasonalRange = function (index) {
        if (confirm('Are you sure you want to remove this date range?')) {
            selectedSeasonalRanges.splice(index, 1);
            updateSeasonalUI();

            const seasonalDatesInput = document.getElementById('seasonal_dates_input');
            if (seasonalDatesInput) {
                seasonalDatesInput.value = selectedSeasonalRanges.length > 0
                    ? JSON.stringify(selectedSeasonalRanges)
                    : '';
            }

            if (selectedSeasonalRanges.length === 0) {
                clearSeasonalWeekendDays();
                $('.seasonal-weekend-price').addClass('d-none');
            } else {
                filterSelectedSeasonalWeekendDays();
                updateDaySelectorUI();
            }
        }
    };

    // ============ Function to Clear Seasonal Weekend Days ============
    function clearSeasonalWeekendDays() {
        const checkboxes = document.querySelectorAll('.seasonal-day-checkbox');
        checkboxes.forEach(cb => cb.checked = false);

        const displayContainer = document.getElementById('selectedSeasonalWeekendDatesContainer');
        const listContainer = document.getElementById('selectedSeasonalWeekendDatesList');

        if (listContainer) listContainer.innerHTML = '';
        if (displayContainer) displayContainer.classList.add('d-none');

        const hiddenInput = document.getElementById('selectedSeasonalDaysInput');
        if (hiddenInput) hiddenInput.value = '';
    }

    window.seasonalDateManager = {
        getRanges: () => selectedSeasonalRanges,
        clearRanges: () => {
            selectedSeasonalRanges = [];
            updateSeasonalUI();
            clearSeasonalWeekendDays();
            $('.seasonal-weekend-price').addClass('d-none');
        }
    };

    /*========================weekend days selected========================*/
    document.getElementById('confirmDays').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('.day-checkbox:checked');
        const selectedDays = Array.from(checkboxes).map(cb => cb.value);

        const displayContainer = document.getElementById('selectedDatesContainer');
        const listContainer = document.getElementById('selectedDatesList');
        const hiddenInput = document.getElementById('selectedDaysInput');

        const hasSelection = selectedDays.length > 0;

        listContainer.innerHTML = selectedDays.map(day =>
            `<span class="badge px-3 py-2 m-1 badge-info">${day}</span>`
        ).join('');

        displayContainer.classList.toggle('d-none', !hasSelection);
        hiddenInput.value = hasSelection ? selectedDays.join(',') : '';

        $('#setWeekend').modal('hide');
    });

    /*========================seasonal weekend dates selected========================*/
    document.getElementById('confirmSeasonalDays').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('.seasonal-day-checkbox:checked');
        const selectedDays = Array.from(checkboxes).map(cb => cb.value);

        const displayContainer = document.getElementById('selectedSeasonalWeekendDatesContainer');
        const listContainer = document.getElementById('selectedSeasonalWeekendDatesList');
        const hiddenInput = document.getElementById('selectedSeasonalDaysInput');

        const hasSelection = selectedDays.length > 0;

        listContainer.innerHTML = selectedDays.map(day =>
            `<span class="badge px-3 py-2 m-1 badge-info">${day}</span>`
        ).join('');

        displayContainer.classList.toggle('d-none', !hasSelection);
        hiddenInput.value = hasSelection ? selectedDays.join(',') : '';

        $('#seasonalWeekendModal').modal('hide');
    });

    /*========================Document Ready========================*/
    $(document).ready(function () {
        // ===== Regular Weekend Days Logic =====
        const displayContainer = $('#selectedDatesContainer');
        const listContainer = $('#selectedDatesList');
        const hiddenInput = $('#selectedDaysInput');

        $('#setWeekend').on('show.bs.modal', function (event) {
            $('.day-checkbox').prop('checked', false);

            let rawData = $(event.relatedTarget).data('value');
            if (!rawData) return console.log('No data-value found');

            try {
                let selectedDays = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
                if (typeof selectedDays === 'string') selectedDays = JSON.parse(selectedDays);

                if (Array.isArray(selectedDays)) {
                    selectedDays.forEach(day => {
                        $(`#day_${day.trim()}`).prop('checked', true);
                    });
                }
            } catch (e) {
                console.error("Error parsing weekend dates:", e);
            }
        });

        if (hiddenInput.val()) {
            const daysArray = hiddenInput.val().split(',');
            listContainer.html(daysArray.map(day =>
                `<span class="badge px-3 py-2 m-1 badge-info">${day.trim()}</span>`
            ).join(''));
            displayContainer.removeClass('d-none');
        }

        // ===== Seasonal Weekend Days Logic =====
        const seasonalWeekendHiddenInput = $('#selectedSeasonalDaysInput');
        const seasonalWeekendDisplayContainer = $('#selectedSeasonalWeekendDatesContainer');
        const seasonalWeekendListContainer = $('#selectedSeasonalWeekendDatesList');

        // Show seasonal weekend days for edit
        $('#seasonalWeekendModal').on('show.bs.modal', function (event) {
            // First uncheck all
            $('.seasonal-day-checkbox').prop('checked', false);

            let rawData = $(event.relatedTarget).data('value');
            if (!rawData) return;

            try {
                let selectedDays = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
                if (typeof selectedDays === 'string') selectedDays = JSON.parse(selectedDays);

                if (Array.isArray(selectedDays)) {
                    selectedDays.forEach(day => {
                        const trimmedDay = day.trim();
                        $(`#seasonal_weekend_day_${trimmedDay}`).prop('checked', true);
                    });
                }
            } catch (e) {
                console.error("Error parsing seasonal weekend dates:", e);
            }
        });

        // Load existing seasonal weekend days on page load
        if (seasonalWeekendHiddenInput.val()) {
            const daysArray = seasonalWeekendHiddenInput.val().split(',');
            seasonalWeekendListContainer.html(daysArray.map(day =>
                `<span class="badge px-3 py-2 m-1 badge-info">${day.trim()}</span>`
            ).join(''));
            seasonalWeekendDisplayContainer.removeClass('d-none');
        }
    });

})();
