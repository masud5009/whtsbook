@extends('front.layout')

@section('pagename')
    - {{ __('Blogs') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->blogs_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->blogs_meta_keywords : '')

@section('breadcrumb-title', !empty($heading) ? $heading->blog_title : '')
@section('breadcrumb-link', !empty($heading) ? $heading->blog_title : '')

@section('content')
    <div class="blog-area pt-100 pb-100">
        <div class="container">
            <div class="row justify-content-center">
                @forelse($blogs as $blog)
                    @php
                        $delay = (($loop->iteration - 1) % 3 + 1) * 100;
                    @endphp
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $delay }}">
                        <article class="blog-card mb-30">
                            <figure class="blog-image">
                                <a href="{{ route('front.blogdetails', ['id' => $blog->id, 'slug' => $blog->slug]) }}"
                                    class="lazy-container ratio">
                                    <img class="blur-up lazyload" src="{{ asset('assets/front/images/placeholder.png') }}"
                                        data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}"
                                        alt="{{ $blog->title }}">
                                </a>
                                <span
                                    class="date">{{ \Carbon\Carbon::parse($blog->created_at)->format('d F, Y') }}</span>
                            </figure>

                            <div class="blog-content">
                                <ul class="list-inline reset-ul">
                                    <li class="list-inline-item small">
                                        <span class="fw-semibold">
                                            <i class="fa-regular fa-circle-user"></i>
                                            {{ __('By Admin') }}
                                        </span>
                                    </li>
                                    <li class="list-inline-item small">
                                        <span>
                                            <i class="fa-regular fa-folder"></i>
                                            {{ $blog->categoryName }}
                                        </span>
                                    </li>
                                </ul>
                                <h4 class="title lc-2 mb-10">
                                    <a
                                        href="{{ route('front.blogdetails', ['id' => $blog->id, 'slug' => $blog->slug]) }}">
                                        {{ $blog->title }}
                                    </a>
                                </h4>
                                <p class="card-text lc-2 mb-30">
                                    {!! strlen(strip_tags($blog->content)) > 140
                                        ? mb_substr(strip_tags($blog->content), 0, 140, 'utf-8') . '...'
                                        : strip_tags($blog->content) !!}
                                </p>
                                <a href="{{ route('front.blogdetails', ['id' => $blog->id, 'slug' => $blog->slug]) }}"
                                    class="btn anim-btn radius-30">{{ __('Read More') }}
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="bg-light text-center py-5 d-block w-100">
                        <h3>{{ __('No Post Found!') }}</h3>
                    </div>
                @endforelse
            </div>

            @if ($blogs->hasPages())
                <div class="pagination mb-30 justify-content-center">
                    {{ $blogs->appends(['category' => request()->input('category'), 'term' => request()->input('term')])->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
