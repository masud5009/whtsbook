@extends('admin.layout')

@php
    $selLang = \App\Models\Language::where('code', request()->input('language'))->first();
@endphp
@if (!empty($selLang) && $selLang->rtl == 1)
    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Platform Modules') }}</h4>
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
                <a href="#">{{ __('Pages') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Home Page') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Platform Modules') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <div class="card-title d-inline-block">{{ __('Platform Modules') }}</div>
                        </div>
                        <div class="col-lg-3">
                            @if (!empty($langs))
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text"><i class="fas fa-language"
                                                style="color: #1572E8 ; font-size:20px "></i></div>
                                    </div>
                                    <select name="language" class="form-control"
                                        onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">
                                        <option value="" selected disabled>{{ __('Select a Language') }}</option>
                                        @foreach ($langs as $lang)
                                            <option value="{{ $lang->code }}"
                                                {{ $lang->code == request()->input('language') ? 'selected' : '' }}>
                                                {{ $lang->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-5 mt-2 mt-lg-0">
                            <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                data-target="#createModal"><i class="fas fa-plus"></i> {{ __('Add Module') }}</a>
                            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                data-href="{{ route('admin.platform_module.bulk.delete') }}"><i
                                    class="flaticon-interface-5"></i>
                                {{ __('Delete') }}</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($platformModules) == 0)
                                <h3 class="text-center">{{ __('NO PLATFORM MODULE FOUND!') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Icon') }}</th>
                                                <th scope="col">{{ __('Image') }}</th>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Subtitle') }}</th>
                                                <th scope="col">{{ __('Serial Number') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($platformModules as $module)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $module->id }}">
                                                    </td>
                                                    <td>
                                                        <img src="{{ $module->icon ? asset('assets/front/img/platform_modules/' . $module->icon) : asset('assets/admin/img/noimage.jpg') }}"
                                                            class="max-width-60" alt="icon">
                                                    </td>
                                                    <td>
                                                        <img src="{{ $module->image ? asset('assets/front/img/platform_modules/' . $module->image) : asset('assets/admin/img/noimage.jpg') }}"
                                                            class="max-w-130" alt="module-image">
                                                    </td>
                                                    <td>
                                                        {{ strlen($module->title) > 40 ? mb_substr($module->title, 0, 40, 'UTF-8') . '...' : $module->title }}
                                                    </td>
                                                    <td>
                                                        {{ strlen($module->subtitle) > 40 ? mb_substr($module->subtitle, 0, 40, 'UTF-8') . '...' : $module->subtitle }}
                                                    </td>
                                                    <td>{{ $module->serial_number }}</td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm editbtn mb-1" href="#editModal"
                                                            data-toggle="modal"
                                                            data-platform_module_id="{{ $module->id }}"
                                                            data-title="{{ $module->title }}"
                                                            data-subtitle="{{ $module->subtitle }}"
                                                            data-serial_number="{{ $module->serial_number }}"
                                                            data-icon="{{ $module->icon ? asset('assets/front/img/platform_modules/' . $module->icon) : asset('assets/admin/img/noimage.jpg') }}"
                                                            data-image="{{ $module->image ? asset('assets/front/img/platform_modules/' . $module->image) : asset('assets/admin/img/noimage.jpg') }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>
                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('admin.platform_module.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="platform_module_id"
                                                                value="{{ $module->id }}">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm deletebtn mb-1">
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


    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Platform Module') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxForm" class="modal-form create" action="{{ route('admin.platform_module.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="">{{ __('Language') }} <span class="text-danger">**</span></label>
                            <select name="language_id" class="form-control">
                                <option value="" selected disabled>{{ __('Select a language') }}</option>
                                @foreach ($langs as $lang)
                                    <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                @endforeach
                            </select>
                            <p id="err_language_id" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-12 mb-2 pl-0">
                                        <label for="icon"><strong>{{ __('Icon') }} <span
                                                    class="text-danger">**</span></strong></label>
                                    </div>
                                    <div class="col-md-12 showImage mb-3 pl-0">
                                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                            class="img-thumbnail">
                                    </div>
                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                        {{ __('Choose Icon') }}
                                        <input type="file" class="img-input" name="icon">
                                    </div>
                                    <p id="err_icon" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-12 mb-2 pl-0">
                                        <label for="image"><strong>{{ __('Image') }} <span
                                                    class="text-danger">**</span></strong></label>
                                    </div>
                                    <div class="col-md-12 showImage3 mb-3 pl-0">
                                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                            class="img-thumbnail">
                                    </div>
                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image3">
                                        {{ __('Choose Image') }}
                                        <input type="file" class="img-input" name="image">
                                    </div>
                                    <p id="err_image" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Title') }} <span class="text-danger">**</span></label>
                            <input type="text" class="form-control" name="title" value=""
                                placeholder="{{ __('Enter title') }}">
                            <p id="err_title" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Subtitle') }} <span class="text-danger">**</span></label>
                            <textarea class="form-control" name="subtitle" rows="3" placeholder="{{ __('Enter subtitle') }}"></textarea>
                            <p id="err_subtitle" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                            <input type="number" class="form-control ltr" name="serial_number" value=""
                                placeholder="{{ __('Enter Serial Number') }}">
                            <p id="err_serial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning">
                                <small>{{ __('The higher the serial number is, the later the module will be shown.') }}</small>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                    <button id="submitBtn" type="button" class="btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Platform Module') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxEditForm" action="{{ route('admin.platform_module.update') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input id="in_platform_module_id" type="hidden" name="platform_module_id" value="">

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-12 mb-2 pl-0">
                                        <label for="icon"><strong>{{ __('Icon') }}</strong></label>
                                    </div>
                                    <div class="col-md-12 showImage2 mb-3 pl-0">
                                        <img id="in_icon" src="{{ asset('assets/admin/img/noimage.jpg') }}"
                                            alt="..." class="img-thumbnail image">
                                    </div>
                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image2">
                                        {{ __('Choose Icon') }}
                                        <input type="file" class="img-input" name="icon">
                                    </div>
                                    <p id="editErr_icon" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-12 mb-2 pl-0">
                                        <label for="image"><strong>{{ __('Image') }}</strong></label>
                                    </div>
                                    <div class="col-md-12 showImage4 mb-3 pl-0">
                                        <img id="in_image" src="{{ asset('assets/admin/img/noimage.jpg') }}"
                                            alt="..." class="img-thumbnail image">
                                    </div>
                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image4">
                                        {{ __('Choose Image') }}
                                        <input type="file" class="img-input" name="image">
                                    </div>
                                    <p id="editErr_image" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Title') }} <span class="text-danger">**</span></label>
                            <input id="in_title" type="text" class="form-control" name="title" value=""
                                placeholder="{{ __('Enter title') }}">
                            <p id="editErr_title" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Subtitle') }} <span class="text-danger">**</span></label>
                            <textarea id="in_subtitle" class="form-control" name="subtitle" rows="3"
                                placeholder="{{ __('Enter subtitle') }}"></textarea>
                            <p id="editErr_subtitle" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                            <input id="in_serial_number" type="number" class="form-control ltr" name="serial_number"
                                value="" placeholder="{{ __('Enter Serial Number') }}">
                            <p id="editErr_serial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning">
                                <small>{{ __('The higher the serial number is, the later the module will be shown.') }}</small>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                    <button id="updateBtn" type="button" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
