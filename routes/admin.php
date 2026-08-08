<?php

use Illuminate\Support\Facades\Route;

$domain = env('WEBSITE_HOST');

if (!app()->runningInConsole()) {
    if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
        $domain = 'www.' . env('WEBSITE_HOST');
    }
}


Route::domain($domain)->group(function () {
    Route::get('admin/set-locale', 'Admin\BasicController@setLocaleAdmin')->name('admin.change.local.language');

    Route::group(['prefix' => 'admin', 'middleware' => ['guest:admin', 'adminLang']], function () {

        Route::get('/', 'Admin\LoginController@login')->name('admin.login');
        Route::post('/login', 'Admin\LoginController@authenticate')->name('admin.auth');

        Route::get('/mail-form', 'Admin\ForgetController@mailForm')->name('admin.forget.form');
        Route::get('/create/password-form', 'Admin\ForgetController@passwordCreateForm')->name('admin.create.pasword.form');
        Route::post('/create/password-form/submit', 'Admin\ForgetController@createNewPassword')->name('admin.create.password.submit')->middleware('Demo');
        Route::post('/sendmail', 'Admin\ForgetController@sendmail')->name('admin.forget.mail')->middleware('Demo');
    });

    Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin', 'checkstatus', 'adminLang', 'Demo']], function () {
        // RTL check
        Route::get('/rtlcheck/{langid}', 'Admin\LanguageController@rtlcheck')->name('admin.rtlcheck');
        // admin redirect to dashboard route
        Route::get('/change-theme', 'Admin\DashboardController@changeTheme')->name('admin.theme.change');

        // Summernote image upload
        Route::post('/summernote/upload', 'Admin\SummernoteController@upload')->name('admin.summernote.upload');
        // Admin logout Route
        Route::get('/logout', 'Admin\LoginController@logout')->name('admin.logout');
        // Admin Dashboard Routes
        Route::get('/dashboard', 'Admin\DashboardController@dashboard')->name('admin.dashboard');
        // Admin Profile Routes
        Route::get('/changePassword', 'Admin\ProfileController@changePass')->name('admin.changePass');
        Route::post('/profile/updatePassword', 'Admin\ProfileController@updatePassword')->name('admin.updatePassword');
        Route::get('/profile/edit', 'Admin\ProfileController@editProfile')->name('admin.editProfile');
        Route::post('/profile/update', 'Admin\ProfileController@updateProfile')->name('admin.updateProfile');

        Route::group(['middleware' => 'checkpermission:Settings'], function () {
            // Admin Basic Information Routes
            Route::get('/basicinfo', 'Admin\BasicController@basicinfo')->name('admin.basicinfo');
            Route::post('/basicinfo/post', 'Admin\BasicController@updatebasicinfo')->name('admin.basicinfo.update');
            // Admin Email Settings Routes
            Route::get('/mail-from-admin', 'Admin\EmailController@mailFromAdmin')->name('admin.mailFromAdmin');
            Route::post('/mail-from-admin/update', 'Admin\EmailController@updateMailFromAdmin')->name('admin.mailfromadmin.update');
            Route::get('/mail-to-admin', 'Admin\EmailController@mailToAdmin')->name('admin.mailToAdmin');
            Route::post('/mail-to-admin/update', 'Admin\EmailController@updateMailToAdmin')->name('admin.mailtoadmin.update');

            Route::get('/mail-templates', 'Admin\MailTemplateController@mailTemplates')->name('admin.mail_templates');
            Route::get('/edit_mail_template/{id}', 'Admin\MailTemplateController@editMailTemplate')->name('admin.edit_mail_template');
            Route::post('/update_mail_template/{id}', 'Admin\MailTemplateController@updateMailTemplate')->name('admin.update_mail_template');
            // Admin Breadcrumb Routes
            Route::get('/breadcrumb', 'Admin\BasicController@breadcrumb')->name('admin.breadcrumb');
            Route::post('/breadcrumb/update', 'Admin\BasicController@updatebreadcrumb')->name('admin.breadcrumb.update');
            // Admin Scripts Routes
            Route::get('/script', 'Admin\BasicController@script')->name('admin.script');
            Route::post('/script/update', 'Admin\BasicController@updatescript')->name('admin.script.update');
            // Admin Social Routes
            Route::get('/social', 'Admin\SocialController@index')->name('admin.social.index');
            Route::post('/social/store', 'Admin\SocialController@store')->name('admin.social.store');
            Route::get('/social/{id}/edit', 'Admin\SocialController@edit')->name('admin.social.edit');
            Route::post('/social/update', 'Admin\SocialController@update')->name('admin.social.update');
            Route::post('/social/delete', 'Admin\SocialController@delete')->name('admin.social.delete');
            // Admin Maintenance Mode Routes
            Route::get('/maintainance', 'Admin\BasicController@maintainance')->name('admin.maintainance');
            Route::post('/maintainance/update', 'Admin\BasicController@updatemaintainance')->name('admin.maintainance.update');
            // Admin Section Customization Routes
            Route::get('/sections', 'Admin\BasicController@sections')->name('admin.sections.index');
            Route::post('/sections/update', 'Admin\BasicController@updatesections')->name('admin.sections.update');
            // Admin Cookie Alert Routes
            Route::get('/cookie-alert', 'Admin\BasicController@cookiealert')->name('admin.cookie.alert');
            Route::post('/cookie-alert/{langid}/update', 'Admin\BasicController@updatecookie')->name('admin.cookie.update');
            // basic settings seo route
            Route::get('/seo', 'Admin\BasicController@seo')->name('admin.seo');
            Route::post('/seo/update', 'Admin\BasicController@updateSEO')->name('admin.seo.update');

            // Admin Language Routes
            Route::get('/languages', 'Admin\LanguageController@index')->name('admin.language.index');
            Route::get('/language/{id}/edit', 'Admin\LanguageController@edit')->name('admin.language.edit');
            Route::get('/language/{id}/edit/keyword', 'Admin\LanguageController@editKeyword')->name('admin.language.editKeyword');
            Route::post('/language/store', 'Admin\LanguageController@store')->name('admin.language.store');
            Route::post('/language/upload', 'Admin\LanguageController@upload')->name('admin.language.upload');
            Route::post('/language/{id}/uploadUpdate', 'Admin\LanguageController@uploadUpdate')->name('admin.language.uploadUpdate');
            Route::post('/language/{id}/default', 'Admin\LanguageController@default')->name('admin.language.default')->withoutMiddleware('Demo');
            Route::post('/language/{id}/delete', 'Admin\LanguageController@delete')->name('admin.language.delete');
            Route::post('/language/update', 'Admin\LanguageController@update')->name('admin.language.update');
            Route::post('/language/{id}/update/keyword', 'Admin\LanguageController@updateKeyword')->name('admin.language.updateKeyword');
            Route::post('/language/add-keyword', 'Admin\LanguageController@addKeyword')->name('admin.language.add_keyword');
            Route::post('/language/add-keyword/admin', 'Admin\LanguageController@addKeywordForAdmin')->name('admin.language.add_keyword.admin.dashboard');
            Route::post('/language/add-keyword/user-dashboard', 'Admin\LanguageController@addKeywordForUserDashboard')->name('admin.language.add_keyword.user.dashboard');
            Route::post('/language/{id}/dashboard-default', 'Admin\LanguageController@dashboardDefault')->name('admin.language.dashboardDefault')->withoutMiddleware('Demo');

            //admin admin dashbaord
            Route::get('/language/{id}/edit/admin-dashboard/keyword', 'Admin\LanguageController@editAdminKeyword')->name('admin.language.admin_dashboard.editKeyword');
            Route::post('/language/{id}/update/admin-dashboard-keyword', 'Admin\LanguageController@updateAdminKeyword')->name('admin.language.admin_dashboard.updateKeyword');

            //user dashbaord keywords
            Route::get('/language/{id}/edit/user-dashboard/keyword', 'Admin\LanguageController@editUserKeyword')->name('admin.language.user_dashboard.editKeyword');
            Route::post('/language/{id}/update/user-dashboard-keyword', 'Admin\LanguageController@updateUserDashboardKeyword')->name('admin.language.user_dashboard.updateKeyword');

            // user frontent
            Route::get('/language/{id}/edit/user-frontend/keyword', 'Admin\LanguageController@editUserFrontendKeyword')->name('admin.language.user_frontend.editKeyword');
            Route::post('/language/{id}/update/user-frontend-keyword', 'Admin\LanguageController@updateCustomerKeyword')->name('admin.language.user_frontend.updateKeyword');
            // Admin online Gateway Routes
            Route::get('/gateways', 'Admin\GatewayController@index')->name('admin.gateway.index');
            Route::post('/update/gateway', 'Admin\GatewayController@updateGateway')->name('admin.gateway.update');

            // Admin Offline Gateway Routes
            Route::get('/offline/gateways', 'Admin\GatewayController@offline')->name('admin.gateway.offline');
            Route::post('/offline/gateway/store', 'Admin\GatewayController@store')->name('admin.gateway.offline.store');
            Route::post('/offline/gateway/update', 'Admin\GatewayController@update')->name('admin.gateway.offline.update');
            Route::post('/offline/status', 'Admin\GatewayController@status')->name('admin.offline.status');
            Route::post('/offline/gateway/delete', 'Admin\GatewayController@delete')->name('admin.offline.gateway.delete');

            //sitemap
            Route::get('/sitemap', 'Admin\SitemapController@index')->name('admin.sitemap.index');
            Route::post('/sitemap/store', 'Admin\SitemapController@store')->name('admin.sitemap.store');
            Route::get('/sitemap/{id}/update', 'Admin\SitemapController@update')->name('admin.sitemap.update');
            Route::post('/sitemap/{id}/delete', 'Admin\SitemapController@delete')->name('admin.sitemap.delete');
            Route::post('/sitemap/download', 'Admin\SitemapController@download')->name('admin.sitemap.download');

            // Admin Cache Clear Routes
            Route::get('/cache-clear', 'Admin\CacheController@clear')->name('admin.cache.clear');
        });

        Route::group(['middleware' => 'checkpermission:Users Management'], function () {
            // Register User start
            Route::get('register/users', 'Admin\RegisterUserController@index')->name('admin.register.user');
            Route::post('register/user/store', 'Admin\RegisterUserController@store')->name('register.user.store');
            Route::post('secret/user/login', 'Admin\RegisterUserController@secretLogin')->name('register.user.secretLogin')->withoutMiddleware('Demo');
            Route::post('register/users/ban', 'Admin\RegisterUserController@userban')->name('register.user.ban');
            Route::post('register/users/publicly', 'Admin\RegisterUserController@userPublicly')->name('register.user.publicly');
            Route::post('register/users/featured', 'Admin\RegisterUserController@userFeatured')->name('register.user.featured');
            Route::post('register/users/email', 'Admin\RegisterUserController@emailStatus')->name('register.user.email');
            Route::get('register/user/details/{id}', 'Admin\RegisterUserController@view')->name('register.user.view');
            Route::post('/update/register/user/', 'Admin\RegisterUserController@updateUser')->name('update.register.user');
            Route::post('/user/current-package/remove', 'Admin\RegisterUserController@removeCurrPackage')->name('user.currPackage.remove');
            Route::post('/user/current-package/change', 'Admin\RegisterUserController@changeCurrPackage')->name('user.currPackage.change');
            Route::post('/user/current-package/add', 'Admin\RegisterUserController@addCurrPackage')->name('user.currPackage.add');
            Route::post('/user/next-package/remove', 'Admin\RegisterUserController@removeNextPackage')->name('user.nextPackage.remove');
            Route::post('/user/next-package/change', 'Admin\RegisterUserController@changeNextPackage')->name('user.nextPackage.change');
            Route::post('/user/next-package/add', 'Admin\RegisterUserController@addNextPackage')->name('user.nextPackage.add');
            Route::post('register/user/delete', 'Admin\RegisterUserController@delete')->name('register.user.delete');
            Route::post('register/user/bulk-delete', 'Admin\RegisterUserController@bulkDelete')->name('register.user.bulk.delete');
            Route::get('register/user/{id}/changePassword', 'Admin\RegisterUserController@changePass')->name('register.user.changePass');
            Route::post('register/user/updatePassword', 'Admin\RegisterUserController@updatePassword')->name('register.user.updatePassword');

            Route::get('topup-ai-crdits/{id}', 'Admin\RegisterUserController@topupAICredits')->name('admin.register.topup-ai-crdits');
            // Balance Add Subtract
            Route::get('/add/subtract/user/balance/{id}', 'Admin\RegisterUserController@userBalnaceAddSubtract')->name('user.add.subtract');
            //Register User end

            // Admin Subscriber Routes
            Route::get('/subscribers', 'Admin\SubscriberController@index')->name('admin.subscriber.index');
            Route::get('/mailsubscriber', 'Admin\SubscriberController@mailsubscriber')->name('admin.mailsubscriber');
            Route::post('/subscribers/sendmail', 'Admin\SubscriberController@subscsendmail')->name('admin.subscribers.sendmail');
            Route::post('/subscriber/delete', 'Admin\SubscriberController@delete')->name('admin.subscriber.delete');
            Route::post('/subscriber/bulk-delete', 'Admin\SubscriberController@bulkDelete')->name('admin.subscriber.bulk.delete');
        });


        Route::group(['middleware' => 'checkpermission:Pages'], function () {
            // Menu Manager Routes
            Route::get('/pages', 'Admin\PageController@index')->name('admin.page.index');
            Route::get('/page/create', 'Admin\PageController@create')->name('admin.page.create');
            Route::post('/page/store', 'Admin\PageController@store')->name('admin.page.store');
            Route::get('/page/{menuID}/edit', 'Admin\PageController@edit')->name('admin.page.edit');
            Route::post('/page/update', 'Admin\PageController@update')->name('admin.page.update');
            Route::post('/page/delete', 'Admin\PageController@delete')->name('admin.page.delete');
            Route::post('/page/bulk-delete', 'Admin\PageController@bulkDelete')->name('admin.page.bulk.delete');

            //aditional section routes

            Route::prefix('additional-sections')->group(function () {
                Route::get('sections', 'Admin\AdditionalSectionController@index')->name('admin.additional_sections');
                Route::get('add-section', 'Admin\AdditionalSectionController@create')->name('admin.additional_section.create');
                Route::post('store-section', 'Admin\AdditionalSectionController@store')->name('admin.additional_section.store');
                Route::get('edit-section/{id}', 'Admin\AdditionalSectionController@edit')->name('admin.additional_section.edit');
                Route::post('update/{id}', 'Admin\AdditionalSectionController@update')->name('admin.additional_section.update');
                Route::post('delete/{id}', 'Admin\AdditionalSectionController@delete')->name('admin.additional_section.delete');
                Route::post('bulkdelete', 'Admin\AdditionalSectionController@bulkdelete')->name('admin.additional_section.bulkdelete');
            });

            // Admin Hero Section Image & Text Routes
            Route::get('/home-page/imgtext', 'Admin\HerosectionController@imgtext')->name('admin.herosection.imgtext');
            Route::post('/herosection/{langid}/update', 'Admin\HerosectionController@update')->name('admin.herosection.update');

            //Hero Section Slider Routes
            Route::get('/slider', 'Admin\SliderController@index')->name('admin.slider.index');
            Route::get('/slider/create', 'Admin\SliderController@create')->name('admin.slider.create');
            Route::post('/slider/store', 'Admin\SliderController@store')->name('admin.slider.store');
            Route::post('/slider/update', 'Admin\SliderController@update')->name('admin.slider.update');
            Route::post('/slider/delete', 'Admin\SliderController@delete')->name('admin.slider.delete');
            Route::post('/slider/bulk-delete', 'Admin\SliderController@bulkDelete')->name('admin.slider.bulk.delete');

            // Platform Modules Routes
            Route::get('/platform-modules', 'Admin\PlatformModuleController@index')->name('admin.platform_module.index');
            Route::get('/platform-modules/create', 'Admin\PlatformModuleController@create')->name('admin.platform_module.create');
            Route::post('/platform-modules/store', 'Admin\PlatformModuleController@store')->name('admin.platform_module.store');
            Route::post('/platform-modules/update', 'Admin\PlatformModuleController@update')->name('admin.platform_module.update');
            Route::post('/platform-modules/delete', 'Admin\PlatformModuleController@delete')->name('admin.platform_module.delete');
            Route::post('/platform-modules/bulk-delete', 'Admin\PlatformModuleController@bulkDelete')->name('admin.platform_module.bulk.delete');

            //about page image texts
            Route::get('/about-page/imgtext', 'Admin\AboutPageController@imgtext')->name('admin.aboutpage.imgtext');
            Route::post('/about/{langid}/update', 'Admin\AboutPageController@update')->name('admin.aboutpage.update');
            Route::post('/about/{langid}/gallery/upload', 'Admin\AboutPageController@galleryUpload')->name('admin.aboutpage.gallery.upload');
            Route::post('/about/gallery/{id}/delete', 'Admin\AboutPageController@galleryDelete')->name('admin.aboutpage.gallery.delete');

            // Admin Feature Routes
            Route::get('/features', 'Admin\FeatureController@index')->name('admin.feature.index');
            Route::post('/feature/store', 'Admin\FeatureController@store')->name('admin.feature.store');
            Route::get('/feature/{id}/edit', 'Admin\FeatureController@edit')->name('admin.feature.edit');
            Route::post('/feature/update', 'Admin\FeatureController@update')->name('admin.feature.update');
            Route::post('/feature/delete', 'Admin\FeatureController@delete')->name('admin.feature.delete');

            // Admin Work Process Routes
            Route::get('/process', 'Admin\ProcessController@index')->name('admin.process.index');
            Route::post('/process/store', 'Admin\ProcessController@store')->name('admin.process.store');
            Route::get('/process/{id}/edit', 'Admin\ProcessController@edit')->name('admin.process.edit');
            Route::post('/process/update', 'Admin\ProcessController@update')->name('admin.process.update');
            Route::post('/process/delete', 'Admin\ProcessController@delete')->name('admin.process.delete');

            // Admin Intro Section Routes
            Route::post('/introsection/remove/image', 'Admin\IntrosectionController@removeImage')->name('admin.introsection.img.rmv');

            // Admin Testimonial Routes
            Route::get('/testimonials', 'Admin\TestimonialController@index')->name('admin.testimonial.index');
            Route::get('/testimonial/create', 'Admin\TestimonialController@create')->name('admin.testimonial.create');
            Route::post('/testimonial/store', 'Admin\TestimonialController@store')->name('admin.testimonial.store');
            Route::get('/testimonial/{id}/edit', 'Admin\TestimonialController@edit')->name('admin.testimonial.edit');
            Route::post('/testimonial/update', 'Admin\TestimonialController@update')->name('admin.testimonial.update');
            Route::post('/testimonial/delete', 'Admin\TestimonialController@delete')->name('admin.testimonial.delete');
            Route::post('/testimonialtext/{langid}/update', 'Admin\TestimonialController@textupdate')->name('admin.testimonialtext.update');

            // Admin Partner Routes
            Route::get('/partners', 'Admin\PartnerController@index')->name('admin.partner.index');
            Route::post('/partner/store', 'Admin\PartnerController@store')->name('admin.partner.store');
            Route::post('/partner/upload', 'Admin\PartnerController@upload')->name('admin.partner.upload');
            Route::get('/partner/{id}/edit', 'Admin\PartnerController@edit')->name('admin.partner.edit');
            Route::post('/partner/update', 'Admin\PartnerController@update')->name('admin.partner.update');
            Route::post('/partner/{id}/uploadUpdate', 'Admin\PartnerController@uploadUpdate')->name('admin.partner.uploadUpdate');
            Route::post('/partner/delete', 'Admin\PartnerController@delete')->name('admin.partner.delete');

            //about us pages
            Route::group(['prefix' => 'about-us'], function () {
                Route::get('/update-section-status', 'Admin\BasicController@aboutSectionInfo')->name('admin.abouts.section.hide_show');
                Route::post('/update-section-status/update', 'Admin\BasicController@aboutSectionInfoUpdate')->name('admin.abouts.section.hide_show.update');
            });

            //additional sections
            Route::prefix('additional-sections-about-us')->group(function () {
                Route::get('sections', 'Admin\AboutAdditionSectionController@index')->name('admin.about_us.additional_sections');
                Route::get('add-section', 'Admin\AboutAdditionSectionController@create')->name('admin.about_us.additional_section.create');
                Route::post('store-section', 'Admin\AboutAdditionSectionController@store')->name('admin.about_us.additional_section.store');
                Route::get('edit-section/{id}', 'Admin\AboutAdditionSectionController@edit')->name('admin.about_us.additional_section.edit');
                Route::post('update/{id}', 'Admin\AboutAdditionSectionController@update')->name('admin.about_us.additional_section.update');
                Route::post('delete/{id}', 'Admin\AboutAdditionSectionController@delete')->name('admin.about_us.additional_section.delete');
                Route::post('bulkdelete', 'Admin\AboutAdditionSectionController@bulkdelete')->name('admin.about_us.additional_section.bulkdelete');
            });

            Route::get('headings', 'Admin\BasicController@heading')->name('admin.breadcrumb.heading');
            Route::post('headings/update', 'Admin\BasicController@update_heading')->name('admin.breadcrumb.heading_update');
            //404 page error
            Route::get('error-page-404', 'Admin\BasicController@error_404')->name('admin.error_404');
            Route::post('/update/error-page-404', 'Admin\BasicController@updateError_404')->name('admin.update_error_404');


            // Admin FAQ Routes
            Route::get('/faqs', 'Admin\FaqController@index')->name('admin.faq.index');
            Route::get('/faq/create', 'Admin\FaqController@create')->name('admin.faq.create');
            Route::post('/faq/store', 'Admin\FaqController@store')->name('admin.faq.store');
            Route::post('/faq/update', 'Admin\FaqController@update')->name('admin.faq.update');
            Route::post('/faq/delete', 'Admin\FaqController@delete')->name('admin.faq.delete');
            Route::post('/faq/bulk-delete', 'Admin\FaqController@bulkDelete')->name('admin.faq.bulk.delete');

            // Admin Blog Category Routes
            Route::get('/bcategorys', 'Admin\BcategoryController@index')->name('admin.bcategory.index');
            Route::post('/bcategory/store', 'Admin\BcategoryController@store')->name('admin.bcategory.store');
            Route::get('/bcategory/edit/{id}', 'Admin\BcategoryController@edit')->name('admin.bcategory.edit');
            Route::post('/bcategory/update', 'Admin\BcategoryController@update')->name('admin.bcategory.update');
            Route::post('/bcategory/delete', 'Admin\BcategoryController@delete')->name('admin.bcategory.delete');
            Route::post('/bcategory/bulk-delete', 'Admin\BcategoryController@bulkDelete')->name('admin.bcategory.bulk.delete');

            // Admin Blog Routes
            Route::get('/blogs', 'Admin\BlogController@index')->name('admin.blog.index');
            Route::post('/blog/upload', 'Admin\BlogController@upload')->name('admin.blog.upload');
            Route::post('/blog/store', 'Admin\BlogController@store')->name('admin.blog.store');
            Route::get('/blog/{id}/edit', 'Admin\BlogController@edit')->name('admin.blog.edit');
            Route::post('/blog/update', 'Admin\BlogController@update')->name('admin.blog.update');
            Route::post('/blog/{id}/uploadUpdate', 'Admin\BlogController@uploadUpdate')->name('admin.blog.uploadUpdate');
            Route::post('/blog/delete', 'Admin\BlogController@delete')->name('admin.blog.delete');
            Route::post('/blog/bulk-delete', 'Admin\BlogController@bulkDelete')->name('admin.blog.bulk.delete');
            Route::get('/blog/{langid}/getcats', 'Admin\BlogController@getcats')->name('admin.blog.getcats');

            // Admin Contact Routes
            Route::get('/contact', 'Admin\ContactController@index')->name('admin.contact.index');
            Route::post('/contact/{langid}/post', 'Admin\ContactController@update')->name('admin.contact.update');

            // Admin Footer Logo Text Routes
            Route::get('/footers', 'Admin\FooterController@index')->name('admin.footer.index');
            Route::post('/footer/{langid}/update', 'Admin\FooterController@update')->name('admin.footer.update');
            Route::post('/footer/remove/image', 'Admin\FooterController@removeImage')->name('admin.footer.rmvimg');

            // Admin Useful link Routes
            Route::get('/ulinks', 'Admin\UlinkController@index')->name('admin.ulink.index');
            Route::get('/ulink/create', 'Admin\UlinkController@create')->name('admin.ulink.create');
            Route::post('/ulink/store', 'Admin\UlinkController@store')->name('admin.ulink.store');
            Route::get('/ulink/{id}/edit', 'Admin\UlinkController@edit')->name('admin.ulink.edit');
            Route::post('/ulink/update', 'Admin\UlinkController@update')->name('admin.ulink.update');
            Route::post('/ulink/delete', 'Admin\UlinkController@delete')->name('admin.ulink.delete');

            //menu builder
            Route::get('/menu-builder', 'Admin\MenuBuilderController@index')->name('admin.menu_builder.index');
            Route::post('/menu-builder/update', 'Admin\MenuBuilderController@update')->name('admin.menu_builder.update');
        });

        // Announcement Popup Routes
        Route::group(['middleware' => 'checkpermission:Announcement Popup'], function () {
            Route::get('popups', 'Admin\PopupController@index')->name('admin.popup.index');
            Route::get('popup/types', 'Admin\PopupController@types')->name('admin.popup.types');
            Route::get('popup/{id}/edit', 'Admin\PopupController@edit')->name('admin.popup.edit');
            Route::get('popup/create', 'Admin\PopupController@create')->name('admin.popup.create');
            Route::post('popup/store', 'Admin\PopupController@store')->name('admin.popup.store');
            Route::post('popup/delete', 'Admin\PopupController@delete')->name('admin.popup.delete');
            Route::post('popup/bulk-delete', 'Admin\PopupController@bulkDelete')->name('admin.popup.bulk.delete');
            Route::post('popup/status', 'Admin\PopupController@status')->name('admin.popup.status');
            Route::post('popup/update', 'Admin\PopupController@update')->name('admin.popup.update');
        });


        Route::group(['middleware' => 'checkpermission:Admins Management'], function () {
            // Admin Users Routes
            Route::get('/users-admin', 'Admin\UserController@index')->name('admin.user.index');
            Route::post('/user/upload', 'Admin\UserController@upload')->name('admin.user.upload');
            Route::post('/user/store', 'Admin\UserController@store')->name('admin.user.store');
            Route::get('/user/{id}/edit', 'Admin\UserController@edit')->name('admin.user.edit');
            Route::post('/user/update', 'Admin\UserController@update')->name('admin.user.update');
            Route::post('/user/{id}/uploadUpdate', 'Admin\UserController@uploadUpdate')->name('admin.user.uploadUpdate');
            Route::post('/user/delete', 'Admin\UserController@delete')->name('admin.user.delete');

            // Admin Roles Routes
            Route::get('/roles', 'Admin\RoleController@index')->name('admin.role.index');
            Route::post('/role/store', 'Admin\RoleController@store')->name('admin.role.store');
            Route::post('/role/update', 'Admin\RoleController@update')->name('admin.role.update');
            Route::post('/role/delete', 'Admin\RoleController@delete')->name('admin.role.delete');
            Route::get('role/{id}/permissions/manage', 'Admin\RoleController@managePermissions')->name('admin.role.permissions.manage');
            Route::post('role/permissions/update', 'Admin\RoleController@updatePermissions')->name('admin.role.permissions.update');
        });

        Route::group(['middleware' => 'checkpermission:Packages'], function () {
            // Package Settings routes
            Route::get('/package/settings', 'Admin\PackageController@settings')->name('admin.package.settings');
            Route::post('/package/settings', 'Admin\PackageController@updateSettings')->name('admin.package.settings');
            // Package Settings routes
            Route::get('/package/features', 'Admin\PackageController@features')->name('admin.package.features');
            Route::post('/update/package/features', 'Admin\PackageController@updateFeatures')->name('admin.package.features.update');
            // Package routes
            Route::get('packages', 'Admin\PackageController@index')->name('admin.package.index');
            Route::post('package/upload', 'Admin\PackageController@upload')->name('admin.package.upload');
            Route::post('package/store', 'Admin\PackageController@store')->name('admin.package.store');
            Route::get('package/{id}/edit', 'Admin\PackageController@edit')->name('admin.package.edit');
            Route::post('package/update', 'Admin\PackageController@update')->name('admin.package.update');
            Route::post('package/{id}/uploadUpdate', 'Admin\PackageController@uploadUpdate')->name('admin.package.uploadUpdate');
            Route::post('package/delete', 'Admin\PackageController@delete')->name('admin.package.delete');
            Route::post('package/bulk-delete', 'Admin\PackageController@bulkDelete')->name('admin.package.bulk.delete');

            // Admin Coupon Routes
            Route::get('/coupon', 'Admin\CouponController@index')->name('admin.coupon.index');
            Route::post('/coupon/store', 'Admin\CouponController@store')->name('admin.coupon.store');
            Route::get('/coupon/{id}/edit', 'Admin\CouponController@edit')->name('admin.coupon.edit');
            Route::post('/coupon/update', 'Admin\CouponController@update')->name('admin.coupon.update');
            Route::post('/coupon/delete', 'Admin\CouponController@delete')->name('admin.coupon.delete');
            // Admin Coupon Routes End
        });

            // Payment Log
            Route::get('/subscription-log', 'Admin\PaymentLogController@index')->name('admin.payment-log.index');
            Route::post('/subscription-log/update', 'Admin\PaymentLogController@update')->name('admin.payment-log.update');

        // AI Credit
        Route::group(['middleware' => 'checkpermission:Additional AI Tokens'], function () {
            Route::get('/price-settings', 'Admin\AiCreditController@priceSettings')->name('admin.ai-credit.price-settings');
            Route::get('/additional-credit-request', 'Admin\AiCreditController@index')->name('admin.ai-credit.index');


            Route::post('/ai-credit/price-update', 'Admin\AiCreditController@creditPrice')->name('admin.ai-credit.price-update');
            Route::post('/ai-credit/recharge', 'Admin\AiCreditController@recharge')->name('admin.ai-credit.recharge');
            Route::post('/ai-credit/topup-status', 'Admin\AiCreditController@updateTopupStatus')
                ->name('admin.ai-credit.topup-status');
        });

        // Admin Support Ticket Routes
        Route::group(['middleware' => 'checkpermission:Support Tickets'], function () {
            Route::get('/all/tickets', 'Admin\TicketController@all')->name('admin.tickets.all');
            Route::get('/pending/tickets', 'Admin\TicketController@pending')->name('admin.tickets.pending');
            Route::get('/open/tickets', 'Admin\TicketController@open')->name('admin.tickets.open');
            Route::get('/closed/tickets', 'Admin\TicketController@closed')->name('admin.tickets.closed');
            Route::get('/ticket/messages/{id}', 'Admin\TicketController@messages')->name('admin.ticket.messages');
            Route::post('/ticket/reply/{id}', 'Admin\TicketController@ticketReply')->name('admin.ticket.reply');
            Route::get('/ticket/close/{id}', 'Admin\TicketController@ticketclose')->name('admin.ticket.close');
            Route::post('/ticket/assign/staff', 'Admin\TicketController@ticketAssign')->name('ticket.assign.staff');
            Route::get('/ticket/settings', 'Admin\TicketController@settings')->name('admin.ticket.settings');
            Route::post('/ticket/settings', 'Admin\TicketController@updateSettings')->name('admin.ticket.settings');
            Route::get('/ticket/create', 'Admin\TicketController@create')->name('admin.ticket.create');
            Route::post('/ticket/store/', 'Admin\TicketController@ticketstore')->name('admin.ticket.store');
            Route::post('/zip-file/upload', 'Admin\TicketController@zip_upload')->name('admin.zip.upload');

            Route::get('/transcation', 'Admin\TranscationController@transcation')->name('admin.transcation');
            Route::post('/transcation/delete', 'Admin\TranscationController@destroy')->name('admin.transcation.delete');
            Route::post('/transcation/bulk-delete', 'Admin\TranscationController@bulk_destroy')->name('admin.transcation.bulk_delete');
        });
    });
});
