<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Cart</p>
      <h2 class="section__title">Your cart</h2>
    </header>

    @if (session('status'))
      <div class="auth-status reveal">{{ session('status') }}</div>
    @endif

    @if ($lines->isEmpty())
      <div class="auth-card glass reveal">
        <p class="auth-card__sub" style="margin-bottom:16px;">Your cart is empty.</p>
        <a href="{{ route('marketplace') }}" class="btn btn--primary ripple">Browse the marketplace</a>
      </div>
    @else
      <div class="auth-card glass reveal" style="overflow-x:auto; margin-bottom:24px;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="text-align:left; border-bottom:1px solid var(--line);">
              <th style="padding:10px 12px;">Product</th>
              <th style="padding:10px 12px;">Qty</th>
              <th style="padding:10px 12px;">Price</th>
              <th style="padding:10px 12px;">Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($lines as $line)
              <tr style="border-bottom:1px solid var(--line);">
                <td style="padding:10px 12px;">
                  <a href="{{ route('products.show', $line['product']) }}" class="auth-link">{{ $line['product']->name }}</a>
                  @if ($line['target_url'])
                    <div style="font-size:.82rem; color:var(--text-2);">Target: {{ $line['target_url'] }}</div>
                  @endif
                  @if ($line['anchor_text'])
                    <div style="font-size:.82rem; color:var(--text-2);">Anchor: {{ $line['anchor_text'] }}</div>
                  @endif
                  @if ($line['target_country'])
                    <div style="font-size:.82rem; color:var(--text-2);">Country: {{ $line['target_country'] }}</div>
                  @endif
                </td>
                <td style="padding:10px 12px;">
                  <form method="POST" action="{{ route('cart.update', $line['line_id']) }}" style="display:flex; align-items:center; gap:8px;">
                    @csrf
                    @method('patch')
                    <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="100" class="auth-input" style="width:70px; padding:6px 10px;">
                    <button type="submit" class="btn btn--glass btn--sm">Update</button>
                  </form>
                </td>
                <td style="padding:10px 12px;">${{ $line['product']->formatted_price }}</td>
                <td style="padding:10px 12px;">${{ number_format($line['subtotal'], 2) }}</td>
                <td style="padding:10px 12px;">
                  <form method="POST" action="{{ route('cart.remove', $line['line_id']) }}">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn--glass btn--sm">Remove</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="auth-card glass reveal" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
        <p style="font-family:var(--font-display); font-size:1.2rem;">Total: ${{ number_format($total, 2) }}</p>
        <a href="{{ route('checkout.index') }}" class="btn btn--primary btn--lg ripple">Proceed to checkout</a>
      </div>
    @endif
  </div>
</section>

</x-app-layout>
