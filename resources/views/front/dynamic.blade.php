@extends('front.layout')

@section('pagename')
- {{$page->name}}
@endsection

@section('meta-description', @$meta_description)
@section('meta-keywords', @$meta_keywords)

@section('pagename')
    -  {{ !empty($title) ? $title : $page->title }}
@endsection

@section('breadcrumb-title')
  {{ !empty($title) ? $title : $page->title }}
@endsection

@section('breadcrumb-link')
    {{ !empty($title) ? $title : $page->title }}
@endsection

@section('content')

    <!--====== Start faqs-section ======-->
    <section class="terms-condition-area pt-90 pb-90">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 m-auto">

                    <div class="item-single mb-30" data-aos="fade-up">
                        {!! replaceBaseUrl($page->body) !!}
                    </div>

                </div>
            </div>
        </div>
    </section>
   <!--====== End faqs-section ======-->
@endsection
