@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Failed Messages') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="#">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Failed Messages') }}</a>
            </li>
        </ul>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card-title d-inline-block">{{ __('Failed Messages') }}</div>
                        </div>
                        <div class="col-lg-4  mt-2 mt-lg-0">
                            <button class="btn btn-danger btn-sm float-right mr-2 d-none bulk-delete"
                                data-href="{{ route('user.whatsapp_failed_messages.bulk_delete') }}"><i
                                    class="flaticon-interface-5"></i>
                                {{ __('Delete') }}</button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($messages) == 0)
                                <h3 class="text-center">{{ __('NO MESSAGE FOUND') . '!' }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Customer Number') }}</th>
                                                <th scope="col">{{ __('Received Time') }}</th>
                                                <th scope="col">{{ __('Last Message') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($messages as $message)
                                                @php
                                                    $content = json_decode($message->content, true);
                                                    $lastMessageContent = $content['incoming_message'] ?? '-';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $message->customer_phone }}">
                                                    </td>
                                                    <td>{{ $message->customer_phone }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($message->received_at)->diffForHumans() }}
                                                    </td>
                                                    <td>{{ truncateString($lastMessageContent, 50) }}</td>
                                                    <td>{{ ucfirst($message->status) }}</td>
                                                    <td>
                                                        <a href="https://wa.me/{{ $message->customer_phone }}?text=Hello, our system couldn't respond to your message earlier. Please wait a moment, I will assist you shortly." target="_blank"
                                                            class="btn btn-success btn-sm mr-1">
                                                             <span class="btn-label"><i class="fab fa-whatsapp"></i></span>
                                                            {{ __('Message on WhatsApp') }}
                                                        </a>
                                                        <a class="btn btn-secondary btn-sm mr-1"
                                                            href="{{ route('user.whatsapp_failed_messages.details', ['id' => $message->id]) }}">
                                                            <span class="btn-label"><i class="fas fa-eye"></i></span>
                                                           {{ __('View Failed Messages') }}
                                                        </a>
                                                        <form class="deleteForm d-inline-block"
                                                            action="{{ route('user.whatsapp_failed_messages.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="customer_phone"
                                                                value="{{ $message->customer_phone }}">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm deleteBtn mb-1">
                                                                <span class="btn-label"><i class="fas fa-trash"></i></span>
                                                                {{ __('Delete') }}
                                                            </button>
                                                        </form>
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
@endsection
