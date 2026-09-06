<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecalculateSeoOrderProgress;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSeoPublicationRequest;
use App\Http\Requests\Admin\UpdateSeoPublicationRequest;
use App\Models\SeoOrder;
use App\Models\SeoPublication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SeoPublicationController extends Controller
{
    public function store(StoreSeoPublicationRequest $request, SeoOrder $seoOrder): RedirectResponse
    {
        $seoOrder->publications()->create($request->validated() + ['added_by' => Auth::id()]);

        (new RecalculateSeoOrderProgress)->handle($seoOrder);

        return redirect()->route('admin.seo-orders.show', $seoOrder)->with('status', 'Publication record added.');
    }

    public function update(UpdateSeoPublicationRequest $request, SeoOrder $seoOrder, SeoPublication $publication): RedirectResponse
    {
        abort_unless($publication->seo_order_id === $seoOrder->id, 404);

        $publication->update($request->validated());

        (new RecalculateSeoOrderProgress)->handle($seoOrder);

        return redirect()->route('admin.seo-orders.show', $seoOrder)->with('status', 'Publication record updated.');
    }

    public function destroy(SeoOrder $seoOrder, SeoPublication $publication): RedirectResponse
    {
        abort_unless($publication->seo_order_id === $seoOrder->id, 404);

        $publication->delete();

        (new RecalculateSeoOrderProgress)->handle($seoOrder);

        return redirect()->route('admin.seo-orders.show', $seoOrder)->with('status', 'Publication record removed.');
    }
}
