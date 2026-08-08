<script>
  "use strict";
  var mainURL = "{{ url('/') }}";
  var imgupload = "{{ route('admin.summernote.upload') }}";
  var storeURL = "";
  var removeURL = "";
  var rmvdbURL = "";
  var loadImgs = "";
  var userStatusRoute = "{{ route('user-status') }}";

  var are_you_sure = "{{ __('Are you sure ?') }}";
  var wont_revert_text = "{{ __('You won not be able to revert this!') }}";
  var yes_delete_it = "{{ __('Yes, delete it') }}";
  var cancel = "{{ __('Cancel') }}";
  var success = "{{ __('Success') }}";
  var warning = "{{ __('Warning') }}";
  var error = "{{ __('Error') }}";
  var your_feature_limit_is_over_or_down_graded = "{{ __('Your feature limit is over or down graded!') }}";
  var you_want_to_close_this_ticket = "{{ __('You want to close this ticket!') }}";
  var yes_close_it = "{{ __('Yes, close it') }}";
  var demo_mode = "{{ env('DEMO_MODE') }}";
  const __Processing__ = "{{ __('Processing') }}";

  var nextText = "{{ __('Next') }}";
  var previousText = "{{ __('Previous') }}";
  var showText = "{{ __('Show') }}";
  var entriesText = "{{ __('entries') }}";
  var Search = "{{ __('Search') }}";
  var Showing = "{{ __('Showing') }}";
  var to = "{{ __('to') }}";
  var ofText = "{{ __('of') }}";
  var selectCategoryText = "{{ __('Select a Category') }}"
</script>

{{-- core js files --}}
<script src="{{ asset('assets/tenant/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/tenant/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/tenant/js/bootstrap.min.js') }}"></script>
{{-- vue js --}}
<script src="{{ asset('assets/admin/js/plugin/vue/vue.js') }}"></script>
{{-- axios --}}
<script src="{{ asset('assets/tenant/js/axios-0.21.0.min.js') }}"></script>

{{-- jQuery ui --}}
<script src="{{ asset('assets/tenant/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/tenant/js/jquery.ui.touch-punch.min.js') }}"></script>

{{-- time-picker --}}
<script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>

{{-- jQuery scrollbar --}}
<script src="{{ asset('assets/tenant/js/jquery.scrollbar.min.js') }}"></script>

{{-- bootstrap notify --}}
<script src="{{ asset('assets/tenant/js/bootstrap-notify.min.js') }}"></script>

{{-- sweet alert --}}
<script src="{{ asset('assets/tenant/js/sweetalert.min.js') }}"></script>

{{-- bootstrap tags input --}}
<script src="{{ asset('assets/tenant/js/bootstrap-tagsinput.min.js') }}"></script>

<!-- Datatable -->
<script src="{{ asset('assets/tenant/js/datatables.min.js') }}"></script>

{{-- dropzone js --}}
<script src="{{ asset('assets/tenant/js/dropzone.min.js') }}"></script>

{{-- jQuery dm-uploader js --}}
<script src="{{ asset('assets/tenant/js/jquery.dm-uploader.min.js') }}"></script>

{{-- tinymce --}}
<script src="{{ asset('assets/tenant/js/tinymce/js/tinymce/tinymce.min.js') }}"></script>
{{-- js color --}}
<script src="{{ asset('assets/tenant/js/jscolor.js') }}"></script>

{{-- atlantis js --}}
<script src="{{ asset('assets/admin/js/atlantis.min.js') }}"></script>
<script src="{{ asset('assets/tenant/js/atlantis.js') }}"></script>

{{-- fontawesome icon picker js --}}
<script src="{{ asset('assets/tenant/js/fontawesome-iconpicker.min.js') }}"></script>

{{-- fonts and icons script --}}
<script src="{{ asset('assets/tenant/js/webfont.min.js') }}"></script>

{{-- moment js --}}
<script type="text/javascript" src="{{ asset('assets/tenant/js/moment.min.js') }}"></script>

{{-- date-range-picker js --}}
<script type="text/javascript" src="{{ asset('assets/tenant/js/daterangepicker.min.js') }}"></script>

{{-- select2 js --}}
<script type="text/javascript" src="{{ asset('assets/tenant/js/select2.min.js') }}"></script>
{{-- admin-main js --}}
<script src="{{ asset('assets/tenant/js/main.js') }}"></script>


<script>
  $("#toggle-btn").on('change', function() {
    var value = null;
    if (this.checked) {
      value = this.getAttribute('data-on');
    } else {
      value = this.getAttribute('data-off');
    }
    $.post(userStatusRoute, {
        value: value
      },
      function(data) {
        history.go(0);
      });
  });
</script>

@if (session()->has('success'))
  <script>
    "use strict";
    var content = {};

    content.message = '{{ session('success') }}';
    content.title = success;
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: 'success',
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      delay: 4000
    });
  </script>
@endif

@if (session()->has('warning'))
  <script>
    "use strict";
    var content = {};

    content.message = '{{ session('warning') }}';
    content.title = warning;
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: 'warning',
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      delay: 4000
    });
  </script>
@endif

@if (session()->has('error'))
  <script>
    "use strict";
    var content = {};

    content.message = '{{ session('error') }}';
    content.title = error;
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: 'danger',
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      delay: 4000
    });
  </script>
@endif
