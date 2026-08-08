"use strict";
$(document).ready(function () {

  var position = currency_info.base_currency_symbol_position;
  var symbol = currency_info.base_currency_symbol;

  // search package by category
  $('ul.categories li a').on('click', function (e) {
    e.preventDefault();
    if (!$(this).closest('li').hasClass('active')) {
    resetAllInputs();
    }
    var value = $(this).data('category_id');
    $('#categoryKey').val(value);
    $('li').removeClass('active');
    $(this).closest('li').addClass('active');
    filterInputs()
  });

  // search package by giving input in search field
  $('#searchInput').on('keypress', function (event) {
    // check whether 'enter' key is pressed or not
    if (event.which == 13) {
      var searchVal = $(this).val();
      $('#searchKey').val(searchVal);
      filterInputs();
    }
  });

  // search package by days
  $('#days').on('change', function () {
    var value = $(this).val();

    $('#daysKey').val(value);
    filterInputs();
  });

  // search package by persons
  $('#persons').on('change', function () {
    var value = $(this).val();

    $('#personsKey').val(value);
    filterInputs();
  });

  // search package by sorting
  $('#sortType').on('change', function () {
    var value = $(this).val();

    $('#sortKey').val(value);
    filterInputs();
  });

  // search package by giving location name as input in search field
  $('#locationSearchInput').on('keypress', function (event) {
    // check whether 'enter' key is pressed or not
    if (event.which == 13) {
      var locationVal = $(this).val();
      $('#locationKey').val(locationVal);
      filterInputs();
    }
  });

  // package price range slider
  $('#slider-range').slider({
    range: true,
    min: minprice,
    max: maxprice,
    values: priceValues,
    slide: function (event, ui) {
      //while the slider moves, then this function will show that range value
      $('#amount').val((position == 'left' ? symbol + ' ' : '') + ui.values[0] + (position == 'right' ? ' ' + symbol : '') + ' - ' + (position == 'left' ? symbol + ' ' : '') + ui.values[1] + (position == 'right' ? ' ' + symbol : ''));
    }
  });

  // initially this is showing the price range value
  $('#amount').val((position == 'left' ? symbol + ' ' : '') + $('#slider-range').slider('values', 0) + (position == 'right' ? ' ' + symbol : '') + ' - ' + (position == 'left' ? symbol + ' ' : '') + $('#slider-range').slider('values', 1) + (position == 'right' ? ' ' + symbol : ''));

  // search package by filtering the price
  $('#slider-range').on('slidestop', function () {
    var filterPrice = $('#amount').val();

    filterPrice = filterPrice.split('-');
    var minCost = parseFloat(filterPrice[0].replace('$', ' '));
    var maxCost = parseFloat(filterPrice[1].replace('$', ' '));

    $('#minPriceKey').val(minCost);
    $('#maxPriceKey').val(maxCost);
    filterInputs();
  });

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

  function resetAllInputs(){
    $('#categoryKey').val('');
    $('#searchKey').val('');
    $('#daysKey').val('');
    $('#personsKey').val('');
    $('#sortKey').val('');
    $('#locationKey').val('');
    $("#minPriceKey").val('');
    $("#maxPriceKey").val('');

    //input
    $("#searchInput").val('');
    $("#locationSearchInput").val('');
    $("#days").val('').niceSelect('update');
    $("#persons").val('').niceSelect('update');
    $("#sortType").val('new-packages').niceSelect('update');
    $('#slider-range').slider('values', [minprice, maxprice]);
    $('#amount').val((position == 'left' ? symbol + ' ' : '') + $('#slider-range').slider('values', 0) + (position == 'right' ? ' ' + symbol : '') + ' - ' + (position == 'left' ? symbol + ' ' : '') + $('#slider-range').slider('values', 1) + (position == 'right' ? ' ' + symbol : ''));

  }

  $("#reset_all_btn").on('click', function ($e) {
    window.location.href = searchUrl;
  })

});
