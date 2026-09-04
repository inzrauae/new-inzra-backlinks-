<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
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

        return view('admin.dashboard', [
            'seo' => SeoData::forNoIndex('Admin | INZRA', route('admin.dashboard')),
            'counts' => $counts,
            'recentOrders' => Order::with('user')->latest()->take(8)->get(),
        ]);
    }
}
