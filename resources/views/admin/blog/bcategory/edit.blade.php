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
                    <div class="card-title d-inline-block">{{ __('Edit Category') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('admin.bcategory.index') . '?language=' . $default->code }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 m-auto">
                            <div class="alert alert-danger pb-1 dis-none" id="blogErrors">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <ul></ul>
                            </div>
                            <form id="categoryForm" action="{{ route('admin.bcategory.update') }}"
                                method="POST">
                                @csrf

                                @foreach ($languages as $language)
                                    @php
                                        $category = \App\Models\Bcategory::where([
                                            ['language_id', $language->id],
                                            ['indx', $bcategory->indx]])->first();

                                    @endphp
                                    <input type="hidden" name="{{ $language->code }}_id" value="{{ @$category->id }}">
                                    <div class="form-group">
                                        <label for="">{{ __('Name') }} <span class="text-danger">**</span>
                                            ({{ $language->name }})
                                        </label>
                                        <input type="text"
                                            class="form-control {{ $language->rtl == 1 ? 'important_rtl text-right' : 'important_ltr' }}"
                                            name="{{ $language->code }}_name" value="{{ @$category->name }}"
                                            placeholder="{{ __('Enter name') }}">
                                        <p id="err_{{ $language->code }}_name" class="mb-0 text-danger em"></p>
                                    </div>
                                @endforeach

                                <input type="hidden" name="category_id" value="{{ $bcategory->id }}">
                                <input type="hidden" name="category_indx" value="{{ $bcategory->indx }}">
                                <div class="form-group">
                                    <label for="">{{ __('Status') }} <span class="text-danger">**</span></label>
                                    <select class="form-control ltr" name="status">
                                        <option value="" selected disabled>{{ __('Enter name') }}
                                        </option>
                                        <option value="1" {{ $bcategory->status == 1 ? 'selected' : '' }}>
                                            {{ __('Active') }}</option>
                                        <option value="0" {{ $bcategory->status == 0 ? 'selected' : '' }}>
                                            {{ __('Deactive') }}</option>
                                    </select>
                                    <p id="err_status" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Serial Number') }} <span
                                            class="text-danger">**</span></label>
                                    <input type="number" class="form-control ltr" name="serial_number"
                                        placeholder="{{ __('Enter Serial Number') }}" value="{{ $bcategory->serial_number }}">
                                    <p id="err_serial_number" class="mb-0 text-danger em"></p>
                                    <p class="text-warning">
                                        <small>{{ __('The higher the serial number is, the later the blog category will be shown.') }}</small>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" form="categoryForm"
                                    class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
