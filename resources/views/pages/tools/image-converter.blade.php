<x-app-layout :seo="$seo" active="tools">

<section class="section" id="converter">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> 100% free tool</p>
      <h1 class="section__title">Free Online Image Converter</h1>
      <p class="section__sub">Convert JPG, PNG, WebP and AVIF images instantly — free, with no sign-up. Everything happens in your browser, so your images are never uploaded anywhere.</p>
      <ul class="tag-list" aria-label="Key facts">
        <li><i class="fa-solid fa-tag" aria-hidden="true"></i> 100% free</li>
        <li><i class="fa-solid fa-lock" aria-hidden="true"></i> No uploads</li>
        <li><i class="fa-solid fa-server" aria-hidden="true"></i> No server processing</li>
        <li><i class="fa-solid fa-user-secret" aria-hidden="true"></i> 100% private</li>
      </ul>
    </header>

    <noscript>
      <div class="conv-noscript glass">
        This tool requires JavaScript to convert images in your browser. Please enable JavaScript and reload the page.
      </div>
    </noscript>

    <div id="convApp" class="conv-app" hidden>

      <div class="conv-drop" id="convDrop" tabindex="0" role="button" aria-label="Choose or drop images to convert">
        <i class="fa-solid fa-cloud-arrow-up conv-drop__icon" aria-hidden="true"></i>
        <p class="conv-drop__title">Drag &amp; drop your images here</p>
        <p class="conv-drop__sub">JPG, PNG, WebP, GIF, BMP, or AVIF — up to 25MB each</p>
        <div class="conv-drop__actions">
          <button type="button" class="btn btn--primary ripple" id="convChooseBtn">Choose Images</button>
          <button type="button" class="btn btn--glass ripple" id="convPasteBtn" hidden>Paste Image</button>
        </div>
        <input type="file" id="convFileInput" accept="image/*" multiple class="sr-only" aria-hidden="true">
      </div>

      <p id="convError" class="conv-error" role="alert" hidden></p>

      <div class="conv-settings glass" id="convSettings" hidden>
        <div class="conv-settings__row">
          <div class="auth-group">
            <label class="auth-label" for="convFormat">Convert to</label>
            <select id="convFormat" class="auth-input"></select>
          </div>

          <div class="auth-group" id="convQualityGroup">
            <label class="auth-label" for="convQuality">Quality: <span id="convQualityValue">80%</span></label>
            <input type="range" id="convQuality" min="1" max="100" value="80">
          </div>

          <div class="auth-group" id="convBgGroup" hidden>
            <label class="auth-label" for="convBackground">Background (for transparent images)</label>
            <select id="convBackground" class="auth-input">
              <option value="#ffffff">White</option>
              <option value="#000000">Black</option>
              <option value="custom">Custom color…</option>
            </select>
            <input type="color" id="convBackgroundCustom" value="#ffffff" hidden style="margin-top:8px;">
          </div>
        </div>

        <div class="conv-settings__row">
          <div class="auth-group">
            <label class="auth-label" for="convResizeMode">Resize</label>
            <select id="convResizeMode" class="auth-input">
              <option value="original">Original size</option>
              <option value="custom">Custom width/height</option>
              <option value="percent">Percentage</option>
            </select>
          </div>

          <div class="auth-group conv-resize-dims" id="convDimsGroup" hidden>
            <label class="auth-label">Dimensions</label>
            <div style="display:flex; gap:10px; align-items:center;">
              <input type="number" id="convWidth" class="auth-input" placeholder="Width" min="1">
              <span>×</span>
              <input type="number" id="convHeight" class="auth-input" placeholder="Height" min="1">
            </div>
            <label style="display:flex; align-items:center; gap:8px; font-size:.82rem; color:var(--text-2); margin-top:8px;">
              <input type="checkbox" id="convLockRatio" checked> Lock aspect ratio
            </label>
          </div>

          <div class="auth-group conv-resize-dims" id="convPercentGroup" hidden>
            <label class="auth-label" for="convPercent">Resize to <span id="convPercentValue">100%</span> of original</label>
            <input type="range" id="convPercent" min="1" max="100" value="100">
          </div>
        </div>
      </div>

      <div class="conv-actions" id="convActions" hidden>
        <button type="button" class="btn btn--primary btn--lg ripple" id="convConvertAllBtn">Convert All</button>
        <button type="button" class="btn btn--glass ripple" id="convDownloadAllBtn" hidden>Download All (.zip)</button>
        <button type="button" class="btn btn--ghost ripple" id="convClearAllBtn">Clear All</button>
        <span class="conv-progress" id="convProgress" role="status" aria-live="polite"></span>
      </div>

      <div class="conv-grid" id="convGrid"></div>
    </div>

  </div>
</section>

