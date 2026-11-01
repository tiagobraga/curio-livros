<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch current application locale and persist in session.
     */
    public function switchLocale($locale)
    {
        $supported = ['pt_BR', 'en_US', 'es'];

        if (in_array($locale, $supported)) {
            Session::put('locale', $locale);
        }

        return redirect()->back();
    }
}
