<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Helvetica, Arial, sans-serif; color: #0F172A; font-size: 11px; }
  h1 { font-size: 18px; margin: 0 0 4px; }
  .muted { color: #64748B; }
  table.meta { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
  table.meta td { padding: 3px 0; }
  table.meta td.label { color: #64748B; width: 160px; }
  table.items { width: 100%; border-collapse: collapse; }
  table.items th, table.items td { border: 1px solid #CBD5E1; padding: 5px 6px; text-align: left; font-size: 10px; }
  table.items th { background: #F1F5F9; }
</style>
</head>
<body>
  <h1>INZRA — SEO Order Report</h1>
  <p class="muted">Order {{ $order->order_number }}</p>

  <table class="meta">
    <tr><td class="label">Customer</td><td>{{ $order->user->name }} ({{ $order->user->email }})</td></tr>
    <tr><td class="label">Service</td><td>{{ $order->service_name }}</td></tr>
    <tr><td class="label">Target URL</td><td>{{ $order->target_url }}</td></tr>
    <tr><td class="label">Country</td><td>{{ $order->country?->name ?? '—' }}</td></tr>
    <tr><td class="label">Quantity ordered</td><td>{{ number_format($order->quantity) }}</td></tr>
    <tr><td class="label">Completed (verified)</td><td>{{ number_format($publications->count()) }}</td></tr>
    <tr><td class="label">Order date</td><td>{{ $order->created_at->format('j F Y') }}</td></tr>
    <tr><td class="label">Completion date</td><td>{{ optional($order->completed_at)->format('j F Y') ?? '—' }}</td></tr>
  </table>

  <table class="items">
    <thead>
      <tr>
        <th>No.</th>
        <th>Publisher</th>
        <th>Publisher URL</th>
        <th>Published URL</th>
        <th>Target URL</th>
        <th>Anchor / Text</th>
        <th>Country</th>
        <th>Publication Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($publications as $i => $publication)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $publication->publisher_name }}</td>
          <td>{{ $publication->publisher_url }}</td>
          <td>{{ $publication->published_url }}</td>
          <td>{{ $publication->target_url ?: $order->target_url }}</td>
          <td>{{ $publication->anchor_text }}</td>
          <td>{{ $publication->country }}</td>
          <td>{{ optional($publication->publication_date)->format('j M Y') }}</td>
          <td>{{ $publication->status->label() }}</td>
        </tr>
      @empty
        <tr><td colspan="9">No verified publication records yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
