<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['pt_BR', 'en_US', 'es'];

        if (Session::has('locale') && in_array(Session::get('locale'), $supportedLocales)) {
            $locale = Session::get('locale');
        } else {
            // Auto detect from browser Accept-Language header
            $browserLang = $request->header('Accept-Language');
            $locale = 'pt_BR'; // Default fallback

            if (!empty($browserLang)) {
                $primaryLang = strtolower(substr($browserLang, 0, 2));
                if ($primaryLang === 'en') {
                    $locale = 'en_US';
                } elseif ($primaryLang === 'es') {
                    $locale = 'es';
                } elseif ($primaryLang === 'pt') {
                    $locale = 'pt_BR';
                }
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
