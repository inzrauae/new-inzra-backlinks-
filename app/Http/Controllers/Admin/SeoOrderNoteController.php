<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSeoOrderNoteRequest;
use App\Models\SeoOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SeoOrderNoteController extends Controller
{
    public function store(StoreSeoOrderNoteRequest $request, SeoOrder $seoOrder): RedirectResponse
    {
        $seoOrder->notes()->create($request->validated() + ['user_id' => Auth::id()]);

        return redirect()->route('admin.seo-orders.show', $seoOrder)->with('status', 'Note added.');
    }
}
