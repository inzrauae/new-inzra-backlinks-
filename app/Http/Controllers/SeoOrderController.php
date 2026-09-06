<?php

namespace App\Http\Controllers;

use App\Models\SeoOrder;
use App\Support\SeoData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeoOrderController extends Controller
{
    public function index(): View
    {
        $orders = Auth::user()->seoOrders()->with('service')->latest()->paginate(10);

        return view('seo-orders.index', [
            'seo' => SeoData::forNoIndex('My SEO Orders | INZRA', route('seo-orders.index')),
            'orders' => $orders,
        ]);
    }

    public function show(SeoOrder $seoOrder): View
    {
        Gate::authorize('view', $seoOrder);

        $seoOrder->load(['service', 'country', 'keywords', 'report', 'statusHistory']);

        return view('seo-orders.show', [
            'seo' => SeoData::forNoIndex("SEO Order {$seoOrder->order_number} | INZRA", route('seo-orders.show', $seoOrder)),
            'order' => $seoOrder,
        ]);
    }

    public function downloadReportPdf(SeoOrder $seoOrder): StreamedResponse
    {
        Gate::authorize('view', $seoOrder);

        $report = $seoOrder->report;
        abort_unless($report && $report->isReady() && $report->pdf_path && Storage::disk('local')->exists($report->pdf_path), 404);

        return Storage::disk('local')->download($report->pdf_path, "{$seoOrder->order_number}-report.pdf");
    }

    public function downloadReportCsv(SeoOrder $seoOrder): StreamedResponse
    {
        Gate::authorize('view', $seoOrder);

        $report = $seoOrder->report;
        abort_unless($report && $report->isReady() && $report->csv_path && Storage::disk('local')->exists($report->csv_path), 404);

        return Storage::disk('local')->download($report->csv_path, "{$seoOrder->order_number}-report.csv");
    }
}
