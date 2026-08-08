@extends('user.layout')

@section('content')
    <div class="permission-manage-page">
        <div class="page-header">
            <h4 class="page-title">{{ __('Permissions Management') }}</h4>
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
                <li class="separator">
                    <i class="flaticon-right-arrow"></i>
                </li>
                <li class="nav-item">
                    <a href="#">{{ $role->name }}</a>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title d-inline-block">
                            {{ __('Permissions Management') }}
                            <span class="role-chip">{{ $role->name }}</span>
                        </div>
                        <a class="btn btn-info btn-sm float-right d-inline-block"
                            href="{{ route('tenant.staff_management.roles') }}">
                            <span class="btn-label">
                                <i class="fas fa-backward"></i>
                            </span>
                            {{ __('Back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-10 offset-lg-1">
                                @php
                                    $assignedPermissions = is_array($role->permissions) ? $role->permissions : [];
                                @endphp

                                <div class="alert alert-secondary" role="alert">
                                    <p class="mb-1"><strong>{{ __('Dashboard') }}</strong>
                                        {{ __('is always enabled for every role.') }}</p>
                                </div>

                                <form action="{{ route('tenant.staff_management.role.permissions.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="role_id" value="{{ $role->id }}">
                                    <input type="hidden" name="permissions[]" value="Dashboard">

                                    <div class="permission-summary">
                                        <div class="permission-counter">
                                            <span>{{ __('Selected permissions') }}</span>
                                            <span class="badge badge-primary" id="selectedPermissionCount">0</span>
                                        </div>
                                        <div class="permission-search">
                                            <input type="text" class="form-control" id="permissionSearch"
                                                placeholder="{{ __('Search modules or actions').'...' }}">
                                        </div>
                                    </div>

                                    @foreach ($permissionGroups as $groupTitle => $items)
                                        <div class="permission-group-card" data-group-title="{{ strtolower($groupTitle) }}">
                                            <div class="permission-group-title">
                                                <h5>{{ __($groupTitle) }}</h5>
                                                <span class="badge badge-pill">{{ count($items) }} {{ __('Modules') }}</span>
                                            </div>

                                            <div>
                                                @foreach ($items as $item)
                                                    @php
                                                        $permission = $item['permission'];
                                                        $children = $item['children'] ?? [];
                                                        $isChecked = in_array($permission, $assignedPermissions);
                                                    @endphp

                                                    <div class="permission-block permission-module"
                                                        data-search="{{ strtolower($item['label'] . ' ' . $permission . ' ' . collect($children)->pluck('label')->implode(' ')) }}">
                                                        <div class="permission-module-header">
                                                            <div class="selectgroup selectgroup-pills">
                                                                <label class="selectgroup-item mb-0">
                                                                    <input type="checkbox" name="permissions[]"
                                                                        value="{{ $permission }}"
                                                                        class="selectgroup-input permission-parent-trigger"
                                                                        data-target="{{ Illuminate\Support\Str::slug($permission) }}"
                                                                        @checked($isChecked)>
                                                                    <span class="selectgroup-button">{{ __($item['label']) }}</span>
                                                                </label>
                                                            </div>
                                                            @if (!empty($children))
                                                                <div class="permission-module-meta">
                                                                    {{ count($children) }} {{ __('actions') }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if (!empty($children))
                                                            <div class="permission-children border rounded p-3 mt-3 ml-md-4"
                                                                data-parent="{{ Illuminate\Support\Str::slug($permission) }}">
                                                                <div class="small text-muted mb-3">
                                                                    {{ __('Allowed actions under this module') }}
                                                                </div>

                                                                <div class="selectgroup selectgroup-pills">
                                                                    @foreach ($children as $child)
                                                                        <label class="selectgroup-item mb-0">
                                                                            <input type="checkbox" name="permissions[]"
                                                                                value="{{ $child['permission'] }}"
                                                                                class="selectgroup-input permission-child-input"
                                                                                @checked(in_array($child['permission'], $assignedPermissions))>
                                                                            <span
                                                                                class="selectgroup-button">{{ __($child['label']) }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="permission-actionbar">
                                        <div class="permission-actionbar-note">
                                            {{ __('Changes apply immediately for staff accounts assigned to this role.') }}
                                        </div>
                                        <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/tenant/js/permission.js') }}"></script>
@endsection
