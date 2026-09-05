import { PdfEditorError } from './loader.js';
import { readPageContentBytes, writePageContentBytes, tokenizeContentStream, spliceInstructions, computeXObjectBoxes, TEXT_SHOW_OPS } from './content-stream.js';
import { overlapFraction } from '../utils/geometry.js';
import { hexToRgb01 } from '../utils/color.js';
import { renderPageToCanvas } from '../rendering/page-renderer.js';
import { applyFormValues } from '../forms/form-fields.js';
import * as PDFLib from './pdflib.js';

function lib() { return PDFLib; }

async function loadSourceDoc(bytes) {
  const { PDFDocument } = lib();
  return PDFDocument.load(bytes.slice(0));
}

function canvasToPngBytes(canvas) {
  return new Promise((resolve, reject) => {
    canvas.toBlob((blob) => {
      if (!blob) { reject(new Error('Could not rasterize page')); return; }
      blob.arrayBuffer().then((buf) => resolve(new Uint8Array(buf)));
    }, 'image/png');
  });
}

/**
 * Attempts genuine removal of content overlapping `rect` (PDF user-space,
 * y-up) on `outPage`, whose content stream at this point still holds only
 * the original, unmodified source content. `textRunRects` is the list of
 * this page's *raw* pdf.js text-item rects (not our grouped runs — one
 * entry per text-showing operator, same order the content stream emits
 * them in) so we can correlate rect-overlap decisions back to specific
 * operators. Falls back to flattening the whole page to an image when the
 * stream can't be safely parsed — see pdf/content-stream.js for why that
 * boundary exists.
 */
async function applyRedactionToPage({ outputDoc, outPage, pdfjsPage, rects }) {
  try {
    const bytes = readPageContentBytes(outPage);
    const { instructions, hasInlineImage } = tokenizeContentStream(bytes);
    if (hasInlineImage) throw new Error('inline images unsupported for surgical redaction');

    const textContent = await pdfjsPage.getTextContent();
    const items = textContent.items.filter((it) => typeof it.str === 'string' && it.str !== '');

    let textIndex = -1;
    const textInstrDecision = new Map(); // instruction -> keep boolean

    instructions.forEach((instr) => {
      if (!TEXT_SHOW_OPS.has(instr.op)) return;
      textIndex += 1;
      const item = items[textIndex];
      if (!item) { textInstrDecision.set(instr, true); return; }
      const [, , , , e, f] = item.transform;
      const itemRect = { x: e, y: f - (item.height || 10) * 0.2, width: item.width || 1, height: item.height || 10 };
      const hit = rects.some((r) => overlapFraction(itemRect, r) > 0.5);
      textInstrDecision.set(instr, !hit);
    });

    const xObjectBoxes = computeXObjectBoxes(instructions);
    const dropXObjectInstr = new Set();
    xObjectBoxes.forEach(({ instruction, bbox }) => {
      if (rects.some((r) => overlapFraction(bbox, r) > 0.5)) dropXObjectInstr.add(instruction);
    });

    const newBytes = spliceInstructions(bytes, instructions, (instr) => {
      if (textInstrDecision.has(instr)) return textInstrDecision.get(instr);
      if (dropXObjectInstr.has(instr)) return false;
      return true;
    });

    tokenizeContentStream(newBytes); // sanity re-parse; throws if we produced something malformed
    writePageContentBytes(outPage, newBytes);
    return { flattened: false };
  } catch {
    const { width, height } = outPage.getSize();
    const canvas = document.createElement('canvas');
    const scale = Math.min(3, 2000 / Math.max(width, height));
    await renderPageToCanvas(pdfjsPage, canvas, { scale: Math.max(scale, 1) });
    const pngBytes = await canvasToPngBytes(canvas);
    const image = await outputDoc.embedPng(pngBytes);
    const xKey = outPage.node.newXObjectKey('Redacted');
    outPage.node.setXObject(xKey, image.ref);
    const contentStr = `q ${width} 0 0 ${height} 0 0 cm /${xKey.asString()} Do Q`;
    writePageContentBytes(outPage, new TextEncoder().encode(contentStr));
    return { flattened: true };
  }
}

