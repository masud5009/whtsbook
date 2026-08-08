<aside class="sidebar-widget-area">
    <div class="widget widget-post mb-30">
        <h3 class="title">{{ __('Recent Posts')}}</h3>
        @foreach($allBlogs as $blog)
        <article class="article-item mb-30">
            <div class="image">
                <a href="{{route('front.blogdetails',['id' => $blog->id,'slug' => $blog->slug])}}" class="lazy-container aspect-ratio-1-1 d-block">
                    <img class="lazyload lazy-image" src="{{asset('assets/front/img/blogs/'.$blog->main_image)}}"
                        data-src="{{asset('assets/front/img/blogs/'.$blog->main_image)}}" alt="Blog Image">
                </a>
            </div>
            <div class="content">
                <h6 class="lc-2">
                    <a href="{{route('front.blogdetails',['id' => $blog->id,'slug' => $blog->slug])}}">
                        {{strlen($blog->title) > 60 ? mb_substr($blog->title, 0, 60, 'utf-8') . '...' : $blog->title }}
                    </a>
                </h6>
                <div class="time">
                    {{ $blog->created_at->diffForHumans() }}
                </div>
            </div>
        </article>
        @endforeach
    </div>
    <div class="widget widget-categories mb-30">
        <h3 class="title">{{__('Categories')}}</h3>
        <ul class="list-unstyled m-0">
            @foreach ($bcats as $key => $bcat)
            <li class="d-flex align-items-center justify-content-between @if(request()->input('category') == $bcat->slug) active @endif">
                <a href="{{route('front.blogs', ['category'=>$bcat->slug])}}"><i class="fal fa-folder"></i>{{$bcat->name}}</a>
                @php
                  $blogs_cat = App\Models\Blog::where('category_index',$bcat->indx)->where('language_id',$lang_id)->count();
                @endphp
                <span class="tqy">({{$blogs_cat}})</span>
            </li>
            @endforeach
        </ul>
    </div>

</aside>


