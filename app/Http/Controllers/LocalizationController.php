<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    /**
     * Get language.
     *
     * @return string
     */
    public function getLang(): string
    {
        return App::getLocale();
    }

    /**
     * Set language.
     *
     * @param string $lang The language.
     * @return RedirectResponse The redirect response.
     */
    public function setLang(string $lang): RedirectResponse
    {
        Session::put('lang', $lang);
        return redirect()->back();
    }
}
