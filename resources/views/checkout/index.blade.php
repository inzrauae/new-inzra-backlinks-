<x-app-layout :seo="$seo">

<section class="section">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Checkout</p>
      <h2 class="section__title">Checkout</h2>
    </header>

    <div class="auth-card glass reveal" style="overflow-x:auto; margin-bottom:24px;">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:4px;">Order summary</h3>
      <p class="pdp__note" style="margin-bottom:16px;">You'll add each item's target URL, anchor text and target country from your order page after you complete this purchase.</p>
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:10px 12px;">Product</th>
            <th style="padding:10px 12px;">Qty</th>
            <th style="padding:10px 12px;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($lines as $line)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;">{{ $line['product']->name }}</td>
              <td style="padding:10px 12px;">{{ $line['quantity'] }}</td>
              <td style="padding:10px 12px;">${{ number_format($line['subtotal'], 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <p style="text-align:right; font-family:var(--font-display); font-size:1.2rem; margin-top:16px;">Total: ${{ number_format($total, 2) }}</p>
    </div>

    <div class="auth-card glass reveal">
      <h3 style="font-family:var(--font-display); font-size:1.1rem; margin-bottom:16px;">Payment</h3>

      @if ($paypal->enabled && $paypal->client_id)
        <div id="paypal-button-container" data-create-url="{{ route('paypal.cart-orders.create') }}"></div>
        <div id="paypal-card-button-container" style="margin-top:10px;"></div>
        <p class="pdp__note">You'll be redirected to PayPal to complete your payment securely — no PayPal account needed, a "Debit or Credit Card" option is offered too.</p>
      @else
        <p class="pdp__note" style="margin-bottom:16px;">Online payment isn't configured yet. You can still place this order through WhatsApp.</p>
        <form method="POST" action="{{ route('checkout.whatsapp') }}">
          @csrf
          <button type="submit" class="btn btn--glass btn--lg btn--block"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Order via WhatsApp</button>
        </form>
      @endif
    </div>
  </div>
</section>

@if ($paypal->enabled && $paypal->client_id)
@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypal->client_id }}&currency={{ $lines->first()['product']->currency }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var container = document.getElementById('paypal-button-container');
  var cardContainer = document.getElementById('paypal-card-button-container');
  if (!container || typeof paypal === 'undefined') return;
  var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  function createOrder() {
    return fetch(container.dataset.createUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      }
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.error) { throw new Error(data.error); }
        return data.id;
      });
  }

  function onApprove(data) {
    return fetch('/paypal/orders/' + data.orderID + '/capture', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
      .then(function (res) { return res.json(); })
      .then(function (result) {
        if (result.redirect) { window.location.href = result.redirect; }
      });
  }

  function onError(err) {
    console.error('PayPal checkout error', err);
    alert('Something went wrong starting PayPal checkout. Please try again in a moment.');
  }

  paypal.Buttons({
    style: { layout: 'horizontal', color: 'gold', shape: 'pill', label: 'paypal', height: 45 },
    createOrder: createOrder,
    onApprove: onApprove,
    onError: onError
  }).render('#paypal-button-container');

  if (cardContainer) {
    var cardButtons = paypal.Buttons({
      fundingSource: paypal.FUNDING.CARD,
      style: { shape: 'pill', height: 45 },
      createOrder: createOrder,
      onApprove: onApprove,
      onError: onError
    });

    if (cardButtons.isEligible()) {
      cardButtons.render('#paypal-card-button-container');
    }
  }
});
</script>
@endpush
@endif

</x-app-layout>
