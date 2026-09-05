/**
 * The 14 standard PDF fonts (guaranteed present in every PDF viewer, no
 * embedding required) grouped by classification, using pdf-lib's exact
 * StandardFonts string values so the exporter can pass `matchedFamily`
 * straight to `pdfDoc.embedFont()`.
 */
export const STANDARD_FONT_GROUPS = {
  'sans-serif': {
    regular: 'Helvetica',
    bold: 'Helvetica-Bold',
    italic: 'Helvetica-Oblique',
    boldItalic: 'Helvetica-BoldOblique',
    cssFamily: 'Arial, Helvetica, sans-serif',
  },
  serif: {
    regular: 'Times-Roman',
    bold: 'Times-Bold',
    italic: 'Times-Italic',
    boldItalic: 'Times-BoldItalic',
    cssFamily: '"Times New Roman", Times, serif',
  },
  monospace: {
    regular: 'Courier',
    bold: 'Courier-Bold',
    italic: 'Courier-Oblique',
    boldItalic: 'Courier-BoldOblique',
    cssFamily: '"Courier New", Courier, monospace',
  },
};

export function cssFontFamilyFor(matchedFamily) {
  for (const group of Object.values(STANDARD_FONT_GROUPS)) {
    if ([group.regular, group.bold, group.italic, group.boldItalic].includes(matchedFamily)) return group.cssFamily;
  }
  return STANDARD_FONT_GROUPS['sans-serif'].cssFamily;
}
