@extends('front.layout')

@section('meta-description', !empty($seo) ? $seo->contact_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->contact_meta_keywords : '')

@section('pagename')
    - {{ __('Contact') }}
@endsection

@section('breadcrumb-title', !empty($heading) ? $heading->contact_title : __('Contact'))
@section('breadcrumb-link', !empty($heading) ? $heading->contact_title : __('Contact'))

@section('content')
    @php
        $phones = isset($be->contact_numbers) ? array_filter(array_map('trim', explode(',', $be->contact_numbers))) : [];
        $mails = isset($be->contact_mails) ? array_filter(array_map('trim', explode(',', $be->contact_mails))) : [];
        $addresses = isset($be->contact_addresses) ? array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $be->contact_addresses))) : [];
        $hasLatLng = !empty($bs->latitude) && !empty($bs->longitude);
        $mapSrc = $hasLatLng
            ? 'https://maps.google.com/maps?q=' . $bs->latitude . ',' . $bs->longitude . '&z=15&output=embed'
            : 'https://www.google.com/maps?q=' . urlencode($addresses[0] ?? ($bs->website_title ?? 'Dhaka, Bangladesh')) . '&output=embed';
    @endphp

    <div class="contact-area pt-lg-100 pt-90 pb-lg-100 pb-60">
        <div class="container">
            <div class="row gx-lg-5">
                <div class="col-12 col-lg-5 col-xxl-4">
                    <div class="contact-info mb-40 overflow-hidden">
                        <h3 class="title">{{ $bs->contact_info_title ?? __('Need more help?') }}</h3>
                        @if (!empty($bs->contact_text))
                            <p class="mb-25">{{ $bs->contact_text }}</p>
                        @endif

                        @if (count($phones) > 0)
                            <div data-aos="fade-left" data-aos-delay="100">
                                <div class="contact-info-item mb-20">
                                    <div class="icon">
                                        <i class="fal fa-phone-plus"></i>
                                    </div>
                                    <div class="card-text">
                                        <h6 class="mb-1">{{ __('Mobile') }}</h6>
                                        @foreach ($phones as $phone)
                                            <a class="contact-info-item-link" href="tel:{{ $phone }}">{{ $phone }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (count($mails) > 0)
                            <div data-aos="fade-left" data-aos-delay="200">
                                <div class="contact-info-item mb-20">
                                    <div class="icon">
                                        <i class="fal fa-envelope"></i>
                                    </div>
                                    <div class="card-text">
                                        <h6 class="mb-1">{{ __('Email') }}</h6>
                                        @foreach ($mails as $mail)
                                            <a class="contact-info-item-link" href="mailto:{{ $mail }}"
                                                title="{{ $mail }}">{{ $mail }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (count($addresses) > 0)
                            <div data-aos="fade-left" data-aos-delay="250">
                                <div class="contact-info-item">
                                    <div class="icon">
                                        <i class="fal fa-map-marker-alt"></i>
                                    </div>
                                    <div class="card-text">
                                        <h6 class="mb-1">{{ __('Location') }}</h6>
                                        @foreach ($addresses as $address)
                                            <address class="contact-list-item-text">{{ $address }}</address>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-7 col-xxl-8">
                    <div class="contact-from-wrapper mb-30" data-aos="fade-up" data-aos-delay="100">
                        <h3 class="title pb-20">{{ $bs->contact_form_title ?? __('Get in touch with us.') }}</h3>

                        <form action="{{ route('front.admin.contact.message') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label for="contact_name" class="form-label required">{{ __('Full Name') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control radius-sm" id="contact_name"
                                            placeholder="{{ __('Enter Your Name') }}" value="{{ old('name') }}">
                                        @error('name')
                                            <p class="text-danger mb-0 mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label for="contact_email" class="form-label required">{{ __('Email') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control radius-sm" id="contact_email"
                                            placeholder="{{ __('Enter Your Email') }}" value="{{ old('email') }}">
                                        @error('email')
                                            <p class="text-danger mb-0 mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="contact_subject" class="form-label required">{{ __('Subject') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control radius-sm" id="contact_subject"
                                    placeholder="{{ __('Enter Subject') }}" value="{{ old('subject') }}">
                                @error('subject')
                                    <p class="text-danger mb-0 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="contact_message" class="form-label required">{{ __('Message') }} <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control radius-sm" id="contact_message" rows="5" name="message"
                                    placeholder="{{ __('Enter Your Message') }}">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="text-danger mb-0 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($bs->is_recaptcha == 1)
                                <div class="mb-4">
                                    {!! NoCaptcha::renderJs() !!}
                                    {!! NoCaptcha::display() !!}
                                    @if ($errors->has('g-recaptcha-response'))
                                        @php
                                            $errmsg = $errors->first('g-recaptcha-response');
                                        @endphp
                                        <p class="text-danger mb-0 mt-2">{{ __($errmsg) }}</p>
                                    @endif
                                </div>
                            @endif

                            <div class="pt-2 text-center">
                                <button type="submit" class="btn btn-primary radius-sm">{{ __('Send Message') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="map-area overflow-hidden">
        <div class="container-fluid px-0">
            <div class="col-lg-12">
                <div class="contact-map">
                    <iframe class="radius-sm" src="{{ $mapSrc }}"
                        width="100%" height="500" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
