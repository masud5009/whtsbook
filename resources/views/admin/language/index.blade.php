@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Languages') }}</h4>
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
                <a href="#">{{ __('Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Languages') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-title d-inline-block">{{ __('Languages') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end gap-3 language_relateBtn">

                                <a href="#" class="btn btn-secondary btn-sm mr-1" data-toggle="modal"
                                    data-target="#addModal">
                                    <span class="btn-label">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    {{ __('Add Frontend Keyword') }}
                                </a>

                                <a href="#" class="btn btn-secondary btn-sm mr-1" data-toggle="modal"
                                    data-target="#addAdminKeywordModal">
                                    <span class="btn-label">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    {{ __('Add Admin Keyword') }}
                                </a>

                                <a href="#" class="btn btn-success btn-sm mr-1" data-toggle="modal"
                                    data-target="#addUserKeywordModal">
                                    <span class="btn-label">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    {{ __('Add User Dashboard Keyword') }}
                                </a>

                                <a href="#" class="btn btn-info btn-sm mx-1" data-toggle="modal"
                                    data-target="#createModal"><i class="fas fa-plus"></i> {{ __('Add Language') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($languages) == 0)
                                <h3 class="text-center">{{ __('NO LANGUAGE FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Code') }}</th>
                                                <th scope="col">{{ __('Default in Website') }}</th>
                                                <th scope="col">{{ __('Default in Dashboard') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($languages as $key => $language)
                                                <tr>
                                                    <td>{{ $loop->iteration + 1 }}</td>
                                                    <td>{{ $language->name }}</td>
                                                    <td>{{ $language->code }}</td>
                                                    <td>
                                                        @if ($language->is_default == 1)
                                                            <strong
                                                                class="badge badge-success">{{ __('Default') }}</strong>
                                                        @else
                                                            <form class="d-inline-block"
                                                                action="{{ route('admin.language.default', $language->id) }}"
                                                                method="post">
                                                                @csrf
                                                                <button class="btn btn-primary btn-sm" type="submit"
                                                                    name="button">{{ __('Make Default') }}</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($language->dashboard_default == 1)
                                                            <strong
                                                                class="badge badge-success">{{ __('Default') }}</strong>
                                                        @else
                                                            <form class="d-inline-block"
                                                                action="{{ route('admin.language.dashboardDefault', $language->id) }}"
                                                                method="post">
                                                                @csrf
                                                                <button class="btn btn-primary btn-sm" type="submit"
                                                                    name="button">{{ __('Make Default') }}</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                                type="button" id="dropdownMenuButton"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                {{ __('Select') }}
                                                            </button>

                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                <a href="{{ route('admin.language.edit', $language->id) }}"
                                                                    class="dropdown-item">
                                                                    {{ __('Edit') }}
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.language.editKeyword', $language->id) }}">
                                                                    {{ __('Edit Admin Frontend Keywords') }}
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.language.admin_dashboard.editKeyword', $language->id) }}">
                                                                    {{ __('Edit Admin Dashboard Keywords') }}
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.language.user_dashboard.editKeyword', $language->id) }}">
                                                                    {{ __('Edit User Dashboard Keywords') }}
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.language.user_frontend.editKeyword', $language->id) }}">
                                                                    {{ __('Edit User Frontend Keywords') }}
                                                                </a>
                                                                <form class="deleteform"
                                                                    action="{{ route('admin.language.delete', $language->id) }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <button type="submit" class="deletebtn px-3">
                                                                        {{ __('Delete') }}
                                                                    </button>
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

            </div>
        </div>
    </div>

    {{-- language keyword for admin modal start --}}
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Frontend Keyword') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="ajaxForm2" action="{{ route('admin.language.add_keyword') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="">{{ __('Keyword') . ' **' }}</label>
                            <input type="text" class="form-control" name="keyword"
                                placeholder="{{ __('Enter Keyword') }}">
                            <p id="err_keyword" class="mt-1 mb-0 text-danger em"></p>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                    <button id="submitBtn2" type="button" class="btn btn-primary btn-sm">
                        {{ __('Submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- language keyword for admin modal end --}}


    {{-- language keyword for admin modal start --}}
    <div class="modal fade" id="addAdminKeywordModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Admin Keyword') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="ajaxForm3" action="{{ route('admin.language.add_keyword.admin.dashboard') }}"
                        method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="">{{ __('Keyword') . ' **' }}</label>
                            <input type="text" class="form-control" name="keyword"
                                placeholder="{{ __('Enter Keyword') }}">
                            <p id="err_rkeyword" class="mt-1 mb-0 text-danger em"></p>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                    <button id="submitBtn3" type="button" class="btn btn-primary btn-sm">
                        {{ __('Submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- language keyword for admin modal end --}}


    {{-- language keyword for User dashboard --}}
    <div class="modal fade" id="addUserKeywordModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add User Dashboard Keyword') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="ajaxForm4" action="{{ route('admin.language.add_keyword.user.dashboard') }}"
                        method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="">{{ __('Keyword') . ' **' }}</label>
                            <input type="text" class="form-control" name="keyword"
                                placeholder="{{ __('Enter Keyword') }}">
                            <p id="err_rkeyword" class="mt-1 mb-0 text-danger em"></p>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                    <button id="submitBtn4" type="button" class="btn btn-primary btn-sm">
                        {{ __('Submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- language keyword for user dashboard --}}

    <!-- Create Language Modal -->
    @includeif('admin.language.create')
@endsection
