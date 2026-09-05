/**
 * A small, self-contained PDF content-stream tokenizer/splicer used by the
 * redaction tool to genuinely remove operators (not just draw over them).
 *
 * pdf-lib does not expose a public API to parse an *existing* page's
 * content stream into a mutable operator list (verified against its
 * published type declarations for 1.17.1 — it only builds new streams from
 * scratch). This module fills that gap with the minimum needed to safely
 * find and excise specific text-showing / XObject-drawing instructions:
 * it never re-generates PDF syntax, it only splices out whole byte ranges
 * of a decoded content stream and leaves everything else byte-for-byte
 * untouched, which keeps the risk of corrupting the output low. Anything
 * it doesn't recognize causes it to throw rather than guess, so callers
 * can fall back to a safe alternative (see redaction/redact-tool.js).
 */
import * as PDFLib from './pdflib.js';

const WHITESPACE = new Set([0x00, 0x09, 0x0a, 0x0c, 0x0d, 0x20]);
const DELIMS = new Set([0x28, 0x29, 0x3c, 0x3e, 0x5b, 0x5d, 0x7b, 0x7d, 0x2f, 0x25]);

function isWhitespace(b) { return b !== undefined && WHITESPACE.has(b); }
function isDelim(b) { return b !== undefined && DELIMS.has(b); }
function isRegular(b) { return b !== undefined && !isWhitespace(b) && !isDelim(b); }

function latin1(bytes, start, end) {
  let s = '';
  for (let k = start; k < end; k++) s += String.fromCharCode(bytes[k]);
  return s;
}

export class ContentStreamParseError extends Error {}

