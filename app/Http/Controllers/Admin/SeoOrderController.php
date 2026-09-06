<?php

namespace App\Http\Controllers\Admin;

use App\Actions\RecordSeoOrderStatusChange;
use App\Enums\PaymentStatus;
use App\Enums\PublicationStatus;
use App\Enums\SeoOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoOrderRequest;
use App\Mail\SeoOrderStatusChanged;
use App\Models\Country;
use App\Models\SeoOrder;
use App\Models\SeoService;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SeoOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = SeoOrder::with(['user', 'service', 'country'])
            ->when($request->filled('status'), fn ($q) => $q->where('order_status', $request->string('status')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('service_id'), fn ($q) => $q->where('seo_service_id', $request->integer('service_id')))
            ->when($request->filled('country_id'), fn ($q) => $q->where('country_id', $request->integer('country_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.seo-orders.index', [
            'seo' => SeoData::forNoIndex('SEO Orders | INZRA Admin', route('admin.seo-orders.index')),
            'orders' => $orders,
            'statuses' => SeoOrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'services' => SeoService::orderBy('name')->get(),
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function show(SeoOrder $seoOrder): View
    {
        $seoOrder->load(['user', 'service', 'country', 'keywords', 'statusHistory.changedBy', 'notes.author', 'report']);

        // Orders can carry thousands of publication records — paginate
        // rather than loading them all into one page.
        $publications = $seoOrder->publications()->latest()->paginate(50)->withQueryString();

        return view('admin.seo-orders.show', [
            'seo' => SeoData::forNoIndex("SEO Order {$seoOrder->order_number} | INZRA Admin", route('admin.seo-orders.show', $seoOrder)),
            'order' => $seoOrder,
            'publications' => $publications,
            'statuses' => SeoOrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'publicationStatuses' => PublicationStatus::cases(),
        ]);
    }

    public function update(UpdateSeoOrderRequest $request, SeoOrder $seoOrder): RedirectResponse
    {
        $data = $request->validated();
        $newStatus = SeoOrderStatus::from($data['order_status']);

        $seoOrder->update([
            'payment_status' => $data['payment_status'],
            'estimated_completion_at' => $data['estimated_completion_at'] ?? null,
        ]);

        if ($newStatus !== $seoOrder->order_status) {
            (new RecordSeoOrderStatusChange)->handle($seoOrder, $newStatus, Auth::user());

            if (in_array($newStatus, [SeoOrderStatus::Completed], true) && ! $seoOrder->completed_at) {
                $seoOrder->update(['completed_at' => now()]);
            }

            Mail::to($seoOrder->user->email)->send(new SeoOrderStatusChanged($seoOrder->fresh()));
        }

        return redirect()->route('admin.seo-orders.show', $seoOrder)->with('status', 'Order updated.');
    }
}
