{{-- fontawesome css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/all.min.css') }}">

{{-- fontawesome icon picker css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/fontawesome-iconpicker.min.css') }}">

{{-- dropzone css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/dropzone.min.css') }}">

{{-- jQuery dm-uploader css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/jquery.dm-uploader.min.css') }}">

{{-- bootstrap css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/bootstrap.min.css') }}">

{{-- bootstrap tags-input css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/bootstrap-tagsinput.css') }}">

{{-- jQuery-ui css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/jquery-ui.min.css') }}">

{{-- timepicker css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">

{{-- date-range-picker css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/daterangepicker.min.css') }}">

{{-- atlantis css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/atlantis.css') }}">

{{-- select2 css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/select2.min.css') }}">

@if (request()->cookie('user-theme') == 'dark')
  <link rel="stylesheet" href="{{ asset('assets/tenant/css/dark.css') }}">
@endif
{{-- admin-main css --}}
<link rel="stylesheet" href="{{ asset('assets/tenant/css/main.css') }}">

@if ($dashboard_language->rtl == 1)
   <link rel="stylesheet" href="{{ asset('assets/tenant/css/rtl-style.css') }}">
@endif

