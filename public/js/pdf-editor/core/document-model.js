import { uid } from '../utils/id.js';

/**
 * Internal document model. The UI and history engine only ever read/write
 * these structures; PDF.js is used to populate them on load and pdf-lib is
 * used to turn them back into real PDF bytes on export (see pdf/exporter.js).
 */

export const OBJECT_TYPES = {
  IMAGE: 'image',
  SHAPE: 'shape',
  DRAWING: 'drawing',
  ANNOTATION: 'annotation',
  SIGNATURE: 'signature',
  WHITEOUT: 'whiteout',
  REDACTION: 'redaction',
};

export class PdfProject {
  constructor() {
    this.id = uid('proj');
    this.fileName = 'document.pdf';
    /** @type {Map<string, {name: string, bytes: ArrayBuffer}>} source PDFs keyed by docKey: 'main' + 'import-N' for merged-in files */
    this.sources = new Map();
    /** @type {Page[]} pages in current display/export order */
    this.pages = [];
    this.metadata = { title: '', author: '', subject: '', keywords: '', creator: 'INZRA PDF Editor' };
    /** @type {Map<string, Map<string, any>>} pending AcroForm field values, keyed by docKey then field name */
    this.formValues = new Map();
    this.dirty = false;
  }
}

export class Page {
  constructor({ source, width, height, rotation = 0 }) {
    this.id = uid('page');
    /** @type {{docKey: string, pageIndex: number} | {blank: true}} where this page's content comes from */
    this.source = source;
    this.width = width;
    this.height = height;
    /** additional rotation on top of the source page's own rotation, degrees: 0/90/180/270 */
    this.rotation = rotation;
    this.isScanned = false;
    this.scanCoverage = 0;
    this.cropRect = null; // {x, y, width, height} in PDF user space, applied as the exported page's CropBox
    this.textObjects = [];
    this.objects = [];
    this.formFields = [];
  }
}

export function makeTextObject(props) {
  return {
    id: uid('text'),
    kind: props.kind || 'existing', // 'existing' (extracted from the PDF) | 'new' (added by the user)
    originalText: props.originalText ?? props.text ?? '',
    text: props.text ?? '',
    x: props.x,
    y: props.y,
    width: props.width,
    height: props.height,
    fontSize: props.fontSize ?? 12,
    fontFamily: props.fontFamily ?? 'Helvetica',
    bold: !!props.bold,
    italic: !!props.italic,
    color: props.color ?? '#000000',
    opacity: props.opacity ?? 1,
    align: props.align ?? 'left',
    letterSpacing: props.letterSpacing ?? 0,
    lineHeight: props.lineHeight ?? 1.2,
    rotation: props.rotation ?? 0,
    /** honest provenance of the font choice — see text/font-matcher.js. Never marked "exact". */
    fontMatch: props.fontMatch ?? { status: 'approx-match', matchedFamily: props.fontFamily ?? 'Helvetica', sourceName: props.sourceName ?? null },
    sourceOperatorRef: props.sourceOperatorRef ?? null,
    edited: false,
    deleted: false,
  };
}

export function makeObject(type, props) {
  return {
    id: uid(type),
    type,
    rotation: 0,
    opacity: 1,
    deleted: false,
    ...props,
  };
}

export function clonePlain(value) {
  return JSON.parse(JSON.stringify(value));
}
