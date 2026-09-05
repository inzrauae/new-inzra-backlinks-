export function clamp(value, min, max) {
  return Math.min(max, Math.max(min, value));
}

export function rectsIntersect(a, b) {
  return a.x < b.x + b.width && a.x + a.width > b.x && a.y < b.y + b.height && a.y + a.height > b.y;
}

/**
 * Fraction of `inner`'s area that overlaps `outer`. Used by the redaction
 * tool to decide whether a content-stream operator's bbox counts as
 * "inside" the redaction rectangle.
 */
export function overlapFraction(inner, outer) {
  const x1 = Math.max(inner.x, outer.x);
  const y1 = Math.max(inner.y, outer.y);
  const x2 = Math.min(inner.x + inner.width, outer.x + outer.width);
  const y2 = Math.min(inner.y + inner.height, outer.y + outer.height);
  const overlapW = Math.max(0, x2 - x1);
  const overlapH = Math.max(0, y2 - y1);
  const innerArea = Math.max(1e-6, inner.width * inner.height);
  return (overlapW * overlapH) / innerArea;
}

export function snap(value, step) {
  return Math.round(value / step) * step;
}
