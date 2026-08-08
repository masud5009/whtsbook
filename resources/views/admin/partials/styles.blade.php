<!-- CSS Files -->
<link href="{{asset('assets/front/css/all.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/admin/css/fontawesome-iconpicker.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/dropzone.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap-tagsinput.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/jquery-ui.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/jquery.timepicker.min.css')}}">
{{-- timepicker css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">

<link rel="stylesheet" href="{{asset('assets/admin/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/atlantis.css')}}">
<link rel="stylesheet" href="{{asset('assets/admin/css/custom.css')}}">
@if(request()->cookie('admin-theme') == 'dark')
<link rel="stylesheet" href="{{asset('assets/admin/css/dark.css')}}">
@endif
@if ($default->rtl == 1)
<link rel="stylesheet" href="{{ asset('assets/admin/css/rtl-style.css') }}">
@endif

@yield('styles')
