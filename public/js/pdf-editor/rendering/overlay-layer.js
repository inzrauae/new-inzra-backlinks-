/**
 * Coordinate math between our document model (PDF user-space, y-up,
 * bottom-left origin — the same convention pdf-lib's drawing calls use)
 * and on-screen CSS pixels. Reuses PDF.js's own PageViewport transform
 * (from page-renderer.js) so overlay elements land exactly on top of the
 * rendered canvas, including under rotation and zoom.
 */

export function pdfRectToScreen(viewport, x, y, width, height) {
  // PDF.js's PageViewport only exposes point conversion (no rectangle
  // helper), so both corners are converted individually.
  const [sx1, sy1] = viewport.convertToViewportPoint(x, y);
  const [sx2, sy2] = viewport.convertToViewportPoint(x + width, y + height);
  return {
    left: Math.min(sx1, sx2),
    top: Math.min(sy1, sy2),
    width: Math.abs(sx2 - sx1),
    height: Math.abs(sy2 - sy1),
  };
}

export function screenPointToPdf(viewport, screenX, screenY) {
  return viewport.convertToPdfPoint(screenX, screenY);
}

export function screenDeltaToPdf(viewport, dx, dy) {
  const [ox, oy] = viewport.convertToPdfPoint(0, 0);
  const [px, py] = viewport.convertToPdfPoint(dx, dy);
  return [px - ox, py - oy];
}

/** Positions `node` (position:absolute inside the page layer) to match a model object's rect + rotation. */
export function positionElement(node, viewport, obj) {
  const rect = pdfRectToScreen(viewport, obj.x, obj.y, obj.width, obj.height);
  node.style.left = `${rect.left}px`;
  node.style.top = `${rect.top}px`;
  node.style.width = `${rect.width}px`;
  node.style.height = `${rect.height}px`;
  node.style.transform = obj.rotation ? `rotate(${-obj.rotation}deg)` : '';
  node.style.transformOrigin = 'left top';
  return rect;
}
