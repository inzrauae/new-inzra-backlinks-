import { renderPageToCanvas, renderThumbnail } from '../rendering/page-renderer.js';
import { positionElement } from '../rendering/overlay-layer.js';
import { el, clear } from '../utils/dom.js';
import { styleTextNode, positionTextNode, renderObjectNode } from '../editor/object-controller.js';

function blankViewport(page, scale) {
  return {
    scale,
    convertToViewportPoint(x, y) {
      return [x * scale, (page.height - y) * scale];
    },
    convertToPdfPoint(x, y) {
      return [x / scale, page.height - y / scale];
    },
  };
}

/** Renders pages + thumbnails and hosts the drag/select/edit overlay for every object on the page. */
export class PageManager {
  constructor({ canvasWrap, thumbsEl, getPdfjsPage, controller, onActivePageChange, onTextDoubleClick, onReorder, onOverlayPointerDown }) {
    this.canvasWrap = canvasWrap;
    this.thumbsEl = thumbsEl;
    this.getPdfjsPage = getPdfjsPage;
    this.controller = controller;
    this.onActivePageChange = onActivePageChange || (() => {});
    this.onTextDoubleClick = onTextDoubleClick || (() => {});
    this.onReorder = onReorder || (() => {});
    this.onOverlayPointerDown = onOverlayPointerDown || (() => {});
    this.entries = new Map();
    this.project = null;
    this.scale = 1;

    this.lazyObserver = new IntersectionObserver((es) => this.handleLazy(es), { root: canvasWrap, rootMargin: '600px 0px' });
    this.activeObserver = new IntersectionObserver((es) => this.handleActive(es), { root: canvasWrap, threshold: [0, 0.5, 1] });
  }

  async mount(project) {
    this.project = project;
    clear(this.canvasWrap);
    clear(this.thumbsEl);
    this.entries.clear();
    project.pages.forEach((page) => this.buildPageShell(page));
    await this.rebuildThumbnails();
    await Promise.all(project.pages.slice(0, 2).map((p) => this.renderPage(p)));
  }

  async remount() {
    const project = this.project;
    const scrollTop = this.canvasWrap.scrollTop;
    await this.mount(project);
    this.canvasWrap.scrollTop = scrollTop;
  }

  buildPageShell(page) {
    const wrap = el('div', { class: 'pe-page', dataset: { pageId: page.id } });
    const canvas = el('canvas');
    const overlay = el('div', { class: 'pe-page__overlay' });
    wrap.append(canvas, overlay);
    if (page.isScanned) {
      wrap.appendChild(el('div', { class: 'pe-page__scan-banner' }, 'This page appears to be scanned. Text editing/search aren’t available on it yet.'));
    }
    this.canvasWrap.appendChild(wrap);
    const entry = { wrap, canvas, overlay, viewport: null, rendered: false };
    this.entries.set(page.id, entry);
    this.lazyObserver.observe(wrap);
    this.activeObserver.observe(wrap);
    overlay.addEventListener('pointerdown', (evt) => {
      if (evt.target !== overlay) return; // let object nodes handle their own interactions
      this.onOverlayPointerDown(page.id, evt, () => this.entries.get(page.id).viewport, overlay);
    });
    return entry;
  }

  handleLazy(entries) {
    entries.forEach((e) => {
      if (!e.isIntersecting || !this.project) return;
      const page = this.project.pages.find((p) => p.id === e.target.dataset.pageId);
      if (page) this.renderPage(page);
    });
  }

  handleActive(entries) {
    if (!this.project) return;
    const best = entries.filter((e) => e.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
    if (!best) return;
    const idx = this.project.pages.findIndex((p) => p.id === best.target.dataset.pageId);
    if (idx !== -1) this.onActivePageChange(idx);
  }

  /**
   * The IntersectionObserver can fire for a page that's already being
   * eagerly rendered by mount() — without de-duping, the second call's
   * pdf.js render cancels the first's render task, which then rejects
   * before ever reaching syncOverlay(), leaving the page with a canvas
   * but no interactive text/object overlay. Concurrent calls for the same
   * page share one in-flight render instead of racing.
   */
  renderPage(page) {
    const entry = this.entries.get(page.id);
    if (!entry) return Promise.resolve();
    if (entry.renderPromise) return entry.renderPromise;
    entry.renderPromise = this.doRenderPage(page, entry).finally(() => { entry.renderPromise = null; });
    return entry.renderPromise;
  }

  async doRenderPage(page, entry) {
    let viewport;
    if (page.source.blank) {
      const scale = this.scale;
      const cssW = Math.floor(page.width * scale);
      const cssH = Math.floor(page.height * scale);
      const dpr = window.devicePixelRatio || 1;
      entry.canvas.width = cssW * dpr;
      entry.canvas.height = cssH * dpr;
      entry.canvas.style.width = `${cssW}px`;
      entry.canvas.style.height = `${cssH}px`;
      const ctx = entry.canvas.getContext('2d');
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, cssW, cssH);
      viewport = blankViewport(page, scale);
    } else {
      const pdfjsPage = await this.getPdfjsPage(page);
      viewport = await renderPageToCanvas(pdfjsPage, entry.canvas, { scale: this.scale, extraRotation: page.rotation });
    }
    entry.viewport = viewport;
    entry.rendered = true;
    this.syncOverlay(page);
  }

