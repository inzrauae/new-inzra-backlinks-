<x-app-layout :seo="$seo" active="tools">

@push('head')
<link rel="stylesheet" href="{{ asset('css/pdf-editor.css') }}">
@endpush

<section class="section" id="pdf-editor">
  <div class="container">
    <x-breadcrumbs :items="$seo->breadcrumbItems" />

    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> 100% free tool</p>
      <h1 class="section__title">Free Online PDF Editor</h1>
      <p class="section__sub">Edit text, add images and signatures, merge and organize pages — free, with no sign-up. Everything happens in your browser, so your PDF is never uploaded anywhere.</p>
      <ul class="tag-list" aria-label="Key facts">
        <li><i class="fa-solid fa-tag" aria-hidden="true"></i> 100% free</li>
        <li><i class="fa-solid fa-lock" aria-hidden="true"></i> No uploads</li>
        <li><i class="fa-solid fa-server" aria-hidden="true"></i> No server processing</li>
        <li><i class="fa-solid fa-user-secret" aria-hidden="true"></i> 100% private</li>
      </ul>
    </header>

    <noscript>
      <div class="conv-noscript glass">
        This tool requires JavaScript to edit PDFs in your browser. Please enable JavaScript and reload the page.
      </div>
    </noscript>

    <div id="pdfApp" hidden>

      <div class="pe-drop" id="peDrop" tabindex="0" role="button" aria-label="Choose or drop a PDF to edit">
        <i class="fa-solid fa-file-pdf pe-drop__icon" aria-hidden="true"></i>
        <p class="pe-drop__title">Drag &amp; drop a PDF here</p>
        <p class="pe-drop__sub">Your file stays on this device — up to 100MB</p>
        <div class="pe-drop__actions">
          <button type="button" class="btn btn--primary ripple" id="peChooseBtn">Choose PDF</button>
        </div>
      </div>

      <p id="peLoadError" class="conv-error" role="alert" hidden></p>

      <div id="peShell" hidden>

        <div class="pe-menubar" id="peMenubar" role="menubar" aria-label="Editor menu"></div>

        <div class="pe-toolbar" id="peToolbar" role="toolbar" aria-label="Editor tools"></div>

        <div class="pe-body">
          <aside class="pe-thumbs" id="peThumbs" aria-label="Pages"></aside>

          <div class="pe-canvas-wrap" id="peCanvasWrap"></div>

          <aside class="pe-panel" id="pePanel" aria-label="Properties">
            <h4>Properties</h4>
            <div id="pePanelBody" class="pe-panel__empty">Select an object to edit its properties.</div>
          </aside>
        </div>

        <div class="pe-status" id="peStatus">
          <span id="peStatusPage">Page 1 of 1</span>
          <span class="pe-status__zoom">
            <button type="button" class="pe-tool" data-action="zoom-out" title="Zoom out" aria-label="Zoom out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
            <select id="peZoomSelect" aria-label="Zoom level"></select>
            <button type="button" class="pe-tool" data-action="zoom-in" title="Zoom in" aria-label="Zoom in"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
          </span>
          <span class="pe-status__spacer"></span>
          <span id="peStatusText"></span>
          <span class="pe-status__spacer"></span>
          <span class="pe-status__save" id="peSaveIndicator"><i class="fa-solid fa-circle-check"></i> <span>Saved locally</span></span>
        </div>
      </div>

      <input type="file" id="peFileInput" accept="application/pdf,.pdf" class="sr-only" aria-hidden="true">
      <input type="file" id="peImportFileInput" accept="application/pdf,.pdf" multiple class="sr-only" aria-hidden="true">
      <input type="file" id="peImageFileInput" accept="image/png,image/jpeg,image/webp" class="sr-only" aria-hidden="true">
      <input type="file" id="peSigImageFileInput" accept="image/png,image/jpeg,image/webp" class="sr-only" aria-hidden="true">

      <div id="peModalRoot"></div>
      <div id="peToast" class="pe-toast" role="status" aria-live="polite"></div>
    </div>

  </div>
</section>

<section class="section section--tint">
  <div class="container container--narrow">
    <div class="pdp__body reveal">
      <h2>What this free tool does</h2>
      <p>The INZRA PDF Editor is a free, browser-based tool for editing PDF files — changing existing text, adding new text and images, drawing, annotating, signing, and organizing pages. Your PDF is opened, edited and exported entirely on your device using JavaScript (PDF.js and pdf-lib); there's no upload step, so the file itself never touches our servers.</p>
    </div>

    <div class="pdp__body reveal">
      <h2>Supported PDF types</h2>
      <p>Native-text PDFs (from Word, Google Docs, Canva, most invoices and contracts) get the most accurate editing, including font detection and matching. Scanned or image-only PDFs are detected automatically — you can still add new text, images, annotations and signatures on them, though editing the existing (image) text isn't available yet. Encrypted PDFs can be opened if you already know the password; adding new password protection on export isn't supported yet.</p>
    </div>

    <div class="pdp__body reveal">
      <h2>How to edit a PDF online</h2>
      <ol style="padding-left:20px; color:var(--text-2); line-height:1.8;">
        <li>Open a PDF by dragging it in or clicking "Choose PDF."</li>
        <li>Click any existing text to edit it, or use the toolbar to add text, images, shapes, annotations or a signature.</li>
        <li>Use the page panel on the left to reorder, rotate, delete, extract or merge pages.</li>
        <li>Click "Download" to generate the edited PDF and save it to your device.</li>
      </ol>
    </div>

    <div class="pdp__body reveal">
      <h2>Is my PDF uploaded?</h2>
      <p>No. Every part of this editor — rendering, text extraction, editing and export — runs locally in your browser. Nothing about your document is sent to our server. You can confirm this yourself by checking your browser's network activity while editing: the only network requests are the one-time downloads of the editor's own libraries, never your file.</p>
    </div>
  </div>
</section>

<section class="section" id="pdf-editor-faq">
  <div class="container container--narrow">
    <header class="section__head reveal">
      <p class="eyebrow"><span class="dot"></span> FAQ</p>
      <h2 class="section__title">Questions about the PDF editor</h2>
    </header>

    @php
      $editorFaqs = collect($seo->jsonLd)->firstWhere('@type', 'FAQPage')['mainEntity'] ?? [];
    @endphp

    <div class="faq">
      @foreach ($editorFaqs as $faq)
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
      <h2 class="section__title">Editing PDFs is one part of getting found</h2>
      <p class="section__sub">If you're polishing documents for a site that also needs to rank, backlinks are usually the next lever. A few current listings from the marketplace:</p>
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
<script type="module" src="{{ asset('js/pdf-editor/main.js') }}"></script>
@endpush

</x-app-layout>
