@extends('layouts.app')

@section('styles')
    <style>
        .copy-container {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 10px;
            position: relative;
        }

        .copy-container span {
            flex: 1;
        }

        .copy-container button {
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
@endsection

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <section class="login page-login">
        <div class="login_box">
            <div class="left">
                <div class="contact">
                    <div class="login-box">
                        @if ($error = session()->pull('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ $error }}
                            </div>
                        @endif

                        <div class="login-logo">
                            <h2>Admin Signin</h2>
                            <p style="font-size: medium;">Welcome back login to your panel.</p>
                        </div>

                        <div class="login-box-body">
                            @if (session('message'))
                                <p class="alert alert-info">{{ session('message') }}</p>
                            @endif

                            <div id="loader" style="display: none;">
                                <div class="spinner"></div>
                            </div>

                            <form method="POST" name="loginform" id="loginform" action="{{ route('login') }}">
                                @csrf

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} has-feedback">
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        class="email form-control"
                                        required
                                        autocomplete="email"
                                        autofocus
                                        placeholder="{{ trans('global.login_email') }}"
                                        value="{{ old('email', null) }}">
                                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                                    <span class="credentail"></span>
                                    <span class="email"></span>

                                    @if ($errors->has('email'))
                                        <p class="help-block">{{ $errors->first('email') }}</p>
                                    @endif
                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} has-feedback">
                                    <div class="fas fa-lock form-control-feedback"></div>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        class="password form-control"
                                        required
                                        autocomplete="current-password"
                                        placeholder="{{ trans('global.login_password') }}">
                                    <span class="input-group-text toggle-password" onclick="togglePassword()">
                                        <i id="eye-icon" class="fas fa-eye-slash"></i>
                                    </span>

                                    @if ($errors->has('password'))
                                        <p class="help-block">{{ $errors->first('password') }}</p>
                                    @endif
                                </div>

                                <div class="form-actions">
                                    <div class="remember-me">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="remember" value="1">
                                                {{ trans('global.remember_me') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="forgot-password-link">
                                        <a href="{{ route('password.request') }}" class="btn btn-link">
                                            {{ trans('global.forgot_password') }}
                                        </a>
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('g-recaptcha-response') ? ' has-error' : '' }} has-feedback">
                                    @if ($general_captcha == 'yes')
                                        <div class="g-recaptcha" data-sitekey="{{ $site_key }}"></div>
                                        <p class="captchamsg"></p>

                                        @if ($errors->has('g-recaptcha-response'))
                                            <p class="help-block">{{ $errors->first('g-recaptcha-response') }}</p>
                                        @endif
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-primary btn-block btn-flat">
                                    {{ trans('global.login') }}
                                </button>

                                <br><br>
                                <div class="row">
                                    @include('admin.demo.demo-user')
                                </div>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @if ($general_captcha == 'yes')
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const showing = passwordField.type === 'text';

            passwordField.type = showing ? 'password' : 'text';
            eyeIcon.classList.toggle('fa-eye', !showing);
            eyeIcon.classList.toggle('fa-eye-slash', showing);
        }

        $(function () {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });

            $('.copy_cred').click(function (event) {
                event.preventDefault();
                $('#email').val('admin@sizhitsolutions.com');
                $('#password').val('Admin@1234');

                toastr.options.closeButton = true;
                toastr.options.progressBar = true;
                toastr.options.positionClass = 'toast-bottom-right';
                toastr.success('Email and password copied!');
            });

            $('#loginform').submit(function (event) {
                event.preventDefault();

                $('.email, .password, .captchamsg, .credentail').text('');
                $('.email, .password, .captchamsg, .credentail').removeClass('error');

                const email = $('#email').val();
                const password = $('#password').val();

                if (!email) {
                    $('.email').text('The email field is required').addClass('error');
                    return;
                }

                if (!password) {
                    $('.password').text('Please fill the password.').addClass('error');
                    return;
                }

                @if ($general_captcha == 'yes')
                    if (!grecaptcha.getResponse()) {
                        $('.captchamsg').text('Please fill the reCAPTCHA.').addClass('error');
                        return;
                    }
                @endif

                $('#loader').show();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('login') }}',
                    data: $('#loginform').serialize(),
                    success: function (data) {
                        $('#loader').hide();
                        window.location.href = data && data.redirect ? data.redirect : '/admin';
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        const message = response && response.errors && response.errors.email
                            ? response.errors.email[0]
                            : 'Login failed. Please try again.';

                        $('.credentail').text(message).addClass('error');
                        $('#loader').hide();

                        @if ($general_captcha == 'yes')
                            grecaptcha.reset();
                        @endif
                    }
                });
            });
        });
    </script>

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
