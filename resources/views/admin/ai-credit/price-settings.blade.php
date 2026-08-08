@extends('admin.layout')
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Price Settings') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('AI Credits') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Price Settings') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Price Settings') }}</div>
                </div>
                <div class="card-body">
                    <div class="col-lg-6 mx-auto">
                        <form action="{{ route('admin.ai-credit.price-update') }}" method="POST" id="PricingForm">
                            @csrf

                            <div class="alert alert-info py-2 text-dark">
                                {{ __('Currently selected AI provider') }}:
                                <strong>{{ ucfirst($current_ai_provider) }}</strong>
                            </div>

                            <label class="mb-2">{{ __('Gemini Credit Rate') }}</label>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="input-group flex-grow-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">1 {{ __('Credit') }} =</span>
                                    </div>

                                    <input type="number" step="0.00000001" min="0" name="gemini_credit_price"
                                        class="form-control ltr"
                                        value="{{ old('gemini_credit_price', $gemini_price_per_token) }}"
                                        placeholder="e.g. 0.00000200" required>

                                    <div class="input-group-append">
                                        <span class="input-group-text">{{ $base_currency_text }}</span>
                                    </div>
                                </div>
                            </div>
                            @error('gemini_credit_price')
                                <p class="text-danger mb-2">{{ $message }}</p>
                            @enderror

                            <label class="mb-2 mt-3">{{ __('OpenAI Credit Rate') }}</label>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="input-group flex-grow-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">1 {{ __('Token') }} =</span>
                                    </div>

                                    <input type="number" step="0.00000001" min="0" name="openai_credit_price"
                                        class="form-control ltr"
                                        value="{{ old('openai_credit_price', $openai_price_per_token) }}"
                                        placeholder="e.g. 0.00000200" required>

                                    <div class="input-group-append">
                                        <span class="input-group-text">{{ $base_currency_text }}</span>
                                    </div>
                                </div>
                            </div>
                            @error('openai_credit_price')
                                <p class="text-danger mb-2">{{ $message }}</p>
                            @enderror

                            <p class="text-warning mb-0">
                                {{ __('Specify how many') .' '. $base_currency_text .' '. __('you want to charge per AI credit') }}
                            </p>
                            <p class="text-info mb-0 mt-2">
                                {{ __('The active AI provider price will be used for new token purchases and manual top-ups.') }}
                            </p>
                        </form>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" form="PricingForm" class="btn btn-primary">
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
