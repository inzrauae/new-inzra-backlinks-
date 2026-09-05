import { uid } from './utils/id.js';
import { $, $$, el, clear } from './utils/dom.js';
import { downloadBlob } from './utils/download.js';
import { cssRgbToHex } from './utils/color.js';
import { History } from './core/history.js';
import { PdfProject, Page, makeTextObject, makeObject } from './core/document-model.js';
import { openPdf, readFileAsArrayBuffer, PdfEditorError } from './pdf/loader.js';
import { analyzePage } from './pdf/text-extractor.js';
import { matchFont, standardFontFor } from './text/font-matcher.js';
import { exportProject } from './pdf/exporter.js';
import { ViewportState, ZOOM_PRESETS } from './rendering/viewport.js';
import { screenPointToPdf } from './rendering/overlay-layer.js';
import { PageManager } from './pages/page-manager.js';
import { ObjectController } from './editor/object-controller.js';
import { saveProjectLocally, loadSavedProject } from './storage/local-project.js';
import { findMatches, replaceInText } from './search/search.js';
import { detectFormFields } from './forms/form-fields.js';
import { clamp } from './utils/geometry.js';

const app = $('#pdfApp');
if (app) {
  const state = {
    project: null,
    pdfjsDocs: new Map(),
    history: new History(() => afterHistoryChange()),
    viewport: new ViewportState(),
    activeTool: 'select',
    currentPageIndex: 0,
    currentPageId: null,
    flattenForms: false,
  };
  let pendingImageTarget = null;
  let pendingSignatureTarget = null;
  const pdfjsPageCache = new Map();

  const controller = new ObjectController({
    history: state.history,
    onSelect: () => renderProperties(),
    onChange: (pageId) => { pageManager.refreshPage(pageId); scheduleAutosave(); },
  });

  const pageManager = new PageManager({
    canvasWrap: $('#peCanvasWrap'),
    thumbsEl: $('#peThumbs'),
    getPdfjsPage,
    controller,
    onActivePageChange: handleActivePageChange,
    onTextDoubleClick: (pageId, obj, node) => enterTextEditMode(pageId, obj, node),
    onReorder: (fromIndex, toIndex) => {
      const before = state.project.pages;
      const after = before.slice();
      const [moved] = after.splice(fromIndex, 1);
      after.splice(toIndex, 0, moved);
      state.history.push({
        do: () => { state.project.pages = after; pageManager.remount(); },
        undo: () => { state.project.pages = before; pageManager.remount(); },
      });
    },
    onOverlayPointerDown,
  });

  /* ==========================================================
     FILE LOADING
     ========================================================== */
  async function getPdfjsPage(modelPage) {
    const key = `${modelPage.source.docKey}:${modelPage.source.pageIndex}`;
    if (!pdfjsPageCache.has(key)) {
      const doc = state.pdfjsDocs.get(modelPage.source.docKey);
      pdfjsPageCache.set(key, doc.getPage(modelPage.source.pageIndex + 1));
    }
    return pdfjsPageCache.get(key);
  }

  function buildTextObjectFromRun(run) {
    const fm = matchFont(run);
    return makeTextObject({
      kind: 'existing',
      text: run.text,
      originalText: run.text,
      x: run.x, y: run.y, width: run.width, height: run.height,
      fontSize: run.fontSize,
      bold: run.bold,
      italic: run.italic,
      color: cssRgbToHex(run.color),
      fontFamily: fm.matchedFamily,
      fontMatch: fm,
    });
  }

  async function openFile(file) {
    $('#peLoadError').hidden = true;
    try {
      showToast('Opening PDF…');
      const buffer = await readFileAsArrayBuffer(file);
      let wasEncrypted = false;
      const pdfjsDoc = await openPdf(buffer, {
        onPasswordNeeded: async (isRetry) => { wasEncrypted = true; return promptPassword(isRetry); },
      });

      const project = new PdfProject();
      project.fileName = file.name.replace(/\.pdf$/i, '') || 'document';
      project.wasEncrypted = wasEncrypted;
      project.sources.set('main', { name: file.name, bytes: buffer });
      state.pdfjsDocs.clear();
      pdfjsPageCache.clear();
      state.pdfjsDocs.set('main', pdfjsDoc);

      for (let i = 0; i < pdfjsDoc.numPages; i++) {
        const pdfjsPage = await pdfjsDoc.getPage(i + 1);
        const [x0, y0, x1, y1] = pdfjsPage.view;
        const page = new Page({ source: { docKey: 'main', pageIndex: i }, width: x1 - x0, height: y1 - y0, rotation: 0 });
        const analysis = await analyzePage(pdfjsPage);
        page.isScanned = analysis.isScanned;
        page.textObjects = analysis.textRuns.map(buildTextObjectFromRun);
        page.formFields = await detectFormFields(pdfjsPage);
        project.pages.push(page);
      }

      setProject(project);
      showToast(`Opened ${file.name} — ${project.pages.length} page${project.pages.length === 1 ? '' : 's'}`);
    } catch (err) {
      reportLoadError(err);
    }
  }

  function setProject(project) {
    state.project = project;
    state.currentPageIndex = 0;
    controller.deselect();
    $('#peDrop').hidden = true;
    $('#peShell').hidden = false;
    pageManager.mount(project);
    state.history.clear();
    buildZoomSelect();
    updateStatusBar();
  }

  async function rehydrateProject(saved) {
    state.pdfjsDocs.clear();
    pdfjsPageCache.clear();
    for (const [docKey, source] of saved.sources) {
      const doc = await openPdf(source.bytes.slice(0), { onPasswordNeeded: (r) => promptPassword(r) });
      state.pdfjsDocs.set(docKey, doc);
    }
    saved.pages.forEach((page) => {
      (page.objects || []).forEach((obj) => {
        if ((obj.type === 'image' || obj.type === 'signature') && obj.bytes && obj.mode !== 'typed') {
          obj.previewUrl = URL.createObjectURL(new Blob([obj.bytes.buffer ? obj.bytes : new Uint8Array(obj.bytes)], { type: obj.mimeType || 'image/png' }));
        }
      });
    });
    if (!(saved.formValues instanceof Map)) saved.formValues = new Map();
    state.project = saved;
    controller.deselect();
    $('#peDrop').hidden = true;
    $('#peShell').hidden = false;
    await pageManager.mount(saved);
    state.history.clear();
    buildZoomSelect();
    updateStatusBar();
  }

  async function tryRestoreSession(forceOpen) {
    try {
      const saved = await loadSavedProject();
      if (!saved) { if (forceOpen) showToast('No saved session found'); return; }
      if (!forceOpen) {
        const ok = await confirmModal('Restore your last session?', `A previous project was saved locally in this browser: ${saved.fileName || 'document'}.pdf`);
        if (!ok) return;
      }
      await rehydrateProject(saved);
      showToast('Session restored');
    } catch (err) {
      console.warn('Restore failed', err);
      if (forceOpen) showToast('Could not restore a saved session', { error: true });
    }
  }

  /* ==========================================================
     MENU + TOOLBAR
     ========================================================== */
  const MENUS = [
    { label: 'File', items: [
      { label: 'Open PDF…', action: 'open-file' },
      { label: 'Import PDF to Merge…', action: 'import-file' },
      { sep: true },
      { label: 'Save Locally', action: 'save-local', shortcut: 'Ctrl+S' },
      { label: 'Restore Last Session', action: 'restore-session' },
      { sep: true },
      { label: 'Download PDF', action: 'download' },
    ] },
    { label: 'Edit', items: [
      { label: 'Undo', action: 'undo', shortcut: 'Ctrl+Z' },
      { label: 'Redo', action: 'redo', shortcut: 'Ctrl+Y' },
      { sep: true },
      { label: 'Find & Replace', action: 'open-search', shortcut: 'Ctrl+F' },
      { label: 'Delete Selected', action: 'delete-selected', shortcut: 'Del' },
    ] },
    { label: 'View', items: [
      { label: 'Zoom In', action: 'zoom-in', shortcut: '+' },
      { label: 'Zoom Out', action: 'zoom-out', shortcut: '-' },
      { label: 'Fit Width', action: 'fit-width' },
      { label: 'Fit Page', action: 'fit-page' },
      { label: 'Actual Size (100%)', action: 'zoom-100' },
    ] },
    { label: 'Insert', items: [
      { label: 'Text', action: 'tool-text' },
      { label: 'Image', action: 'tool-image' },
      { label: 'Signature', action: 'tool-signature' },
      { sep: true },
      { label: 'Blank Page', action: 'insert-blank-page' },
      { label: 'Watermark…', action: 'open-watermark' },
      { label: 'Header/Footer…', action: 'open-header-footer' },
    ] },
    { label: 'Annotate', items: [
      { label: 'Highlight', action: 'tool-highlight' },
      { label: 'Underline', action: 'tool-underline' },
      { label: 'Strikethrough', action: 'tool-strikethrough' },
      { label: 'Sticky Note', action: 'tool-note' },
      { label: 'Draw', action: 'tool-draw' },
      { sep: true },
      { label: 'Whiteout / Cover', action: 'tool-whiteout' },
      { label: 'Redact (removes content)', action: 'tool-redact' },
    ] },
    { label: 'Pages', items: [
      { label: 'Rotate Left', action: 'rotate-left' },
      { label: 'Rotate Right', action: 'rotate-right' },
      { label: 'Duplicate Page', action: 'duplicate-page' },
      { label: 'Delete Page', action: 'delete-page' },
      { label: 'Crop Page', action: 'tool-crop' },
      { label: 'Clear Crop', action: 'clear-crop' },
      { sep: true },
      { label: 'Extract Current Page…', action: 'extract-page' },
      { label: 'Merge PDF…', action: 'import-file' },
    ] },
    { label: 'Tools', items: [
      { label: 'Fill Form Fields', action: 'open-form-panel' },
      { label: 'Edit Metadata…', action: 'open-metadata' },
    ] },
    { label: 'Help', items: [
      { label: 'Privacy: how this editor works', action: 'show-privacy' },
    ] },
  ];

  const TOOLBAR = [
    { tool: 'select', icon: 'fa-arrow-pointer', label: 'Select' },
    { tool: 'text', icon: 'fa-font', label: 'Text' },
    { tool: 'image', icon: 'fa-image', label: 'Image' },
    { tool: 'draw', icon: 'fa-pen', label: 'Draw' },
    { tool: 'highlight', icon: 'fa-highlighter', label: 'Highlight' },
    { tool: 'signature', icon: 'fa-signature', label: 'Sign' },
    { tool: 'shape:rect', icon: 'fa-shapes', label: 'Shape' },
    { tool: 'redact', icon: 'fa-eraser', label: 'Redact' },
    { action: 'open-search', icon: 'fa-magnifying-glass', label: 'Search' },
  ];

  function buildMenubar() {
    const bar = $('#peMenubar');
    clear(bar);
    MENUS.forEach((menu) => {
      const wrap = el('div', { class: 'pe-menu' });
      const btn = el('button', { type: 'button', class: 'pe-menu__btn' }, menu.label);
      const list = el('div', { class: 'pe-menu__list' });
      menu.items.forEach((item) => {
        if (item.sep) { list.appendChild(el('div', { class: 'pe-menu__sep' })); return; }
        const mi = el('button', { type: 'button', class: 'pe-menu__item' }, [
          el('span', {}, item.label),
          item.shortcut ? el('kbd', {}, item.shortcut) : null,
        ]);
        mi.addEventListener('click', () => { closeAllMenus(); dispatch(item.action); });
        list.appendChild(mi);
      });
      wrap.append(btn, list);
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const wasOpen = wrap.classList.contains('is-open');
        closeAllMenus();
        if (!wasOpen) wrap.classList.add('is-open');
      });
      bar.appendChild(wrap);
    });
    bar.appendChild(el('div', { class: 'pe-menubar__spacer' }));
    bar.appendChild(el('div', { class: 'pe-menubar__brand' }, 'Processed locally — never uploaded'));
    document.addEventListener('click', closeAllMenus);
  }
  function closeAllMenus() { $$('.pe-menu.is-open').forEach((m) => m.classList.remove('is-open')); }

  function buildToolbar() {
    const bar = $('#peToolbar');
    clear(bar);
    TOOLBAR.forEach((item) => {
      const btn = el('button', { type: 'button', class: 'pe-tool', dataset: { tool: item.tool || '' }, title: item.label }, [
        el('i', { class: `fa-solid ${item.icon}` }),
        el('span', {}, item.label),
      ]);
      btn.addEventListener('click', () => { if (item.action) dispatch(item.action); else setActiveTool(item.tool); });
      bar.appendChild(btn);
    });
    bar.appendChild(el('div', { class: 'pe-toolbar__spacer' }));
    const undoBtn = el('button', { type: 'button', class: 'pe-tool', id: 'peUndoBtn', title: 'Undo' }, el('i', { class: 'fa-solid fa-rotate-left' }));
    const redoBtn = el('button', { type: 'button', class: 'pe-tool', id: 'peRedoBtn', title: 'Redo' }, el('i', { class: 'fa-solid fa-rotate-right' }));
    undoBtn.addEventListener('click', () => dispatch('undo'));
    redoBtn.addEventListener('click', () => dispatch('redo'));
    const downloadBtn = el('button', { type: 'button', class: 'btn btn--primary btn--sm', style: 'margin-left:8px;' }, 'Download');
    downloadBtn.addEventListener('click', () => dispatch('download'));
    bar.append(undoBtn, redoBtn, downloadBtn);
  }

  function setActiveTool(tool) {
    state.activeTool = tool;
    $$('.pe-tool[data-tool]').forEach((b) => b.classList.toggle('is-active', b.dataset.tool === tool));
  }

  async function dispatch(action) {
    if (!state.project && !['open-file', 'restore-session'].includes(action)) return;
    switch (action) {
      case 'open-file': $('#peFileInput').click(); break;
      case 'import-file': $('#peImportFileInput').click(); break;
      case 'save-local': await persistNow(); showToast('Saved locally'); break;
      case 'restore-session': await tryRestoreSession(true); break;
      case 'download': await handleDownload(); break;
      case 'undo': state.history.undo(); break;
      case 'redo': state.history.redo(); break;
      case 'open-search': openSearchPanel(); break;
      case 'delete-selected': controller.deleteSelected(); break;
      case 'zoom-in': setZoom(stepZoom(1)); break;
      case 'zoom-out': setZoom(stepZoom(-1)); break;
      case 'fit-width': state.viewport.setFitWidth(); applyZoomToAllPages(); break;
      case 'fit-page': state.viewport.setFitPage(); applyZoomToAllPages(); break;
      case 'zoom-100': setZoom(1); break;
      case 'insert-blank-page': insertBlankPageAction(); break;
      case 'open-watermark': openWatermarkModal(); break;
      case 'open-header-footer': openHeaderFooterModal(); break;
      case 'rotate-left': rotateCurrentPage(-90); break;
      case 'rotate-right': rotateCurrentPage(90); break;
      case 'duplicate-page': duplicateCurrentPage(); break;
      case 'delete-page': deleteCurrentPage(); break;
      case 'extract-page': extractCurrentPage(); break;
      case 'clear-crop': clearCropOnCurrentPage(); break;
      case 'open-form-panel': openFormPanel(); break;
      case 'open-metadata': openMetadataModal(); break;
      case 'show-privacy': showToast('Your PDF is processed entirely in this browser tab and is never uploaded.'); break;
      default:
        if (action.startsWith('tool-')) setActiveTool(action.slice(5));
        break;
    }
  }

  /* ==========================================================
     ZOOM
     ========================================================== */
  function stepZoom(dir) {
    const idx = ZOOM_PRESETS.findIndex((z) => Math.abs(z - state.viewport.scale) < 0.001);
    const newIdx = clamp((idx === -1 ? 3 : idx) + dir, 0, ZOOM_PRESETS.length - 1);
    return ZOOM_PRESETS[newIdx];
  }
  function setZoom(scale) { state.viewport.setScale(scale); applyZoomToAllPages(); }
  async function applyZoomToAllPages() {
    const firstPage = state.project?.pages[0];
    const resolved = state.viewport.mode === 'value'
      ? state.viewport.scale
      : state.viewport.resolveScale(firstPage?.width || 612, firstPage?.height || 792, $('#peCanvasWrap').clientWidth, $('#peCanvasWrap').clientHeight);
    pageManager.scale = resolved;
    await pageManager.rerenderAll();
    updateZoomSelect();
  }
  function buildZoomSelect() {
    const sel = $('#peZoomSelect');
    clear(sel);
    ZOOM_PRESETS.forEach((z) => sel.appendChild(el('option', { value: z }, `${Math.round(z * 100)}%`)));
    sel.appendChild(el('option', { value: 'fit-width' }, 'Fit Width'));
    sel.appendChild(el('option', { value: 'fit-page' }, 'Fit Page'));
    sel.value = '1';
    sel.onchange = () => {
      if (sel.value === 'fit-width') { state.viewport.setFitWidth(); applyZoomToAllPages(); }
      else if (sel.value === 'fit-page') { state.viewport.setFitPage(); applyZoomToAllPages(); }
      else setZoom(Number(sel.value));
    };
  }
  function updateZoomSelect() {
    const sel = $('#peZoomSelect');
    if (!sel) return;
    if (state.viewport.mode === 'fit-width') sel.value = 'fit-width';
    else if (state.viewport.mode === 'fit-page') sel.value = 'fit-page';
    else sel.value = String(ZOOM_PRESETS.reduce((a, b) => (Math.abs(b - state.viewport.scale) < Math.abs(a - state.viewport.scale) ? b : a)));
  }

  /* ==========================================================
     TOOL GESTURES (click-to-place / drag-to-rect / freehand)
     ========================================================== */
  function getPage(pageId) { return state.project.pages.find((p) => p.id === pageId); }
  function currentPage() { return state.project.pages[state.currentPageIndex]; }

  function pushAdd(page, collection, obj) {
    state.history.push({
      do: () => page[collection].push(obj),
      undo: () => { const i = page[collection].indexOf(obj); if (i !== -1) page[collection].splice(i, 1); },
    });
  }

  function onOverlayPointerDown(pageId, evt, getViewport, overlay) {
    const tool = state.activeTool;
    if (tool === 'select') { controller.deselect(); return; }
    if (tool === 'draw') { startFreehandDraw(pageId, evt, getViewport, overlay); return; }

    const [x, y] = screenPointToPdf(getViewport(), evt.offsetX, evt.offsetY);

    if (tool === 'text') { addTextAt(pageId, x, y); setActiveTool('select'); return; }
    if (tool === 'image') { pendingImageTarget = { pageId, x, y }; $('#peImageFileInput').click(); return; }
    if (tool === 'signature') { pendingSignatureTarget = { pageId, x, y }; openSignatureModal(); return; }
    if (tool === 'note') {
      const page = getPage(pageId);
      const obj = makeObject('annotation', { x, y: y - 60, width: 140, height: 70, annotationKind: 'note', text: '' });
      pushAdd(page, 'objects', obj);
      controller.select(pageId, obj, null);
      setActiveTool('select');
      return;
    }
    startRectDrag(pageId, evt, getViewport, overlay, tool);
  }

  function startRectDrag(pageId, evt, getViewport, overlay, tool) {
    const startX = evt.offsetX;
    const startY = evt.offsetY;
    const preview = el('div', { style: 'position:absolute;border:1.5px dashed #2563EB;background:rgba(37,99,235,.08);pointer-events:none;' });
    overlay.appendChild(preview);
    overlay.setPointerCapture(evt.pointerId);
    const update = (x1, y1, x2, y2) => {
      preview.style.left = `${Math.min(x1, x2)}px`;
      preview.style.top = `${Math.min(y1, y2)}px`;
      preview.style.width = `${Math.abs(x2 - x1)}px`;
      preview.style.height = `${Math.abs(y2 - y1)}px`;
    };
    update(startX, startY, startX, startY);
    const onMove = (e) => update(startX, startY, e.offsetX, e.offsetY);
    const onUp = (e) => {
      overlay.removeEventListener('pointermove', onMove);
      overlay.removeEventListener('pointerup', onUp);
      overlay.removeChild(preview);
      const viewport = getViewport();
      const [px1, py1] = screenPointToPdf(viewport, startX, startY);
      const [px2, py2] = screenPointToPdf(viewport, e.offsetX, e.offsetY);
      const rect = { x: Math.min(px1, px2), y: Math.min(py1, py2), width: Math.max(4, Math.abs(px2 - px1)), height: Math.max(4, Math.abs(py2 - py1)) };
      finishRectTool(tool, pageId, rect);
    };
    overlay.addEventListener('pointermove', onMove);
    overlay.addEventListener('pointerup', onUp);
  }

  function finishRectTool(tool, pageId, rect) {
    const page = getPage(pageId);
    if (tool === 'crop') {
      const before = page.cropRect;
      const after = { ...rect };
      state.history.push({ do: () => { page.cropRect = after; }, undo: () => { page.cropRect = before; } });
      setActiveTool('select');
      return;
    }
    let obj;
    if (tool.startsWith('shape:')) {
      obj = makeObject('shape', { ...rect, shapeKind: tool.split(':')[1], strokeColor: '#0F172A', strokeWidth: 2, fillColor: null });
    } else if (tool === 'highlight' || tool === 'underline' || tool === 'strikethrough') {
      obj = makeObject('annotation', { ...rect, annotationKind: tool, color: tool === 'highlight' ? '#FDE68A' : '#DC2626' });
    } else if (tool === 'redact') {
      obj = makeObject('redaction', { ...rect });
    } else if (tool === 'whiteout') {
      obj = makeObject('whiteout', { ...rect });
    } else {
      return;
    }
    pushAdd(page, 'objects', obj);
    controller.select(pageId, obj, null);
    setActiveTool('select');
  }

  function startFreehandDraw(pageId, evt, getViewport, overlay) {
    overlay.setPointerCapture(evt.pointerId);
    const screenPoints = [{ x: evt.offsetX, y: evt.offsetY }];
    const ns = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(ns, 'svg');
    svg.style.position = 'absolute'; svg.style.inset = '0'; svg.style.pointerEvents = 'none'; svg.style.overflow = 'visible';
    const poly = document.createElementNS(ns, 'polyline');
    poly.setAttribute('fill', 'none');
    poly.setAttribute('stroke', '#0F172A');
    poly.setAttribute('stroke-width', '2.5');
    poly.setAttribute('stroke-linecap', 'round');
    poly.setAttribute('stroke-linejoin', 'round');
    svg.appendChild(poly);
    overlay.appendChild(svg);
    const update = () => poly.setAttribute('points', screenPoints.map((p) => `${p.x},${p.y}`).join(' '));
    update();
    const onMove = (e) => { screenPoints.push({ x: e.offsetX, y: e.offsetY }); update(); };
    const onUp = () => {
      overlay.removeEventListener('pointermove', onMove);
      overlay.removeEventListener('pointerup', onUp);
      overlay.removeChild(svg);
      if (screenPoints.length < 2) { setActiveTool('select'); return; }
      const viewport = getViewport();
      const pdfPoints = screenPoints.map((p) => { const [x, y] = screenPointToPdf(viewport, p.x, p.y); return { x, y }; });
      const xs = pdfPoints.map((p) => p.x);
      const ys = pdfPoints.map((p) => p.y);
      const x = Math.min(...xs);
      const y = Math.min(...ys);
      const page = getPage(pageId);
      const obj = makeObject('drawing', { x, y, width: Math.max(1, Math.max(...xs) - x), height: Math.max(1, Math.max(...ys) - y), points: pdfPoints, strokeColor: '#0F172A', strokeWidth: 2.5, locked: true });
      pushAdd(page, 'objects', obj);
      setActiveTool('select');
    };
    overlay.addEventListener('pointermove', onMove);
    overlay.addEventListener('pointerup', onUp);
  }

  function addTextAt(pageId, x, y) {
    const page = getPage(pageId);
    const obj = makeTextObject({ kind: 'new', text: 'New text', x, y: y - 8, width: 160, height: 20, fontSize: 14, fontFamily: 'Helvetica', color: '#0F172A' });
    pushAdd(page, 'textObjects', obj);
    controller.select(pageId, obj, null);
    requestAnimationFrame(() => {
      const node = pageManager.findObjectNode(pageId, obj.id);
      if (node) enterTextEditMode(pageId, obj, node);
    });
  }

  $('#peImageFileInput').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    e.target.value = '';
    if (!file || !pendingImageTarget) return;
    const buf = await readFileAsArrayBuffer(file);
    const bytes = new Uint8Array(buf);
    const mimeType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
    const previewUrl = URL.createObjectURL(new Blob([bytes], { type: file.type }));
    const dims = await getImageDimensions(previewUrl);
    const { pageId, x, y } = pendingImageTarget;
    pendingImageTarget = null;
    const width = 180;
    const height = 180 * ((dims.height || 1) / (dims.width || 1));
    const page = getPage(pageId);
    const obj = makeObject('image', { x: x - width / 2, y: y - height / 2, width, height, bytes, mimeType, previewUrl });
    pushAdd(page, 'objects', obj);
    controller.select(pageId, obj, null);
    setActiveTool('select');
  });

  function getImageDimensions(url) {
    return new Promise((resolve) => {
      const img = new Image();
      img.onload = () => resolve({ width: img.naturalWidth, height: img.naturalHeight });
      img.onerror = () => resolve({ width: 200, height: 200 });
      img.src = url;
    });
  }

  /* ==========================================================
     TEXT EDIT MODE
     ========================================================== */
  function enterTextEditMode(pageId, obj, node) {
    node.contentEditable = 'true';
    node.focus();
    placeCaretAtEnd(node);
    const before = obj.text;
    const onKeydown = (e) => {
      if (e.key === 'Escape') { node.textContent = before; node.blur(); }
      e.stopPropagation();
    };
    const commit = () => {
      node.contentEditable = 'false';
      node.removeEventListener('keydown', onKeydown);
      const after = node.textContent;
      if (after === before) return;
      const wasEdited = obj.edited;
      state.history.push({
        do: () => { obj.text = after; obj.edited = true; },
        undo: () => { obj.text = before; obj.edited = wasEdited; },
      });
    };
    node.addEventListener('blur', commit, { once: true });
    node.addEventListener('keydown', onKeydown);
  }
  function placeCaretAtEnd(node) {
    const range = document.createRange();
    range.selectNodeContents(node);
    range.collapse(false);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
  }

  /* ==========================================================
     PROPERTIES PANEL
     ========================================================== */
  function mutateObj(pageId, obj, patch) {
    const before = {};
    Object.keys(patch).forEach((k) => { before[k] = obj[k]; });
    const wasEdited = obj.edited;
    const markEdited = obj.kind === 'existing';
    state.history.push({
      do: () => { Object.assign(obj, patch); if (markEdited) obj.edited = true; },
      undo: () => { Object.assign(obj, before); if (markEdited) obj.edited = wasEdited; },
    });
  }

  function renderProperties() {
    const body = $('#pePanelBody');
    clear(body);
    const sel = controller.selected;
    if (!sel) { body.className = 'pe-panel__empty'; body.textContent = 'Select an object to edit its properties.'; return; }
    body.className = '';
    const { obj, pageId } = sel;
    if (obj.fontSize !== undefined && obj.align !== undefined) renderTextProperties(body, pageId, obj);
    else if (obj.type === 'image' || obj.type === 'signature') renderImageProperties(body, pageId, obj);
    else if (obj.type === 'shape') renderShapeProperties(body, pageId, obj);
    else if (obj.type === 'drawing') renderDrawingProperties(body, pageId, obj);
    else if (obj.type === 'annotation') renderAnnotationProperties(body, pageId, obj);
    else if (obj.type === 'redaction') {
      body.appendChild(el('p', {}, 'This area is permanently removed from the exported PDF where technically possible. If the page is too complex to safely edit, that page is flattened to an image instead so removal is still guaranteed.'));
      appendDeleteButton(body, pageId, obj);
    } else if (obj.type === 'whiteout') {
      body.appendChild(el('p', {}, "Whiteout only visually covers content — it isn't secure removal. Use Redact if the underlying content needs to be gone."));
      appendDeleteButton(body, pageId, obj);
    }
  }

  function appendDeleteButton(body, pageId, obj) {
    const btn = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Delete');
    btn.addEventListener('click', () => { controller.select(pageId, obj, pageManager.findObjectNode(pageId, obj.id)); controller.deleteSelected(); });
    body.appendChild(el('div', { class: 'pe-btn-row' }, [btn]));
  }

  function toggleButton(label, active) {
    return el('button', { type: 'button', class: `pe-tool${active ? ' is-active' : ''}` }, label);
  }

  function renderTextProperties(body, pageId, obj) {
    body.appendChild(el('span', { class: 'pe-badge pe-badge--approx' }, `Font: ${obj.fontMatch?.matchedFamily || obj.fontFamily} (closest match)`));

    const textArea = el('textarea', { class: 'auth-input', rows: '3' });
    textArea.value = obj.text;
    textArea.addEventListener('change', () => mutateObj(pageId, obj, { text: textArea.value }));
    body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Text'), textArea]));

    const sizeInput = el('input', { type: 'number', class: 'auth-input', value: obj.fontSize, min: '4', max: '200' });
    sizeInput.addEventListener('change', () => mutateObj(pageId, obj, { fontSize: Number(sizeInput.value) }));
    const colorInput = el('input', { type: 'color', value: obj.color });
    colorInput.addEventListener('change', () => mutateObj(pageId, obj, { color: colorInput.value }));
    body.appendChild(el('div', { class: 'pe-field-row' }, [
      el('div', { class: 'pe-field' }, [el('label', {}, 'Size'), sizeInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Color'), colorInput]),
    ]));

    const boldBtn = toggleButton('B', obj.bold);
    boldBtn.addEventListener('click', () => { const v = !obj.bold; mutateObj(pageId, obj, { bold: v, fontMatch: { ...obj.fontMatch, matchedFamily: standardFontFor(obj.fontMatch?.matchedFamily, v, obj.italic) } }); });
    const italicBtn = toggleButton('I', obj.italic);
    italicBtn.addEventListener('click', () => { const v = !obj.italic; mutateObj(pageId, obj, { italic: v, fontMatch: { ...obj.fontMatch, matchedFamily: standardFontFor(obj.fontMatch?.matchedFamily, obj.bold, v) } }); });
    body.appendChild(el('div', { class: 'pe-btn-row' }, [boldBtn, italicBtn]));

    const alignRow = el('div', { class: 'pe-btn-row' }, ['left', 'center', 'right'].map((a) => {
      const b = el('button', { type: 'button', class: `pe-tool${obj.align === a ? ' is-active' : ''}` }, a);
      b.addEventListener('click', () => mutateObj(pageId, obj, { align: a }));
      return b;
    }));
    body.appendChild(alignRow);

    const opacityInput = el('input', { type: 'range', min: '10', max: '100', value: Math.round((obj.opacity ?? 1) * 100) });
    opacityInput.addEventListener('change', () => mutateObj(pageId, obj, { opacity: Number(opacityInput.value) / 100 }));
    body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Opacity'), opacityInput]));

    const rotInput = el('input', { type: 'number', class: 'auth-input', value: obj.rotation || 0 });
    rotInput.addEventListener('change', () => mutateObj(pageId, obj, { rotation: Number(rotInput.value) }));
    body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Rotation (deg)'), rotInput]));

    appendDeleteButton(body, pageId, obj);
  }

  function renderImageProperties(body, pageId, obj) {
    const opacityInput = el('input', { type: 'range', min: '10', max: '100', value: Math.round((obj.opacity ?? 1) * 100) });
    opacityInput.addEventListener('change', () => mutateObj(pageId, obj, { opacity: Number(opacityInput.value) / 100 }));
    body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Opacity'), opacityInput]));
    const rotInput = el('input', { type: 'number', class: 'auth-input', value: obj.rotation || 0 });
    rotInput.addEventListener('change', () => mutateObj(pageId, obj, { rotation: Number(rotInput.value) }));
    body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Rotation (deg)'), rotInput]));
    appendDeleteButton(body, pageId, obj);
  }

  function renderShapeProperties(body, pageId, obj) {
    const strokeInput = el('input', { type: 'color', value: obj.strokeColor || '#0F172A' });
    strokeInput.addEventListener('change', () => mutateObj(pageId, obj, { strokeColor: strokeInput.value }));
    const fillToggle = el('input', { type: 'checkbox' });
    fillToggle.checked = !!obj.fillColor;
    const fillInput = el('input', { type: 'color', value: obj.fillColor || '#93c5fd' });
    fillInput.addEventListener('change', () => { if (fillToggle.checked) mutateObj(pageId, obj, { fillColor: fillInput.value }); });
    fillToggle.addEventListener('change', () => mutateObj(pageId, obj, { fillColor: fillToggle.checked ? fillInput.value : null }));
    const widthInput = el('input', { type: 'number', class: 'auth-input', value: obj.strokeWidth || 1.5, min: '0', step: '0.5' });
    widthInput.addEventListener('change', () => mutateObj(pageId, obj, { strokeWidth: Number(widthInput.value) }));
    body.append(
      el('div', { class: 'pe-field' }, [el('label', {}, 'Stroke color'), strokeInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, [fillToggle, ' Fill']), fillInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Stroke width'), widthInput]),
    );
    appendDeleteButton(body, pageId, obj);
  }

  function renderDrawingProperties(body, pageId, obj) {
    const strokeInput = el('input', { type: 'color', value: obj.strokeColor || '#0F172A' });
    strokeInput.addEventListener('change', () => mutateObj(pageId, obj, { strokeColor: strokeInput.value }));
    body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Color'), strokeInput]));
    appendDeleteButton(body, pageId, obj);
  }

  function renderAnnotationProperties(body, pageId, obj) {
    if (obj.annotationKind === 'note') {
      const textArea = el('textarea', { class: 'auth-input', rows: '4' });
      textArea.value = obj.text || '';
      textArea.addEventListener('change', () => mutateObj(pageId, obj, { text: textArea.value }));
      body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Note'), textArea]));
    } else {
      const colorInput = el('input', { type: 'color', value: obj.color || '#FDE68A' });
      colorInput.addEventListener('change', () => mutateObj(pageId, obj, { color: colorInput.value }));
      body.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Color'), colorInput]));
    }
    appendDeleteButton(body, pageId, obj);
  }

  /* ==========================================================
     SIGNATURE
     ========================================================== */
  function wireSignaturePad(canvas) {
    const ctx = canvas.getContext('2d');
    ctx.lineWidth = 2.4; ctx.lineCap = 'round'; ctx.strokeStyle = '#111827';
    let drawing = false;
    canvas.addEventListener('pointerdown', (e) => { drawing = true; ctx.beginPath(); ctx.moveTo(e.offsetX, e.offsetY); canvas.setPointerCapture(e.pointerId); });
    canvas.addEventListener('pointermove', (e) => { if (!drawing) return; ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke(); canvas._hasInk = true; });
    canvas.addEventListener('pointerup', () => { drawing = false; });
  }

  function openSignatureModal() {
    let mode = 'draw';
    let typedText = '';
    let uploaded = null;
    const tabs = el('div', { class: 'pe-sig-tabs' });
    const drawTab = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Draw');
    const typeTab = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Type');
    const uploadTab = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Upload');
    tabs.append(drawTab, typeTab, uploadTab);
    const content = el('div', {});
    const body = el('div', {}, [tabs, content]);

    function renderContent() {
      clear(content);
      [drawTab, typeTab, uploadTab].forEach((b) => b.classList.remove('btn--primary'));
      if (mode === 'draw') {
        drawTab.classList.add('btn--primary');
        const pad = el('div', { class: 'pe-signature-pad' });
        const canvas = el('canvas', { width: '460', height: '180' });
        pad.appendChild(canvas);
        content.appendChild(pad);
        wireSignaturePad(canvas);
        content._getResult = () => (canvas._hasInk ? { dataUrl: canvas.toDataURL('image/png') } : null);
      } else if (mode === 'type') {
        typeTab.classList.add('btn--primary');
        const input = el('input', { type: 'text', class: 'auth-input', placeholder: 'Type your name', value: typedText });
        input.addEventListener('input', () => { typedText = input.value; });
        content.appendChild(el('div', { class: 'pe-field' }, [el('label', {}, 'Your name'), input]));
        content._getResult = () => (typedText.trim() ? { typed: typedText.trim() } : null);
      } else {
        uploadTab.classList.add('btn--primary');
        const chooseBtn = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Choose Image');
        const preview = el('div', { style: 'margin-top:10px;' });
        if (uploaded) preview.appendChild(el('img', { src: uploaded.dataUrl, style: 'max-width:100%;max-height:120px;' }));
        chooseBtn.addEventListener('click', () => $('#peSigImageFileInput').click());
        content.append(chooseBtn, preview);
        content._preview = preview;
        content._getResult = () => uploaded;
      }
    }
    drawTab.addEventListener('click', () => { mode = 'draw'; renderContent(); });
    typeTab.addEventListener('click', () => { mode = 'type'; renderContent(); });
    uploadTab.addEventListener('click', () => { mode = 'upload'; renderContent(); });
    renderContent();

    const onUpload = async (e) => {
      const file = e.target.files[0];
      e.target.value = '';
      if (!file) return;
      const buf = await readFileAsArrayBuffer(file);
      const bytes = new Uint8Array(buf);
      const mimeType = file.type.includes('png') ? 'image/png' : 'image/jpeg';
      uploaded = { bytes, mimeType, dataUrl: URL.createObjectURL(new Blob([bytes], { type: mimeType })) };
      if (mode === 'upload') { clear(content._preview); content._preview.appendChild(el('img', { src: uploaded.dataUrl, style: 'max-width:100%;max-height:120px;' })); }
    };
    $('#peSigImageFileInput').onchange = onUpload;

    openModal({
      title: 'Add your signature',
      body,
      actions: [
        { label: 'Cancel', onClick: () => { pendingSignatureTarget = null; } },
        { label: 'Insert Signature', primary: true, onClick: () => {
          const result = content._getResult ? content._getResult() : null;
          if (!result) { showToast('Add a signature first', { error: true }); return; }
          placeSignature(result);
        } },
      ],
    });
  }

  function placeSignature(result) {
    const target = pendingSignatureTarget || defaultCenterOfCurrentPage();
    pendingSignatureTarget = null;
    const page = getPage(target.pageId);
    const width = 180;
    const height = 50;
    let obj;
    if (result.typed) {
      obj = makeObject('signature', { x: target.x - width / 2, y: target.y - height / 2, width, height, mode: 'typed', text: result.typed, color: '#111111', fontFamily: 'serif' });
    } else {
      obj = makeObject('signature', { x: target.x - width / 2, y: target.y - height / 2, width, height, mode: 'image', bytes: dataUrlToBytes(result.dataUrl), mimeType: 'image/png', previewUrl: result.dataUrl });
    }
    pushAdd(page, 'objects', obj);
    controller.select(page.id, obj, null);
    setActiveTool('select');
  }

  function dataUrlToBytes(dataUrl) {
    const base64 = dataUrl.split(',')[1];
    const binary = atob(base64);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
    return bytes;
  }

  function defaultCenterOfCurrentPage() {
    const page = currentPage() || state.project.pages[0];
    return { pageId: page.id, x: page.width / 2, y: page.height / 2 };
  }

  /* ==========================================================
     WATERMARK / HEADER-FOOTER / METADATA
     ========================================================== */
  function openWatermarkModal() {
    const textInput = el('input', { type: 'text', class: 'auth-input', value: 'CONFIDENTIAL' });
    const opacityInput = el('input', { type: 'range', min: '5', max: '80', value: '25' });
    const rotationInput = el('input', { type: 'number', class: 'auth-input', value: '-45' });
    const rangeSelect = el('select', { class: 'auth-input' }, [el('option', { value: 'all' }, 'All pages'), el('option', { value: 'current' }, 'Current page only')]);
    const body = el('div', {}, [
      el('div', { class: 'pe-field' }, [el('label', {}, 'Watermark text'), textInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Opacity'), opacityInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Rotation (degrees)'), rotationInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Apply to'), rangeSelect]),
    ]);
    openModal({ title: 'Add watermark', body, actions: [
      { label: 'Cancel', onClick: () => {} },
      { label: 'Add Watermark', primary: true, onClick: () => applyWatermark({ text: textInput.value, opacity: Number(opacityInput.value) / 100, rotation: Number(rotationInput.value), scope: rangeSelect.value }) },
    ] });
  }

  function applyWatermark({ text, opacity, rotation, scope }) {
    if (!text.trim()) return;
    const pages = scope === 'current' ? [currentPage()] : state.project.pages;
    const created = [];
    pages.forEach((page) => {
      const width = page.width * 0.7;
      const height = 60;
      const obj = makeObject('watermark', { x: (page.width - width) / 2, y: (page.height - height) / 2, width, height, watermarkType: 'text', text, opacity, rotation, color: '#94a3b8', fontFamily: 'Helvetica', fontSize: 36, align: 'center', bold: false, italic: false, letterSpacing: 0, lineHeight: 1.2 });
      created.push({ page, obj });
    });
    state.history.push({
      do: () => created.forEach(({ page, obj }) => page.objects.push(obj)),
      undo: () => created.forEach(({ page, obj }) => { const i = page.objects.indexOf(obj); if (i !== -1) page.objects.splice(i, 1); }),
    });
    showToast(`Watermark added to ${created.length} page${created.length === 1 ? '' : 's'}`);
  }

  function openHeaderFooterModal() {
    const leftInput = el('input', { type: 'text', class: 'auth-input', placeholder: 'e.g. Confidential' });
    const centerInput = el('input', { type: 'text', class: 'auth-input', placeholder: 'Center text' });
    const rightInput = el('input', { type: 'text', class: 'auth-input', value: 'Page {page} of {pages}' });
    const positionSelect = el('select', { class: 'auth-input' }, [el('option', { value: 'footer' }, 'Footer'), el('option', { value: 'header' }, 'Header')]);
    const body = el('div', {}, [
      el('div', { class: 'pe-field' }, [el('label', {}, 'Position'), positionSelect]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Left'), leftInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Center'), centerInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Right (supports {page} and {pages})'), rightInput]),
    ]);
    openModal({ title: 'Add header / footer', body, actions: [
      { label: 'Cancel', onClick: () => {} },
      { label: 'Apply to All Pages', primary: true, onClick: () => applyHeaderFooter({ left: leftInput.value, center: centerInput.value, right: rightInput.value, position: positionSelect.value }) },
    ] });
  }

  function applyHeaderFooter({ left, center, right, position }) {
    const total = state.project.pages.length;
    const created = [];
    state.project.pages.forEach((page, idx) => {
      const y = position === 'footer' ? 24 : page.height - 40;
      const fill = (s) => s.replace(/\{page\}/g, idx + 1).replace(/\{pages\}/g, total);
      const specs = [
        left && { text: fill(left), x: 36, width: 200, align: 'left' },
        center && { text: fill(center), x: 0, width: page.width, align: 'center' },
        right && { text: fill(right), x: 0, width: page.width - 36, align: 'right' },
      ].filter(Boolean);
      specs.forEach((s) => {
        const obj = makeTextObject({ kind: 'new', text: s.text, x: s.x, y, width: s.width, height: 16, fontSize: 9, color: '#64748B', align: s.align, fontFamily: 'Helvetica' });
        created.push({ page, obj });
      });
    });
    state.history.push({
      do: () => created.forEach(({ page, obj }) => page.textObjects.push(obj)),
      undo: () => created.forEach(({ page, obj }) => { const i = page.textObjects.indexOf(obj); if (i !== -1) page.textObjects.splice(i, 1); }),
    });
    showToast('Header/footer applied to all pages');
  }

  function openMetadataModal() {
    const m = state.project.metadata;
    const fields = ['title', 'author', 'subject', 'keywords'].map((key) => {
      const input = el('input', { type: 'text', class: 'auth-input', value: m[key] || '' });
      return { key, input, row: el('div', { class: 'pe-field' }, [el('label', {}, key[0].toUpperCase() + key.slice(1)), input]) };
    });
    const body = el('div', {}, fields.map((f) => f.row));
    openModal({ title: 'Edit document metadata', body, actions: [
      { label: 'Clear All', close: false, onClick: () => fields.forEach((f) => { f.input.value = ''; }) },
      { label: 'Cancel', onClick: () => {} },
      { label: 'Save', primary: true, onClick: () => {
        const before = { ...m };
        const after = {};
        fields.forEach((f) => { after[f.key] = f.input.value; });
        state.history.push({ do: () => Object.assign(state.project.metadata, after), undo: () => Object.assign(state.project.metadata, before) });
      } },
    ] });
  }

  /* ==========================================================
     SEARCH / FIND & REPLACE
     ========================================================== */
  function openSearchPanel() {
    const queryInput = el('input', { type: 'text', class: 'auth-input', placeholder: 'Find' });
    const replaceInput = el('input', { type: 'text', class: 'auth-input', placeholder: 'Replace with' });
    const status = el('div', { class: 'pe-panel__empty' }, '');
    let matches = [];
    let matchIndex = -1;

    function runFind() {
      matches = state.project ? findMatches(state.project, queryInput.value) : [];
      matchIndex = matches.length ? 0 : -1;
      updateStatus();
      goToMatch();
    }
    function updateStatus() {
      status.textContent = matches.length ? `Match ${matchIndex + 1} of ${matches.length}` : (queryInput.value ? 'No matches' : '');
    }
    function goToMatch() {
      if (matchIndex === -1) return;
      const m = matches[matchIndex];
      pageManager.scrollToPage(m.pageId);
      const node = pageManager.findObjectNode(m.pageId, m.textId);
      if (node) { node.classList.add('is-selected'); setTimeout(() => node.classList.remove('is-selected'), 1200); }
    }
    queryInput.addEventListener('input', runFind);
    const prevBtn = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Previous');
    prevBtn.addEventListener('click', () => { if (!matches.length) return; matchIndex = (matchIndex - 1 + matches.length) % matches.length; updateStatus(); goToMatch(); });
    const nextBtn = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Next');
    nextBtn.addEventListener('click', () => { if (!matches.length) return; matchIndex = (matchIndex + 1) % matches.length; updateStatus(); goToMatch(); });
    const replaceBtn = el('button', { type: 'button', class: 'btn btn--glass btn--sm' }, 'Replace');
    replaceBtn.addEventListener('click', () => {
      if (matchIndex === -1) return;
      const m = matches[matchIndex];
      const page = getPage(m.pageId);
      const obj = page.textObjects.find((t) => t.id === m.textId);
      const { after, changed } = replaceInText(obj.text, queryInput.value, replaceInput.value);
      if (!changed) return;
      mutateObj(m.pageId, obj, { text: after });
      runFind();
    });
    const replaceAllBtn = el('button', { type: 'button', class: 'btn btn--primary btn--sm' }, 'Replace All');
    replaceAllBtn.addEventListener('click', () => {
      const affected = [];
      state.project.pages.forEach((page) => page.textObjects.forEach((t) => {
        if (t.deleted) return;
        const { after, changed } = replaceInText(t.text, queryInput.value, replaceInput.value);
        if (changed) affected.push({ t, before: t.text, after, wasEdited: t.edited });
      }));
      if (!affected.length) return;
      state.history.push({
        do: () => affected.forEach((a) => { a.t.text = a.after; a.t.edited = true; }),
        undo: () => affected.forEach((a) => { a.t.text = a.before; a.t.edited = a.wasEdited; }),
      });
      showToast(`Replaced ${affected.length} match${affected.length === 1 ? '' : 'es'}`);
      runFind();
    });

    const body = el('div', {}, [
      el('div', { class: 'pe-field' }, [el('label', {}, 'Find'), queryInput]),
      el('div', { class: 'pe-field' }, [el('label', {}, 'Replace with'), replaceInput]),
      el('div', { class: 'pe-btn-row' }, [prevBtn, nextBtn, replaceBtn, replaceAllBtn]),
      status,
    ]);
    openModal({ title: 'Find & Replace', body, actions: [{ label: 'Close', onClick: () => {} }] });
    setTimeout(() => queryInput.focus(), 50);
  }

  /* ==========================================================
     FORMS
     ========================================================== */
  function openFormPanel() {
    const page = currentPage();
    if (!page || !page.formFields?.length) { showToast('No fillable form fields detected on the current page'); return; }
    const inputs = page.formFields.map((f) => {
      let input;
      if (f.isCheckbox) { input = el('input', { type: 'checkbox' }); if (f.value) input.checked = true; }
      else if (f.options?.length) input = el('select', { class: 'auth-input' }, f.options.map((o) => el('option', { value: o }, o)));
      else input = el('input', { type: 'text', class: 'auth-input', value: String(f.value || '') });
      return { field: f, input, row: el('div', { class: 'pe-field' }, [el('label', {}, f.name), input]) };
    });
    const flattenCheck = el('input', { type: 'checkbox' });
    flattenCheck.checked = state.flattenForms;
    const body = el('div', {}, [...inputs.map((i) => i.row), el('div', { class: 'pe-field' }, [el('label', {}, [flattenCheck, ' Flatten form into the PDF on download'])])]);
    openModal({ title: 'Fill form fields (current page)', body, actions: [
      { label: 'Cancel', onClick: () => {} },
      { label: 'Apply', primary: true, onClick: () => {
        if (!state.project.formValues.has('main')) state.project.formValues.set('main', new Map());
        const values = state.project.formValues.get('main');
        inputs.forEach((i) => values.set(i.field.name, i.field.isCheckbox ? i.input.checked : i.input.value));
        state.flattenForms = flattenCheck.checked;
        showToast('Form values saved — they’ll be applied when you download');
      } },
    ] });
  }

  /* ==========================================================
     PAGE ACTIONS
     ========================================================== */
  function deepClonePage(page) {
    const clone = new Page({ source: page.source, width: page.width, height: page.height, rotation: page.rotation });
    clone.isScanned = page.isScanned;
    clone.cropRect = page.cropRect ? { ...page.cropRect } : null;
    clone.textObjects = page.textObjects.map((t) => ({ ...t, id: uid('text') }));
    clone.objects = page.objects.map((o) => ({ ...o, id: uid(o.type) }));
    clone.formFields = page.formFields ? [...page.formFields] : [];
    return clone;
  }

  function rotateCurrentPage(delta) {
    const page = currentPage();
    if (!page) return;
    const before = page.rotation;
    const after = ((before + delta) % 360 + 360) % 360;
    // The re-render lives inside do/undo (not just called once after push) so
    // it also runs correctly when this command is later undone or redone.
    state.history.push({
      do: () => { page.rotation = after; pageManager.renderPage(page); pageManager.rebuildThumbnails(); },
      undo: () => { page.rotation = before; pageManager.renderPage(page); pageManager.rebuildThumbnails(); },
    });
  }

  function clearCropOnCurrentPage() {
    const page = currentPage();
    if (!page || !page.cropRect) { showToast('No crop is staged on this page'); return; }
    const before = page.cropRect;
    state.history.push({
      do: () => { page.cropRect = null; pageManager.refreshPage(page.id); },
      undo: () => { page.cropRect = before; pageManager.refreshPage(page.id); },
    });
  }

  function duplicateCurrentPage() {
    const idx = state.currentPageIndex;
    const before = state.project.pages;
    const clone = deepClonePage(before[idx]);
    const after = [...before.slice(0, idx + 1), clone, ...before.slice(idx + 1)];
    state.history.push({
      do: () => { state.project.pages = after; pageManager.remount(); },
      undo: () => { state.project.pages = before; pageManager.remount(); },
    });
  }

  function deleteCurrentPage() {
    if (state.project.pages.length <= 1) { showToast("Can't delete the only page", { error: true }); return; }
    const idx = state.currentPageIndex;
    const before = state.project.pages;
    const after = before.filter((_, i) => i !== idx);
    state.history.push({
      do: () => { state.project.pages = after; pageManager.remount(); },
      undo: () => { state.project.pages = before; pageManager.remount(); },
    });
  }

  function insertBlankPageAction() {
    const idx = state.currentPageIndex;
    const before = state.project.pages;
    const size = before[idx] || { width: 612, height: 792 };
    const blank = new Page({ source: { blank: true }, width: size.width, height: size.height });
    const after = [...before.slice(0, idx + 1), blank, ...before.slice(idx + 1)];
    state.history.push({
      do: () => { state.project.pages = after; pageManager.remount(); },
      undo: () => { state.project.pages = before; pageManager.remount(); },
    });
  }

  async function extractCurrentPage() {
    const page = currentPage();
    if (!page) return;
    try {
      showToast('Preparing extracted page…');
      const tempProject = { ...state.project, pages: [page], formValues: new Map() };
      const { blob } = await exportProject(tempProject, { pdfjsDocs: state.pdfjsDocs });
      downloadBlob(blob, `${state.project.fileName}-page-${state.currentPageIndex + 1}.pdf`);
    } catch (err) {
      reportExportError(err);
    }
  }

  $('#peImportFileInput').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    e.target.value = '';
    if (!file) return;
    try {
      showToast('Importing PDF…');
      const buffer = await readFileAsArrayBuffer(file);
      const pdfjsDoc = await openPdf(buffer, { onPasswordNeeded: (isRetry) => promptPassword(isRetry) });
      const docKey = `import-${state.pdfjsDocs.size}`;
      state.pdfjsDocs.set(docKey, pdfjsDoc);
      state.project.sources.set(docKey, { name: file.name, bytes: buffer });
      const insertAt = state.currentPageIndex;
      const before = state.project.pages;
      const newPages = [];
      for (let i = 0; i < pdfjsDoc.numPages; i++) {
        const pdfjsPage = await pdfjsDoc.getPage(i + 1);
        const [x0, y0, x1, y1] = pdfjsPage.view;
        const page = new Page({ source: { docKey, pageIndex: i }, width: x1 - x0, height: y1 - y0, rotation: 0 });
        const analysis = await analyzePage(pdfjsPage);
        page.isScanned = analysis.isScanned;
        page.textObjects = analysis.textRuns.map(buildTextObjectFromRun);
        newPages.push(page);
      }
      const after = [...before.slice(0, insertAt + 1), ...newPages, ...before.slice(insertAt + 1)];
      state.history.push({
        do: () => { state.project.pages = after; pageManager.remount(); },
        undo: () => { state.project.pages = before; pageManager.remount(); },
      });
      showToast(`Merged ${newPages.length} page${newPages.length === 1 ? '' : 's'} from ${file.name}`);
    } catch (err) {
      reportLoadError(err);
    }
  });

  /* ==========================================================
     EXPORT / DOWNLOAD
     ========================================================== */
  async function handleDownload() {
    if (!state.project) return;
    try {
      showToast('Preparing your PDF…');
      const { blob, flattenedPages } = await exportProject(state.project, { pdfjsDocs: state.pdfjsDocs, flattenForms: state.flattenForms });
      downloadBlob(blob, `${state.project.fileName}-edited.pdf`);
      showToast(flattenedPages?.length
        ? `Downloaded. ${flattenedPages.length} page(s) with redactions were flattened to an image to guarantee removal.`
        : 'Downloaded — generated entirely in your browser.');
    } catch (err) {
      reportExportError(err);
    }
  }
  function reportExportError(err) {
    console.error(err);
    showToast(err instanceof PdfEditorError ? err.message : 'Something went wrong generating the PDF.', { error: true });
  }
  function reportLoadError(err) {
    console.error(err);
    const box = $('#peLoadError');
    box.hidden = false;
    box.textContent = err instanceof PdfEditorError ? err.message : 'Something went wrong opening this PDF.';
  }

  /* ==========================================================
     MODALS / TOAST / STATUS
     ========================================================== */
  function openModal({ title, body, actions }) {
    const root = $('#peModalRoot');
    clear(root);
    const backdrop = el('div', { class: 'pe-modal-backdrop' });
    const modal = el('div', { class: 'pe-modal' });
    modal.appendChild(el('h3', {}, title));
    modal.appendChild(body);
    const actionsRow = el('div', { class: 'pe-modal__actions' });
    actions.forEach((a) => {
      const btn = el('button', { type: 'button', class: `btn ${a.primary ? 'btn--primary' : 'btn--glass'} btn--sm` }, a.label);
      btn.addEventListener('click', () => { a.onClick(); if (a.close !== false) closeModal(); });
      actionsRow.appendChild(btn);
    });
    modal.appendChild(actionsRow);
    backdrop.appendChild(modal);
    backdrop.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });
    root.appendChild(backdrop);
  }
  function closeModal() { clear($('#peModalRoot')); }
  function confirmModal(title, message) {
    return new Promise((resolve) => {
      openModal({ title, body: el('p', {}, message), actions: [
        { label: 'No', onClick: () => resolve(false) },
        { label: 'Yes', primary: true, onClick: () => resolve(true) },
      ] });
    });
  }
  function promptPassword(isRetry) {
    return new Promise((resolve) => {
      const input = el('input', { type: 'password', class: 'auth-input', placeholder: 'PDF password' });
      const body = el('div', {}, [el('p', {}, isRetry ? 'Incorrect password — try again.' : 'This PDF is password protected.'), el('div', { class: 'pe-field' }, [el('label', {}, 'Password'), input])]);
      openModal({ title: 'Password required', body, actions: [
        { label: 'Cancel', onClick: () => resolve(null) },
        { label: 'Unlock', primary: true, onClick: () => resolve(input.value) },
      ] });
      setTimeout(() => input.focus(), 50);
    });
  }

  let toastTimer = null;
  function showToast(message, { error = false } = {}) {
    const t = $('#peToast');
    t.textContent = message;
    t.classList.toggle('is-error', error);
    t.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('is-visible'), 3200);
  }

  function updateStatusBar() {
    if (!state.project) return;
    $('#peStatusPage').textContent = `Page ${state.currentPageIndex + 1} of ${state.project.pages.length}`;
  }
  function handleActivePageChange(idx) {
    state.currentPageIndex = idx;
    const page = state.project.pages[idx];
    if (page) { state.currentPageId = page.id; pageManager.setActiveThumb(page.id); }
    updateStatusBar();
  }

  function afterHistoryChange() {
    const u = $('#peUndoBtn');
    const r = $('#peRedoBtn');
    if (u) u.disabled = !state.history.canUndo;
    if (r) r.disabled = !state.history.canRedo;
    if (state.project) state.project.pages.forEach((p) => pageManager.refreshPage(p.id));
    renderProperties();
    scheduleAutosave();
  }

  let autosaveTimer = null;
  function scheduleAutosave() {
    if (!state.project) return;
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(persistNow, 1500);
  }
  async function persistNow() {
    if (!state.project) return;
    try {
      await saveProjectLocally(state.project);
      const indicator = $('#peSaveIndicator');
      if (indicator) { indicator.style.opacity = '1'; }
    } catch (err) { console.warn('Autosave failed', err); }
  }

  /* ==========================================================
     KEYBOARD SHORTCUTS
     ========================================================== */
  document.addEventListener('keydown', (e) => {
    if (!state.project) return;
    const tag = document.activeElement?.tagName;
    const isEditable = document.activeElement?.isContentEditable || tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
    const mod = e.ctrlKey || e.metaKey;
    if (mod && !e.shiftKey && e.key.toLowerCase() === 'z') { e.preventDefault(); dispatch('undo'); }
    else if (mod && (e.key.toLowerCase() === 'y' || (e.shiftKey && e.key.toLowerCase() === 'z'))) { e.preventDefault(); dispatch('redo'); }
    else if (mod && e.key.toLowerCase() === 's') { e.preventDefault(); dispatch('save-local'); }
    else if (mod && e.key.toLowerCase() === 'f') { e.preventDefault(); dispatch('open-search'); }
    else if (e.key === 'Delete' && !isEditable) { dispatch('delete-selected'); }
    else if (e.key === 'Escape') { controller.deselect(); closeAllMenus(); closeModal(); }
    else if ((e.key === '+' || e.key === '=') && !isEditable) { dispatch('zoom-in'); }
    else if (e.key === '-' && !isEditable) { dispatch('zoom-out'); }
    else if (e.key.startsWith('Arrow') && controller.selected && !isEditable) {
      e.preventDefault();
      const { pageId, obj } = controller.selected;
      const step = e.shiftKey ? 1 : 5;
      const dx = e.key === 'ArrowLeft' ? -step : e.key === 'ArrowRight' ? step : 0;
      const dy = e.key === 'ArrowUp' ? step : e.key === 'ArrowDown' ? -step : 0;
      if (dx || dy) mutateObj(pageId, obj, { x: obj.x + dx, y: obj.y + dy });
    }
  });

  /* ==========================================================
     BOOT
     ========================================================== */
  function wireDropZone() {
    const drop = $('#peDrop');
    $('#peChooseBtn').addEventListener('click', () => $('#peFileInput').click());
    drop.addEventListener('click', (e) => { if (e.target.closest('button')) return; $('#peFileInput').click(); });
    drop.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $('#peFileInput').click(); } });
    ['dragenter', 'dragover'].forEach((evt) => drop.addEventListener(evt, (e) => { e.preventDefault(); drop.classList.add('is-dragover'); }));
    ['dragleave', 'drop'].forEach((evt) => drop.addEventListener(evt, (e) => { e.preventDefault(); drop.classList.remove('is-dragover'); }));
    drop.addEventListener('drop', (e) => { const f = e.dataTransfer.files[0]; if (f) openFile(f); });
    $('#peFileInput').addEventListener('change', (e) => { const f = e.target.files[0]; e.target.value = ''; if (f) openFile(f); });
  }

  app.hidden = false;
  buildMenubar();
  buildToolbar();
  wireDropZone();
  tryRestoreSession(false);
}
