<?php

use App\Models\Page;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User\AutoResMessage;
use App\Models\User\Language as UserLanguage;

if (!function_exists('truncateString')) {
    function truncateString($string, $maxLength)
    {
        return strlen($string) > $maxLength ? mb_substr($string, 0, $maxLength, 'UTF-8') . '...' : $string;
    }
}

if (!function_exists('setEnvironmentValue')) {
    function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }
            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        }

        return true;
    }
}

if (!function_exists('replaceBaseUrl')) {
    function replaceBaseUrl($html)
    {
        $startDelimiter = 'src=';
        $endDelimiter = public_path('assets/front/img/summernote');
        $startDelimiterLength = strlen($startDelimiter);
        $endDelimiterLength = strlen($endDelimiter);
        $startFrom = $contentStart = $contentEnd = 0;
        while (false !== ($contentStart = strpos($html, $startDelimiter, $startFrom))) {
            $contentStart += $startDelimiterLength;
            $contentEnd = strpos($html, $endDelimiter, $contentStart);
            if (false === $contentEnd) {
                break;
            }
            $html = substr_replace($html, url('/'), $contentStart, $contentEnd - $contentStart);
            $startFrom = $contentEnd + $endDelimiterLength;
        }
        return $html;
    }
}


if (!function_exists('setAwsCredentials')) {
    function setAwsCredentials($key, $secret, $region, $bucket)
    {
        config([
            'filesystems.disks.s3.key' => $key,
            'filesystems.disks.s3.secret' => $secret,
            'filesystems.disks.s3.region' => $region,
            'filesystems.disks.s3.bucket' => $bucket,
        ]);
    }
}

if (!function_exists('convertUtf8')) {
    function convertUtf8($value)
    {
        if (!empty($value)) {
            return mb_detect_encoding($value, mb_detect_order(), true) === 'UTF-8' ? $value : mb_convert_encoding($value, 'UTF-8');
        } else {
            return null;
        }
    }
}

if (!function_exists('make_slug')) {
    function make_slug($string)
    {
        $slug = preg_replace('/\s+/u', '-', trim($string));
        $slug = str_replace("/", "", $slug);
        $slug = str_replace("?", "", $slug);
        return mb_strtolower($slug, 'UTF-8');
    }
}


if (!function_exists('slug_create')) {
    function slug_create($val)
    {
        $slug = preg_replace('/\s+/u', '-', trim($val));
        $slug = str_replace("/", "", $slug);
        $slug = str_replace("?", "", $slug);
        return mb_strtolower($slug, 'UTF-8');
    }
}

if (!function_exists('hex2rgb')) {
    function hex2rgb($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }
}

if (!function_exists('getHref')) {
    function getHref($link)
    {
        $href = "#";
        if ($link["type"] == 'home') {
            $href = route('front.index');
        } else if ($link["type"] == 'listings') {
            $href = "#";
        } else if ($link["type"] == 'pricing') {
            $href = route('front.pricing');
        } else if ($link["type"] == 'faq') {
            $href = route('front.faq.view');
        } else if ($link["type"] == 'blog') {
            $href = route('front.blogs');
        } else if ($link["type"] == 'contact') {
            $href = route('front.contact');
        } else if ($link["type"] == 'about') {
            $href = route('front.about');
        } else if ($link["type"] == 'custom') {
            if (empty($link["href"])) {
                $href = "#";
            } else {
                $href = $link["href"];
            }
        } else {
            $pageid = (int) $link["type"];
            $page = Page::find($pageid);
            if (!empty($page)) {
                $href = route('front.dynamicPage', [$page->slug]);
            }
        }
        return $href;
    }
}

/**
 * Admin price format
 */
if (!function_exists('format_price')) {
    function format_price($value): string
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))
                ->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $bex = $currentLang->basic_extended;
        if ($bex->base_currency_symbol_position == 'left') {
            return $bex->base_currency_symbol . $value;
        } else {
            return $value . $bex->base_currency_symbol;
        }
    }
}



if (!function_exists('get_keywords')) {
    function get_keywords($userId)
    {
        if (session()->has('user_front_lang')) {
            $userCurrentLang = UserLanguage::where('code', session()->get('user_front_lang'))->where('user_id', $userId)->first();
            if (empty($userCurrentLang)) {
                $userCurrentLang = UserLanguage::where('is_default', 1)->where('user_id', $userId)->first();
                session()->put('user_front_lang', $userCurrentLang->code);
            }
        } else {
            $userCurrentLang = UserLanguage::where('is_default', 1)->where('user_id', $userId)->first();
        }
        return json_decode($userCurrentLang->keywords, true);
    }
}


if (!function_exists('currencyTextPrice')) {
    function currencyTextPrice($price, $symbol, $position)
    {
        $price = number_format($price, 2);
        if ($position == 'left') {
            $value = $symbol . ' ' . $price;
        } else {
            $value = $price . ' ' . $symbol;
        }
        return $value;
    }
}


