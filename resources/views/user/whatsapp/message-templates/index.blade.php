@extends('user.layout')
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Manage Templates') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Whatsapp Numbers') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Manage Templates') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card-title">
                                {{ __('Message Templates') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($templates) == 0)
                                <h3 class="text-center">{{ __('NO TEMPLATE FOUND!') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Event Type') }}</th>
                                                <th scope="col">{{ __('Message') }}</th>
                                                <th scope="col">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($templates as $template)
                                                @php
                                                    if ($template->event_type == 'booking_placed') {
                                                        $text = __(
                                                            'When booking is placed from whatsapp without payment',
                                                        );
                                                    } elseif ($template->event_type == 'system_fallback') {
                                                        $text = __('When AI or Whatsapp cannot process user message');
                                                    } elseif ($template->event_type == 'send_payment_link') {
                                                        $text = __('When sending payment link to customer');
                                                    } elseif ($template->event_type == 'booking_status_update') {
                                                        $text = __('When booking status is update from admin panel');
                                                    } elseif ($template->event_type == 'refund_message') {
                                                        $text = __('When a booking refund is processed for the customer');
                                                    }elseif ($template->event_type == 'price_increased') {
                                                        $text = __('When booking price increases after update and extra payment is needed');
                                                    }elseif ($template->event_type == 'price_decreased') {
                                                        $text = __('When booking price decreases after update and refund is applicable');
                                                    }elseif($template->event_type == 'payment_complete') {
                                                        $text = __('Sent after payment confirmation to notify the customer and share next steps');
                                                    }
                                                    else {
                                                        $text = '';
                                                    }
                                                    $templateType = Str::ucfirst(
                                                        str_replace('_', ' ', $template->event_type),
                                                    );
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td width="30%">
                                                        {{ __($templateType) }}
                                                        <br>
                                                        <span class="text-primary">({{ $text }})</span>
                                                    </td>
                                                    <td width="50%">{{ truncateString($template->message, 200) }}</td>
                                                    <td width="20%">
                                                        <a class="btn btn-secondary btn-sm editBtn" href=""
                                                            data-toggle="modal"
                                                            data-target="#editTemplateModal{{ $template->event_type }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                            {{ __('Edit') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach ($templates as $template)
        @include('user.whatsapp.message-templates.edit')
    @endforeach
@endsection
