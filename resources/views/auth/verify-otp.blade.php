@extends('layouts.app')

@section('styles')
    <style>
        .otp-input {
            letter-spacing: 12px;
            text-align: center;
            font-size: 26px;
            font-weight: 700;
        }

        .otp-help {
            margin: 12px 0 20px;
            color: #6c757d;
            line-height: 1.6;
        }

        .otp-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
        }

        .otp-actions form {
            margin: 0;
        }

        .resend-button {
            border: 0;
            background: transparent;
            color: #f2b800;
            padding: 0;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <section class="login page-login">
        <div class="login_box">
            <div class="left">
                <div class="contact">
                    <div class="login-box">
                        <div class="login-logo">
                            <h2>SMS Verification</h2>
                            <p style="font-size: medium;">Confirm the administrator login.</p>
                        </div>

                        <div class="login-box-body">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <p class="otp-help">
                                A six-digit verification code was sent to
                                <strong>{{ $maskedPhone }}</strong>.
                                The code is valid for five minutes.
                            </p>

                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <input type="hidden" name="otp_step" value="verify">

                                <div class="form-group{{ $errors->has('otp') ? ' has-error' : '' }}">
                                    <input
                                        id="otp"
                                        type="text"
                                        name="otp"
                                        class="form-control otp-input"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="6"
                                        autocomplete="one-time-code"
                                        autofocus
                                        required
                                        placeholder="••••••">

                                    @if ($errors->has('otp'))
                                        <p class="help-block">
                                            {{ $errors->first('otp') }}
                                        </p>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-primary btn-block btn-flat">
                                    Verify and sign in
                                </button>
                            </form>

                            <div class="otp-actions">
                                <a href="{{ route('login') }}" onclick="event.preventDefault(); document.getElementById('restart-login-form').submit();">
                                    Back to sign in
                                </a>

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <input type="hidden" name="otp_step" value="resend">
                                    <button type="submit" class="resend-button">
                                        Resend code
                                    </button>
                                </form>
                            </div>

                            <form id="restart-login-form" method="POST" action="{{ route('logout') }}" style="display:none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="right">
                <div class="right-text">
                    @if ($logoUrl && file_exists(public_path($logoUrl)))
                        <img src="{{ $logoUrl }}" alt="{{ $siteName ?? trans('global.site_title') }}" />
                    @else
                        <b>{{ trans('global.site_title') }}</b>
                    @endif
                    <h5>{{ $tagLine }}</h5>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('admin.common.addSteps.footer.footerJs')

@section('scripts')
    <style>
        .login-page .right {
            background: linear-gradient(212.38deg,
                    rgba(255, 56, 92, 0.7) 0%,
                    rgba(252, 29, 69, 0.71) 100%),
                url({{ $loginBackgroud }});
            background-size: cover;
            color: #fff;
            position: relative;
        }
    </style>
@endsection
