import { positionElement, screenDeltaToPdf } from '../rendering/overlay-layer.js';
import { cssFontFamilyFor } from '../text/font-catalog.js';
import { el, clear } from '../utils/dom.js';

/**
 * Generic selection/move/resize/edit behavior shared by every object type
 * (text, image, shape, drawing, annotation, signature, redaction). Each
 * type only needs to say how to *render* its node and, for text, how to
 * go in and out of edit mode — dragging, resizing, selecting and deleting
 * are handled once here instead of six times.
 */
export class ObjectController {
  constructor({ history, onSelect, onChange, isReadOnly }) {
    this.history = history;
    this.onSelect = onSelect || (() => {});
    this.onChange = onChange || (() => {});
    this.isReadOnly = isReadOnly || (() => false);
    this.selected = null; // { pageId, obj, node }
  }

  select(pageId, obj, node) {
    if (this.selected && this.selected.node) this.selected.node.classList.remove('is-selected');
    this.selected = obj ? { pageId, obj, node } : null;
    if (node) node.classList.add('is-selected');
    this.onSelect(this.selected);
  }

  deselect() {
    this.select(null, null, null);
  }

  deleteSelected() {
    if (!this.selected) return;
    const { pageId, obj } = this.selected;
    const wasDeleted = obj.deleted;
    this.history.push({
      do: () => { obj.deleted = true; },
      undo: () => { obj.deleted = wasDeleted; },
    });
    this.deselect();
    this.onChange(pageId);
  }

  /** Wires selection + drag-move + resize handles onto `node` for `obj` (mutated in place, via history). */
  attachInteractions(node, pageId, obj, getViewport) {
    node.addEventListener('pointerdown', (evt) => {
      if (evt.target.closest('.pe-obj__handle')) return;
      if (this.isReadOnly()) return;
      evt.stopPropagation();
      this.select(pageId, obj, node);
      if (obj.locked) return;
      this.startDrag(evt, pageId, obj, node, getViewport);
    });

    if (!obj.locked) {
      const handles = ['nw', 'ne', 'sw', 'se'].map((pos) => {
        const h = el('div', { class: `pe-obj__handle pe-obj__handle--${pos}`, dataset: { pos } });
        node.appendChild(h);
        h.addEventListener('pointerdown', (evt) => {
          evt.stopPropagation();
          this.select(pageId, obj, node);
          this.startResize(evt, pos, pageId, obj, node, getViewport);
        });
        return h;
      });
      node._peHandles = handles;
    }
  }

  startDrag(evt, pageId, obj, node, getViewport) {
    const startX = evt.clientX;
    const startY = evt.clientY;
    const origX = obj.x;
    const origY = obj.y;
    let moved = false;
    node.setPointerCapture(evt.pointerId);

    const onMove = (e) => {
      const viewport = getViewport();
      const [dx, dy] = screenDeltaToPdf(viewport, e.clientX - startX, e.clientY - startY);
      obj.x = origX + dx;
      obj.y = origY + dy;
      moved = true;
      positionElement(node, viewport, obj);
    };
    const onUp = () => {
      node.removeEventListener('pointermove', onMove);
      node.removeEventListener('pointerup', onUp);
      if (!moved) return;
      const newX = obj.x;
      const newY = obj.y;
      obj.x = origX;
      obj.y = origY;
      this.history.push({
        do: () => { obj.x = newX; obj.y = newY; },
        undo: () => { obj.x = origX; obj.y = origY; },
      });
      this.onChange(pageId);
    };
    node.addEventListener('pointermove', onMove);
    node.addEventListener('pointerup', onUp);
  }

  startResize(evt, pos, pageId, obj, node, getViewport) {
    const startX = evt.clientX;
    const startY = evt.clientY;
    const orig = { x: obj.x, y: obj.y, width: obj.width, height: obj.height };
    node.setPointerCapture(evt.pointerId);

    const onMove = (e) => {
      const viewport = getViewport();
      const [dx, dy] = screenDeltaToPdf(viewport, e.clientX - startX, e.clientY - startY);
      let { x, y, width, height } = orig;
      if (pos.includes('e')) width = Math.max(4, orig.width + dx);
      if (pos.includes('w')) { width = Math.max(4, orig.width - dx); x = orig.x + orig.width - width; }
      if (pos.includes('n')) { height = Math.max(4, orig.height + dy); }
      if (pos.includes('s')) { height = Math.max(4, orig.height - dy); y = orig.y + orig.height - height; }
      Object.assign(obj, { x, y, width, height });
      positionElement(node, viewport, obj);
    };
    const onUp = () => {
      node.removeEventListener('pointermove', onMove);
      node.removeEventListener('pointerup', onUp);
      const updated = { x: obj.x, y: obj.y, width: obj.width, height: obj.height };
      Object.assign(obj, orig);
      this.history.push({
        do: () => Object.assign(obj, updated),
        undo: () => Object.assign(obj, orig),
      });
      this.onChange(pageId);
    };
    node.addEventListener('pointermove', onMove);
    node.addEventListener('pointerup', onUp);
  }
}

