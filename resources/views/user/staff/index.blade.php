@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Staffs') }}</h4>
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
                <a href="#">{{ __('Staff Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Staffs') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info" role="alert">
                {{ __('Staff login URL') }}:
                <a href="{{ route('staff.login') }}" target="_blank">{{ route('staff.login') }}</a>
            </div>

            @if (count($roles) == 0)
                <div class="alert alert-warning" role="alert">
                    {{ __('Create a role before adding staff members.') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title d-inline-block">{{ __('Staffs') }}</div>
                    @if (count($roles) > 0)
                        <a href="#" class="btn btn-primary float-right" data-toggle="modal"
                            data-target="#createModal">
                            <i class="fas fa-plus"></i> {{ __('Add Staff') }}
                        </a>
                    @else
                        <button type="button" class="btn btn-primary float-right" disabled>
                            <i class="fas fa-plus"></i> {{ __('Add Staff') }}
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($staffs) == 0)
                                <h3 class="text-center">{{ __('NO STAFF FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Username') }}</th>
                                                <th scope="col">{{ __('Email') }}</th>
                                                <th scope="col">{{ __('Role') }}</th>
                                                <th scope="col">{{ __('Permissions') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($staffs as $staff)
                                                @php
                                                    $staffPermissions = is_array(optional($staff->roleInfo)->permissions)
                                                        ? count($staff->roleInfo->permissions)
                                                        : 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $staff->name }}</td>
                                                    <td>{{ $staff->username }}</td>
                                                    <td>{{ $staff->email }}</td>
                                                    <td>
                                                        {{ optional($staff->roleInfo)->name ?: __('No Role Assigned') }}
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">{{ $staffPermissions }}</span>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm editBtn mb-1" href="#editModal"
                                                            data-toggle="modal" data-staff_id="{{ $staff->id }}"
                                                            data-name="{{ $staff->name }}"
                                                            data-username="{{ $staff->username }}"
                                                            data-email="{{ $staff->email }}"
                                                            data-role="{{ $staff->role }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>

                                                        <form class="deleteForm d-inline-block"
                                                            action="{{ route('tenant.staff_management.staff.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="staff_id"
                                                                value="{{ $staff->id }}">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm deleteBtn mb-1">
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

    @if (count($roles) > 0)
        @includeIf('user.staff.create')
        @includeIf('user.staff.edit')
    @endif
@endsection
