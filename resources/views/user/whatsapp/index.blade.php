@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Whatsapp Numbers') }}</h4>
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
                <a href="#">{{ __('Whatsapp Numbers') }}</a>
            </li>
        </ul>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card callback-panel mb-4">
                <button type="button" class="callback-panel__header callback-panel__header--compact" data-toggle="collapse"
                    data-target="#callbackPanelCollapse" aria-expanded="true" aria-controls="callbackPanelCollapse">
                    <span class="callback-panel__header-copy callback-panel__header-copy--title-only">
                        <span class="callback-panel__icon mr-3">
                            <i class="fas fa-link"></i>
                        </span>
                        <span>
                            <span class="callback-panel__title">{{ __('WhatsApp Configuration URLs') }}</span>
                        </span>
                    </span>
                    <span class="callback-panel__panel-icon">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </button>

                <div id="callbackPanelCollapse" class="collapse">
                    <div class="card-body px-4 pb-4 pt-0">
                        @php
                            $callbackUrls = [
                                [
                                    'key' => 'webhook',
                                    'label' => __('Webhook Callback URL'),
                                    'value' => url('/whatsapp/webhook'),
                                    'summary' => __('Main webhook endpoint for WhatsApp events'),
                                    'note' => __(
                                        'Use this URL as the main WhatsApp webhook callback so Meta can send verification requests and incoming message events to your application.',
                                    ),
                                ],
                                [
                                    'key' => 'terms',
                                    'label' => __('Terms and Conditions URL'),
                                    'value' => url('/terms-and-conditions.html'),
                                    'summary' => __('Public terms page for Meta review and verification'),
                                    'note' => __(
                                        'Use this public URL when WhatsApp or Meta asks for your Terms and Conditions page during app review or business verification.',
                                    ),
                                ],
                                [
                                    'key' => 'privacy',
                                    'label' => __('Privacy Policy URL'),
                                    'value' => url('/privacy-policy.html'),
                                    'summary' => __('Public privacy page explaining data usage'),
                                    'note' => __(
                                        'Use this public URL when WhatsApp or Meta asks for your Privacy Policy page so users can see how their data is collected and used.',
                                    ),
                                ],
                                [
                                    'key' => 'dataDeletion',
                                    'label' => __('Data Deletion URL'),
                                    'value' => url('/data-deletion.html'),
                                    'summary' => __('Public instructions for account or data deletion'),
                                    'note' => __(
                                        'Use this public URL when WhatsApp or Meta asks for your data deletion instructions so users know how to request account or data removal.',
                                    ),
                                ],
                            ];
                        @endphp

                        <p class="callback-panel__text mb-3">
                            {{ __('Expand each section to copy the required webhook and compliance URLs for your WhatsApp integration') }}
                        </p>

                        <div class="callback-panel__accordion" id="callbackUrlAccordion">
                            @foreach ($callbackUrls as $callbackUrl)
                                <div class="callback-panel__accordion-item">
                                    <button type="button"
                                        class="callback-panel__accordion-trigger {{ $loop->first ? '' : 'collapsed' }}"
                                        data-toggle="collapse" data-target="#{{ $callbackUrl['key'] }}UrlCollapse"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="{{ $callbackUrl['key'] }}UrlCollapse">
                                        <span class="callback-panel__accordion-copy">
                                            <span class="callback-panel__accordion-title">{{ $callbackUrl['label'] }}</span>
                                            <span
                                                class="callback-panel__accordion-summary">{{ $callbackUrl['summary'] }}</span>
                                        </span>
                                        <span class="callback-panel__accordion-icon">
                                            <i class="fas fa-chevron-down"></i>
                                        </span>
                                    </button>

                                    <div id="{{ $callbackUrl['key'] }}UrlCollapse"
                                        class="collapse {{ $loop->first ? 'show' : '' }}"
                                        data-parent="#callbackUrlAccordion">
                                        <div class="callback-panel__accordion-body">
                                            <div class="callback-panel__input-group">
                                                <input type="text" id="{{ $callbackUrl['key'] }}UrlInput"
                                                    class="form-control callback-panel__input"
                                                    value="{{ $callbackUrl['value'] }}" readonly>

                                                <button type="button" class="btn btn-outline-primary callback-copy-btn"
                                                    data-copy-target="#{{ $callbackUrl['key'] }}UrlInput">
                                                    <i class="fas fa-copy mr-2"></i>
                                                    <span>{{ __('Copy URL') }}</span>
                                                </button>
                                            </div>

                                            <p class="callback-panel__note">
                                                <strong>{{ __('Note:') }}</strong> {{ $callbackUrl['note'] }}
                                            </p>

                                            @if ($callbackUrl['key'] === 'webhook')
                                                <div class="callback-panel__verify-box">
                                                    <div class="callback-panel__verify-title">
                                                        <i class="fas fa-key"></i>
                                                        <span>{{ __('Verify Token') }}</span>
                                                    </div>

                                                    <p class="callback-panel__verify-text">
                                                        {{ __('There is no separate URL for the verify token. When Meta asks for a Verify Token, use the same token from the Add Number or Edit Number form for the WhatsApp number you are connecting.') }}
                                                    </p>

                                                    <p class="callback-panel__verify-text">
                                                        <strong>{{ __('Important:') }}</strong>
                                                        {{ __('The token in Meta must match your saved token exactly, otherwise webhook verification will fail.') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div
                    class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center align-items-start">
                    <div class="card-title d-inline-block mb-2 mb-lg-0 text-nowrap">{{ __('Whatsapp Numbers') }}</div>

                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end ml-lg-auto">
                        <button class="btn btn-danger d-none bulk-delete mr-2 mb-2 mb-lg-0"
                            data-href="{{ route('user.whatsapp_list_bulk_delete') }}"><i class="flaticon-interface-5"></i>
                            {{ __('Delete') }}</button>

                        <a href="#" data-toggle="modal" data-target="#createModal"
                            class="btn btn-primary mb-2 mb-lg-0">
                            <i class="fas fa-plus"></i>
                            {{ __('Add New Number') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($wp_infos) == 0)
                                <h3 class="text-center">{{ __('NO NUMBER FOUND') . '!' }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('From Number') }}</th>
                                                <th scope="col">{{ __('Business Number') }}</th>
                                                <th scope="col">{{ __('Booking Fields') }}</th>
                                                <th scope="col">{{ __('Hotel Information') }}</th>
                                                <th scope="col">{{ __('Auto Response Message') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($wp_infos as $wp_info)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $wp_info->id }}">
                                                    </td>
                                                    <td>
                                                        {{ $wp_info->wp_from_number }}
                                                    </td>
                                                    <td>
                                                        {{ $wp_info->wp_business_acc_number }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('user.configure_booking_fields', ['wp_id' => $wp_info->id]) }}"
                                                            class="btn btn-info btn-sm" data-toggle="tooltip"
                                                            title="{{ __('Create custom fields that the WhatsApp bot will use to collect information from customers during booking. (e.g. name, email, phone, etc.)') }}">
                                                            <i class="fas fa-cogs mr-1"></i>
                                                            {{ __('Configure Booking Fields') }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('user.whatsapp_share_information', ['wp_id' => $wp_info->id]) }}"
                                                            class="btn btn-info btn-sm" data-toggle="tooltip"
                                                            title="{{ __('If customer ask to share location, number, etc. via WhatsApp') }}">
                                                            <i class="fas fa-cogs mr-1"></i>
                                                            {{ __('Manage') }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('user.whatsapp_template_messages', ['wp_id' => $wp_info->id]) }}"
                                                            class="btn btn-info btn-sm" data-toggle="tooltip"
                                                            title="{{ __('Configure auto-response templates (e.g. booking placed etc.)') }}">
                                                            <i class="fas fa-cogs mr-1"></i>
                                                            {{ __('Manage Templates') }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm mr-1 editBtn" href="#"
                                                            data-toggle="modal" data-target="#editModal"
                                                            data-id="{{ $wp_info->id }}"
                                                            data-whatsapp_number_id="{{ $wp_info->wp_phone_number }}"
                                                            data-whatsapp_business_account_number="{{ $wp_info->wp_business_acc_number }}"
                                                            data-whatsapp_verify_token="{{ $wp_info->wp_verify_token }}"
                                                            data-whatsapp_access_token="{{ $wp_info->wp_access_token }}"
                                                            data-whatsapp_from_number="{{ $wp_info->wp_from_number }}"
                                                            data-status="{{ $wp_info->status }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                            {{ __('Edit') }}
                                                        </a>
                                                        <form class="deleteForm d-inline-block"
                                                            action="{{ route('user.whatsapp_list_delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="id"
                                                                value="{{ $wp_info->id }}">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm deleteBtn mb-1">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-trash"></i>
                                                                </span>
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
    @include('user.whatsapp.create')
    @include('user.whatsapp.edit')
@endsection

@section('script')
    <script>
        const __copyUrlBtn__ = @json(__('Copied'));
        const __copyUrlSuccessAlert__ = @json(__('URL copied successfully.'));
        const __copyUrlErrorAlert__ = @json(__('Unable to copy URL.'));
    </script>
    <script src="{{ asset('assets/tenant/js/whatsapp-configure.js') }}"></script>
@endsection
