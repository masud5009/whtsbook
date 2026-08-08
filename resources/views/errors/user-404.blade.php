@extends('user-front.common.layout')

@section('pageHeading')
  {{__('404')}}
@endsection


@section('content')
@php
    $breadcrumbInfo = App\Traits\MiscellaneousTrait::getBreadcrumb();
    $language=  App\Traits\MiscellaneousTrait::getLanguage();
    try{
        $pageHeading = App\Models\User\PageHeading::where([
            'user_id' => getUser()->id,
            'language_id' => $language->id
            ])->first();
        $userBe = App\Models\User\BasicExtended::query()
        ->where('user_id', getUser()->id)
        ->where('language_id', $language->id)
        ->first();
        $keywords = json_decode($language->keywords, true);
     
    }catch(\Exeption $e){
        
    }
@endphp
<main>
    <!-- Breadcrumb Section Start -->
      @includeIf('user-front.common.partials.breadcrumb', ['breadcrumb' => @$breadcrumbInfo->breadcrumb, 'title' => isset($pageHeading) ? $pageHeading->error_page_title : __('404')  ])
    <!-- Breadcrumb Section End -->

    <!--    Error section start   -->
    <div class="error-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="not-found">
                        <img src="{{asset(Constant::WEBSITE_ERROR_IMAGE . '/' . @$userBe->page_not_found_image_one)}}" alt="">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="error-txt">
                        <div class="oops">
                        <img src="{{asset(Constant::WEBSITE_ERROR_IMAGE . '/' . @$userBe->page_not_found_image_two)}}" alt="">
                        </div>
                        <h2>{{ @$userBe->page_not_found_title }}</h2>
                        <p>{{ @$userBe->page_not_found_subtitle }}</p>
                        
                        <a href="{{route('front.user.detail.view', getParam())}}" class="go-home-btn">{{   $keywords['Back to Home'] ??__("Back to Home") }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--    Error section end   -->
</main>

@endsection


