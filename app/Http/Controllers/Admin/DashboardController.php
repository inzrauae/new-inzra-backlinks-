<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SeoOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SeoOrder;
use App\Models\User;
use App\Support\SeoData;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counts = [
            'users' => User::count(),
            'orders' => Order::count(),
            'pending' => Order::where('status', OrderStatus::Pending)->count(),
            'processing' => Order::where('status', OrderStatus::Processing)->count(),
            'completed' => Order::where('status', OrderStatus::Completed)->count(),
            'sales' => Order::where('payment_status', PaymentStatus::Paid)->sum('total'),
        ];

        $seoCounts = [
            'orders' => SeoOrder::count(),
            'pending' => SeoOrder::whereIn('order_status', [SeoOrderStatus::PendingPayment, SeoOrderStatus::Paid, SeoOrderStatus::OrderReceived])->count(),
            'in_progress' => SeoOrder::whereIn('order_status', [SeoOrderStatus::InProgress, SeoOrderStatus::PartiallyCompleted])->count(),
            'completed' => SeoOrder::where('order_status', SeoOrderStatus::Completed)->count(),
            'revenue' => SeoOrder::where('payment_status', PaymentStatus::Paid)->sum('total'),
            'customers' => SeoOrder::where('payment_status', PaymentStatus::Paid)->distinct('user_id')->count('user_id'),
        ];

        return view('admin.dashboard', [
            'seo' => SeoData::forNoIndex('Admin | INZRA', route('admin.dashboard')),
            'counts' => $counts,
            'seoCounts' => $seoCounts,
            'recentOrders' => Order::with('user')->latest()->take(8)->get(),
            'recentSeoOrders' => SeoOrder::with(['user', 'service'])->latest()->take(8)->get(),
        ]);
    }
}
