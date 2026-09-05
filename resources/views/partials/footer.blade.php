<footer class="footer">
  <div class="container">
    <div class="footer__grid">

      <div class="footer__brand">
        <a class="brand" href="{{ url('/') }}">
          <span class="brand__text">
            <span class="brand__name">INZRA</span>
            <span class="brand__tagline">Powered by Applantics (PVT) LTD</span>
          </span>
        </a>
        <p>A curated marketplace for backlinks that come from sites people actually read. Vetted publishers, verified metrics, replacement guarantee.</p>
        <div class="socials">
          <a href="{{ route('contact') }}" aria-label="INZRA on X"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="{{ route('contact') }}" aria-label="INZRA on LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
          <a href="{{ route('contact') }}" aria-label="INZRA on YouTube"><i class="fa-brands fa-youtube"></i></a>
          <a href="{{ route('contact') }}" aria-label="INZRA on Discord"><i class="fa-brands fa-discord"></i></a>
        </div>
      </div>

      <nav class="footer__col" aria-label="Company">
        <h3>Company</h3>
        <a href="{{ url('/#why') }}">About us</a>
        <a href="{{ url('/#process') }}">How it works</a>
        <a href="{{ url('/#reviews') }}">Customer reviews</a>
        <a href="{{ route('contact') }}">Careers</a>
        <a href="{{ route('contact') }}">Become a publisher</a>
      </nav>

      <nav class="footer__col" aria-label="Marketplace">
        <h3>Marketplace</h3>
        <a href="{{ route('categories') }}">Guest posts</a>
        <a href="{{ route('categories') }}">Niche edits</a>
        <a href="{{ route('categories') }}">EDU &amp; GOV links</a>
        <a href="{{ route('categories') }}">Local citations</a>
        <a href="{{ route('pricing') }}">Monthly retainers</a>
        <a href="{{ route('markets.index') }}">Backlinks by market</a>
      </nav>

      <nav class="footer__col" aria-label="Support">
        <h3>Support</h3>
        <a href="{{ route('contact') }}#faq">Help centre</a>
        <a href="{{ route('contact') }}">Contact support</a>
        <a href="{{ route('contact') }}#faq">Order status</a>
        <a href="{{ route('contact') }}#faq">Refund policy</a>
        <a href="{{ route('contact') }}#faq">Report a dropped link</a>
      </nav>

      <nav class="footer__col" aria-label="Resources">
        <h3>Resources</h3>
        <a href="{{ route('blog.index') }}">Blog</a>
        <a href="{{ route('blog.index') }}">Link building guide</a>
        <a href="{{ route('blog.index') }}">Anchor text calculator</a>
        <a href="{{ route('blog.index') }}">DA vs DR explained</a>
        <a href="{{ route('contact') }}">API docs</a>
      </nav>

    </div>

    <div class="footer__bottom">
      <p>&copy; {{ now()->year }} INZRA Ltd. All rights reserved.</p>
      <nav class="footer__legal" aria-label="Legal">
        <a href="{{ route('contact') }}">Privacy</a>
        <a href="{{ route('contact') }}">Terms</a>
        <a href="{{ route('contact') }}">Cookies</a>
      </nav>
    </div>
  </div>
</footer>

<button class="to-top" id="toTop" type="button" aria-label="Back to top">
  <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
</button>
