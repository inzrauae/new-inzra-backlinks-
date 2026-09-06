<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateSeoOrderReport;
use App\Http\Controllers\Controller;
use App\Mail\SeoReportReady;
use App\Models\SeoOrder;
use App\Models\SeoReport;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SeoReportController extends Controller
{
    public function index(): View
    {
        $reports = SeoReport::with(['order.user', 'order.service'])
            ->latest('generated_at')
            ->paginate(20);

        return view('admin.seo-reports.index', [
            'seo' => SeoData::forNoIndex('SEO Reports | INZRA Admin', route('admin.seo-reports.index')),
            'reports' => $reports,
        ]);
    }

    public function regenerate(SeoOrder $seoOrder): RedirectResponse
    {
        $report = (new GenerateSeoOrderReport)->handle($seoOrder);

        Mail::to($seoOrder->user->email)->send(new SeoReportReady($seoOrder->fresh()));

        return redirect()->route('admin.seo-orders.show', $seoOrder)
            ->with('status', "Report regenerated with {$report->publication_count} verified record(s).");
    }
}
