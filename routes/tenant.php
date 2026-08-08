<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

$domain = env('WEBSITE_HOST');

if (! app()->runningInConsole()) {
    if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
        $domain = 'www.' . env('WEBSITE_HOST');
    }
}

Route::fallback(function () {
    return view('errors.404');
});
Route::get('/config-clear', function () {
    Artisan::call('config:clear');
});

Route::get('/forget/coupon', function () {
    request()->session()->forget('discountedCourse');
    request()->session()->forget('discount');
    request()->session()->forget('discountedPrice');
});

// cron job for sending expiry mail

Route::domain($domain)->group(function () {
    Route::get('/subcheck', 'CronJobController@expired')->name('cron.expired');

    Route::group(['prefix' => 'user', 'middleware' => ['auth:web', 'userstatus', 'setUserLang', 'tenantStaffAccess', 'Demo']], function () {

        Route::prefix('connect-with-whatsapp')->group(function () {
            Route::get('/', 'User\WhatsappController@index')->name('user.whatsapp_list');
            Route::post('/store', 'User\WhatsappController@store')->name('user.whatsapp_list_store')
                ->middleware('limitCheck:wp_numbers,store');
            Route::post('/update', 'User\WhatsappController@update')->name('user.whatsapp_list_update')
                ->middleware('limitCheck:wp_numbers,update');
            Route::post('/delete', 'User\WhatsappController@delete')->name('user.whatsapp_list_delete');
            Route::post('/bulk_delete', 'User\WhatsappController@bulk_delete')->name('user.whatsapp_list_bulk_delete');

            Route::get('/configure-booking-fields/{wp_id}', 'User\WhatsappController@configureBookingFields')->name('user.configure_booking_fields');
            Route::post('/configure-booking-fields/update', 'User\WhatsappController@updateconfigureBookingFields')
                ->name('user.whatsapp_configure_booking_fields.update');


            Route::get('/share-information/{wp_id}', 'User\WhatsappController@shareInformation')->name('user.whatsapp_share_information');
            Route::post('/share-information/update', 'User\WhatsappController@updateShareInformation')
                ->name('user.whatsapp_share_information.update');

            Route::get('/templates/{wp_id}', 'User\AutoResponseMessage@templates')
                ->name('user.whatsapp_template_messages');
            Route::post('/template/update/{id}', 'User\AutoResponseMessage@updateTemplate')
                ->name('user.whatsapp_template.update');
        });

        Route::prefix('whatsapp-failed-message')->group(function () {
            Route::get('/', 'User\BotChatController@index')->name('user.whatsapp_failed_messages');
            Route::get('/{id}', 'User\BotChatController@details')->name('user.whatsapp_failed_messages.details');
            Route::post('/delete', 'User\BotChatController@delete')->name('user.whatsapp_failed_messages.delete');
            Route::post('/bulk_delete', 'User\BotChatController@bulk_delete')->name('user.whatsapp_failed_messages.bulk_delete');
            Route::post('/update-status/{id}', 'User\BotChatController@updateStatus')->name('user.whatsapp_message_status_update');
        });

        Route::get('/dashboard', 'User\UserController@index')->name('user-dashboard');
        Route::get('/credit-topup-history', 'User\AiController@creditTopupHistory')
            ->name('user.credit_topup.history');

        Route::get('success', 'User\AiController@paymentSuccess')->name('tenant.credit_buy.success');
        Route::get('cancel', 'User\AiController@paymentCancel')->name('tenant.credit_buy.cancel');

        Route::get('success-page', 'User\AiController@viewsuccess')->name('tenant.credit_buy.payment_success.view');
        Route::get('cancel-page', 'User\AiController@viewcancel')->name('tenant.credit_buy.payment_cancel.view');

        Route::post('/buy-credit', 'User\AiController@buy')->name('user.buy-credit');
        Route::post('razorpay/success', 'User\AiController@razorpaySuccess')
            ->name('tenant.razorpay.success');
        Route::post('paytm/success', 'User\AiController@paytmSuccess')
            ->name('tenant.paytm.success');
        Route::post('paytabs/success', 'User\AiController@paytabsSuccess')
            ->name('tenant.paytabs.success');
        Route::post('iyzico/success', 'User\AiController@iyzicoSuccess')
            ->name('tenant.iyzico.success');

        Route::get('/pdf-test-2', 'User\RoomController@pdfTest');
        Route::get('/pdf-test-package', 'User\RoomController@pdfPackageTest');
        Route::get('/reset', 'User\UserController@resetform')->name('user-reset');
        Route::post('/reset', 'User\UserController@reset')->name('user-reset-submit');

        Route::get('/logout', 'User\Auth\LoginController@logout')->name('user-logout');
        Route::post('/change-status', 'User\UserController@status')->name('user-status');
        // user theme change
        Route::get('/change-theme', 'User\UserController@changeTheme')->name('user.theme.change');
        // RTL check
        Route::get('/rtlcheck/{langid}', 'User\LanguageController@rtlcheck')->name('user.rtlcheck');
        Route::get('user/rtlcheck/{langid}', 'User\LanguageController@rtlcheck')->name('user.rtlcheck_2');
        // Summernote image upload
        Route::post('/summernote/upload', 'Admin\SummernoteController@upload')->name('user.summernote.upload');

        Route::prefix('/train-ai-assistant')->group(function () {
            Route::get('/', 'User\AiKnowledgeVaultController@index')->name('user.ai_knowledge_vault.index');
            Route::post('/store', 'User\AiKnowledgeVaultController@store')->name('user.ai_knowledge_vault.store');
            Route::post('/delete', 'User\AiKnowledgeVaultController@destroy')->name('user.ai_knowledge_vault.delete');
        });

        // room management route start
        Route::prefix('/room-management')->group(function () {
            Route::group(['prefix' => 'settings'], function () {

                //preferences routes
                Route::group(['prefix' => 'preferences'], function () {
                    Route::get('/', 'User\RoomController@settings')->name('tenant.rooms_management.settings');
                    Route::post('/update', 'User\RoomController@updateSettings')->name('tenant.rooms_management.update_settings');
                });
                // amenities route
                //tax , fee routes
                Route::group(['prefix' => 'tax-fee'], function () {
                    Route::get('/', 'User\RoomController@taxFee')->name('tenant.rooms_management.tax_fee');
                    Route::post('/update', 'User\RoomController@updateTaxFee')->name('tenant.rooms_management.update_tax_fee');
                });

                Route::group(['prefix' => 'amenities'], function () {
                    Route::get('/', 'User\RoomController@amenities')->name('tenant.rooms_management.amenities');
                    Route::post('/store', 'User\RoomController@storeAmenity')->name('tenant.rooms_management.store_amenity');
                    Route::get('/edit/{id}', 'User\RoomController@editAmenity')->name('tenant.rooms_management.edit_amenity');
                    Route::post('/update', 'User\RoomController@updateAmenity')->name('tenant.rooms_management.update_amenity');
                    Route::post('/delete', 'User\RoomController@deleteAmenity')->name('tenant.rooms_management.delete_amenity');
                    Route::post('/bulk_delete', 'User\RoomController@bulkDeleteAmenity')->name('tenant.rooms_management.bulk_delete_amenity');
                });

                // coupon routes
                Route::group(['prefix' => 'coupons'], function () {
                    Route::get('/', 'User\RoomController@coupons')->name('tenant.rooms_management.coupons');
                    Route::post('/store-coupon', 'User\RoomController@storeCoupon')->name('tenant.rooms_management.store_coupon')
                        ->middleware('limitCheck:room_categories,store');
                    Route::post('/update-coupon', 'User\RoomController@updateCoupon')->name('tenant.rooms_management.update_coupon')->middleware('limitCheck:room_categories,update');
                    Route::post('/delete-coupon/{id}', 'User\RoomController@destroyCoupon')->name('tenant.rooms_management.delete_coupon');
                });
            });
            // rooms route
            Route::group(['prefix' => 'categories'], function () {
                Route::get('/', 'User\RoomController@rooms')->name('tenant.rooms_management.categories');
                Route::get('/create', 'User\RoomController@createRoom')->name('tenant.rooms_management.create_category');
                Route::post('/store', 'User\RoomController@storeRoom')->name('tenant.rooms_management.store_category')->middleware('limitCheck:room_categories,store');
                Route::get('/edit/{id}', 'User\RoomController@editRoom')->name('tenant.rooms_management.edit_category');
                Route::post('/slider_images', 'User\RoomController@sliderImgageUpload')->name('tenant.rooms_management.sliderImage');
                Route::post('/remove-slider-image', 'User\RoomController@removeImage')->name('tenant.rooms_management.remove_slider_image');
                Route::post('/detach-slider-image', 'User\RoomController@detachImage')->name('tenant.rooms_management.detach_slider_image');
                Route::post('/update/{id}', 'User\RoomController@updateRoom')->name('tenant.rooms_management.update_category')->middleware('limitCheck:room_categories,update');
                Route::post('/delete', 'User\RoomController@deleteRoom')->name('tenant.rooms_management.delete_category');
                Route::post('/bulk_delete', 'User\RoomController@bulkDeleteRoom')->name('tenant.rooms_management.bulk_delete_category');
            });
            Route::prefix('rooms')->group(function () {
                Route::get('/', 'User\RoomController@roomNumbers')->name('tenant.rooms_management.rooms');
                Route::get('/get-langwise-room-category', 'User\RoomController@getLangwiseRoomCategory')->name('tenant.rooms_management.get_langwise_room_category');
                Route::post('/store', 'User\RoomController@roomNumberStore')->name('tenant.rooms_management.room.store')->middleware('limitCheck:rooms,store');
                Route::post('/update', 'User\RoomController@roomNumberUpdate')->name('tenant.rooms_management.room.update')->middleware('limitCheck:rooms,update');
                Route::post('/delete', 'User\RoomController@roomNumberDelete')->name('tenant.rooms_management.room.delete');
                Route::post('/bulk_delete', 'User\RoomController@roomNumberBulkDelete')->name('tenant.rooms_management.room.bulk_delete');
            });
        });
        // room management route end

        // Room Bookings Routes
        Route::prefix('/room-bookings')->group(function () {
            Route::get('send-payment-link', 'User\RoomController@sendPaymentLink')->name('tenant.room_bookings.send_payment_link');

            Route::post('/total-rooms', 'User\RoomController@totalRooms')
                ->name('tenant.rooms_management.bookings.total_rooms');

            Route::get('/all', 'User\RoomController@bookings')->name('tenant.room_bookings.all_bookings');
            Route::get('/approved', 'User\RoomController@bookings')->name('tenant.room_bookings.approved_bookings');
            Route::get('/pending', 'User\RoomController@bookings')->name('tenant.room_bookings.pending_bookings');
            Route::get('/rejected', 'User\RoomController@bookings')->name('tenant.room_bookings.canceled_bookings');

            Route::get('/active', 'User\RoomController@bookings')->name('tenant.room_bookings.active_bookings');
            Route::get('/todays-booked', 'User\RoomController@todaysBooked')->name('tenant.room_bookings.todays_booked');

            Route::post('/update-payment-status', 'User\RoomController@updatePaymentStatus')->name('tenant.room_bookings.update_payment_status');
            Route::post('/update-partial-amount', 'User\RoomController@updatePartialAmount')->name('tenant.room_bookings.update_partial_amount');

            Route::post('/update-stay-status', 'User\RoomController@updateStayStatus')->name('tenant.room_bookings.update_stay_status');

            Route::post('/update-booking-status', 'User\RoomController@updateBookingStatus')->name('tenant.room_bookings.update_booking_status');

            Route::post('/make-refund', 'User\RoomController@makeRefund')->name('tenant.room_bookings.update_booking_cancel_refund');
            Route::prefix('refunds')->group(function () {
                Route::get('/', 'User\RoomController@refunds')->name('tenant.room_bookings.refunds');
                Route::post('/delete-refund', 'User\RoomController@deleteRefund')->name('tenant.room_bookings.refund.delete');
            });

            Route::get('/booking-details/{id}', 'User\RoomController@roomBookingDetails')->name('tenant.room_bookings.booking_details');

            Route::get('/booking-details-and-edit/{id}', 'User\RoomController@editBookingDetails')->name('admin.room_bookings.booking_details_and_edit');
            Route::post('/update', 'User\RoomController@updateBooking')->name('admin.room_bookings.update_booking');

            Route::post('/send-mail', 'User\RoomController@sendMail')->name('tenant.room_bookings.send_mail');

            Route::post('/delete-booking/{id}', 'User\RoomController@deleteBooking')->name('tenant.room_bookings.delete_booking');
            Route::post('/bulk-delete-booking', 'User\RoomController@bulkDeleteBooking')->name('tenant.room_bookings.bulk_delete_booking');

            //booking show create page and store them
            Route::get('/get-booked-dates', 'User\RoomController@bookedDates')->name('tenant.room_bookings.get_booked_dates');
            Route::get('/booking-form', 'User\RoomController@bookingForm')->name('tenant.room_bookings.booking_form');
            Route::post('/make-booking', 'User\RoomController@makeBooking')->name('tenant.room_bookings.make_booking')->middleware('limitCheck:room_bookings,store');

            //booking show edit page and update them
            Route::get('/booking-edit/{id}', 'User\RoomController@roomBookingEdit')->name('tenant.room_bookings.booking_edit');
            Route::post('/update_booking', 'User\RoomController@updateBooking')->name('tenant.room_bookings.update_booking')
                ->middleware('limitCheck:room_bookings,update');
            Route::post('/update-extra-payment/{booking_id}', 'User\RoomController@updateExtraPayment')
                ->name('tenant.room_bookings.update_extra_payment');


            // room booking report
            Route::get('/room/report', 'User\RoomController@report')->name('tenant.rooms_management.report');
            Route::get('/room/export-report', 'User\RoomController@exportReport')->name('tenant.rooms_management.export_report');

            // Check-ins Routes
            Route::prefix('check-ins')->group(function () {
                Route::get('/delayed', 'User\RoomController@checkIn')->name('tenant.room_bookings.check_ins.delayed');
                Route::get('/upcoming', 'User\RoomController@checkIn')->name('tenant.room_bookings.check_ins.upcoming');
            });

            // Check-ins Routes
            Route::prefix('check-outs')->group(function () {
                Route::get('/delayed', 'User\RoomController@checkOut')->name('tenant.room_bookings.check_outs.delayed');
                Route::get('/upcoming', 'User\RoomController@checkOut')->name('tenant.room_bookings.check_outs.upcoming');
            });
        });
        // Room Booking routes end

        // Staff Management routes start
        Route::prefix('/staff-management')->group(function () {
            Route::prefix('roles')->group(function () {
                Route::get('/', 'User\RoleController@index')->name('tenant.staff_management.roles');
                Route::post('/store', 'User\RoleController@store')->name('tenant.staff_management.role.store');
                Route::post('/update', 'User\RoleController@update')->name('tenant.staff_management.role.update');
                Route::post('/delete', 'User\RoleController@delete')->name('tenant.staff_management.role.delete');
                Route::get('/{id}/permissions/manage', 'User\RoleController@managePermissions')
                    ->name('tenant.staff_management.role.permissions.manage');
                Route::post('/permissions/update', 'User\RoleController@updatePermissions')
                    ->name('tenant.staff_management.role.permissions.update');
            });

            Route::prefix('staffs')->group(function () {
                Route::get('/', 'User\StaffController@index')->name('tenant.staff_management.staffs');
                Route::post('/store', 'User\StaffController@store')->name('tenant.staff_management.staff.store');
                Route::post('/update', 'User\StaffController@update')->name('tenant.staff_management.staff.update');
                Route::post('/delete', 'User\StaffController@delete')->name('tenant.staff_management.staff.delete');
            });
        });
        // Staff Management routes end

        // basic settings
        Route::group(['prefix' => 'settings'], function ($e) {
            //general-settings Routes
            Route::group(['prefix' => 'general-settings'], function () {
                Route::get('/', 'User\BasicController@generalSettings')->name('user.site_settings.general_settings');
                Route::post('/update', 'User\BasicController@updateGeneralSettings')->name('user.site_settings.update_general_settings');
            });
            //lanuages Routes
            Route::group(['prefix' => 'language'], function () {
                Route::get('/all', 'User\LanguageController@index')->name('user.language.index');
                Route::get('/{id}/edit', 'User\LanguageController@edit')->name('user.language.edit');
                Route::get('/{id}/edit/keyword', 'User\LanguageController@editKeyword')->name('user.language.editKeyword');
                Route::post('/{id}/update/keyword', 'User\LanguageController@updateKeyword')->name('user.language.updateKeyword')->middleware('limitCheck:languages,update');
                Route::post('/store', 'User\LanguageController@store')->name('user.language.store')->middleware('limitCheck:languages,store');
                Route::post('/upload', 'User\LanguageController@upload')->name('user.language.upload');
                Route::post('/{id}/uploadUpdate', 'User\LanguageController@uploadUpdate')->name('user.language.uploadUpdate');
                Route::post('/{id}/default', 'User\LanguageController@makeDefault')->name('user.language.default')->withoutMiddleware('Demo');
                Route::post('/{id}/dashboard-default', 'User\LanguageController@makeDashboardDefault')->name('user.language.dashboard_default')->withoutMiddleware('Demo');
                Route::post('/{id}/delete', 'User\LanguageController@destroy')->name('user.language.delete');
                Route::post('/update', 'User\LanguageController@update')->name('user.language.update')->middleware('limitCheck:languages,update');
            });

            //Gateways Routes
            Route::group(['prefix' => 'gateways'], function () {
                //User Online Gateway Routes
                Route::get('/', 'User\GatewayController@index')->name('user.gateway.index');
                Route::post('/update', 'User\GatewayController@updateGateway')->name('user.gateway.update');

                // User Offline Gateway Routes
                Route::get('/offline', 'User\GatewayController@offline')->name('user.gateway.offline');
                Route::post('/offline/store', 'User\GatewayController@store')->name('user.gateway.offline.store');
                Route::post('/offline/update', 'User\GatewayController@update')->name('user.gateway.offline.update');
                Route::post('/offline/status', 'User\GatewayController@status')->name('user.offline.status');
                Route::post('/offline/delete', 'User\GatewayController@delete')->name('user.offline.gateway.delete');
            });

            // User Change Password Routes
            Route::group(['prefix' => 'password'], function () {
                Route::get('/change', 'User\UserController@changePass')->name('user.changePass');
                Route::post('/update', 'User\UserController@updatePassword')->name('user.updatePassword');
            });

            //profile Routes
            Route::group(['prefix' => 'profile'], function () {
                Route::get('/edit', 'User\UserController@profile')->name('user.profile_edit');
                Route::post('/update', 'User\UserController@profileupdate')->name('user-profile-update');
            });

            // mail  routes
            Route::group(['prefix' => 'mail'], function ($e) {
                Route::get('/information', 'User\BasicController@getMailInformation')->name('user.mail.info');
                Route::post('/information', 'User\BasicController@storeMailInformation')->name('user.mail.info.update');
                Route::get('/templates', 'User\MailTemplateController@index')->name('user.mail_templates');
                Route::get('/edit-mail-template/{id}', 'User\MailTemplateController@edit')->name('user.edit_mail_template');
                Route::post('/update-mail-template/{id}', 'User\MailTemplateController@update')->name('user.update_mail_template');
            });
        });
        // Tenant tickets route start
        Route::group(['middleware' => 'checkUserPermission:Support Ticket'], function () {
            Route::get('/tickets', 'User\TicketController@index')->name('tenant.tickets');
            Route::get('/ticket/messages/{id}', 'User\TicketController@messages')->name('tenant.ticket.messages');
            Route::post('/ticket/store/', 'User\TicketController@ticketstore')->name('tenant.ticket.store');
            Route::post('/ticket/reply/{id}', 'User\TicketController@ticketreply')->name('tenant.ticket.reply');
            Route::post('/zip-file/upload', 'User\TicketController@zip_upload')->name('zip.upload');
        });

        //user package extend route
        Route::group(['prefix' => 'membership'], function ($e) {
            Route::get('/package-list', 'User\BuyPlanController@index')->name('user.plan.extend.index');
            Route::get('/package/checkout/{package_id}', 'User\BuyPlanController@checkout')->name('user.plan.extend.checkout');
            Route::post('/package/checkout', 'User\UserCheckoutController@checkout')->name('user.plan.checkout');
            // Payment Log
            Route::get('/payment-log', 'User\PaymentLogController@index')->name('user.payment-log.index');
        });
        // user QR Builder
        Route::group(['middleware' => 'checkUserPermission:QR Builder'], function () {
            Route::get('/saved/qrs', 'User\QrController@index')->name('user.qrcode.index');
            Route::post('/saved/qr/delete', 'User\QrController@delete')->name('user.qrcode.delete')->withoutMiddleware('Demo');;
            Route::post('/saved/qr/bulk-delete', 'User\QrController@bulkDelete')->name('user.qrcode.bulk.delete')->withoutMiddleware('Demo');
            Route::get('/qr-code', 'User\QrController@qrCode')->name('user.qrcode');
            Route::post('/qr-code/generate', 'User\QrController@generate')->name('user.qrcode.generate')->withoutMiddleware('Demo');
            Route::get('/qr-code/clear', 'User\QrController@clear')->name('user.qrcode.clear');
            Route::post('/qr-code/save', 'User\QrController@save')->name('user.qrcode.save')->withoutMiddleware('Demo');;
            Route::get('qr-code/download/{name?}', 'User\QrController@download')->name('user.qrcode.download');
        });
    });
});