  async rerenderAll() {
    for (const page of this.project.pages) {
      const entry = this.entries.get(page.id);
      if (entry && entry.rendered) await this.renderPage(page);
    }
  }

  syncOverlay(page) {
    const entry = this.entries.get(page.id);
    if (!entry || !entry.viewport) return;
    clear(entry.overlay);
    const viewport = entry.viewport;
    const getViewport = () => this.entries.get(page.id).viewport;

    page.textObjects.filter((t) => !t.deleted).forEach((t) => {
      const node = el('div', { class: 'pe-text-obj', tabindex: '0', dataset: { objId: t.id } });
      styleTextNode(node, t);
      positionTextNode(node, viewport, t);
      entry.overlay.appendChild(node);
      this.controller.attachInteractions(node, page.id, t, getViewport);
      node.addEventListener('dblclick', (evt) => { evt.stopPropagation(); this.onTextDoubleClick(page.id, t, node); });
    });

    (page.objects || []).filter((o) => !o.deleted).forEach((obj) => {
      const node = el('div', { class: 'pe-obj', dataset: { objId: obj.id } });
      renderObjectNode(node, obj);
      positionElement(node, viewport, obj);
      entry.overlay.appendChild(node);
      this.controller.attachInteractions(node, page.id, obj, getViewport);
    });

    if (page.cropRect) {
      const cropNode = el('div', { style: 'position:absolute;pointer-events:none;border:2px dashed #2563EB;box-shadow:0 0 0 2000px rgba(15,23,42,.35);' });
      positionElement(cropNode, viewport, page.cropRect);
      entry.overlay.appendChild(cropNode);
    }
  }

  findObjectNode(pageId, objId) {
    const entry = this.entries.get(pageId);
    return entry ? entry.overlay.querySelector(`[data-obj-id="${objId}"]`) : null;
  }

  refreshPage(pageId) {
    if (!this.project) return;
    const page = this.project.pages.find((p) => p.id === pageId);
    if (page) this.syncOverlay(page);
  }

  scrollToPage(pageId) {
    this.entries.get(pageId)?.wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  async rebuildThumbnails() {
    clear(this.thumbsEl);
    this.project.pages.forEach((page, i) => {
      const wrap = el('div', { class: 'pe-thumb', dataset: { pageId: page.id }, draggable: 'true' });
      const canvas = el('canvas');
      wrap.appendChild(canvas);
      wrap.appendChild(el('div', { class: 'pe-thumb__num' }, `${i + 1}`));
      if (page.isScanned) wrap.appendChild(el('span', { class: 'pe-thumb__scanned' }, 'Scanned'));
      this.thumbsEl.appendChild(wrap);
      wrap.addEventListener('click', () => this.scrollToPage(page.id));
      this.wireThumbDrag(wrap, page.id);
      this.renderThumbCanvas(page, canvas);
    });
  }

  async renderThumbCanvas(page, canvas) {
    if (page.source.blank) {
      canvas.width = 140;
      canvas.height = Math.round(140 * (page.height / page.width));
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      return;
    }
    try {
      const pdfjsPage = await this.getPdfjsPage(page);
      await renderThumbnail(pdfjsPage, canvas, { maxWidth: 140, extraRotation: page.rotation });
    } catch { /* thumbnail failures are non-critical */ }
  }

  wireThumbDrag(wrap, pageId) {
    wrap.addEventListener('dragstart', (e) => {
      wrap.classList.add('is-dragging');
      e.dataTransfer.setData('text/plain', pageId);
    });
    wrap.addEventListener('dragend', () => wrap.classList.remove('is-dragging'));
    wrap.addEventListener('dragover', (e) => e.preventDefault());
    wrap.addEventListener('drop', (e) => {
      e.preventDefault();
      const draggedId = e.dataTransfer.getData('text/plain');
      if (!draggedId || draggedId === pageId) return;
      const fromIndex = this.project.pages.findIndex((p) => p.id === draggedId);
      const toIndex = this.project.pages.findIndex((p) => p.id === pageId);
      if (fromIndex !== -1 && toIndex !== -1) this.onReorder(fromIndex, toIndex);
    });
  }

  setActiveThumb(pageId) {
    this.thumbsEl.querySelectorAll('.pe-thumb').forEach((t) => t.classList.toggle('is-active', t.dataset.pageId === pageId));
    this.canvasWrap.querySelectorAll('.pe-page').forEach((p) => p.classList.toggle('is-active', p.dataset.pageId === pageId));
  }
}

export function clonePageObject(obj, uidFn) {
  return { ...obj, id: uidFn(obj.type || 'text') };
}
