@php($adminActive ??= null)
<nav aria-label="Admin sections" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:28px;">
  <a href="{{ route('admin.dashboard') }}" class="btn btn--sm {{ $adminActive === 'dashboard' ? 'btn--primary' : 'btn--glass' }}">Dashboard</a>
  <a href="{{ route('admin.orders.index') }}" class="btn btn--sm {{ $adminActive === 'orders' ? 'btn--primary' : 'btn--glass' }}">Orders</a>
  <a href="{{ route('admin.seo-orders.index') }}" class="btn btn--sm {{ $adminActive === 'seo-orders' ? 'btn--primary' : 'btn--glass' }}">SEO Orders</a>
  <a href="{{ route('admin.seo-services.index') }}" class="btn btn--sm {{ $adminActive === 'seo-services' ? 'btn--primary' : 'btn--glass' }}">SEO Services</a>
  <a href="{{ route('admin.seo-reports.index') }}" class="btn btn--sm {{ $adminActive === 'seo-reports' ? 'btn--primary' : 'btn--glass' }}">SEO Reports</a>
  <a href="{{ route('admin.settings.payment.edit') }}" class="btn btn--sm {{ $adminActive === 'payment' ? 'btn--primary' : 'btn--glass' }}">Payment Settings</a>
</nav>
