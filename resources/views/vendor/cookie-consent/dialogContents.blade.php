<div class="js-cookie-consent cookie-consent">

    @php
        $parsedUrl = parse_url(url()->current());
        $host = $parsedUrl['host'];
        $cookieText = '';
        $cookieBtnText = '';
        
        if (!empty($userBe) && \Request::getHost() != env('WEBSITE_HOST')) {
            $cookieText = $userBe->cookie_alert_text;
            $cookieBtnText = $userBe->cookie_alert_btn_text;
        } else {
            if (!empty($userBe) && !empty(Request::route('username'))) {
                $cookieText = $userBe->cookie_alert_text;
                $cookieBtnText = $userBe->cookie_alert_btn_text;
            } else {
                if (!empty($be->cookie_alert_text)) {
                    $cookieText = $be->cookie_alert_text;
                }
                if (!empty($be->cookie_alert_button_text)) {
                    $cookieBtnText = $be->cookie_alert_button_text;
                }
            }
        }
    @endphp
    <div class="container">
        <div class="cookie-container">
            <span class="cookie-consent__message">
                @if (!empty($cookieText))
                    {!! replaceBaseUrl($cookieText) !!}
                @endif
            </span>

            <button class="js-cookie-consent-agree cookie-consent__agree">
                {{ $cookieBtnText }}
            </button>
        </div>
    </div>

</div>