<template id="convCardTemplate">
  <article class="conv-card glass">
    <div class="conv-card__thumb"><img alt=""></div>
    <div class="conv-card__body">
      <p class="conv-card__name"></p>
      <p class="conv-card__meta conv-card__meta--original"></p>
      <p class="conv-card__meta conv-card__meta--result" hidden></p>
      <p class="conv-card__status"></p>
      <div class="conv-card__actions">
        <a class="btn btn--glass btn--sm conv-card__download" hidden download>Download</a>
        <button type="button" class="btn btn--ghost btn--sm conv-card__remove">Remove</button>
      </div>
    </div>
  </article>
</template>

<section class="section section--tint">
  <div class="container container--narrow">
    <div class="pdp__body reveal">
      <h2>What this free tool does</h2>
      <p>The INZRA Image Converter is a free tool that changes an image from one file format to another — for example turning a PNG into a WebP, or a JPG into a PNG — directly on your device. There's no sign-up and no upload step: your browser reads the file, redraws it using the Canvas API, and hands you back a new file in the format you picked, at no cost.</p>
    </div>

    <div class="pdp__body reveal">
      <h2>Supported image formats</h2>
      <p>You can upload JPG, PNG, WebP, GIF, BMP, and AVIF (where your browser can decode it). You can convert to JPG, PNG, or WebP in virtually any modern browser — AVIF is offered as an output format only when your specific browser supports encoding it, which today mainly means recent Chrome and Edge versions. The format dropdown only ever shows options your browser can actually produce.</p>
    </div>

    <div class="pdp__body reveal">
      <h2>Which format should I use?</h2>
      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; margin-top:8px;">
          <thead>
            <tr style="text-align:left; border-bottom:1px solid var(--line);">
              <th style="padding:10px 12px;">Format</th>
              <th style="padding:10px 12px;">Best for</th>
              <th style="padding:10px 12px;">Transparency</th>
              <th style="padding:10px 12px;">Typical file size</th>
            </tr>
          </thead>
          <tbody>
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;"><b>JPG</b></td>
              <td style="padding:10px 12px;">Photos and complex images</td>
              <td style="padding:10px 12px;">No</td>
              <td style="padding:10px 12px;">Small — best for photos</td>
            </tr>
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;"><b>PNG</b></td>
              <td style="padding:10px 12px;">Logos, screenshots, graphics</td>
              <td style="padding:10px 12px;">Yes</td>
              <td style="padding:10px 12px;">Larger — lossless</td>
            </tr>
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:10px 12px;"><b>WebP</b></td>
              <td style="padding:10px 12px;">General web use</td>
              <td style="padding:10px 12px;">Yes</td>
              <td style="padding:10px 12px;">Smaller than JPG/PNG at similar quality</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;"><b>AVIF</b></td>
              <td style="padding:10px 12px;">Modern web, max compression</td>
              <td style="padding:10px 12px;">Yes</td>
              <td style="padding:10px 12px;">Smallest, but slower to encode and less universally supported</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="pdp__body reveal">
      <h2>How to convert an image</h2>
      <ol style="padding-left:20px; color:var(--text-2); line-height:1.8;">
        <li>Upload one or more images by dragging them in or clicking "Choose Images."</li>
        <li>Pick the output format you want from the dropdown.</li>
        <li>Adjust quality or resize dimensions if needed.</li>
        <li>Click "Convert All."</li>
        <li>Download each result individually, or all at once as a ZIP.</li>
      </ol>
    </div>

    <div class="pdp__body reveal">
      <h2>Is my image uploaded?</h2>
      <p>No. Every conversion runs locally in your browser using the Canvas API — your image data never leaves your device or touches our server. That's not a privacy promise layered on top of a server-side tool; it's how this page is actually built, and you can confirm it yourself by checking your browser's network activity while converting.</p>
    </div>
  </div>
</section>

<section class="section" id="converter-faq">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> FAQ</p>
      <h2 class="section__title">Questions about the image converter</h2>
    </header>

    @php
      $converterFaqs = collect($seo->jsonLd)->firstWhere('@type', 'FAQPage')['mainEntity'] ?? [];
    @endphp

    <div class="faq">
      @foreach ($converterFaqs as $faq)
        <div class="faq__item reveal">
          <button class="faq__q" type="button" aria-expanded="false">
            <span>{{ $faq['name'] }}</span>
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
          </button>
          <div class="faq__a"><p>{{ $faq['acceptedAnswer']['text'] }}</p></div>
        </div>
      @endforeach
    </div>
  </div>
</section>

@if ($products->isNotEmpty())
<section class="section section--tint">
  <div class="container">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> Also from INZRA</p>
      <h2 class="section__title">Optimizing images is one part of SEO</h2>
      <p class="section__sub">If you're compressing images for a faster site, backlinks are usually the next lever for rankings. A few current listings from the marketplace:</p>
    </header>

    <div class="listing-grid">
      @foreach ($products as $product)
        @include('partials.products.card', ['product' => $product])
      @endforeach
    </div>

    <div class="section__more reveal">
      <a href="{{ route('marketplace') }}" class="btn btn--glass btn--lg ripple">Browse the full marketplace <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
  </div>
</section>
@endif

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="{{ asset('js/image-converter.js') }}"></script>
@endpush

</x-app-layout>
