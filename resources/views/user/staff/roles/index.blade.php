@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Roles & Permissions') }}</h4>
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
                <a href="#">{{ __('Roles & Permissions') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center align-items-start">
                    <div class="card-title d-inline-block mb-2 mb-lg-0 text-nowrap">{{ __('Roles') }}</div>

                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end ml-lg-auto">
                        <a href="#" class="btn btn-primary mb-2 mb-lg-0" data-toggle="modal"
                            data-target="#createModal">
                            <i class="fas fa-plus"></i> {{ __('Add Role') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($roles) == 0)
                                <h3 class="text-center">{{ __('NO ROLE FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Assigned Staffs') }}</th>
                                                <th scope="col">{{ __('Permissions') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($roles as $role)
                                                @php
                                                    $permissionCount = is_array($role->permissions)
                                                        ? count($role->permissions)
                                                        : 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $role->name }}</td>
                                                    <td>
                                                        <span class="badge badge-secondary">{{ $role->staffs_count }}</span>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-info btn-sm mb-1"
                                                            href="{{ route('tenant.staff_management.role.permissions.manage', $role->id) }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-user-shield"></i>
                                                            </span>
                                                            {{ __('Manage') }}
                                                        </a>
                                                        <span class="badge badge-primary">{{ $permissionCount }}</span>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm editBtn mb-1" href="#editModal"
                                                            data-toggle="modal" data-role_id="{{ $role->id }}"
                                                            data-name="{{ $role->name }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>

                                                        <form class="deleteForm d-inline-block"
                                                            action="{{ route('tenant.staff_management.role.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="role_id"
                                                                value="{{ $role->id }}">
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

    @includeIf('user.staff.roles.create')
    @includeIf('user.staff.roles.edit')
@endsection
