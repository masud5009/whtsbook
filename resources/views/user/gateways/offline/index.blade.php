@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Offline Gateways') }}</h4>
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
                <a href="#">{{ __('Offline Gateways') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center align-items-start">
                    <div class="card-title d-inline-block mb-2 mb-lg-0 text-nowrap">{{ __('Offline Gateways') }}</div>

                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end ml-lg-auto">
                        <a href="#" class="btn btn-primary mb-2 mb-lg-0" data-toggle="modal"
                            data-target="#createModal"><i class="fas fa-plus"></i> {{ __('Add Gateway') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($ogateways) == 0)
                                <h3 class="text-center">{{ __('NO OFFLINE PAYMENT GATEWAY FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Serial Number') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ogateways as $key => $ogateway)
                                                <tr>
                                                    <td>
                                                        {{ $ogateway->name }}
                                                    </td>
                                                    <td>
                                                        <form id="productForm{{ $ogateway->id }}" class="d-inline-block"
                                                            action="{{ route('user.offline.status') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="ogateway_id"
                                                                value="{{ $ogateway->id }}">
                                                            <input type="hidden" name="type" value="product">
                                                            <select
                                                                class="form-control {{ $ogateway->status == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                name="status"
                                                                onchange="document.getElementById('productForm{{ $ogateway->id }}').submit();">
                                                                <option value="1"
                                                                    {{ $ogateway->status == 1 ? 'selected' : '' }}>
                                                                    {{ __('Active') }}</option>
                                                                <option value="0"
                                                                    {{ $ogateway->status == 0 ? 'selected' : '' }}>
                                                                    {{ __('Deactive') }}</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                    <td>{{ $ogateway->serial_number }} </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm editBtn mb-1" href="#editModal"
                                                            data-toggle="modal" data-ogateway_id="{{ $ogateway->id }}"
                                                            data-name="{{ $ogateway->name }}"
                                                            data-short_description="{{ $ogateway->short_description }}"
                                                            data-instructions="{{ replaceBaseUrl($ogateway->instructions) }}"
                                                            data-is_receipt="{{ $ogateway->is_receipt }}"
                                                            data-serial_number="{{ $ogateway->serial_number }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>

                                                        <form class="deleteForm d-inline-block"
                                                            action="{{ route('user.offline.gateway.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="offline_gateway_id"
                                                                value="{{ $ogateway->id }}">

                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm deleteBtn  mb-1">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-trash"></i>
                                                                </span>
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


    <!-- Create Offline Gateway Modal -->
    @includeIf('user.gateways.offline.create')



    <!-- Edit Package Modal -->
    @includeIf('user.gateways.offline.edit')

@endsection
