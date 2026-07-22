<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SMSTrait;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers, SMSTrait {
        AuthenticatesUsers::login as protected passwordLogin;
    }

    protected $redirectTo = RouteServiceProvider::HOME;

    private const OTP_SESSION_KEY = 'admin_login_2fa';
    private const OTP_TTL_SECONDS = 300;
    private const OTP_RESEND_SECONDS = 60;
    private const OTP_MAX_ATTEMPTS = 5;
    private const DEFAULT_ADMIN_2FA_PHONE = '995595410033';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        return match ($request->input('otp_step')) {
            'verify' => $this->verifyOtp($request),
            'resend' => $this->resendOtp($request),
            default => $this->passwordLogin($request),
        };
    }

    protected function validateLogin(Request $request)
    {
        $generalCaptcha = GeneralSetting::getMetaValue('general_captcha');
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];

        if ($generalCaptcha === 'yes') {
            $rules['g-recaptcha-response'] = ['required'];
        }

        $request->validate($rules);

        if ($generalCaptcha !== 'yes') {
            return;
        }

        $privateKey = GeneralSetting::getMetaValue('private_key');
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $privateKey,
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (! $response->successful() || ! $response->json('success')) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'reCAPTCHA verification failed.',
            ]);
        }
    }

    protected function authenticated(Request $request, $user)
    {
        if (
            ! ($user instanceof User)
            || ! $user->roles()->exists()
            || ! $this->adminOtpEnabled()
        ) {
            return null;
        }

        $phone = $this->adminOtpPhone();
        if ($phone === '') {
            $this->guard()->logout();

            return $this->loginErrorResponse(
                $request,
                'Admin SMS verification is enabled, but the verification phone number is missing.'
            );
        }

        $challenge = [
            'user_id' => $user->getAuthIdentifier(),
            'remember' => $request->boolean('remember'),
            'phone' => $phone,
            'intended' => $request->session()->pull('url.intended', route('admin.home')),
        ];

        $this->guard()->logout();

        try {
            $this->issueOtp($request, $challenge);
        } catch (\Throwable $exception) {
            $this->clearOtpChallenge($request);
            Log::error('Admin login OTP could not be issued.', [
                'exception' => $exception->getMessage(),
            ]);

            return $this->loginErrorResponse(
                $request,
                'The verification SMS could not be sent. Please try again.'
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'requires_otp' => true,
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login');
    }

    public function showLoginForm(Request $request)
    {
        $challenge = $request->session()->get(self::OTP_SESSION_KEY);

        if (is_array($challenge)) {
            if ($this->challengeExpired($challenge)) {
                $this->clearOtpChallenge($request);
                $request->session()->flash('error', 'The verification code expired. Please sign in again.');
            } else {
                return view('auth.verify-otp', array_merge($this->loginPageSettings(), [
                    'maskedPhone' => $this->maskPhoneNumber((string) ($challenge['phone'] ?? '')),
                    'resendAvailableIn' => max(0, (int) ($challenge['resend_at'] ?? 0) - now()->timestamp),
                ]));
            }
        }

        return view('auth.login', $this->loginPageSettings());
    }

    private function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $challenge = $request->session()->get(self::OTP_SESSION_KEY);
        if (! is_array($challenge)) {
            return redirect()->route('login')->with('error', 'Please sign in again to request a new code.');
        }

        if ($this->challengeExpired($challenge)) {
            $this->clearOtpChallenge($request);

            return redirect()->route('login')->with('error', 'The verification code expired. Please sign in again.');
        }

        $attempts = (int) ($challenge['attempts'] ?? 0);
        if ($attempts >= self::OTP_MAX_ATTEMPTS) {
            $this->clearOtpChallenge($request);

            return redirect()->route('login')->with('error', 'Too many incorrect attempts. Please sign in again.');
        }

        if (! Hash::check((string) $request->input('otp'), (string) ($challenge['otp_hash'] ?? ''))) {
            $challenge['attempts'] = $attempts + 1;
            $request->session()->put(self::OTP_SESSION_KEY, $challenge);

            if ($challenge['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                $this->clearOtpChallenge($request);

                return redirect()->route('login')->with('error', 'Too many incorrect attempts. Please sign in again.');
            }

            throw ValidationException::withMessages([
                'otp' => 'The verification code is incorrect.',
            ]);
        }

        $user = User::find($challenge['user_id'] ?? null);
        if (! $user || ! $user->roles()->exists()) {
            $this->clearOtpChallenge($request);

            return redirect()->route('login')->with('error', 'The administrator account could not be verified.');
        }

        $remember = (bool) ($challenge['remember'] ?? false);
        $intended = (string) ($challenge['intended'] ?? route('admin.home'));

        $this->clearOtpChallenge($request);
        $this->guard()->login($user, $remember);
        $request->session()->regenerate();

        return redirect()->to($intended);
    }

    private function resendOtp(Request $request)
    {
        $challenge = $request->session()->get(self::OTP_SESSION_KEY);
        if (! is_array($challenge)) {
            return redirect()->route('login')->with('error', 'Please sign in again to request a new code.');
        }

        if ($this->challengeExpired($challenge)) {
            $this->clearOtpChallenge($request);

            return redirect()->route('login')->with('error', 'The verification session expired. Please sign in again.');
        }

        $secondsRemaining = (int) ($challenge['resend_at'] ?? 0) - now()->timestamp;
        if ($secondsRemaining > 0) {
            return redirect()->route('login')->withErrors([
                'otp' => "Please wait {$secondsRemaining} seconds before requesting another code.",
            ]);
        }

        try {
            $this->issueOtp($request, $challenge);
        } catch (\Throwable $exception) {
            Log::error('Admin login OTP resend failed.', [
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'otp' => 'The verification SMS could not be resent. Please try again.',
            ]);
        }

        return redirect()->route('login')->with('status', 'A new verification code was sent.');
    }

    private function issueOtp(Request $request, array $challenge): void
    {
        $otp = (string) random_int(100000, 999999);
        $now = now()->timestamp;

        $challenge['otp_hash'] = Hash::make($otp);
        $challenge['expires_at'] = $now + self::OTP_TTL_SECONDS;
        $challenge['resend_at'] = $now + self::OTP_RESEND_SECONDS;
        $challenge['attempts'] = 0;

        $request->session()->put(self::OTP_SESSION_KEY, $challenge);

        $this->sendSMS(
            'Mili Taxi',
            "Mili Taxi admin login verification code: {$otp}. Valid for 5 minutes.",
            (string) $challenge['phone']
        );
    }

    private function adminOtpEnabled(): bool
    {
        return (GeneralSetting::getMetaValue('admin_login_2fa_enabled') ?? 'yes') === 'yes';
    }

    private function adminOtpPhone(): string
    {
        $phone = (string) (GeneralSetting::getMetaValue('admin_login_2fa_phone') ?? self::DEFAULT_ADMIN_2FA_PHONE);

        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function challengeExpired(array $challenge): bool
    {
        return (int) ($challenge['expires_at'] ?? 0) < now()->timestamp;
    }

    private function clearOtpChallenge(Request $request): void
    {
        $request->session()->forget(self::OTP_SESSION_KEY);
    }

    private function loginErrorResponse(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => ['email' => [$message]],
            ], 422);
        }

        return redirect()->route('login')->with('error', $message);
    }

    private function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 6) {
            return str_repeat('*', $length);
        }

        return substr($phone, 0, 3).str_repeat('*', max(0, $length - 7)).substr($phone, -4);
    }

    private function loginPageSettings(): array
    {
        $settings = GeneralSetting::whereIn('meta_key', [
            'general_name',
            'general_description',
            'general_logo',
            'general_favicon',
            'general_loginBackgroud',
            'general_captcha',
            'site_key',
            'private_key',
        ])->pluck('meta_value', 'meta_key');

        return [
            'logoUrl' => '/storage/'.($settings['general_logo'] ?? 'default_logo.png'),
            'siteName' => $settings['general_name'] ?? '',
            'tagLine' => $settings['general_description'] ?? '',
            'faviconUrl' => '/storage/'.($settings['general_favicon'] ?? 'default_favicon.png'),
            'loginBackgroud' => '/storage/'.($settings['general_loginBackgroud'] ?? 'default_bg.png'),
            'general_captcha' => $settings['general_captcha'] ?? '',
            'site_key' => $settings['site_key'] ?? '',
            'private_key' => $settings['private_key'] ?? '',
        ];
    }
}
