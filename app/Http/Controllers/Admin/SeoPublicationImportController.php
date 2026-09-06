<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecalculateSeoOrderProgress;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PreviewSeoPublicationImportRequest;
use App\Models\SeoOrder;
use App\Services\SeoPublicationCsvImporter;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SeoPublicationImportController extends Controller
{
    public function create(SeoOrder $seoOrder): View
    {
        return view('admin.seo-orders.import', [
            'seo' => SeoData::forNoIndex("Import publications — {$seoOrder->order_number} | INZRA Admin", route('admin.seo-orders.publications.import.create', $seoOrder)),
            'order' => $seoOrder,
        ]);
    }

    public function preview(PreviewSeoPublicationImportRequest $request, SeoOrder $seoOrder, SeoPublicationCsvImporter $importer): View
    {
        $result = $importer->preview($request->file('csv_file'), $seoOrder);

        return view('admin.seo-orders.import', [
            'seo' => SeoData::forNoIndex("Import publications — {$seoOrder->order_number} | INZRA Admin", route('admin.seo-orders.publications.import.create', $seoOrder)),
            'order' => $seoOrder,
            'result' => $result,
        ]);
    }

    public function import(Request $request, SeoOrder $seoOrder, SeoPublicationCsvImporter $importer): RedirectResponse
    {
        $request->validate(['token' => ['required', 'uuid']]);

        $imported = $importer->import($request->string('token'), $seoOrder, Auth::id());

        if ($imported > 0) {
            (new RecalculateSeoOrderProgress)->handle($seoOrder);
        }

        return redirect()->route('admin.seo-orders.show', $seoOrder)
            ->with('status', $imported > 0
                ? "Imported {$imported} publication record(s)."
                : 'Nothing to import — the preview may have expired, please upload the file again.');
    }
}
