@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Add Section') }}</h4>
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
                <a href="#">{{ __('Additional Sections') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Add Section') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header additional-card-header">
                    <div class="card-title d-inline-block">{{ __('Add Section') }}</div>
                    <a class="btn btn-info btn-sm float-right  d-inline-block"
                        href="{{ route('admin.additional_sections', ['language' => @$language->code]) }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="alert alert-danger pb-1 dis-none" id="pageErrors">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <ul></ul>
                            </div>
                            <form id="pageForm" action="{{ route('admin.additional_section.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="page_type" value="home">
                                    <div class="col-lg-6">
                                        <div class="form-group p-0">
                                            <label for="">{{ __('Position')  }} <span class="text-danger">**</span></label>
                                            <select name="possition" class="form-control select2">
                                                <option selected disabled>{{ __('Select a Section') }}</option>
                                                @foreach ($all_sections as $section)
                                                    <option value="{{ $section }}">
                                                        {{ __('After ' . str_replace('_', ' ', $section)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group p-0">
                                            <label for="">{{ __('Serial Number')  }} <span class="text-danger">**</span> </label>
                                            <input type="number" name="serial_number" class="form-control">
                                            <p class="text-warning">
                                                {{ __('The higher the serial number is, the later the section will be shown.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div id="accordion" class="mt-3">
                                    @foreach ($languages as $language)
                                        <div class="version">
                                            <div class="version-header" id="heading{{ $language->id }}">
                                                <h5 class="mb-0">
                                                    <button type="button"
                                                        class="btn btn-link {{ $language->rtl == 1 ? 'rtl text-right' : '' }}"
                                                        data-toggle="collapse" data-target="#collapse{{ $language->id }}"
                                                        aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}"
                                                        aria-controls="collapse{{ $language->id }}">
                                                        {{ $language->name . ' ' . __('Language') }}
                                                        {{ $language->is_default == 1 ? '(' . __('Default') . ')' : '' }}
                                                    </button>
                                                </h5>
                                            </div>
                                            <div id="collapse{{ $language->id }}"
                                                class="collapse {{ $language->is_default == 1 ? 'show' : '' }}"
                                                aria-labelledby="heading{{ $language->id }}" data-parent="#accordion">
                                                <div class="version-body">

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div
                                                                class="form-group {{ $language->rtl == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Title')  }} <span class="text-danger">**</span></label>
                                                                <input type="text" class="form-control {{ $language->rtl == 1 ? 'rtl text-right' : '' }}"
                                                                    name="{{ $language->code }}_section_name"
                                                                    placeholder="{{ __('Enter Section Name') }}">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="form-group {{ $language->rtl == 1 ? 'rtl text-right' : '' }}">
                                                                <label>{{ __('Content') }} <span class="text-danger">**</span></label>
                                                                <textarea id="{{ $language->code }}_content" class="form-control summernote {{ $language->rtl == 1 ? 'rtl text-right' : '' }}" name="{{ $language->code }}_content"
                                                                    placeholder="{{ __('Enter Content') }}" data-height="300"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            @php $currLang = $language; @endphp
                                                            @foreach ($languages as $lang)
                                                                @continue($lang->id == $currLang->id)
                                                                <div class="form-check py-0">
                                                                    <label class="form-check-label">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            onchange="cloneInput('collapse{{ $currLang->id }}', 'collapse{{ $lang->id }}', event)">
                                                                        <span class="form-check-sign">{{ __('Clone for') }}
                                                                            <strong
                                                                                class="text-capitalize text-secondary">{{ $lang->name }}</strong>
                                                                            {{ __('language') }}</span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" form="pageForm" class="btn btn-success">
                                {{ __('Save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/addition-page.js') }}"></script>
@endsection
