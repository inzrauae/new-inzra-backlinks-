/* PDF.js is vendored locally (public/js/pdf-editor/vendor/) rather than
   loaded from a CDN. This isn't a style choice: PDF.js needs to spin up its
   parser in a Web Worker, and browsers refuse `new Worker(crossOriginURL)`
   with a SecurityError regardless of CORS headers — worker scripts must be
   same-origin. A CDN-hosted worker would fail in every real browser, not
   just this one, so the worker (and, for simplicity, the main pdf.js
   module alongside it) are served from this site instead. */
import * as pdfjsLib from '../vendor/pdf.min.mjs';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL('../vendor/pdf.worker.min.mjs', import.meta.url).href;

export { pdfjsLib };

export class PdfEditorError extends Error {
  constructor(message, code) {
    super(message);
    this.name = 'PdfEditorError';
    this.code = code;
  }
}

const MAX_BYTES = 300 * 1024 * 1024;

export function readFileAsArrayBuffer(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = () => reject(reader.error);
    reader.readAsArrayBuffer(file);
  });
}

/**
 * Opens a PDF entirely in-browser via PDF.js. `onPasswordNeeded(isRetry)`
 * is called (and awaited) when the file is encrypted; return a password
 * string to retry, or a falsy value to cancel. Never touches the network
 * with the file's bytes — only the PDF.js/worker scripts themselves are
 * fetched, once, from the CDN.
 */
export async function openPdf(arrayBuffer, { onPasswordNeeded } = {}) {
  if (!arrayBuffer || arrayBuffer.byteLength === 0) {
    throw new PdfEditorError('This file is empty.', 'empty');
  }
  if (arrayBuffer.byteLength > MAX_BYTES) {
    throw new PdfEditorError('This PDF is too large for browser-based editing (over 300MB).', 'too-large');
  }

  const loadingTask = pdfjsLib.getDocument({
    data: new Uint8Array(arrayBuffer.slice(0)),
    isEvalSupported: false,
  });

  loadingTask.onPassword = (updatePassword, reason) => {
    const isRetry = reason === pdfjsLib.PasswordResponses.INCORRECT_PASSWORD;
    Promise.resolve(onPasswordNeeded ? onPasswordNeeded(isRetry) : null).then((password) => {
      if (password) updatePassword(password);
      else loadingTask.destroy();
    });
  };

  try {
    return await loadingTask.promise;
  } catch (err) {
    if (err && err.name === 'PasswordException') {
      throw new PdfEditorError('This PDF is password protected.', 'password-required');
    }
    if (err && err.name === 'InvalidPDFException') {
      throw new PdfEditorError('This file is not a valid PDF, or it is corrupted.', 'invalid');
    }
    throw new PdfEditorError(`This PDF could not be opened: ${err && err.message ? err.message : 'unknown error'}`, 'unknown');
  }
}
