<!-- ========= Start Footer ========= -->
<footer class="footer-area pt-80 pb-30 bg-img bg-cover" data-bg-image="{{ asset('assets/images/footer/footer-bg.png') }}">
    @if ($bs->top_footer_section == 1)
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget mb-30">
                        <div class="footer-logo mb-20">
                            <a class="navbar-brand" href="{{ route('front.index') }}">
                                <img src="{{ $bs->footer_logo ? asset('assets/front/img/' . $bs->footer_logo) : asset('assets/tenant/img/defaultlogo.png') }}"
                                    alt="{{ $bs->website_title ?? 'logo' }}">
                            </a>
                        </div>
                        <p class="fw-medium mb-24">
                            {{ $bs->footer_text ?? 'Sed ut perspiciatis unde omnis istecioeio natusioe error sit voluptatem accusa' }}
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget mb-30">
                        <h5 class="mb-24">{{ $bs->useful_links_title ?? 'Useful Links' }}</h5>
                        <div class="footer-widget-item">
                            <ul class="reset-ul">
                                @php
                                    $ulinks = App\Models\Ulink::where('language_id', $currentLang->id)
                                        ->orderby('id', 'desc')
                                        ->get();
                                @endphp
                                @forelse ($ulinks as $ulink)
                                    <li>
                                        <a class="mb-2 fw-medium" href="{{ $ulink->url }}" target="_blank">
                                            {{ $ulink->name }}
                                        </a>
                                    </li>
                                @empty
                                    <li><span class="mb-2 fw-medium">{{ __('No Links Found') }}</span></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget mb-30">
                        <h5 class="mb-24">{{ $bs->contact_info_title ?? 'Contact Us' }}</h5>

                        <ul class="reset-ul">
                            <!-- Address -->
                            <li class="mb-10">
                                <a class="fw-medium" href="#">
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $be->contact_addresses ?? '2416 Mapleview Drive' }}
                                </a>
                            </li>

                            <!-- Phone Numbers -->
                            <li class="mb-10">
                                @php
                                    $phones = isset($be->contact_numbers)
                                        ? explode(',', $be->contact_numbers)
                                        : ['+001 234 567 89'];
                                @endphp
                                @foreach ($phones as $phone)
                                    <a class="fw-medium" href="tel:{{ trim($phone) }}">
                                        <i class="fa-solid fa-headphones"></i>{{ trim($phone) }}
                                    </a>
                                    @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </li>

                            <!-- Email Addresses -->
                            <li class="mb-10">
                                @php
                                    $mails = isset($be->contact_mails)
                                        ? explode(',', $be->contact_mails)
                                        : ['info@example.com'];
                                @endphp
                                @foreach ($mails as $mail)
                                    <a class="fw-medium" href="mailto:{{ trim($mail) }}">
                                        <i class="fa-solid fa-envelope"></i>{{ trim($mail) }}
                                    </a>
                                    @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget mb-30">
                        <h5 class="mb-24">{{ $bs->newsletter_title ?? 'Subscribe Us' }}</h5>
                        <div class="footer-subscribe-widget">
                            <p>{{ $bs->newsletter_subtitle ?? 'Stay update with us and get 40% discount!' }}</p>

                            <form id="footerNewsletterForm" action="{{ route('front.subscribe') }}" method="POST">
                                @csrf
                                <div class="subscribe-group-btn subscribe mb-3">
                                    <input type="email" name="subscriber_email"
                                        placeholder="{{ __('Mail Address') }}" required>
                                    <button class="subscribe-btn" type="submit">{{ __('SUBSCRIBE') }}</button>
                                </div>
                                <p class="text-danger" id="newsletterError"></p>
                                <p class="text-success" id="newsletterSuccess"></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($bs->copyright_section == 1)
        <div class="footer-copyright pt-30">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        @if (isset($socials) && count($socials) > 0)
                            <div class="socials mb-14 justify-content-center">
                                @foreach ($socials as $social)
                                    <a href="{{ $social->url }}" target="_blank">
                                        <i class="{{ $social->icon }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <p class="fw-medium small text-center mb-0">
                            @if (!empty($bs->copyright_text))
                                {!! replaceBaseUrl($bs->copyright_text) !!}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</footer>
