<div class="card-body">

    <div class="room-assignment-header mb-3">
        {{ __('Select or deselect rooms with one click. Booked rooms are disabled. Ensure your selection matches the total room count.') }}
    </div>

    <div class="bookingInfo">
        <table class="table-light table-bordered booking-table table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Room Number') }}</th>
                </tr>
            </thead>
            <tbody class="room-table">
                @foreach ($dates as $day)
                    <tr>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($day['date'])->format('d M, Y') }} -
                            {{ \Carbon\Carbon::parse($day['date'])->addDay()->format('d M, Y') }}
                        </td>
                        <td class="room-column">
                            <div class="d-flex w-100 flex-wrap gap-2 py-2">

                                @php
                                    $selectedRooms = [];
                                @endphp

                                @foreach ($day['rooms'] as $index => $room)
                                    @php
                                        $btnClass = $room['status'] === 'booked' ? 'btn-danger' : 'btn-primary';
                                        $isAvailable = $room['status'] === 'available';

                                        $selectedClass =
                                            $isAvailable && count($selectedRooms) < $totalRooms
                                                ? 'selected btn-success'
                                                : '';

                                        // Skip booked rooms and mark them as disabled
                                        $dataStatus = $room['status'] === 'booked' ? 1 : 0;
                                        $roomId = str_pad($index + 1, 2, '0', STR_PAD_LEFT);

                                        // Add the room to the selected list if it's available
                                        if ($isAvailable && count($selectedRooms) < $totalRooms) {
                                            $selectedRooms[] = $room['room_number'];
                                        }
                                    @endphp
                                    <button type="button" data-toggle="tooltip" data-placement="top"
                                        title="{{ userPriceFormat(Auth::guard('web')->user()->id, $room['rent']) }}"
                                        class="btn btn-sm room-btn available {{ $btnClass }} {{ $selectedClass }}"
                                        room="room-{{ $room['room_number'] }}"
                                        data-room_number="{{ $room['room_number'] }}"
                                        data-room_id="{{ $room['id'] ?? $room['room_number'] }}"
                                        data-rent="{{ $room['rent'] }}" data-date="{{ $day['date'] }}"
                                        data-booked_status="{{ $dataStatus }}"
                                        {{ $room['status'] === 'booked' ? 'disabled' : '' }}>
                                        {{ $room['room_number'] }}
                                    </button>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="mt-1 mb-0 ml-1 em text-danger" id="er_rooms"></p>
    </div>
</div>
