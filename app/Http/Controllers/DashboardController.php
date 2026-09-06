<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\SeoOrderStatus;
use App\Support\SeoData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $orders = $user->orders();
        $seoOrders = $user->seoOrders();

        $counts = [
            'total' => (clone $orders)->count(),
            'pending' => (clone $orders)->where('status', OrderStatus::Pending)->count(),
            'processing' => (clone $orders)->where('status', OrderStatus::Processing)->count(),
            'completed' => (clone $orders)->where('status', OrderStatus::Completed)->count(),
            'cancelled' => (clone $orders)->where('status', OrderStatus::Cancelled)->count(),
        ];

        $seoCounts = [
            'total' => (clone $seoOrders)->count(),
            'active' => (clone $seoOrders)->whereIn('order_status', [
                SeoOrderStatus::OrderReceived, SeoOrderStatus::InProgress, SeoOrderStatus::PartiallyCompleted,
            ])->count(),
            'completed' => (clone $seoOrders)->where('order_status', SeoOrderStatus::Completed)->count(),
            'spending' => (clone $seoOrders)->where('payment_status', 'paid')->sum('total'),
        ];

        return view('dashboard', [
            'seo' => SeoData::forNoIndex('Dashboard | INZRA', route('dashboard')),
            'counts' => $counts,
            'seoCounts' => $seoCounts,
            'recentOrders' => (clone $orders)->latest()->take(5)->get(),
            'recentSeoOrders' => (clone $seoOrders)->with('service')->latest()->take(5)->get(),
        ]);
    }
}
