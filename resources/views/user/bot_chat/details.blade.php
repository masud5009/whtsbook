@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Failed Messages') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('user.whatsapp_failed_messages') }}">{{ __('Failed Messages') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Message Details') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">{{ __('Messages From') }}:
                                <strong class="text-primary">[{{ $message->customer_phone }}]</strong>

                            </h5>
                        </div>
                        <div>
                            <a href="https://wa.me/{{ $message->customer_phone }}?text=Hello, our system couldn't respond to your message earlier. Please wait a moment, I will assist you shortly."
                                    target="_blank" class="btn btn-success btn-sm mr-1 text-white">
                                    <span class="btn-label"><i class="fab fa-whatsapp"></i></span>
                                    {{ __('Message on WhatsApp') }}
                                </a>
                            <a href="{{ route('user.whatsapp_failed_messages') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-backward mr-1"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-striped mt-3" id="basic-datatables">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('Time') }}</th>
                                    <th scope="col">{{ __('Message') }}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allFailedMessages as $msg)
                                    @php
                                        $content = json_decode($msg->content, true);
                                        $incomingMessage =
                                            $content['incoming_message'] ?? __('No message content available');
                                        $isFailed = $msg->status == 'failed';
                                        $receivedTime = date('h:i A', strtotime($msg->received_at));
                                        $receivedDate = date('d M Y', strtotime($msg->received_at));
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($isFailed)
                                                <span class="badge badge-danger">{{ __('Failed') }}</span>
                                            @else
                                                <span class="badge badge-success">{{ __('Resolved') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="message-time" data-toggle="tooltip"
                                                title="{{ $receivedTime . ', ' . $receivedDate }}">
                                                {{ \Carbon\Carbon::parse($msg->received_at)->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="message-preview">
                                                {{ Str::limit($incomingMessage, 150) }}
                                                @if (strlen($incomingMessage) > 150)
                                                    <button type="button" class="btn btn-link btn-sm p-0"
                                                        data-toggle="modal" data-target="#messageModal{{ $msg->id }}">
                                                        {{ __('Show more') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($isFailed)
                                                <form
                                                    action="{{ route('user.whatsapp_message_status_update', ['id' => $msg->id]) }}"
                                                    method="POST" class="mr-1">
                                                    @csrf
                                                    <input type="hidden" name="status" value="replied">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <span class="btn-label"><i class="fas fa-check"></i></span>
                                                        {{ __('Mark as Resolved') }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    {{ __('Resolved') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Message Modal -->
                                    <div class="modal fade" id="messageModal{{ $msg->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="messageModalLabel{{ $msg->id }}"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="messageModalLabel{{ $msg->id }}">
                                                        {{ __('Full Message') }}
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>{{ __('Customer') }}:</strong><br>
                                                            {{ $msg->customer_phone }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>{{ __('Time') }}:</strong><br>
                                                            {{ $receivedTime }}, {{ $receivedDate }}
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>{{ __('Status') }}:</strong>
                                                        @if ($isFailed)
                                                            <span
                                                                class="badge badge-danger ml-2">{{ __('Failed') }}</span>
                                                        @else
                                                            <span
                                                                class="badge badge-success ml-2">{{ __('Resolved') }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="p-3 bg-light rounded">
                                                        <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;">{{ $incomingMessage }}</pre>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">{{ __('Close') }}</button>
                                                    @if ($isFailed)
                                                        <form
                                                            action="{{ route('user.whatsapp_message_status_update', ['id' => $msg->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="status" value="replied">
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-check mr-1"></i>
                                                                {{ __('Mark as Resolved') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                                <div>{{ __('No failed messages found') }}</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
