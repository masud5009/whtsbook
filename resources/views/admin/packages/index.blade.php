@extends('admin.layout')

@php
    use App\Models\Language;
    $setLang = Language::where('code', request()->input('language'))->first();
    $tourPackage = json_decode($be->package_features, true);
@endphp
@if (!empty($setLang) && $setLang->rtl == 1)
    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Packages') }}</h4>
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
                <a href="#">{{ __('Package Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Packages') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card-title d-inline-block">{{ __('Packages') }}</div>
                        </div>
                        <div class="col-lg-4 mt-2 mt-lg-0">
                            <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                data-target="#createModal"><i class="fas fa-plus"></i>
                                {{ __('Add Package') }}</a>
                            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                data-href="{{ route('admin.package.bulk.delete') }}"><i class="flaticon-interface-5"></i>
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($packages) == 0)
                                <h3 class="text-center">{{ __('NO PACKAGE FOUND YET') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Price') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($packages as $key => $package)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $package->id }}">
                                                    </td>
                                                    <td>
                                                        @php
                                                            $packageTitle = __($package->title);
                                                        @endphp
                                                        {{ truncateString($packageTitle, 30) }}
                                                        <span class="badge badge-primary">{{ __($package->term) }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($package->price == 0)
                                                            {{ __('Free') }}
                                                        @else
                                                            {{ format_price($package->price) }}
                                                        @endif

                                                    </td>
                                                    <td>
                                                        @if ($package->status == 1)
                                                            <h2 class="d-inline-block">
                                                                <span
                                                                    class="badge badge-success">{{ __('Active') }}</span>
                                                            </h2>
                                                        @else
                                                            <h2 class="d-inline-block">
                                                                <span
                                                                    class="badge badge-danger">{{ __('Deactive') }}</span>
                                                            </h2>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm mb-1"
                                                            href="{{ route('admin.package.edit', $package->id) . '?language=' . request()->input('language') }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>
                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('admin.package.delete') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="package_id"
                                                                value="{{ $package->id }}">
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
    <!-- Create Blog Modal -->
    @include('admin.packages.create')
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/packages.js') }}"></script>
@endsection