function resolveColor(hex, opacity) {
  const { rgb } = lib();
  const { r, g, b } = hexToRgb01(hex || '#000000');
  return { color: rgb(r, g, b), opacity: opacity == null ? 1 : opacity };
}

async function drawTextObject(outPage, obj, embedFont) {
  const font = await embedFont(obj.fontMatch?.matchedFamily || obj.fontFamily || 'Helvetica');
  const { color, opacity } = resolveColor(obj.color, obj.opacity);
  const lines = String(obj.text ?? '').split('\n');
  const lineHeight = obj.fontSize * (obj.lineHeight || 1.2);
  lines.forEach((line, idx) => {
    let x = obj.x;
    if (obj.align === 'center' || obj.align === 'right') {
      const w = font.widthOfTextAtSize(line, obj.fontSize);
      if (obj.align === 'center') x = obj.x + (obj.width - w) / 2;
      if (obj.align === 'right') x = obj.x + obj.width - w;
    }
    outPage.drawText(line, {
      x,
      y: obj.y + obj.height - obj.fontSize - idx * lineHeight,
      size: obj.fontSize,
      font,
      color,
      opacity,
      rotate: obj.rotation ? lib().degrees(-obj.rotation) : undefined,
    });
  });
}

async function drawImageObject(outputDoc, outPage, obj) {
  const bytes = obj.bytes instanceof Uint8Array ? obj.bytes : new Uint8Array(obj.bytes);
  const image = obj.mimeType === 'image/png' ? await outputDoc.embedPng(bytes) : await outputDoc.embedJpg(bytes);
  outPage.drawImage(image, {
    x: obj.x, y: obj.y, width: obj.width, height: obj.height,
    opacity: obj.opacity == null ? 1 : obj.opacity,
    rotate: obj.rotation ? lib().degrees(-obj.rotation) : undefined,
  });
}

function drawShapeObject(outPage, obj) {
  const stroke = resolveColor(obj.strokeColor || '#0F172A', obj.opacity);
  const fill = obj.fillColor ? resolveColor(obj.fillColor, obj.fillOpacity == null ? obj.opacity : obj.fillOpacity) : null;
  const common = {
    borderColor: stroke.color,
    borderWidth: obj.strokeWidth == null ? 1.5 : obj.strokeWidth,
    borderOpacity: stroke.opacity,
    opacity: fill ? fill.opacity : 0,
    color: fill ? fill.color : undefined,
    rotate: obj.rotation ? lib().degrees(-obj.rotation) : undefined,
  };
  if (obj.shapeKind === 'ellipse') {
    outPage.drawEllipse({ x: obj.x + obj.width / 2, y: obj.y + obj.height / 2, xScale: obj.width / 2, yScale: obj.height / 2, ...common });
  } else if (obj.shapeKind === 'line' || obj.shapeKind === 'arrow') {
    outPage.drawLine({ start: { x: obj.x, y: obj.y }, end: { x: obj.x + obj.width, y: obj.y + obj.height }, color: stroke.color, thickness: common.borderWidth, opacity: stroke.opacity });
    if (obj.shapeKind === 'arrow') drawArrowHead(outPage, obj, stroke);
  } else {
    outPage.drawRectangle({ x: obj.x, y: obj.y, width: obj.width, height: obj.height, ...common });
  }
}

function drawArrowHead(outPage, obj, stroke) {
  const angle = Math.atan2(obj.height, obj.width);
  const size = Math.max(8, (obj.strokeWidth || 1.5) * 4);
  const tipX = obj.x + obj.width;
  const tipY = obj.y + obj.height;
  const spread = Math.PI / 7;
  [angle + Math.PI - spread, angle + Math.PI + spread].forEach((a) => {
    outPage.drawLine({
      start: { x: tipX, y: tipY },
      end: { x: tipX + size * Math.cos(a), y: tipY + size * Math.sin(a) },
      color: stroke.color,
      thickness: stroke ? (obj.strokeWidth || 1.5) : 1.5,
      opacity: stroke.opacity,
    });
  });
}

