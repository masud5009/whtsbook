@extends('front.layout')

@section('pagename')
    - {{ __('Blog Details') }}
@endsection

@section('meta-description', !empty($blog) ? $blog->meta_keywords : '')
@section('meta-keywords', !empty($blog) ? $blog->meta_description : '')

@section('og-meta')
    <meta property="og:image" content="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1024">
    <meta property="og:image:height" content="1024">
@endsection

@section('breadcrumb-title')
    {{ strlen($blog->title) > 30 ? mb_substr($blog->title, 0, 30) . '...' : $blog->title }}
@endsection
@section('breadcrumb-link')
    {{ __('Blog Details') }}
@endsection

@section('content')
    @php
        $blogUrl = route('front.blogdetails', ['id' => $blog->id, 'slug' => $blog->slug]);
        $shareUrl = urlencode($blogUrl);
        $shareTitle = urlencode($blog->title);
        $selectedCategory = !empty($bcats) ? $bcats->firstWhere('indx', $blog->category_index) : null;
        $categoryLink = !empty($selectedCategory) ? route('front.blogs', ['category' => $selectedCategory->slug]) : route('front.blogs');
    @endphp

    <section class="blog-details-area pt-120 pb-70">
        <div class="container">
            <div class="row justify-content-center gx-xl-5">
                <div class="col-lg-8">
                    <div class="blog-description mb-40">
                        <article class="item-single">
                            <div class="blog-image radius-md">
                                <div class="lazy-container ratio ratio-16-9">
                                    <img class="blur-up lazyload" src="{{ asset('assets/front/images/placeholder.png') }}"
                                        data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}"
                                        alt="{{ $blog->title }}">
                                </div>
                                <span class="date">{{ \Carbon\Carbon::parse($blog->created_at)->format('d F, Y') }}</span>
                                <a href="#" class="btn anim-btn radius-md" data-bs-toggle="modal"
                                    data-bs-target="#socialMediaModal"><i class="fas fa-share-alt"></i>{{ __('Share Now') }}</a>
                            </div>
                            <div class="content mt-30">
                                <ul class="list-inline info-list mb-20">
                                    <li class="list-inline-item mr-0">
                                        <span class="fw-semibold">
                                            <i class="fa-regular fa-circle-user"></i>
                                            {{ __('By Admin') }}
                                        </span>
                                    </li>
                                    <li class="list-inline-item mr-0">
                                        <a href="{{ $categoryLink }}"><i class="fal fa-list"></i> {{ $blog->categoryName }}</a>
                                    </li>
                                </ul>
                                <h3 class="title">{{ $blog->title }}</h3>
                                <div class="summernote-content">
                                    {!! replaceBaseUrl($blog->content) !!}
                                </div>
                            </div>
                        </article>
                    </div>

                    @if ($bs->is_disqus == 1)
                        <div class="blog-details-comment mt-5">
                            <div class="comment-lists">
                                <div id="disqus_thread"></div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <aside class="widget-area pb-10">
                        <div class="widget widget-recent-post radius-md mb-30">
                            <h4 class="title mb-20">{{ __('Search Posts') }}</h4>
                            <form class="widget-search-form radius-md" method="GET" action="{{ route('front.blogs') }}">
                                <input type="search" class="search-input form-control"
                                    placeholder="{{ __('Search By Title') }}" name="term"
                                    value="{{ request()->input('term') }}" required>
                                <button class="btn-search" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>

                        <div class="widget widget-blog-categories radius-md mb-30">
                            <h4 class="title mb-20">{{ __('Categories') }}</h4>
                            <ul class="list-unstyled list-group m-0">
                                @foreach ($bcats as $bcat)
                                    @php
                                        $blogsCat = \App\Models\Blog::where('category_index', $bcat->indx)
                                            ->where('language_id', $lang_id)
                                            ->count();
                                    @endphp
                                    <li
                                        class="d-flex align-items-center justify-content-between @if (request()->input('category') == $bcat->slug) active @endif">
                                        <a href="{{ route('front.blogs', ['category' => $bcat->slug]) }}">
                                            <i class="fal fa-folder"></i>
                                            {{ $bcat->name }}
                                        </a>
                                        <span class="tqy">({{ $blogsCat }})</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="widget widget-tags radius-md mb-30">
                            <h4 class="title mb-20">{{ __('Tags') }}</h4>
                            <div class="tag-content">
                                @foreach ($bcats->take(12) as $bcat)
                                    <a href="{{ route('front.blogs', ['category' => $bcat->slug]) }}">{{ $bcat->name }}</a>
                                @endforeach
                            </div>
                        </div>

                        <div class="widget widget-recent-post radius-md mb-10">
                            <h4 class="title mb-20">{{ __('Recent Posts') }}</h4>
                            <div class="article-item-area">
                                @forelse ($allBlogs as $recentBlog)
                                    <article class="article-item">
                                        <div class="image">
                                            <a href="{{ route('front.blogdetails', ['id' => $recentBlog->id, 'slug' => $recentBlog->slug]) }}"
                                                class="lazy-container ratio ratio-1-1">
                                                <img class="blur-up lazyload"
                                                    src="{{ asset('assets/front/images/placeholder.png') }}"
                                                    data-src="{{ asset('assets/front/img/blogs/' . $recentBlog->main_image) }}"
                                                    alt="{{ $recentBlog->title }}">
                                            </a>
                                        </div>
                                        <div class="content">
                                            <ul class="info-list list-unstyled">
                                                <li><i class="fal fa-user"></i>{{ __('Admin') }}</li>
                                                <li><i
                                                        class="fal fa-calendar"></i>{{ \Carbon\Carbon::parse($recentBlog->created_at)->format('d M Y') }}
                                                </li>
                                            </ul>
                                            <h6 class="lc-2">
                                                <a
                                                    href="{{ route('front.blogdetails', ['id' => $recentBlog->id, 'slug' => $recentBlog->slug]) }}">
                                                    {{ $recentBlog->title }}
                                                </a>
                                            </h6>
                                        </div>
                                    </article>
                                @empty
                                    <p class="mb-0">{{ __('No recent posts found.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade socialMediaModal" id="socialMediaModal" tabindex="-1" aria-labelledby="socialMediaModalTitle"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="socialMediaModalTitle">{{ __('Share On') }}</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fas fa-times"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="actions socialMediaModal_list">
                        <div class="action-btn">
                            <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                                target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <span>{{ __('Facebook') }}</span>
                        </div>
                        <div class="action-btn">
                            <a class="linkedin" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
                                target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <span>{{ __('Linkedin') }}</span>
                        </div>
                        <div class="action-btn">
                            <a class="twitter"
                                href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
                                target="_blank">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <span>{{ __('Twitter') }}</span>
                        </div>
                        <div class="action-btn">
                            <a class="whatsapp" href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}"
                                target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <span>{{ __('Whatsapp') }}</span>
                        </div>
                        <div class="action-btn">
                            <a class="copy" href="#" id="copyBlogLink" data-url="{{ $blogUrl }}">
                                <i class="fal fa-copy"></i>
                            </a>
                            <span>{{ __('Copy') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($bs->is_disqus == 1)
        <script>
            "use strict";
            (function() {
                var d = document,
                    s = d.createElement('script');
                s.src = '//{{ $bs->disqus_shortname }}.disqus.com/embed.js';
                s.setAttribute('data-timestamp', +new Date());
                (d.head || d.body).appendChild(s);
            })();
        </script>
    @endif

    <script>
        "use strict";
        $(document).on('click', '#copyBlogLink', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            if (!url) {
                return;
            }
            navigator.clipboard.writeText(url).then(function() {
                toastr.success("{{ __('Link copied') }}");
            });
        });
    </script>
@endsection
