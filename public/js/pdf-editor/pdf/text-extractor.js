import { pdfjsLib } from './loader.js';
import { pdfColorToCss } from '../utils/color.js';

const BOLD_RE = /bold|black|heavy|semibold|demibold/i;
const ITALIC_RE = /italic|oblique/i;

function classifyFamily(genericFamily, fontName) {
  const probe = `${fontName || ''} ${genericFamily || ''}`;
  if (/mono|courier|consol/i.test(probe) || genericFamily === 'monospace') return 'monospace';
  if (/times|georgia|garamond|cambria|book|serif/i.test(probe) || genericFamily === 'serif') return 'serif';
  return 'sans-serif';
}

/**
 * Best-effort fill-color-per-text-run detection: PDF.js's public
 * getTextContent() API doesn't expose color, so we walk the raw operator
 * list ourselves, track the fill color graphics state, and record it at
 * each text-showing operator. This is correlated against getTextContent's
 * items by order below — reliable for the common case (one showText op
 * per text run), not guaranteed for exotic content streams, which is why
 * every text object still exposes its color as an editable swatch rather
 * than presenting this as infallible.
 */
async function extractFillColorsInOrder(pdfPage) {
  const opList = await pdfPage.getOperatorList();
  const { OPS } = pdfjsLib;
  const colors = [];
  let current = [0, 0, 0];

  for (let i = 0; i < opList.fnArray.length; i++) {
    const fn = opList.fnArray[i];
    const args = opList.argsArray[i];
    if (fn === OPS.setFillRGBColor || fn === OPS.setFillCMYKColor) {
      current = args;
    } else if (fn === OPS.setFillGray) {
      current = [args[0]];
    } else if ((fn === OPS.setFillColorN || fn === OPS.setFillColor) && Array.isArray(args) && args.length && typeof args[0] === 'number') {
      current = args;
    } else if (fn === OPS.showText || fn === OPS.showSpacedText) {
      colors.push(pdfColorToCss(current));
    }
  }
  return colors;
}

/**
 * Groups PDF.js text items into editable "runs" (a run is what a user
 * perceives as one clickable piece of text — usually a word, phrase or
 * line fragment sharing font/size/baseline), with real detected bounding
 * boxes, font metrics and (best-effort) color.
 */
export async function analyzePage(pdfPage) {
  const [textContent, opColors] = await Promise.all([
    pdfPage.getTextContent(),
    extractFillColorsInOrder(pdfPage).catch(() => []),
  ]);
  const styles = textContent.styles || {};
  const items = textContent.items.filter((it) => typeof it.str === 'string');

  let meaningfulChars = 0;
  const runs = [];
  let current = null;

  items.forEach((item, index) => {
    if (item.str.trim()) meaningfulChars += item.str.trim().length;
    if (!item.str) {
      if (item.hasEOL && current) { runs.push(current); current = null; }
      return;
    }

    const style = styles[item.fontName] || {};
    const [a, b, c, d, e, f] = item.transform;
    const fontSize = Math.hypot(c, d) || Math.hypot(a, b) || 12;
    const baselineX = e;
    const baselineY = f;
    const bold = BOLD_RE.test(item.fontName || '') || BOLD_RE.test(style.fontFamily || '');
    const italic = ITALIC_RE.test(item.fontName || '') || (Math.abs(b) > 1e-6 && Math.abs(a) > 1e-6);
    const family = classifyFamily(style.fontFamily, item.fontName);
    const color = opColors[index] || opColors[opColors.length - 1] || '#000000';

    const sameRun = current
      && current.fontName === item.fontName
      && Math.abs(current.baselineY - baselineY) < fontSize * 0.35
      && Math.abs(current.fontSize - fontSize) < 0.6
      && (baselineX - current.endX) < fontSize * 0.9
      && (baselineX - current.endX) > -fontSize * 0.6;

    if (sameRun) {
      current.text += item.str;
      current.endX = baselineX + item.width;
      current.height = Math.max(current.height, item.height || fontSize * 1.15);
    } else {
      if (current) runs.push(current);
      current = {
        text: item.str,
        fontName: item.fontName,
        fontSize,
        bold,
        italic,
        family,
        color,
        x: baselineX,
        y: baselineY - fontSize * 0.22,
        endX: baselineX + item.width,
        height: item.height || fontSize * 1.15,
      };
    }

    if (item.hasEOL) { runs.push(current); current = null; }
  });
  if (current) runs.push(current);

  const textRuns = runs
    .filter((r) => r.text.trim() !== '')
    .map((r) => ({
      text: r.text,
      x: r.x,
      y: r.y,
      width: Math.max(1, r.endX - r.x),
      height: r.height,
      fontSize: r.fontSize,
      bold: r.bold,
      italic: r.italic,
      family: r.family,
      fontName: r.fontName,
      color: r.color,
    }));

  const hasImageOp = await pageHasImageOperator(pdfPage);
  const isScanned = meaningfulChars < 5 && hasImageOp;

  return { textRuns, isScanned };
}

async function pageHasImageOperator(pdfPage) {
  const opList = await pdfPage.getOperatorList();
  const { OPS } = pdfjsLib;
  return opList.fnArray.some((fn) => fn === OPS.paintImageXObject || fn === OPS.paintInlineImageXObject || fn === OPS.paintJpegXObject);
}