function drawFreehandObject(outPage, obj) {
  const { color, opacity } = resolveColor(obj.strokeColor || '#0F172A', obj.opacity);
  const points = obj.points || [];
  for (let i = 1; i < points.length; i++) {
    outPage.drawLine({
      start: { x: points[i - 1].x, y: points[i - 1].y },
      end: { x: points[i].x, y: points[i].y },
      color,
      thickness: obj.strokeWidth || 2,
      opacity,
    });
  }
}

function drawAnnotationObject(outPage, obj) {
  if (obj.annotationKind === 'highlight') {
    const { color } = resolveColor(obj.color || '#FDE68A', 1);
    outPage.drawRectangle({ x: obj.x, y: obj.y, width: obj.width, height: obj.height, color, opacity: 0.4 });
  } else if (obj.annotationKind === 'underline' || obj.annotationKind === 'strikethrough') {
    const { color, opacity } = resolveColor(obj.color || '#DC2626', obj.opacity);
    const y = obj.annotationKind === 'underline' ? obj.y : obj.y + obj.height / 2;
    outPage.drawLine({ start: { x: obj.x, y }, end: { x: obj.x + obj.width, y }, color, thickness: 1.4, opacity });
  } else if (obj.annotationKind === 'note') {
    const { color } = resolveColor('#FACC15', 1);
    outPage.drawRectangle({ x: obj.x, y: obj.y, width: obj.width, height: obj.height, color, opacity: 0.9, borderColor: lib().rgb(0.6, 0.45, 0), borderWidth: 1 });
    if (obj.text) {
      outPage.drawText(obj.text, { x: obj.x + 4, y: obj.y + obj.height - 14, size: 9, color: lib().rgb(0.2, 0.15, 0), maxWidth: obj.width - 8, lineHeight: 11 });
    }
  } else if (obj.annotationKind === 'whiteout') {
    outPage.drawRectangle({ x: obj.x, y: obj.y, width: obj.width, height: obj.height, color: lib().rgb(1, 1, 1), opacity: 1 });
  }
}

async function drawSignatureObject(outputDoc, outPage, obj) {
  if (obj.mode === 'typed') {
    const font = await outputDoc.embedFont(obj.fontFamily === 'serif' ? 'Times-Italic' : 'Helvetica-Oblique');
    const { color } = resolveColor(obj.color || '#111111', 1);
    outPage.drawText(obj.text || '', { x: obj.x, y: obj.y + obj.height * 0.25, size: obj.height * 0.6, font, color });
  } else {
    await drawImageObject(outputDoc, outPage, obj);
  }
}

async function drawWatermarkObject(outputDoc, outPage, obj, embedFont) {
  if (obj.watermarkType === 'image') {
    await drawImageObject(outputDoc, outPage, obj);
  } else {
    await drawTextObject(outPage, obj, embedFont);
  }
}

async function drawPageObjects(outputDoc, outPage, modelPage, embedFont) {
  for (const t of modelPage.textObjects) {
    if (t.deleted) continue;
    if (t.kind === 'existing' && t.edited) {
      outPage.drawRectangle({ x: t.x - 1, y: t.y - 1, width: t.width + 2, height: t.height + 2, color: lib().rgb(1, 1, 1), opacity: 1 });
    }
    if (t.kind === 'existing' && !t.edited) continue; // untouched original text is already part of the copied page content
    await drawTextObject(outPage, t, embedFont);
  }

  for (const obj of modelPage.objects) {
    if (obj.deleted) continue;
    if (obj.type === 'image') await drawImageObject(outputDoc, outPage, obj);
    else if (obj.type === 'shape') drawShapeObject(outPage, obj);
    else if (obj.type === 'drawing') drawFreehandObject(outPage, obj);
    else if (obj.type === 'annotation' || obj.type === 'whiteout') drawAnnotationObject(outPage, obj.type === 'whiteout' ? { ...obj, annotationKind: 'whiteout' } : obj);
    else if (obj.type === 'signature') await drawSignatureObject(outputDoc, outPage, obj);
    else if (obj.type === 'watermark') await drawWatermarkObject(outputDoc, outPage, obj, embedFont);
  }

  for (const field of modelPage.formFields || []) {
    field.applied = true; // form values are written directly onto the pdf-lib form (see forms/form-fields.js)
  }
}

