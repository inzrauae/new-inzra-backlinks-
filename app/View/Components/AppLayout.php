<?php

namespace App\View\Components;

use App\Support\SeoData;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public SeoData $seo;

    /**
     * $seo is optional here only so Breeze's own auth-gated views (dashboard,
     * profile) — noindex pages that don't build their own SeoData yet — keep
     * working via <x-app-layout> without passing one. Every real page in
     * pages/* should pass an explicit $seo.
     */
    public function __construct(?SeoData $seo = null, public ?string $active = null)
    {
        $this->seo = $seo ?? SeoData::forNoIndex('INZRA', url()->current());
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
