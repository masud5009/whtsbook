@php
    $bagName = 'errors_' . str_replace('.', '_', $keyword);
    $bag = $errors->getBag($bagName);

    $info = !empty($gateway) ? json_decode($gateway->information ?? '[]', true) : [];

if (!is_array($info)) {
    $info = [];
}
@endphp

<div class="col-lg-4">
    <div class="card">
        <form action="{{ $updateRoute }}" method="post">
            @csrf
            <input type="hidden" name="keyword" value="{{ $keyword }}">

            <div class="card-header">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-title">{{ __($title) }}</div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-5 pb-5">
                <!-- Status -->
                <div class="form-group">
                    <label>{{ __($title) }} {{ __('Status') }}</label>
                    <div class="selectgroup w-100">
                        <label class="selectgroup-item">
                            <input type="radio" name="status" value="1" class="selectgroup-input"
                                {{ !empty($gateway) && (int) $gateway->status === 1 ? 'checked' : '' }}>
                            <span class="selectgroup-button">{{ __('Active') }}</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="status" value="0" class="selectgroup-input"
                                {{ empty($gateway) || (int) $gateway->status === 0 ? 'checked' : '' }}>
                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                        </label>
                    </div>
                    @if ($bag->has('status'))
                        <p class="mb-0 text-danger">{{ $bag->first('status') }}</p>
                    @endif
                </div>

                <!-- Extra radios (optional) -->
                @if (!empty($radios))
                    @foreach ($radios as $r)
                        <div class="form-group">
                            <label>{{ __($r['label']) }}</label>
                            <div class="selectgroup w-100">
                                @foreach ($r['options'] as $opt)
                                    @php
                                        $field = $r['name'];
                                        $checked = array_key_exists($field, $info)
                                            ? (string) $info[$field] === (string) $opt['value']
                                            : !empty($opt['default']);
                                    @endphp
                                    <label class="selectgroup-item">
                                        <input type="radio" name="{{ $field }}" value="{{ $opt['value'] }}"
                                            class="selectgroup-input" {{ $checked ? 'checked' : '' }}>
                                        <span class="selectgroup-button">{{ __($opt['text']) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @if ($bag->has($r['name']))
                                <p class="mb-0 text-danger">{{ $bag->first($r['name']) }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif

                <!-- Inputs -->
                @foreach ($fields as $f)
                    @php
                        $name = $f['name'];
                        $label = $f['label'];
                        $type = $f['type'] ?? 'text';
                        $ltr = !empty($f['ltr']) ? 'ltr' : '';
                        $value = old($name, $info[$name] ?? '');
                    @endphp
                    <div class="form-group">
                        <label>{{ __($label) }}</label>
                        <input type="{{ $type }}" class="form-control {{ $ltr }}"
                            name="{{ $name }}" value="{{ $value }}">
                        @if ($bag->has($name))
                            <p class="mb-0 text-danger">{{ $bag->first($name) }}</p>
                        @endif
                    </div>
                @endforeach
                @if (@$gateway->keyword == 'perfect_money')
                    <span class="form-text text-warning">{{ __('You will get wallet id form here') }}</span>
                @endif
                @if (@$gateway->keyword == 'midtrans')
                    <p class="text-warning mb-0 mt-2">{{ __('Success URL') }} : </p>
                    <p class="text-warning mb-0">{{ __('Cancel URL') }} : </p>
                    <p class="text-warning mb-0">
                        <strong></strong>{{ __('Set url form here') }} : <a href="https://prnt.sc/OiucUCeYJIXo"
                            target="_blank">https://prnt.sc/OiucUCeYJIXo</a>
                    </p>
                @endif
                @if (@$gateway->keyword == 'iyzico')
                    <p class="text-warning"><strong> {{ __('Cron Job Command') }}:</strong> <br>
                        <code class="copy">curl -sS https://ecommet.xyz/check-payment</code>
                    </p>
                    <strong class="text-warning"> {{ __('Set the cron job following this video') }} : </strong>
                    <a href="https://www.awesomescreenshot.com/video/25404126?key=3f7a7fa8cf2391113bb926f43609fa56"
                        target="_blank">https://www.awesomescreenshot.com/video/25404126?key=3f7a7fa8cf2391113bb926f43609fa56</a>
                    <p class="text-danger"> {{ __("without cronjob setup, Iyzico payment method won't work") }}</p>
                @endif
            </div>

            <div class="card-footer">
                <div class="form">
                    <div class="form-group from-show-notify row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