export function styleTextNode(node, obj) {
  node.style.fontFamily = cssFontFamilyFor(obj.fontMatch?.matchedFamily);
  node.style.fontWeight = obj.bold ? '700' : '400';
  node.style.fontStyle = obj.italic ? 'italic' : 'normal';
  node.style.color = obj.color;
  node.style.opacity = obj.opacity == null ? 1 : obj.opacity;
  node.style.textAlign = obj.align || 'left';
  node.style.letterSpacing = `${obj.letterSpacing || 0}px`;
  node.textContent = obj.text;
}

export function positionTextNode(node, viewport, obj) {
  positionElement(node, viewport, obj);
  node.style.fontSize = `${Math.max(1, obj.fontSize * viewport.scale)}px`;
  node.style.lineHeight = `${obj.lineHeight || 1.2}`;
}

/** Renders a generic (non-text) object's DOM appearance based on its type. */
export function renderObjectNode(node, obj) {
  clear(node);
  node.style.opacity = obj.opacity == null ? 1 : obj.opacity;
  node.style.background = '';
  node.style.border = '';
  node.style.borderRadius = '';
  if (obj.type === 'image' || obj.type === 'signature') {
    if (obj.previewUrl) {
      const img = el('img', { src: obj.previewUrl, style: 'width:100%;height:100%;object-fit:fill;pointer-events:none;' });
      node.appendChild(img);
    } else if (obj.mode === 'typed') {
      node.style.display = 'flex';
      node.style.alignItems = 'center';
      node.textContent = obj.text || '';
      node.style.fontFamily = obj.fontFamily === 'serif' ? '"Brush Script MT", cursive' : 'cursive';
      node.style.fontSize = `${(obj.height || 30) * 0.6}px`;
      node.style.color = obj.color || '#111';
    }
  } else if (obj.type === 'shape') {
    const stroke = obj.strokeColor || '#0F172A';
    const fill = obj.fillColor || 'transparent';
    if (obj.shapeKind === 'ellipse') node.style.borderRadius = '50%';
    if (obj.shapeKind === 'line' || obj.shapeKind === 'arrow') {
      node.style.background = `linear-gradient(${stroke}, ${stroke}) no-repeat center / 100% ${obj.strokeWidth || 2}px`;
    } else {
      node.style.border = `${obj.strokeWidth || 1.5}px solid ${stroke}`;
      node.style.background = fill;
      node.style.opacity = obj.fillOpacity == null ? node.style.opacity : obj.fillOpacity;
    }
  } else if (obj.type === 'annotation') {
    if (obj.annotationKind === 'highlight') node.style.background = `${obj.color || '#FDE68A'}66`;
    else if (obj.annotationKind === 'underline') node.style.borderBottom = `2px solid ${obj.color || '#DC2626'}`;
    else if (obj.annotationKind === 'strikethrough') node.style.borderTop = `2px solid ${obj.color || '#DC2626'}`;
    else if (obj.annotationKind === 'note') {
      node.style.background = '#FACC15';
      node.style.border = '1px solid #A16207';
      node.style.fontSize = '11px';
      node.style.padding = '4px';
      node.style.overflow = 'auto';
      node.textContent = obj.text || '';
    }
  } else if (obj.type === 'whiteout') {
    node.style.background = '#ffffff';
    node.style.border = '1px dashed #cbd5e1';
  } else if (obj.type === 'redaction') {
    node.style.background = 'repeating-linear-gradient(45deg, #000 0 6px, #222 6px 12px)';
  } else if (obj.type === 'drawing') {
    const w = Math.max(1, obj.width);
    const h = Math.max(1, obj.height);
    const pts = (obj.points || []).map((p) => `${(p.x - obj.x).toFixed(1)},${(h - (p.y - obj.y)).toFixed(1)}`).join(' ');
    const ns = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
    svg.setAttribute('preserveAspectRatio', 'none');
    svg.style.width = '100%';
    svg.style.height = '100%';
    svg.style.overflow = 'visible';
    const poly = document.createElementNS(ns, 'polyline');
    poly.setAttribute('points', pts);
    poly.setAttribute('fill', 'none');
    poly.setAttribute('stroke', obj.strokeColor || '#0F172A');
    poly.setAttribute('stroke-width', obj.strokeWidth || 2);
    poly.setAttribute('stroke-linecap', 'round');
    poly.setAttribute('stroke-linejoin', 'round');
    svg.appendChild(poly);
    node.appendChild(svg);
    node.style.pointerEvents = 'none';
  } else if (obj.type === 'watermark') {
    node.style.display = 'flex';
    node.style.alignItems = 'center';
    node.style.justifyContent = 'center';
    node.style.color = obj.color || '#94a3b8';
    node.style.fontSize = `${Math.min(obj.height, 24)}px`;
    if (obj.watermarkType !== 'image') node.textContent = obj.text || '';
  }
}
