@extends('admin.layout')

@if (!empty($abs->language) && $abs->language->rtl == 1)

@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Testimonials') }}</h4>
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
                <a href="#">{{ __('Testimonials') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-5 mb-3 mb-lg-0">
                            <div class="card-title d-inline-block">{{ __('Testimonials') }}</div>
                        </div>
                        <div class="col-lg-2">
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
                        <div class="col-lg-5">
                            <a href="#" class="btn btn-primary float-right btn-sm mt-2 mt-lg-0" data-toggle="modal"
                                data-target="#createModal"><i class="fas fa-plus"></i> {{ __('Add Testimonial') }}</a>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($testimonials) == 0)
                                <h3 class="text-center">{{ __('NO TESTIMONIAL FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">{{ __('Image') }}</th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Rank') }}</th>
                                                <th scope="col">{{ __('Serial Number') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($testimonials as $key => $testimonial)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><img src="{{ asset('assets/front/img/testimonials/' . $testimonial->image) }}"
                                                            class="max-width-60"></td>
                                                    <td>{{ $testimonial->name }}</td>
                                                    <td>{{ $testimonial->rank }}</td>
                                                    <td>{{ $testimonial->serial_number }}</td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm my-1 my-lg-0"
                                                            href="{{ route('admin.testimonial.edit', $testimonial->id) . '?language=' . request()->input('language') }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>
                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('admin.testimonial.delete') }}"
                                                            method="post">
                                                            @csrf
                                                            <input type="hidden" name="testimonial_id"
                                                                value="{{ $testimonial->id }}">
                                                            <button type="submit" class="btn btn-danger btn-sm deletebtn">
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

    <!-- Create Testimonial Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Testimonial') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxForm" class="modal-form" action="{{ route('admin.testimonial.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="col-12 mb-2 pl-0">
                                        <label for="image"><strong>{{ __('Image') }}</strong></label>
                                    </div>
                                    <div class="col-md-12 showImage mb-3 pl-0">
                                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                            class="img-thumbnail">
                                    </div>
                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                        {{ __('Choose Image') }}
                                        <input type="file" class="img-input" name="image">
                                    </div>

                                    <p id="err_image" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                        </div>
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
                        <div class="form-group">
                            <label for="">{{ __('Comment') }} <span class="text-danger">**</span></label>
                            <textarea class="form-control" name="comment" rows="3" cols="80"
                                placeholder="{{ __('Enter comment') }}"></textarea>
                            <p id="err_comment" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Name') }} <span class="text-danger">**</span></label>
                            <input type="text" class="form-control" name="name" value=""
                                placeholder="{{ __('Enter Name') }}">
                            <p id="err_name" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Rank') }} <span class="text-danger">**</span></label>
                            <input type="text" class="form-control" name="rank" value=""
                                placeholder="{{ __('Enter Rank') }}">
                            <p id="err_rank" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                            <input type="number" class="form-control ltr" name="serial_number" value=""
                                placeholder="{{ __('Enter Serial Number') }}">
                            <p id="err_serial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning">
                                <small>{{ __('The higher the serial number is, the later the testimonial will be shown') }}</small>
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
@endsection
