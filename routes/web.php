<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\SeoOrderController as AdminSeoOrderController;
use App\Http\Controllers\Admin\SeoOrderNoteController;
use App\Http\Controllers\Admin\SeoPublicationController;
use App\Http\Controllers\Admin\SeoPublicationImportController;
use App\Http\Controllers\Admin\SeoReportController as AdminSeoReportController;
use App\Http\Controllers\Admin\SeoServiceController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoBacklinkController;
use App\Http\Controllers\SeoOrderController;
use App\Http\Controllers\SeoOrderPayPalController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ToolController;
use App\Support\SeoData;
use Illuminate\Support\Facades\Route;

// Historical numbered-slug URLs, kept for their inbound-link SEO equity.
// Must be registered before the {product:slug}/{post:slug} routes below.
foreach (config('legacy_redirects') as $from => $to) {
    Route::redirect('/'.$from, $to, 301);
}

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
Route::redirect('/products', '/marketplace', 301);

Route::get('/categories', [CategoryController::class, 'index'])->name('categories');

Route::get('/markets', [MarketController::class, 'index'])->name('markets.index');
Route::get('/markets/{market}', [MarketController::class, 'show'])->name('markets.show');

Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/image-converter', [ToolController::class, 'imageConverter'])->name('tools.image-converter');
Route::get('/pdf-editor', [ToolController::class, 'pdfEditor'])->name('tools.pdf-editor');

Route::get('/seo-backlink-services', [SeoBacklinkController::class, 'index'])->name('seo-backlink-services.index');
Route::get('/seo-backlink-services/{service:slug}', [SeoBacklinkController::class, 'show'])->name('seo-backlink-services.show');

Route::get('/pricing', fn () => view('pages.pricing', ['seo' => SeoData::forPricing()]))->name('pricing');
Route::get('/contact', fn () => view('pages.contact', ['seo' => SeoData::forContact()]))->name('contact');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])
    ->name('blog.show')
    ->missing(fn () => redirect()->route('blog.index', [], 301));

Route::get('/products/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show')
    ->missing(fn () => redirect()->route('marketplace', [], 301));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/delivery', [OrderController::class, 'downloadDelivery'])->name('orders.delivery');

    Route::get('/buy/{product:slug}', [OrderController::class, 'store'])->name('orders.store');

    Route::post('/paypal/orders/{product:slug}', [PayPalController::class, 'createOrder'])->name('paypal.orders.create');
    Route::post('/paypal/orders/{paypalOrderId}/capture', [PayPalController::class, 'captureOrder'])->name('paypal.orders.capture');

    Route::get('/seo-orders', [SeoOrderController::class, 'index'])->name('seo-orders.index');
    Route::get('/seo-orders/{seoOrder}', [SeoOrderController::class, 'show'])->name('seo-orders.show');
    Route::get('/seo-orders/{seoOrder}/report/pdf', [SeoOrderController::class, 'downloadReportPdf'])->name('seo-orders.report.pdf');
    Route::get('/seo-orders/{seoOrder}/report/csv', [SeoOrderController::class, 'downloadReportCsv'])->name('seo-orders.report.csv');

    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/seo-backlink-services/{service:slug}/paypal/orders', [SeoOrderPayPalController::class, 'createOrder'])->name('seo-paypal.orders.create');
        Route::post('/seo-paypal/orders/{paypalOrderId}/capture', [SeoOrderPayPalController::class, 'captureOrder'])->name('seo-paypal.orders.capture');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

    Route::get('/settings/payment', [PaymentSettingController::class, 'edit'])->name('settings.payment.edit');
    Route::patch('/settings/payment', [PaymentSettingController::class, 'update'])->name('settings.payment.update');

    Route::get('/seo-services', [SeoServiceController::class, 'index'])->name('seo-services.index');
    Route::get('/seo-services/create', [SeoServiceController::class, 'create'])->name('seo-services.create');
    Route::post('/seo-services', [SeoServiceController::class, 'store'])->name('seo-services.store');
    Route::get('/seo-services/{seoService}/edit', [SeoServiceController::class, 'edit'])->name('seo-services.edit');
    Route::patch('/seo-services/{seoService}', [SeoServiceController::class, 'update'])->name('seo-services.update');

    Route::get('/seo-orders', [AdminSeoOrderController::class, 'index'])->name('seo-orders.index');
    Route::get('/seo-orders/{seoOrder}', [AdminSeoOrderController::class, 'show'])->name('seo-orders.show');
    Route::patch('/seo-orders/{seoOrder}', [AdminSeoOrderController::class, 'update'])->name('seo-orders.update');

    Route::post('/seo-orders/{seoOrder}/notes', [SeoOrderNoteController::class, 'store'])->name('seo-orders.notes.store');

    Route::post('/seo-orders/{seoOrder}/publications', [SeoPublicationController::class, 'store'])->name('seo-orders.publications.store');
    Route::patch('/seo-orders/{seoOrder}/publications/{publication}', [SeoPublicationController::class, 'update'])->name('seo-orders.publications.update');
    Route::delete('/seo-orders/{seoOrder}/publications/{publication}', [SeoPublicationController::class, 'destroy'])->name('seo-orders.publications.destroy');

    Route::get('/seo-orders/{seoOrder}/publications/import', [SeoPublicationImportController::class, 'create'])->name('seo-orders.publications.import.create');
    Route::post('/seo-orders/{seoOrder}/publications/import/preview', [SeoPublicationImportController::class, 'preview'])->name('seo-orders.publications.import.preview');
    Route::post('/seo-orders/{seoOrder}/publications/import', [SeoPublicationImportController::class, 'import'])->name('seo-orders.publications.import');

    Route::post('/seo-orders/{seoOrder}/report/regenerate', [AdminSeoReportController::class, 'regenerate'])->name('seo-orders.report.regenerate');
    Route::get('/seo-reports', [AdminSeoReportController::class, 'index'])->name('seo-reports.index');
});

// PayPal server-to-server webhook — no session, so it sits outside the auth
// group and is exempted from CSRF verification in bootstrap/app.php.
Route::post('/webhooks/paypal', [PayPalWebhookController::class, 'handle'])->name('webhooks.paypal');

require __DIR__.'/auth.php';

// Legacy `.html` URLs -> clean equivalent, e.g. /marketplace.html -> /marketplace
Route::get('/{path}.html', fn (string $path) => redirect('/'.$path, 301))
    ->where('path', '.*')
    ->name('legacy-html');