if (!function_exists('currencySymbolPrice')) {
    function currencySymbolPrice($price, $symbol, $position)
    {
        $price = number_format($price, 2);
        if ($position == 'left') {
            $value = $symbol . $price;
        } else {
            $value = $price . $symbol;
        }
        return $value;
    }
}



if (!function_exists('userPriceFormat')) {
    function userPriceFormat($user_id, $price)
    {
        $bs =  DB::table('user_basic_settings')
            ->where('user_id', $user_id)
            ->select('base_currency_symbol', 'base_currency_symbol_position')
            ->first();

        $symbol = $bs->base_currency_symbol;
        $position = $bs->base_currency_symbol_position;

        $price = number_format($price, 2);
        if ($position == 'left') {
            $value = $symbol . $price;
        } else {
            $value = $price  . $symbol;
        }
        return $value;
    }
}



if (!function_exists('hexToRgba')) {

    function hexToRgba($hex, $alpha = .5)
    {
        // Remove the hash at the start if it's there
        $hex = ltrim($hex, '#');

        // Parse the hex color
        if (strlen($hex) == 6) {
            list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
        } elseif (strlen($hex) == 3) {
            list($r, $g, $b) = sscanf($hex, "%1x%1x%1x");
            $r = $r * 17;
            $g = $g * 17;
            $b = $b * 17;
        } else {
            return '10, 71, 46';
        }

        // Ensure alpha is between 0 and 1
        $alpha = min(max($alpha, 0), 1);

        // Return the rgba color code
        return "$r, $g, $b";
    }
}

/**
 * Response Ai template messages
 */
if (!function_exists('response_from_admin')) {

    function response_from_admin($user_id, $event_type)
    {
        return AutoResMessage::where('wp_id', $user_id)
            ->where('event_type', $event_type)
            ->value('message') ?? 'Thank you for your message. Our system is experiencing high traffic. A team member will respond to you shortly.';
    }
}

/**
 * Convert number to human readable format
 *1000 => 1k, 10000 => 10k, 100000 => 100k, 1000000 => 1M , 1000000000 => 1B
 */
if (!function_exists('human_number')) {
    function human_number($number)
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B';
        }
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'k';
        }

        return (string) $number;
    }
}



/**
 * AJAX (API) and normal HTTP requests.
 *
 * This helper handles:
 * - RedirectResponse (URL redirect)
 * - HTML/View responses
 * - Plain string URLs
 */
if (!function_exists('normalizePaymentResponse')) {
    function normalizePaymentResponse(Request $request, $link)
    {
        // Detect whether the request is AJAX / expects JSON response
        $isAjax = $request->ajax() || $request->wantsJson();

        /**
         * ----------------------------------------------------------
         * Case 1: Gateway returned a Symfony/Laravel Response
         * ----------------------------------------------------------
         * This usually happens when a gateway returns:
         * - RedirectResponse
         * - HTML response
         */
        if ($link instanceof \Symfony\Component\HttpFoundation\Response) {

            // If the response is a redirect, extract the redirect URL
            if (method_exists($link, 'getTargetUrl')) {
                $url = $link->getTargetUrl();
                return $isAjax
                    ? response()->json(['status' => 'success', 'action' => 'redirect', 'url' => $url])
                    : redirect()->away($url);
            }

            // Otherwise, treat it as an HTML response
            $html = method_exists($link, 'getContent') ? $link->getContent() : null;
            return $isAjax
                ? response()->json(['status' => 'success', 'action' => 'html', 'html' => $html])
                : $link;
        }

        /**
         * ----------------------------------------------------------
         * Case 2: Renderable object (View / HTML)
         * ----------------------------------------------------------
         * Example: return view('payment.page')
         */
        if ($link instanceof \Illuminate\Contracts\Support\Renderable) {
            $html = $link->render();
            return $isAjax
                ? response()->json(['status' => 'success', 'action' => 'html', 'html' => $html])
                : $link;
        }


        /**
         * ----------------------------------------------------------
         * Case 3: String URL returned from gateway
         * ----------------------------------------------------------
         */
        if (is_string($link) && ($url = trim($link)) !== '') {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return $isAjax
                    ? response()->json(['status' => 'error', 'error' => __('Invalid payment link.')], 422)
                    : back()->withInput()->with('error', __('Invalid payment link.'));
            }

            return $isAjax
                ? response()->json(['status' => 'success', 'action' => 'redirect', 'url' => $url])
                : redirect()->away($url);
        }

        /**
         * ----------------------------------------------------------
         * Case 4: Fallback error
         * ----------------------------------------------------------
         * Triggered when no valid payment response is detected
         */
        return $isAjax
            ? response()->json(['status' => 'error', 'error' => __('Unable to start payment.')], 422)
            : back()->withInput()->with('error', __('Unable to start payment.'));
    }
}
