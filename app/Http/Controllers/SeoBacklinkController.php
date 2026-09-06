<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\PaymentSetting;
use App\Models\SeoService;
use App\Support\SeoData;
use Illuminate\View\View;

class SeoBacklinkController extends Controller
{
    public function index(): View
    {
        $services = SeoService::where('is_active', true)->orderBy('sort_order')->get();

        return view('pages.seo-backlink-services.index', [
            'seo' => SeoData::forSeoBacklinkServicesIndex(),
            'services' => $services,
        ]);
    }

    public function show(SeoService $service): View
    {
        abort_unless($service->is_active, 404);

        $countries = Country::orderBy('sort_order')->orderBy('name')->get();
        $paypal = PaymentSetting::paypal();
        $quantityPresets = collect(config('seo_backlinks.quantity_presets'))
            ->filter(fn ($qty) => $qty >= $service->min_quantity && $qty <= $service->max_quantity)
            ->values();

        return view('pages.seo-backlink-services.show', [
            'seo' => SeoData::forSeoBacklinkService($service),
            'service' => $service,
            'countries' => $countries,
            'paypal' => $paypal,
            'quantityPresets' => $quantityPresets,
            'termsVersion' => config('seo_backlinks.terms_version'),
        ]);
    }
}
