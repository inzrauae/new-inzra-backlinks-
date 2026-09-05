import { STANDARD_FONT_GROUPS } from './font-catalog.js';

/**
 * Picks the closest available standard PDF font for a detected text run.
 * This is always an approximation — the editor never claims to have
 * extracted and re-embedded the document's actual font program, since
 * that isn't something that can be done reliably for arbitrary PDFs from
 * the browser. `fontMatch.status` stays 'approx-match' for every text
 * object so the UI can be honest about it (see properties panel).
 */
export function matchFont({ family, bold, italic, fontName }) {
  const group = STANDARD_FONT_GROUPS[family] || STANDARD_FONT_GROUPS['sans-serif'];
  const matchedFamily = bold && italic ? group.boldItalic : bold ? group.bold : italic ? group.italic : group.regular;
  return {
    status: 'approx-match',
    matchedFamily,
    sourceName: fontName || null,
  };
}

/** Re-derives the standard font name for a text object after the user toggles bold/italic in the UI. */
export function standardFontFor(matchedFamily, bold, italic) {
  const group = Object.values(STANDARD_FONT_GROUPS).find((g) => [g.regular, g.bold, g.italic, g.boldItalic].includes(matchedFamily))
    || STANDARD_FONT_GROUPS['sans-serif'];
  return bold && italic ? group.boldItalic : bold ? group.bold : italic ? group.italic : group.regular;
}
