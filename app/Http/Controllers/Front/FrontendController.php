<?php

namespace App\Http\Controllers\Front;

use Carbon\Carbon;
use App\Models\Faq;
use App\Models\Seo;
use App\Models\Blog;
use App\Models\Page;
use App\Models\User;
use App\Models\Social;
use App\Models\Feature;
use App\Models\Heading;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Process;
use App\Models\Language;
use App\Models\Bcategory;
use App\Models\HeroSlider;
use App\Models\Subscriber;
use App\Models\Testimonial;
use App\Models\BasicSetting;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Traits\CustomSection;
use App\Models\BasicSetting as BS;
use App\Models\OfflineGateway;
use App\Models\PaymentGateway;
use App\Models\PlatformModule;
use App\Models\BasicExtended as BE;
use App\Http\Helpers\MegaMailer;
use App\Models\AboutGalleryImage;
use App\Models\AdditionalSection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{
    public function __construct()
    {
        $bs = BS::first();
        $be = BE::first();
        Config::set('captcha.sitekey', $bs->google_recaptcha_site_key);
        Config::set('captcha.secret', $bs->google_recaptcha_secret_key);
        Config::set('mail.host', $be->smtp_host);
        Config::set('mail.port', $be->smtp_port);
        Config::set('mail.username', $be->smtp_username);
        Config::set('mail.password', $be->smtp_password);
        Config::set('mail.encryption', $be->encryption);
    }

    public function index()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['language'] = $currentLang;
        $lang_id = $currentLang->id;
        $be = $currentLang->basic_extended;

        $data['processes'] = Process::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
        $data['features'] = Feature::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
        $data['faqs'] = Faq::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();
        $data['featured_users'] = User::where([
            ['featured', 1],
            ['status', 1]
        ])
            ->whereHas('memberships', function ($q) {
                $q->where('status', '=', 1)
                    ->where('start_date', '<=', Carbon::now()->format('Y-m-d'))
                    ->where('expire_date', '>=', Carbon::now()->format('Y-m-d'));
            })->get();
        $data['testimonials'] = Testimonial::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();


        $data['packages'] = Package::query()->where('status', '1')->where('featured', '1')->get();
        $data['partners'] = Partner::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();

        $data['heroSliders'] = HeroSlider::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();
        $data['platformModules'] = PlatformModule::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();

        $data['seo'] = Seo::where('language_id', $lang_id)->first();
        $data['socials'] = Social::all();

        $terms = [];
        if (Package::query()->where('status', '1')->where('featured', '1')->where('term', 'monthly')->count() > 0) {
            $terms[] = 'Monthly';
        }
        if (Package::query()->where('status', '1')->where('featured', '1')->where('term', 'yearly')->count() > 0) {
            $terms[] = 'Yearly';
        }
        if (Package::query()->where('status', '1')->where('featured', '1')->where('term', 'lifetime')->count() > 0) {
            $terms[] = 'Lifetime';
        }
        $data['terms'] = $terms;
        //blog content
        $data['blogs'] = Blog::query()
            ->join('bcategories', 'blogs.category_index', '=', 'bcategories.indx')
            ->where('blogs.language_id', $lang_id)
            ->where('bcategories.language_id', $lang_id)
            ->where('bcategories.status', 1)
            ->orderBy('serial_number', 'asc')
            ->select('blogs.*', 'bcategories.name as categoryName')
            ->get()->take(6);

        $users = User::where('online_status', 1)
            ->where('featured', 1)
            ->whereHas('memberships', function ($q) {
                $q->where('status', '=', 1)
                    ->where('start_date', '<=', Carbon::now()->format('Y-m-d'))
                    ->where('expire_date', '>=', Carbon::now()->format('Y-m-d'));
            })
            ->orderBy('id', 'DESC')
            ->get();

        $data['users'] = $users;

        $be = BasicExtended::select('package_features')->firstOrFail();
        $allPfeatures = $be->package_features ? $be->package_features : "[]";
        $data['allPfeatures'] = json_decode($allPfeatures, true);

        //custom sections
        $customSections = CustomSection::AdminFrontHomePage();
        foreach ($customSections as $section) {
            $data["after_" . str_replace('_section', '', $section)] = AdditionalSection::where('possition', $section)
                ->where('page_type', 'home')
                ->orderBy('serial_number', 'asc')
                ->get();
        }

        return view('front.index', $data);
    }

    public function subscribe(Request $request)
    {

        $rules = [
            'subscriber_email' => 'required|email|unique:subscribers,email'
        ];
        $messages = [
            'subscriber_email.required' => __('The subscriber email is required.'),
            'subscriber_email.email' => __('please validated email.'),
            'subscriber_email.unique' => __('The mail already exits.'),
        ];
        $validator = Validator::make($request->all(), $rules,  $messages);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->getMessageBag()
            ], 400);
        }


        $subsc = new Subscriber;
        $subsc->email = $request->subscriber_email;
        $subsc->save();
        return response()->json([
            'success' => __('Subscribe succesfully')
        ], 200);
    }

    public function checkUsername($username)
    {
        $count = User::where('username', $username)->count();
        $status = $count > 0;
        return response()->json($status);
    }

    public function step1($status, $id)
    {
        Session::forget('coupon');
        Session::forget('coupon_amount');

        if (Auth::check()) {
            return redirect()->route('user.plan.extend.index');
        }

        $data['status'] = $status;
        $data['id'] = $id;
        $package = Package::findOrFail($id);
        $data['package'] = $package;

        $hasSubdomain = false;
        $features = [];
        if (!empty($package->features)) {
            $features = json_decode($package->features, true);
        }
        if (is_array($features) && in_array('Subdomain', $features)) {
            $hasSubdomain = true;
        }
        $data['hasSubdomain'] = $hasSubdomain;
        return view('front.step', $data);
    }


    public function step2(Request $request)
    {
        $data = $request->session()->get('data');

        if (session()->has('coupon_amount')) {
            $data['cAmount'] = session()->get('coupon_amount');
        } else {
            $data['cAmount'] = 0;
        }

        $data['onlineGateways'] = PaymentGateway::query()
            ->where('status', '=', 1)
            ->get();
        $data['offlineGateways'] = OfflineGateway::query()
            ->where('status', '=', 1)
            ->orderBy('serial_number', 'asc')
            ->get();


        //payment gateways
        $data['onlineGateways'] = PaymentGateway::where('status', 1)->get();
        $data['offlineGateways'] = OfflineGateway::where('status', 1)
            ->orderBy('serial_number')
            ->get();

        $stripeInfo = PaymentGateway::where('keyword', 'stripe')->value('information');
        $stripeInfo = $stripeInfo ? json_decode($stripeInfo, true) : null;
        $data['stripe_key'] = $stripeInfo['key'] ?? null;

        $authorizeInfo = PaymentGateway::where('keyword', 'authorize.net')->value('information');
        $authorizeInfo = $authorizeInfo ? json_decode($authorizeInfo, true) : null;

        if ($authorizeInfo) {
            $data['anetSrc'] = $authorizeInfo['sandbox_check'] == 1
                ? 'https://jstest.authorize.net/v1/Accept.js'
                : 'https://js.authorize.net/v1/Accept.js';

            $data['authorizeClientKey'] = $authorizeInfo['public_key'] ?? null;
            $data['authorizeLoginId']   = $authorizeInfo['login_id'] ?? null;
        }
        return view('front.checkout', $data);
    }

    public function checkout(Request $request)
    {

        $this->validate($request, [
            'username' => 'required|alpha_num|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ], [
            'email.unique' => 'The email has already been taken'
        ]);
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $seo = Seo::where('language_id', $currentLang->id)->first();
        $be = $currentLang->basic_extended;
        $data['bex'] = $be;
        $data['username'] = $request->username;
        $data['email'] = $request->email;
        $data['password'] = $request->password;
        $data['status'] = $request->status;
        $data['id'] = $request->id;
        $data['package'] = Package::query()->findOrFail($request->id);
        $data['seo'] = $seo;
        $request->session()->put('data', $data);
        return redirect()->route('front.registration.step2');
    }


    // packages start
    public function pricing(Request $request)
    {
        $adminBs = BasicSetting::first();
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['seo'] = Seo::where('language_id', $currentLang->id)->first();

        $data['bex'] = BE::first();
        $data['abs'] = BS::first();

        $terms = [];
        if (Package::query()->where('status', '1')->where('term', 'monthly')->count() > 0) {
            $terms[] = 'Monthly';
        }
        if (Package::query()->where('status', '1')->where('term', 'yearly')->count() > 0) {
            $terms[] = 'Yearly';
        }
        if (Package::query()->where('status', '1')->where('term', 'lifetime')->count() > 0) {
            $terms[] = 'Lifetime';
        }
        $data['terms'] = $terms;

        $be = BasicExtended::select('package_features')->firstOrFail();
        $allPfeatures = $be->package_features ?? "[]";
        $data['allPfeatures'] = json_decode($allPfeatures, true);
        $data['heading'] = Heading::where('language_id', $currentLang->id)->first();
        // return $data;
        return view('front.pricing', $data);
    }

    // blog section start
    public function blogs(Request $request)
    {

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['seo'] = Seo::where('language_id', $currentLang->id)->first();
        $data['currentLang'] = $currentLang;
        $lang_id = $currentLang->id;

        $category = $request->category;
        $term = $request->term;

        $data['bcats'] = Bcategory::where('language_id', $lang_id)
            ->where('status', 1)
            ->orderBy('serial_number', 'ASC')
            ->get();

        $data['blogs'] = Blog::query()
            ->join('bcategories', 'blogs.category_index', '=', 'bcategories.indx')
            ->when($category, function ($query, $category) {
                $cat = Bcategory::where('slug', $category)->first();
                if (!is_null($cat)) {
                    return $query->where('blogs.category_index', $cat->indx);
                }
            })->when($term, function ($query, $term) {
                return $query->where('blogs.title', 'like', '%' . $term . '%');
            })
            ->where('blogs.language_id', $lang_id)
            ->where('bcategories.language_id', $lang_id)
            ->where('bcategories.status', 1)
            ->orderBy('serial_number', 'ASC')
            ->select('blogs.*', 'bcategories.name as categoryName')
            ->paginate(9);
        $data['heading'] = Heading::where('language_id', $currentLang->id)->first();
        return view('front.blogs', $data);
    }

    public function blogdetails($id, $slug)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $lang_id = $currentLang->id;
        $data['blog'] = Blog::query()
            ->where('blogs.id', $id)
            ->join('bcategories', 'blogs.category_index', '=', 'bcategories.indx')
            ->where('bcategories.language_id', $lang_id)
            ->where('bcategories.status', 1)
            ->select('blogs.*', 'bcategories.name as categoryName')
            ->firstOrFail();
        $data['bcats'] = Bcategory::where('status', 1)
            ->where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();

        $data['allBlogs'] = Blog::where('language_id', $lang_id)->orderBy('id', 'DESC')->limit(5)->get();
        $data['lang_id'] = $lang_id;
        return view('front.blog-details', $data);
    }

    public function contactView()
    {

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['seo'] = Seo::where('language_id', $currentLang->id)->first();
        $data['heading'] = Heading::where('language_id', $currentLang->id)->first();
        return view('front.contact', $data);
    }

    public function faqs()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['seo'] = Seo::where('language_id', $currentLang->id)->first();
        $lang_id = $currentLang->id;
        $data['faqs'] = Faq::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();
        $data['heading'] = Heading::where('language_id', $currentLang->id)->first();
        return view('front.faq', $data);
    }

    public function dynamicPage($slug)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['page'] = Page::where('slug', $slug)->where('status', 1)->firstOrFail();
        $pageId = $data['page']->id;

        $pageHeading = Heading::select('custom_page_heading')
            ->where('language_id', $currentLang->id)
            ->select('custom_page_heading')
            ->first();
        $pageHeading = isset($pageHeading->custom_page_heading) ? json_decode($pageHeading->custom_page_heading, true) : [];
        $data['title'] = (is_array($pageHeading) && isset($pageHeading[$pageId])) ? $pageHeading[$pageId] : '';

        //custom page
        $seoInfo = SEO::select('custome_page_meta_keyword', 'custome_page_meta_description')
            ->where('language_id', $currentLang->id)
            ->first();
        $metaKeyword = isset($seoInfo->custome_page_meta_keyword) ? json_decode($seoInfo->custome_page_meta_keyword, true) : '';
        $metaDescription = isset($seoInfo->custome_page_meta_description) ? json_decode($seoInfo->custome_page_meta_description, true) : '';
        $data['meta_keywords'] = isset($metaKeyword[$pageId]) ? $metaKeyword[$pageId] : '';
        $data['meta_description'] = isset($metaDescription[$pageId]) ? $metaDescription[$pageId] : '';

        return view('front.dynamic', $data);
    }

    public function users(Request $request)
    {

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['seo'] = Seo::where('language_id', $currentLang->id)->first();
        $data['heading'] = Heading::where('language_id', $currentLang->id)->first();

        $users = User::where('online_status', 1)
            ->whereHas('memberships', function ($q) {
                $q->where('status', '=', 1)
                    ->where('start_date', '<=', Carbon::now()->format('Y-m-d'))
                    ->where('expire_date', '>=', Carbon::now()->format('Y-m-d'));
            })
            ->when($request->company, function ($q) use ($request) {
                return $q->where('company_name', 'like', '%' . $request->company . '%');
            })
            ->when($request->location, function ($q) use ($request) {
                return $q->where(function ($query) use ($request) {
                    $query->where('city', 'like', '%' . $request->location . '%')
                        ->orWhere('state', 'like', '%' . $request->location . '%')
                        ->orWhere('address', 'like', '%' . $request->location . '%')
                        ->orWhere('country', 'like', '%' . $request->location . '%');
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate(9);

        $data['users'] = $users;
        return view('front.users', $data);
    }

    public function paymentInstruction(Request $request)
    {
        $offline = OfflineGateway::where('name', $request->name)
            ->select('short_description', 'instructions', 'is_receipt')
            ->first();

        return response()->json([
            'description' => $offline->short_description,
            'instructions' => $offline->instructions,
            'is_receipt' => $offline->is_receipt
        ]);
    }

    public function adminContactMessage(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email:rfc,dns',
            'subject' => 'required',
            'message' => 'required'
        ];

        $bs = BS::select('is_recaptcha')->first();
        if ($bs->is_recaptcha == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }
        $messages = [
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'g-recaptcha-response.captcha' => __('Captcha error! try again later or contact site admin.'),
            'subject.required' => __('The subject field is required.'),
            'message.required' => __('The message field is required.'),
        ];
        $request->validate($rules, $messages);

        $data['subject'] = $request->subject;
        $data['body'] = "<div>$request->message</div><br>
                         <strong>For further contact with the enquirer please use the below information:</strong><br>
                         <strong>Enquirer Name:</strong> $request->name <br>
                         <strong>Enquirer Mail:</strong> $request->email <br>
                         ";
        $data['fullname'] = $request->name;
        $data['email'] = $request->email;
        $mailer = new MegaMailer();
        $mailer->mailToAdmin($data);
        return back();
    }

    public function changeLanguage($lang): \Illuminate\Http\RedirectResponse
    {
        session()->put('lang', $lang);
        app()->setLocale($lang);
        return redirect()->back();
    }
    public function about()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $lang_id = $currentLang->id;

        $data['processes'] = Process::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
        $data['features'] = Feature::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();

        $data['testimonials'] = Testimonial::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();
        $data['blogs'] = Blog::with('bcategory')
            ->where('language_id', $lang_id)
            ->orderBy('id', 'DESC')
            ->take(3)
            ->get();
        $data['aboutGalleryImages'] = AboutGalleryImage::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();

        $data['partners'] = Partner::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();

        $data['seo'] = Seo::where('language_id', $lang_id)->first();

        $sections = [
            'features_section',
            'work_process_section',
            'testimonial_section',
            'blog_section'
        ];
        $pageType = 'about';
        foreach ($sections as $section) {
            $data["after_" . str_replace('_section', '', $section)] = AdditionalSection::where('possition', $section)
                ->where('page_type', $pageType)
                ->orderBy('serial_number', 'asc')
                ->get();
        }

        $data['lang_id'] = $lang_id;

        return view('front.about', $data);
    }
}