export function tokenizeContentStream(bytes) {
  let i = 0;
  const n = bytes.length;
  const instructions = [];
  let operands = [];
  let instrStart = null;
  let hasInlineImage = false;

  function skipWs() {
    while (i < n) {
      if (isWhitespace(bytes[i])) { i++; continue; }
      if (bytes[i] === 0x25) { while (i < n && bytes[i] !== 0x0a && bytes[i] !== 0x0d) i++; continue; }
      break;
    }
  }

  function readNumber() {
    const start = i;
    if (bytes[i] === 0x2b || bytes[i] === 0x2d) i++;
    let saw = false;
    while (i < n && ((bytes[i] >= 0x30 && bytes[i] <= 0x39) || bytes[i] === 0x2e)) { i++; saw = true; }
    if (!saw) throw new ContentStreamParseError(`Invalid number at byte ${start}`);
    return { type: 'num', value: parseFloat(latin1(bytes, start, i)), start, end: i };
  }

  function readName() {
    const start = i;
    i++;
    const nameStart = i;
    while (i < n && isRegular(bytes[i])) i++;
    return { type: 'name', value: latin1(bytes, nameStart, i), start, end: i };
  }

  function readLiteralString() {
    const start = i;
    i++;
    let depth = 1;
    while (i < n && depth > 0) {
      const b = bytes[i];
      if (b === 0x5c) { i += 2; continue; }
      if (b === 0x28) depth++;
      else if (b === 0x29) depth--;
      i++;
    }
    if (depth !== 0) throw new ContentStreamParseError(`Unterminated literal string at byte ${start}`);
    return { type: 'str', start, end: i };
  }

  function readDict() {
    const start = i;
    i += 2;
    let depth = 1;
    while (i < n && depth > 0) {
      if (bytes[i] === 0x28) { readLiteralString(); continue; }
      if (bytes[i] === 0x3c && bytes[i + 1] === 0x3c) { depth++; i += 2; continue; }
      if (bytes[i] === 0x3c) { readHexStringOrDict(); continue; }
      if (bytes[i] === 0x3e && bytes[i + 1] === 0x3e) { depth--; i += 2; continue; }
      i++;
    }
    if (depth !== 0) throw new ContentStreamParseError(`Unterminated dict at byte ${start}`);
    return { type: 'dict', start, end: i };
  }

  function readHexStringOrDict() {
    const start = i;
    if (bytes[i + 1] === 0x3c) return readDict();
    i++;
    while (i < n && bytes[i] !== 0x3e) i++;
    if (i >= n) throw new ContentStreamParseError(`Unterminated hex string at byte ${start}`);
    i++;
    return { type: 'hexstr', start, end: i };
  }

  function readArray() {
    const start = i;
    i++;
    const items = [];
    while (true) {
      skipWs();
      if (i >= n) throw new ContentStreamParseError(`Unterminated array at byte ${start}`);
      if (bytes[i] === 0x5d) { i++; break; }
      items.push(readOperand());
    }
    return { type: 'array', start, end: i, items };
  }

  function readOperand() {
    const b = bytes[i];
    if (b === 0x2f) return readName();
    if (b === 0x28) return readLiteralString();
    if (b === 0x3c) return readHexStringOrDict();
    if (b === 0x5b) return readArray();
    if (b === 0x2d || b === 0x2b || b === 0x2e || (b >= 0x30 && b <= 0x39)) return readNumber();
    throw new ContentStreamParseError(`Unexpected byte 0x${b.toString(16)} at ${i}`);
  }

  function readKeyword() {
    const start = i;
    while (i < n && isRegular(bytes[i])) i++;
    return latin1(bytes, start, i);
  }

  while (i < n) {
    skipWs();
    if (i >= n) break;
    const b = bytes[i];

    if (b === 0x2f || b === 0x28 || b === 0x3c || b === 0x5b) {
      if (instrStart === null) instrStart = i;
      operands.push(readOperand());
      continue;
    }
    if (b === 0x5d || b === 0x3e || b === 0x7b || b === 0x7d) {
      throw new ContentStreamParseError(`Unexpected delimiter 0x${b.toString(16)} at ${i}`);
    }

    if (instrStart === null) instrStart = i;

    if (b === 0x2d || b === 0x2b || b === 0x2e || (b >= 0x30 && b <= 0x39)) {
      operands.push(readNumber());
      continue;
    }

    const keyword = readKeyword();
    if (keyword === '') throw new ContentStreamParseError(`Stuck at byte ${i}`);

    if (keyword === 'BI') {
      hasInlineImage = true;
      while (true) {
        skipWs();
        if (i >= n) throw new ContentStreamParseError('Unterminated inline image (no ID)');
        if (bytes[i] === 0x49 && bytes[i + 1] === 0x44) { i += 2; break; }
        readOperand();
      }
      if (isWhitespace(bytes[i])) i++;
      let found = -1;
      for (let j = i; j < n - 1; j++) {
        if (bytes[j] === 0x45 && bytes[j + 1] === 0x49
          && (j === 0 || isWhitespace(bytes[j - 1]))
          && (j + 2 >= n || isWhitespace(bytes[j + 2]) || isDelim(bytes[j + 2]))) {
          found = j;
          break;
        }
      }
      if (found === -1) throw new ContentStreamParseError('Unterminated inline image (no EI)');
      i = found + 2;
      instructions.push({ op: 'INLINE_IMAGE', args: [], start: instrStart, end: i });
      operands = [];
      instrStart = null;
      continue;
    }

    instructions.push({ op: keyword, args: operands, start: instrStart, end: i });
    operands = [];
    instrStart = null;
  }

  if (operands.length) throw new ContentStreamParseError('Trailing operands with no operator at end of stream');

  return { instructions, hasInlineImage };
}

/** Concatenates `bytes` with the byte ranges of instructions failing `keep(instr, index)` removed. */
export function spliceInstructions(bytes, instructions, keep) {
  const parts = [];
  let cursor = 0;
  instructions.forEach((instr, index) => {
    if (keep(instr, index)) return;
    parts.push(bytes.subarray(cursor, instr.start));
    cursor = instr.end;
  });
  parts.push(bytes.subarray(cursor));
  const total = parts.reduce((sum, p) => sum + p.length, 0);
  const out = new Uint8Array(total);
  let offset = 0;
  for (const part of parts) { out.set(part, offset); offset += part.length; }
  return out;
}

