<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Support\SeoData;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $orders = $user->orders();

        $counts = [
            'total' => (clone $orders)->count(),
            'pending' => (clone $orders)->where('status', OrderStatus::Pending)->count(),
            'processing' => (clone $orders)->where('status', OrderStatus::Processing)->count(),
            'completed' => (clone $orders)->where('status', OrderStatus::Completed)->count(),
            'cancelled' => (clone $orders)->where('status', OrderStatus::Cancelled)->count(),
        ];

        return view('dashboard', [
            'seo' => SeoData::forNoIndex('Dashboard | INZRA', route('dashboard')),
            'counts' => $counts,
            'recentOrders' => (clone $orders)->latest()->take(5)->get(),
        ]);
    }
}
