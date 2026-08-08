<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
  <title>{{ $bs->website_title }}</title>
  <link rel="icon" href="{{ asset('assets/front/img/' . $bs->favicon) }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/login.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/forget.css') }}">
</head>

<body>
  <div class="login-page">
    <div class="text-center mb-4">
      <img class="login-logo" src="{{ asset('assets/front/img/' . $bs->logo) }}" alt="">
    </div>
    <div class="form">
      @if (session()->has('success'))
        <div class="alert alert-success fade show" role="alert">
          <strong>{{ __('Success') }}!</strong> {{ session('success') }}
        </div>
      @endif
      @if (session('link_error'))
        <div class="alert alert-danger">
          {{ session('link_error') }}
        </div>
      @endif
      <form class="login-form" action="{{ route('admin.create.password.submit') }}" method="POST">
        @csrf
        <input type="hidden" name="pass_token" value="{{ request('pass_token') }}">
        <input type="password" name="password" placeholder="{{ __('Enter new password') }}" />
        @if ($errors->has('password'))
          <p class="text-danger text-left">{{ $errors->first('password') }}</p>
        @endif
        <button type="submit">{{ __('Submit') }}</button>
      </form>
    </div>
  </div>

  <!-- jquery js -->
  <script src="{{ asset('assets/admin/js/core/jquery.min.js') }}"></script>
  <!-- popper js -->
  <script src="{{ asset('assets/admin/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/core/bootstrap.min.js') }}"></script>
  <!-- Bootstrap Notify -->
  <script src="{{ asset('assets/admin/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
</body>
</html>
