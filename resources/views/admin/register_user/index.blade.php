@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">
            {{ __('Registered Users') }}
        </h4>
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
                <a href="#">{{ __('Users Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Registered Users') }}</a>
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
                                {{ __('Registered Users') }}
                            </div>
                        </div>
                        <div class="col-lg-6 mt-2 mt-lg-0 d-block d-lg-flex justify-content-end gap-3">
                            <button class="btn btn-danger float-lg-right float-none btn-sm mr-2 mb-1 d-none bulk-delete"
                                data-href="{{ route('register.user.bulk.delete') }}"><i class="flaticon-interface-5"></i>
                                {{ __('Delete') }}</button>

                            <button class="btn btn-primary float-lg-right float-none btn-sm mr-2 mb-1" data-toggle="modal"
                                data-target="#addUserModal"><i class="fas fa-plus"></i> {{ __('Add User') }}</button>

                            <form action="{{ url()->full() }}" class="float-lg-right float-none mr-2">
                                <input type="text" name="term" class="form-control min-w-250 "
                                    value="{{ request()->input('term') }}"
                                    placeholder="{{ __('Search by Username / Email') }}">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">

                            @if (count($users) == 0)
                                <h3 class="text-center">{{ __('NO USER FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3 mb-5">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Username') }}</th>
                                                <th scope="col">{{ __('Email') }}</th>
                                                <th scope="col">{{ __('Email Status') }}</th>
                                                <th scope="col">{{ __('Account') }}</th>
                                                <td scope="col">{{ __('Actions') }}</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $key => $user)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $user->id }}">
                                                    </td>
                                                    <td>{{ $user->username }}</td>
                                                    <td>{{ $user->email }}</td>

                                                    <td>
                                                        <form id="emailForm{{ $user->id }}" class="d-inline-block"
                                                            action="{{ route('register.user.email') }}" method="post">
                                                            @csrf
                                                            <select
                                                                class="min-width-100 form-control form-control-sm {{ strtolower($user->email_verified) == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                name="email_verified"
                                                                onchange="document.getElementById('emailForm{{ $user->id }}').submit();">
                                                                <option value="1"
                                                                    {{ strtolower($user->email_verified) == 1 ? 'selected' : '' }}>
                                                                    {{ __('Verified') }}</option>
                                                                <option value="0"
                                                                    {{ strtolower($user->email_verified) == 0 ? 'selected' : '' }}>
                                                                    {{ __('Unverified') }}</option>
                                                            </select>
                                                            <input type="hidden" name="user_id"
                                                                value="{{ $user->id }}">
                                                        </form>
                                                    </td>

                                                    <td>
                                                        <form id="userFromban{{ $user->id }}" class="d-inline-block"
                                                            action="{{ route('register.user.ban') }}" method="post">
                                                            @csrf
                                                            <select
                                                                class="min-width-80 form-control form-control-sm {{ $user->status == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                name="status"
                                                                onchange="document.getElementById('userFromban{{ $user->id }}').submit();">
                                                                <option value="1"
                                                                    {{ $user->status == 1 ? 'selected' : '' }}>
                                                                    {{ __('Active') }}
                                                                </option>
                                                                <option value="0"
                                                                    {{ $user->status == 0 ? 'selected' : '' }}>
                                                                    {{ __('Deactive') }}
                                                                </option>
                                                            </select>
                                                            <input type="hidden" name="user_id"
                                                                value="{{ $user->id }}">
                                                        </form>
                                                    </td>


                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-info btn-sm dropdown-toggle"
                                                                type="button" id="dropdownMenuButton{{ $user->id }}"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                {{ __('Select') }}
                                                            </button>
                                                            <div class="dropdown-menu"
                                                                aria-labelledby="dropdownMenuButton{{ $user->id }}">

                                                                <a class="dropdown-item"
                                                                    href="{{ route('register.user.view', $user->id) }}">{{ __('Details') }}
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.payment-log.index', ['username' => $user->username]) }}">{{ __('Payment Log') }}</a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('register.user.changePass', $user->id) }}">{{ __('Change Password') }}</a>

                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.register.topup-ai-crdits', $user->id) }}">{{ __('AI Credit Topups') }}
                                                                </a>

                                                                <form class="deleteform d-block"
                                                                    action="{{ route('register.user.delete') }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="user_id"
                                                                        value="{{ $user->id }}">
                                                                    <button type="submit" class="deletebtn">
                                                                        {{ __('Delete') }}
                                                                    </button>
                                                                </form>

                                                                <form class="d-block"
                                                                    action="{{ route('register.user.secretLogin') }}"
                                                                    method="post" target="_blank">
                                                                    @csrf
                                                                    <input type="hidden" name="user_id"
                                                                        value="{{ $user->id }}">
                                                                    <button class="dropdown-item mb-2 "
                                                                        style="cursor:pointer"
                                                                        role="button">{{ __('Secret Login') }}</button>
                                                                </form>

                                                            </div>
                                                        </div>
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
                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $users->appends(['term' => request()->input('term')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add User') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('register.user.store') }}" method="POST" id="ajaxForm">
                        @csrf
                        <div class="form-group">
                            <label for="">{{ __('Username') }} <span class="text-danger">**</span></label>
                            <input class="form-control" type="text" name="username">
                            <p id="err_username" class="text-danger mb-0 em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Email') }} <span class="text-danger">**</span></label>
                            <input class="form-control" type="email" name="email">
                            <p id="err_email" class="text-danger mb-0 em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Password') }} <span class="text-danger">**</span></label>
                            <input class="form-control" type="password" name="password">
                            <p id="err_password" class="text-danger mb-0 em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Confirm Password') }} <span
                                    class="text-danger">**</span></label>
                            <input class="form-control" type="password" name="password_confirmation">
                            <p id="err_password_confirmation" class="text-danger mb-0 em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Package / Plan') }} <span class="text-danger">**</span></label>
                            <select name="package_id" class="form-control">
                                <option disabled selected>{{ __('Select Package/Plan') }}</option>
                                @if (!empty($packages))
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}">
                                            {{ __($package->title) }} ({{ __($package->term) }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p id="err_package_id" class="text-danger mb-0 em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Payment Gateway') }} <span class="text-danger">**</span></label>
                            <select name="payment_gateway" class="form-control">
                                <option disabled selected>{{ __('Select Payment Gateway') }}</option>
                                @if (!empty($gateways))
                                    @foreach ($gateways as $gateway)
                                        <option value="{{ $gateway->name }}">
                                            {{ $gateway->name == 'MyFatoorah' ? $gateway->name : __($gateway->name) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p id="err_payment_gateway" class="text-danger mb-0 em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Publicly Hidden') }} <span class="text-danger">**</span></label>
                            <select name="online_status" class="form-control">
                                <option value="1">{{ __('No') }}</option>
                                <option value="0">{{ __('Yes') }}</option>
                            </select>
                            <p id="err_online_status" class="text-danger mb-0 em"></p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-center">
                    <button id="submitBtn" type="button" class="btn btn-primary">{{ __('Add User') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