function multiplyMatrix(m1, m2) {
  const [a1, b1, c1, d1, e1, f1] = m1;
  const [a2, b2, c2, d2, e2, f2] = m2;
  return [
    a1 * a2 + b1 * c2,
    a1 * b2 + b1 * d2,
    c1 * a2 + d1 * c2,
    c1 * b2 + d1 * d2,
    e1 * a2 + f1 * c2 + e2,
    e1 * b2 + f1 * d2 + f2,
  ];
}

function applyMatrix([a, b, c, d, e, f], x, y) {
  return [a * x + c * y + e, b * x + d * y + f];
}

/** Walks q/Q/cm/Do to compute each image XObject's on-page bounding box in PDF user space. */
export function computeXObjectBoxes(instructions) {
  const IDENTITY = [1, 0, 0, 1, 0, 0];
  let ctm = IDENTITY;
  const stack = [];
  const boxes = [];

  for (const instr of instructions) {
    if (instr.op === 'q') stack.push(ctm);
    else if (instr.op === 'Q') ctm = stack.pop() || IDENTITY;
    else if (instr.op === 'cm') {
      const nums = instr.args.filter((a) => a.type === 'num').map((a) => a.value);
      if (nums.length === 6) ctm = multiplyMatrix(nums, ctm);
    } else if (instr.op === 'Do') {
      const corners = [[0, 0], [1, 0], [1, 1], [0, 1]].map(([x, y]) => applyMatrix(ctm, x, y));
      const xs = corners.map((c) => c[0]);
      const ys = corners.map((c) => c[1]);
      const x = Math.min(...xs);
      const y = Math.min(...ys);
      const nameArg = instr.args.find((a) => a.type === 'name');
      boxes.push({
        instruction: instr,
        name: nameArg ? nameArg.value : null,
        bbox: { x, y, width: Math.max(...xs) - x, height: Math.max(...ys) - y },
      });
    }
  }
  return boxes;
}

export const TEXT_SHOW_OPS = new Set(['Tj', 'TJ', "'", '"']);

/** Reads and fully decodes an existing page's content stream(s) into one Uint8Array. */
export function readPageContentBytes(page) {
  const { decodePDFRawStream } = PDFLib;
  page.node.normalize();
  const entries = page.node.normalizedEntries();
  const contents = entries.Contents;
  if (!contents || contents.size() === 0) return new Uint8Array(0);

  const chunks = [];
  for (let idx = 0; idx < contents.size(); idx++) {
    const ref = contents.get(idx);
    const streamObj = page.doc.context.lookup(ref);
    // A copied/normalized page's content entries can be either a plain
    // PDFRawStream (needs decodePDFRawStream to reverse its /Filter) or
    // pdf-lib's own operator-based PDFContentStream (produced by page
    // copying/merging), which already exposes its bytes directly.
    const decoded = typeof streamObj.getUnencodedContents === 'function'
      ? streamObj.getUnencodedContents()
      : decodePDFRawStream(streamObj).decode();
    chunks.push(decoded);
    if (idx < contents.size() - 1) chunks.push(new Uint8Array([0x0a]));
  }
  const total = chunks.reduce((sum, c) => sum + c.length, 0);
  const out = new Uint8Array(total);
  let offset = 0;
  for (const chunk of chunks) { out.set(chunk, offset); offset += chunk.length; }
  return out;
}

/**
 * Replaces a page's content stream wholesale with `newBytes` (uncompressed
 * — simpler and always valid), and deletes the old content-stream object(s)
 * from the document rather than leaving them as orphaned but still-present
 * bytes in the saved file — otherwise redacted content would still be
 * recoverable by inspecting the raw file, even though no normal viewer or
 * text extractor would ever surface it.
 */
export function writePageContentBytes(page, newBytes) {
  const { PDFName } = PDFLib;
  page.node.normalize();
  const oldContents = page.node.normalizedEntries().Contents;
  const oldRefs = oldContents
    ? Array.from({ length: oldContents.size() }, (_, i) => oldContents.get(i))
    : [];

  const stream = page.doc.context.stream(newBytes);
  const ref = page.doc.context.register(stream);
  page.node.set(PDFName.of('Contents'), ref);

  oldRefs.forEach((oldRef) => {
    try { page.doc.context.delete(oldRef); } catch { /* best-effort cleanup */ }
  });
}
