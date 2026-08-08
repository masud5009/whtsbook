<div class="col-lg-5">
    <table class="table table-striped mb-5 bbcodes-table border">
        <thead>
            <tr>
                <th scope="col">{{ __('Short Code') }}</th>
                <th scope="col">{{ __('Meaning') }}</th>
            </tr>
        </thead>
        <tbody>


            <tr>
                <td>
                    {customer_name}
                </td>
                <th scope="row">
                    {{ __('Customer Name') }}
                </th>
            </tr>

            @if ($templateInfo->mail_type == 'new_booking_notification'
             || $templateInfo->mail_type == 'room_booking_payment_received_for_staff')
                <tr>
                    <td>
                        {booking_link}
                    </td>
                    <th scope="row">
                        {{ __('New Booking Link') }}
                    </th>
                </tr>

                <tr>
                    <td>
                        {pending_booking_link}
                    </td>
                    <th scope="row">
                        {{ __('All Pending Booking Link') }}
                    </th>
                </tr>
            @endif
            @if ($templateInfo->mail_type == 'room_booking_payment_received_for_staff')
                <tr>
                    <td>
                        {staff_name}
                    </td>
                    <th scope="row">
                        {{ __('Staff Name') }}
                    </th>
                </tr>
            @endif

            @if (in_array($templateInfo->mail_type, [
                    'room_booking_for_online_gateway',
                    'room_booking_for_offline_gateway',
                    'room_booking_payment_cancelled',
                    'room_booking_payment_received',
                    'room_booking_status_confirmed',
                    'room_booking_status_rejected',
                    'new_booking_notification',
                    'room_booking_payment_received_for_staff',
                ]))
                <tr>
                    <td>
                        {booking_number}
                    </td>
                    <th scope="row">
                        {{ __('Booking Number') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        {booking_date}
                    </td>
                    <th scope="row">
                        {{ __('Booking Date') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        {number_of_night}
                    </td>
                    <th scope="row">
                        {{ __('Number of Nights') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        {check_in_date}
                    </td>
                    <th scope="row">
                        {{ __('Check in Date') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        {check_out_date}
                    </td>
                    <th scope="row">
                        {{ __('Check out Date') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        {number_of_guests}
                    </td>
                    <th scope="row">
                        {{ __('Number of Guests') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        {room_name}
                    </td>
                    <th scope="row">
                        {{ __('Room Name') }}
                    </th>
                </tr>
                <tr>
                    <td>
                        {room_rent}
                    </td>
                    <th scope="row">
                        {{ __('Room Rent') }}
                    </th>
                </tr>
            @endif
            <tr>
                <td>
                    {website_title}
                </td>
                <th scope="row">
                    {{ __('Website Title') }}
                </th>
            </tr>
        </tbody>
    </table>
</div>
