<?php

namespace App\Http\Middleware;

use App\Models\AppUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->input('lang'); // get lang from JSON body

        if ($locale && in_array($locale, ['en', 'fr', 'es', 'de', 'it', 'ar', 'ka'], true)) {

            app()->setLocale($locale);
            $token = trim((string) $request->input('token'));
            if ($token !== '') {
                AppUser::query()
                    ->where('token', $token)
                    ->where(function ($query) use ($locale): void {
                        $query->whereNull('preferred_locale')
                            ->orWhere('preferred_locale', '!=', $locale);
                    })
                    ->update(['preferred_locale' => $locale]);
            }
        } else {
            app()->setLocale(config('app.locale')); // fallback to default
        }

        return $next($request);

    }
}
