<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSeoServiceRequest;
use App\Http\Requests\Admin\UpdateSeoServiceRequest;
use App\Models\SeoService;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SeoServiceController extends Controller
{
    public function index(): View
    {
        $services = SeoService::withCount('orders')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.seo-services.index', [
            'seo' => SeoData::forNoIndex('SEO Services | INZRA Admin', route('admin.seo-services.index')),
            'services' => $services,
        ]);
    }

    public function create(): View
    {
        return view('admin.seo-services.create', [
            'seo' => SeoData::forNoIndex('New SEO Service | INZRA Admin', route('admin.seo-services.create')),
        ]);
    }

    public function store(StoreSeoServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        SeoService::create($data);

        return redirect()->route('admin.seo-services.index')->with('status', 'Service created.');
    }

    public function edit(SeoService $seoService): View
    {
        return view('admin.seo-services.edit', [
            'seo' => SeoData::forNoIndex("Edit {$seoService->name} | INZRA Admin", route('admin.seo-services.edit', $seoService)),
            'service' => $seoService,
        ]);
    }

    public function update(UpdateSeoServiceRequest $request, SeoService $seoService): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: $seoService->slug;
        $data['is_active'] = $request->boolean('is_active', false);

        $seoService->update($data);

        return redirect()->route('admin.seo-services.index')->with('status', 'Service updated.');
    }
}
