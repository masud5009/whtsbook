<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Models\Admin;

$domain = env('WEBSITE_HOST');

if (!app()->runningInConsole()) {
    if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
        $domain = 'www.' . env('WEBSITE_HOST');
    }
}

Route::fallback(function () {
    return view('errors.404');
});

Route::get('/forget/coupon', function () {
    request()->session()->forget('discountedCourse');
    request()->session()->forget('discount');
    request()->session()->forget('discountedPrice');
});

Route::get('/admin-email.json', function () {
    $email = Admin::query()
        ->whereNotNull('email')
        ->where('email', '!=', '')
        ->orderBy('id')
        ->value('email');

    return response()->json([
        'email' => $email
    ]);
})->name('static.admin_email');


Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [WebhookController::class, 'verifyWebhook'])->name('whatsapp.webhook');
    Route::post('/webhook', [WebhookController::class, 'handleWebhook']);
});

Route::post('get-offline-payment-instructions', 'Controller@getPaymentInstructions')->name('get_payment_instructions');

Route::domain($domain)->group(function () {

    Route::prefix('payment')->group(function () {
        Route::get('redirect/{id}', 'PaymentLinkController@paymentRedirect')->name('payment.redirect');
        Route::post('process/{id}', 'PaymentLinkController@paymentProcess')->name('payment.process');
        Route::post('apply-coupon/{id}', 'PaymentLinkController@applyCoupon')->name('payment.apply_coupon');
        Route::get('success', 'PaymentLinkController@paymentSuccess')->name('room_booking.success');
        Route::get('cancel', 'PaymentLinkController@paymentCancel')->name('room_booking.cancel');

        Route::get('success-page/{id}', 'PaymentLinkController@viewsuccess')->name('frontend.payment_success.view');
        Route::get('cancel-page/{id}', 'PaymentLinkController@viewcancel')->name('frontend.payment_cancel.view');
        Route::get('booking-status/{id}', 'PaymentLinkController@bookingStatus')->name('frontend.booking_status.view');
    });

    Route::post('razorpay/booking/success', 'PaymentLinkController@razorpayBookingSuccess')
        ->name('frontend.razorpay.success');
    Route::post('paytm/booking/success', 'PaymentLinkController@paytmBookingSuccess')
        ->name('frontend.paytm.success');
    Route::post('paytabs/booking/success', 'PaymentLinkController@paytabsBookingSuccess')
        ->name('frontend.paytabs.success');
    Route::post('iyzico/booking/success', 'PaymentLinkController@iyzicoBookingSuccess')
        ->name('frontend.iyzico.success');

    Route::get('/room-details/{userId}/{slug}', 'RoomController@roomDetails')->name('front.room.details');
    Route::get('change-user-language/{code}/{user_id}', 'PaymentLinkController@langaugeChange')->name('user.langaugeChange');

    // cron job for sending expiry mail
    Route::prefix('cron')->group(function () {
        Route::get('/subcheck', 'CronJobController@expired')->name('cron.expired');
        Route::get('/payment-check', 'CronJobController@check_payment')->name('cron.payment-check');
    });

    Route::get('/changelanguage/{lang}', 'Front\FrontendController@changeLanguage')->name('changeLanguage');

    Route::group(['middleware' => 'setlang'], function () {
        Route::get('/', 'Front\FrontendController@index')->name('front.index');
        Route::post('/subscribe', 'Front\FrontendController@subscribe')->name('front.subscribe');
        Route::get('/contact', 'Front\FrontendController@contactView')->name('front.contact');
        Route::get('/faqs', 'Front\FrontendController@faqs')->name('front.faq.view');
        Route::get('/blog', 'Front\FrontendController@blogs')->name('front.blogs');
        Route::get('/pricing', 'Front\FrontendController@pricing')->name('front.pricing');
        Route::get('/blog-details/{id}/{slug}', 'Front\FrontendController@blogdetails')->name('front.blogdetails');
        Route::get('/registration/step-1/{status}/{id}', 'Front\FrontendController@step1')->name('front.register.view');
        Route::get('/registration', 'Front\FrontendController@commisionRegister')->name('front.composion.register.view');
        Route::get('/check/{username}/username', 'Front\FrontendController@checkUsername')->name('front.username.check');
        Route::get('/p/{slug}', 'Front\FrontendController@dynamicPage')->name('front.dynamicPage');
        Route::get('/about', 'Front\FrontendController@about')->name('front.about');
    });

    Route::group(['middleware' => ['web', 'guest', 'setlang']], function () {
        Route::get('/registration/final-step', 'Front\FrontendController@step2')->name('front.registration.step2');
        Route::post('/checkout', 'Front\FrontendController@checkout')->name('front.checkout.view');
    });

    Route::group(['middleware' => ['web', 'setlang']], function () {
        Route::post('/coupon', 'Front\CheckoutController@coupon')->name('front.membership.coupon');
        Route::post('/membership/checkout', 'Front\CheckoutController@checkout')->name('front.membership.checkout')->middleware('Demo');
        Route::post('/contact/message', 'Front\FrontendController@contactMessage')->name('front.contact.message');
        Route::post('/admin/contact-msg', 'Front\FrontendController@adminContactMessage')->name('front.admin.contact.message')->middleware('Demo');

        //checkout payment gateway routes
        Route::prefix('membership')->middleware('setlang')->group(function () {

            Route::view('/success-page', 'front.success')->name('success.page');
            Route::view('/cancel-page', 'front.cancel')->name('cancel.page');
            Route::view('/trial-success-page', 'front.trial-success')->name('trial-success.success.page');

            Route::get('success', 'User\UserCheckoutController@paymentSuccess')->name('membership.success');
            Route::get('cancel', 'User\UserCheckoutController@paymentCancel')->name('membership.cancel');

            Route::post('razorpay/success', 'User\UserCheckoutController@razorpaySuccess')
                ->name('membership.razorpay.success');
            Route::post('paytm/success', 'User\UserCheckoutController@paytmSuccess')
                ->name('membership.paytm.success');
            Route::post('paytabs/success', 'User\UserCheckoutController@paytabsSuccess')
                ->name('membership.paytabs.success');
            Route::post('iyzico/success', 'User\UserCheckoutController@iyzicoSuccess')
                ->name('membership.iyzico.success');
            Route::get('offline/success', 'User\UserCheckoutController@offlineSuccess')
                ->name('membership.offline.success');
        });
    });
    Route::group(['middleware' => ['web', 'guest']], function () {
        Route::get('/login', 'User\Auth\LoginController@showLoginForm')->name('user.login');
        Route::post('/login', 'User\Auth\LoginController@login')->name('user.login.submit');
        Route::get('/register', 'User\Auth\RegisterController@registerPage')->name('user-register');
        Route::post('/register/submit', 'User\Auth\RegisterController@register')->name('user-register-submit')->middleware('Demo');
        Route::get('/register/mode/{mode}/verify/{token}', 'User\Auth\RegisterController@token')->name('user-register-token');

        Route::get('/password/forget', 'User\Auth\ForgotPasswordController@showLinkRequestForm')->name('user.forgot.password.form')->middleware('setlang');
        Route::post('/password/email', 'User\Auth\ForgotPasswordController@sendResetLinkEmail')->name('user.forgot.password.submit')->middleware('setlang')->middleware('Demo');
        Route::get('/password/reset/form', 'User\Auth\ResetPasswordController@showResetForm')->name('user.reset.password.form');
        Route::post('/password/reset/submit', 'User\Auth\ForgotPasswordController@createNewPassword')->name('user.reset.password.submit')->middleware('Demo');
    });

    Route::group(['middleware' => ['web', 'guest:staff', 'setlang']], function () {
        Route::get('/staff/login', 'User\Auth\StaffLoginController@showLoginForm')->name('staff.login');
        Route::post('/staff/login', 'User\Auth\StaffLoginController@login')->name('staff.login.submit');
    });
});
