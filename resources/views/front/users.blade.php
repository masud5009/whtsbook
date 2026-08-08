    @extends('front.layout')

    @section('pagename')
    - {{ __('Listings') }}
    @endsection

    @section('meta-description', !empty($seo) ? $seo->profiles_meta_description : '')
    @section('meta-keywords', !empty($seo) ? $seo->profiles_meta_keywords : '')

    @section('breadcrumb-title', !empty($heading) ? $heading->listing_title : __('Listings'))
    @section('breadcrumb-link', !empty($heading) ? $heading->listing_title : __('Listings'))

    @section('content')

    <!--====== Start saas-featured-users section ======-->

    <section class="user-profile-area ptb-120 pb-80">
        <div class="container">
            <div class="search-filter mb-5">
                <form action="#">
                    <div class="row align-items-center">
                        <div class="col-lg-5">
                            <div class="search-box mt-2">
                                <input type="text" class="form-control" placeholder="{{ __('Search by name') }}"
                                    name="company" value="{{ request()->input('company') }}">
                            </div>
                        </div>
                        <div class="col-lg-5 ">
                            <div class="search-box mt-2">
                                <input type="text" class="form-control" placeholder="{{ __('Search by location') }}"
                                    name="location" value="{{ request()->input('location') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 ">
                            <div class="search-box mt-2">
                                <button type="submit" class="btn-search  primary-btn">
                                    {{ __('Search')}} </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        <div class="row">
            @if (count($users) == 0)
            <div class="bg-light text-center py-5 d-block w-100">
                <h3>{{ __('NO  LISTING FOUND!') }}</h3>
            </div>
            @else
            @foreach ($users as $user)
                <div class="col-lg-4 col-sm-6">
                <div class="swiper-slide user-card mb-30">
                    <div class="card" data-aos="fade-up" data-aos-delay="100">
                    <div class="icon">
                        @if ($user->photo)
                        <img class="lazyload lazy-image" data-src="{{ asset('assets/tenant/img/users/' . $user->photo) }}"
                            alt="user">
                        @else
                        <img class="lazyload lazy-image" data-src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}"
                            alt="user">
                        @endif
                    </div>
                    <div class="card-content green">
                        <h3 class="card-title">{{ $user->company_name }}</h3>
                        <div class="social-link">

                        </div>
                        <div class="cta-btns">
                        @php
                            if (!empty($user)) {
                                $currentPackage = App\Http\Helpers\UserPermissionHelper::userPackage($user->id);
                                $preferences = App\Models\User\UserPermission::where([
                                    ['user_id', $user->id],
                                    ['package_id', $currentPackage->package_id],
                                ])->first();
                                $permissions = isset($preferences) ? json_decode($preferences->permissions, true) : [];
                            }
                        @endphp
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            @endforeach
            @endif
        </div>
        <div class="pagination mb-30 justify-content-center">
            {{ $users->appends(['search' => request()->input('search'), 'designation' => request()->input('designation'), 'location' => request()->input('location')])->links() }}
        </div>
        </div>
    </section>

    <!--====== End saas-featured-users section ======-->
    @endsection
