(function ($) {
    "use strict";
    $('input[name="is_trial"]').on('change',function(){
        if ($(this).val() == 1) {
            $('#trial_day').show();
        } else {
            $('#trial_day').hide();
        }
        $('#trial_days_2').val(null);
        $('#trial_days_1').val(null);
    });

    if(permission.includes("Tour Package")){
        $(".tourPackage_div").addClass('d-block');
    }else{
       $(".tourPackage_div").removeClass('d-block');
    }

    if (permission.includes("Blog")) {
        $(".blogDiv").addClass('d-block');
    } else {
        $(".blogDiv").removeClass('d-block');
    }

    if (permission.includes("Custom Page")) {
        $(".customPageDiv").addClass('d-block');
    } else {
        $(".customPageDiv").removeClass('d-block');
    }


    $(document).on('click','#tourPackage',function(){
        const isChecked = $(this).is(':checked');
        if(isChecked){
            $(".tourPackage_div").addClass('d-block');
             $(".package_coupon").addClass('d-block');
        }else{
             $(".tourPackage_div").removeClass('d-block');
             $(".package_coupon").removeClass('d-block');
             $('.package_coupon_val').prop( "checked", false );
        }
    });

    $(document).on('click', '#blogDiv', function () {
        const isChecked = $(this).is(':checked');
        if (isChecked) {
            $(".blogDiv").addClass('d-block');
        } else {
            $(".blogDiv").removeClass('d-block');
        }
    });

    $(document).on('click', '#customPageDiv', function () {
        const isChecked = $(this).is(':checked');
        if (isChecked) {
            $(".customPageDiv").addClass('d-block');
        } else {
            $(".customPageDiv").removeClass('d-block');
        }
    });
})(jQuery); 
