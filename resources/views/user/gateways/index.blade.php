@extends('user.layout')

@section('content')
       <div class="page-header">
        <h4 class="page-title">{{ __('Online Gateways') }}</h4>
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
                <a href="#">{{ __('Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Payment Gateways') }}</a>
            </li>
                        <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Online Gateways') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        @foreach ($gateways as $g)
            @include('gateway-card', [
                'keyword' => $g['keyword'],
                'title' => $g['title'],
                'gateway' => $g['gateway'] ?? null,
                'fields' => $g['fields'] ?? [],
                'radios' => $g['radios'] ?? [],
                'updateRoute' => route('user.gateway.update')
            ])
        @endforeach
    </div>
@endsection
