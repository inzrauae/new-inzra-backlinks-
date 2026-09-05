function clamp01(n) {
  return Math.min(1, Math.max(0, n));
}

/** PDF.js text-style / graphics-state color array (1, 3 or 4 components, 0-1 range) -> CSS color. */
export function pdfColorToCss(color) {
  if (!color || !color.length) return '#000000';
  if (color.length === 1) {
    const g = Math.round(clamp01(color[0]) * 255);
    return `rgb(${g},${g},${g})`;
  }
  if (color.length === 4) {
    const [c, m, y, k] = color;
    const r = 255 * (1 - c) * (1 - k);
    const g = 255 * (1 - m) * (1 - k);
    const b = 255 * (1 - y) * (1 - k);
    return `rgb(${Math.round(r)},${Math.round(g)},${Math.round(b)})`;
  }
  const [r, g, b] = color;
  return `rgb(${Math.round(clamp01(r) * 255)},${Math.round(clamp01(g) * 255)},${Math.round(clamp01(b) * 255)})`;
}

export function hexToRgb01(hex) {
  const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex || '');
  if (!m) return { r: 0, g: 0, b: 0 };
  return { r: parseInt(m[1], 16) / 255, g: parseInt(m[2], 16) / 255, b: parseInt(m[3], 16) / 255 };
}

export function rgbToHex(r, g, b) {
  const h = (n) => Math.round(clamp01(n) * 255).toString(16).padStart(2, '0');
  return `#${h(r)}${h(g)}${h(b)}`;
}

/** CSS `rgb(r,g,b)` (as produced by pdfColorToCss) -> hex, for <input type=color>. */
export function cssRgbToHex(css) {
  const m = /rgb\((\d+),\s*(\d+),\s*(\d+)\)/.exec(css || '');
  if (!m) return '#000000';
  return rgbToHex(Number(m[1]) / 255, Number(m[2]) / 255, Number(m[3]) / 255);
}
