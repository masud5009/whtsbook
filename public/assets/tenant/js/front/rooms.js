
"use strict";
$(document).ready(function () {
  var formData = {
    dates: null,
    beds: null,
    baths: null,
    guests: null,
    locations: null,
    ratings: null,
    sort_by: null,
    prices: null,
    ammenities: null
  };
  // initialize date range picker
  $('#date-range').daterangepicker({
    opens: 'left',
    autoUpdateInput: false,
    locale: {
      format: 'YYYY-MM-DD'
    }
  });


  // show the dates in input field when user select a date range
  $('#date-range').on('apply.daterangepicker', function (event, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    filterInputs();
  });

  // remove the dates when user click on cancel button
  $('#date-range').on('cancel.daterangepicker', function (event, picker) {
    $(this).val('');
    filterInputs();
  });

  var position = currentPosition;
  var symbol = currentSymbol;

  // price range slider
  $('#price-range-slider').slider({
    range: true,
    min: minprice,
    max: maxprice,
    values: priceValues,
    slide: function (event, ui) {
      //while the slider moves, then this function will show that range value
      $('#amount').val((position == 'left' ? symbol : '') + ui.values[0] + (position == 'right' ? symbol : '') + ' - ' + (position == 'left' ? symbol : '') + ui.values[1] + (position == 'right' ? symbol : ''));
    }
  });

  // initially this is showing the price range value
  $('#amount').val((position == 'left' ? symbol : '') + $('#price-range-slider').slider('values', 0) + (position == 'right' ? symbol : '') + ' - ' + (position == 'left' ? symbol : '') + $('#price-range-slider').slider('values', 1) + (position == 'right' ? symbol : ''));


  function filterInputs() {
    $('#searchForm').submit();
  }

  $('#searchForm').on('submit', function (e) {
    e.preventDefault();
    $('#skeleton-loader').removeClass('d-none');
    var fd = $(this).serialize();
    $('.search-container').html('');
    $.ajax({
      url: searchUrl,
      method: "get",
      data: fd,
      contentType: false,
      processData: false,
      success: function (response) {
        $('.search-container').html(response);
        new LazyLoad();
      },
      error: function (xhr) {
      }
    }).always(function ($e) {
      $('#skeleton-loader').addClass('d-none');
    });
  });



  $('.room_sidebar').on('change', 'select[name="beds"]', function () {
    filterInputs();
  });

  $('.room_sidebar').on('change', 'select[name="baths"]', function () {
    filterInputs();
  });

  $('.room_sidebar').on('change', 'select[name="guests"]', function () {
    filterInputs();
  });

  $('.room_sidebar').on('keypress', 'input[name="locations"]', function () {
    if (event.which === 13) {
      filterInputs();
    }
  });

  $('.room_sidebar').on('click', 'input[name="ratings"]', function () {
    filterInputs();
  });

  $('.room_sidebar').on('change', 'select[name="sort_by"]', function () {
    filterInputs();
  });
  $('.room_sidebar').on('click', 'input[name="ammenities[]"]', function () {
    filterInputs();
  });

  $('#price-range-slider').on('slidestop', function (event, ui) {
    if (event?.originalEvent?.handleObj) {
      filterInputs();
    }

  });

  $(".categoryBtn").on('click', function (param) {
    const category_index = $(this).data('category_index');
    resetAllInputs();
    $('#category_search').val(category_index);
    filterInputs();
    $('li').removeClass('active-f-view');
    $(this).closest('li').addClass('active-f-view');
  })

  $("#reset_all_btn").on('click', function ($e) {
    window.location.href = searchUrl;
  })

  function resetAllInputs() {
    $('select[name="beds"]').val('').niceSelect('update');
    $('select[name = "baths"]').val('').niceSelect('update');
    $('select[name = "guests"]').val('').niceSelect('update');
    $('select[name = "sort_by"]').val('').niceSelect('update');

    $('input[name="ratings"]').prop('checked', false);
    $('input[name="ratings"]:first').prop('checked', true);
    $('input[name="ammenities[]"]').prop('checked', false).trigger('change');
    $('#date-range').val('');
    $('#price-range-slider').slider('values', [minprice, maxprice]);

    $('#amount').val((position == 'left' ? symbol : '') + $('#price-range-slider').slider('values', 0) + (position == 'right' ? symbol : '') + ' - ' + (position == 'left' ? symbol : '') + $('#price-range-slider').slider('values', 1) + (position == 'right' ? symbol : ''));
  }

});
