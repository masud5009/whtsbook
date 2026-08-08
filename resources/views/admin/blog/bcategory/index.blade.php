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
        <h4 class="page-title">{{ __('Categories') }}</h4>
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
                <a href="#">{{ __('Blog') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Categories') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <div class="card-title d-inline-block">{{ __('Categories') }}</div>
                        </div>
                        <div class="col-lg-3">
                            @if (!empty($langs))
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="fas fa-language" style="color: #1572E8 ; font-size:20px "></i></div>
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
                        <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
                            <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                data-target="#createModal"><i class="fas fa-plus"></i> {{ __('Add Category') }}</a>
                            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                data-href="{{ route('admin.bcategory.bulk.delete') }}"><i class="flaticon-interface-5"></i>
                                {{ __('Delete') }}</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($bcategorys) == 0)
                                <h3 class="text-center">{{ __('NO BLOG CATEGORY FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Serial Number') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bcategorys as $key => $bcategory)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $bcategory->id }}">
                                                    </td>
                                                    <td>{{ $bcategory->name }}</td>
                                                    <td>
                                                        @if ($bcategory->status == 1)
                                                            <h2 class="d-inline-block"><span
                                                                    class="badge badge-success">{{ __('Active') }}</span>
                                                            </h2>
                                                        @else
                                                            <h2 class="d-inline-block"><span
                                                                    class="badge badge-danger">{{ __('Deactive') }}</span>
                                                            </h2>
                                                        @endif
                                                    </td>
                                                    <td>{{ $bcategory->serial_number }}</td>

                                                    <td>
                                                        <a class="btn btn-secondary btn-sm  mb-1"
                                                            href="{{ route('admin.bcategory.edit',['id'=>$bcategory->id,'language'=>$default->code]) }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>


                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('admin.bcategory.delete') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="bcategory_id"
                                                                value="{{ $bcategory->id }}">
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

    <!-- Create Blog Category Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Category') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger pb-1 dis-none" id="blogErrors">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <ul></ul>
                    </div>
                    <form id="categoryForm" class="modal-form create" action="{{ route('admin.bcategory.store') }}"
                        method="POST">
                        @csrf

                        @foreach ($langs as $language)
                            <div class="form-group">
                                <label for=""
                                    class="d-flex {{ $language->rtl == 1 ? 'text-right rtl mr-1' : 'ltr' }}">{{ __('Name') }}
                                    ({{ $language->name }})
                                    <span class="text-danger">**</span></label>

                                <input type="text"
                                    class="form-control {{ $language->rtl == 1 ? 'text-right rtl' : '' }}"
                                    name="{{ $language->code }}_name" placeholder="{{ __('Enter Name') }}">
                            </div>
                        @endforeach
                        <div class="form-group">
                            <label for="">{{ __('Status') }} <span class="text-danger">**</span></label>
                            <select class="form-control ltr" name="status">
                                <option value="" selected disabled>{{ __('Select a status') }}</option>
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Deactive') }}</option>
                            </select>
                            <p id="err_status" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                            <input type="number" class="form-control ltr" name="serial_number" value=""
                                placeholder="{{ __('Enter Serial Number') }}">
                            <p id="err_serial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning">
                                <small>{{ __('The higher the serial number is, the later the blog category will be shown.') }}</small>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                    <button form="categoryForm" type="submit" class="btn btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