function applyMetadata(outputDoc, metadata) {
  if (!metadata) return;
  if (metadata.title) outputDoc.setTitle(metadata.title);
  if (metadata.author) outputDoc.setAuthor(metadata.author);
  if (metadata.subject) outputDoc.setSubject(metadata.subject);
  if (metadata.keywords) outputDoc.setKeywords(String(metadata.keywords).split(',').map((k) => k.trim()).filter(Boolean));
  outputDoc.setCreator(metadata.creator || 'INZRA PDF Editor');
  outputDoc.setProducer('INZRA PDF Editor (browser-only, pdf-lib)');
}

/**
 * Builds the final PDF from the document model and returns it as a Blob.
 * Never touches the network — `pdfjsDocs` and `project.sources` are the
 * in-memory bytes/documents already loaded client-side.
 */
export async function exportProject(project, { pdfjsDocs, onProgress, flattenForms = false } = {}) {
  if (project.wasEncrypted) {
    throw new PdfEditorError("This PDF was password protected. Downloading an edited version of an encrypted PDF isn't supported yet.", 'export-encrypted-unsupported');
  }

  const { PDFDocument } = lib();
  const outputDoc = await PDFDocument.create();

  const sourceDocs = new Map();
  for (const [docKey, source] of project.sources) {
    sourceDocs.set(docKey, await loadSourceDoc(source.bytes));
  }

  if (project.formValues) {
    for (const [docKey, values] of project.formValues) {
      const sourceDoc = sourceDocs.get(docKey);
      if (sourceDoc) applyFormValues(sourceDoc, values, { flatten: flattenForms });
    }
  }

  const fontCache = new Map();
  const embedFont = async (name) => {
    const key = name || 'Helvetica';
    if (!fontCache.has(key)) fontCache.set(key, outputDoc.embedFont(key));
    return fontCache.get(key);
  };

  const total = project.pages.length;
  for (let i = 0; i < total; i++) {
    const modelPage = project.pages[i];
    onProgress?.({ stage: 'pages', current: i + 1, total });

    let outPage;
    if (modelPage.source.blank) {
      outPage = outputDoc.addPage([modelPage.width, modelPage.height]);
    } else {
      const sourceDoc = sourceDocs.get(modelPage.source.docKey);
      const [copied] = await outputDoc.copyPages(sourceDoc, [modelPage.source.pageIndex]);
      outPage = outputDoc.addPage(copied);
    }

    if (modelPage.rotation) {
      const base = outPage.getRotation().angle || 0;
      outPage.setRotation(lib().degrees((base + modelPage.rotation + 360) % 360));
    }
    if (modelPage.cropRect) {
      const c = modelPage.cropRect;
      outPage.setCropBox(c.x, c.y, c.width, c.height);
    }

    const redactions = (modelPage.objects || []).filter((o) => o.type === 'redaction' && !o.deleted);
    if (redactions.length && !modelPage.source.blank) {
      const pdfjsDoc = pdfjsDocs.get(modelPage.source.docKey);
      const pdfjsPage = await pdfjsDoc.getPage(modelPage.source.pageIndex + 1);
      const rects = redactions.map((r) => ({ x: r.x, y: r.y, width: r.width, height: r.height }));
      const result = await applyRedactionToPage({ outputDoc, outPage, pdfjsPage, rects });
      modelPage._redactionFlattened = result.flattened;
    }

    await drawPageObjects(outputDoc, outPage, modelPage, embedFont);
  }

  if (flattenForms) {
    try { outputDoc.getForm().flatten(); } catch { /* no form, or a field type flatten() can't handle — leave as-is */ }
  }

  applyMetadata(outputDoc, project.metadata);

  const bytes = await outputDoc.save();
  return { blob: new Blob([bytes], { type: 'application/pdf' }), flattenedPages: project.pages.filter((p) => p._redactionFlattened).map((p) => p.id) };
}
