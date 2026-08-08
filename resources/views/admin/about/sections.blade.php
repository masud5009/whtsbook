@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Section Hide/Show') }}</h4>
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
                <a href="#">{{ __('About Us') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Section Hide/Show') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="" action="{{ route('admin.abouts.section.hide_show.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="card-title">{{ __('Section Hide/Show') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-6 mx-auto">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('Features Section')  }} <span class="text-danger">**</span></label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="about_features_section_status" value="1"
                                                class="selectgroup-input"
                                                {{ $abs->about_features_section_status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="about_features_section_status" value="0"
                                                class="selectgroup-input"
                                                {{ $abs->about_features_section_status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Partner Section')  }} <span class="text-danger">**</span></label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="about_partner_section_status" value="1"
                                                class="selectgroup-input"
                                                {{ $abs->about_partner_section_status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="about_partner_section_status" value="0"
                                                class="selectgroup-input"
                                                {{ $abs->about_partner_section_status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>{{ __('Blog Section')  }} <span class="text-danger">**</span></label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="about_blog_section_status" value="1"
                                                class="selectgroup-input"
                                                {{ $abs->about_blog_section_status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="about_blog_section_status" value="0"
                                                class="selectgroup-input"
                                                {{ $abs->about_blog_section_status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                </div>

                                @if (count($additional_section_statuses) > 0)
                                    @foreach ($additional_section_statuses as $key => $additional_section_status)
                                        @php
                                            $section_content = App\Models\AdditionalSectionContent::where([
                                                ['language_id', $languge_id],
                                                ['addition_section_id', $key],
                                            ])->first();
                                        @endphp
                                        @if ($section_content)
                                            <div class="form-group">
                                                <label>{{ $section_content->section_name . ' ' . __('Section') }} <span class="text-danger">**</span> </label>
                                                <div class="selectgroup w-100">
                                                    <label class="selectgroup-item">
                                                        <input type="radio"
                                                            name="additional_sections[{{ $key }}]"
                                                            value="1" class="selectgroup-input"
                                                            {{ $additional_section_status == 1 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">{{ __('Active') }}</span>
                                                    </label>
                                                    <label class="selectgroup-item">
                                                        <input type="radio"
                                                            name="additional_sections[{{ $key }}]"
                                                            value="0" class="selectgroup-input"
                                                            {{ $additional_section_status == 0 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" id="displayNotif"
                                        class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
