/* ============================================================
   INZRA — Online Image Converter
   ------------------------------------------------------------
   All conversion happens in the browser via the Canvas API.
   Nothing here ever sends image data anywhere.

   01. Capability detection
   02. Format signature detection
   03. State
   04. Validation
   05. Image decoding + conversion
   06. Card rendering
   07. Batch conversion
   08. Download / ZIP
   09. Event wiring
   ============================================================ */

(function () {
  'use strict';

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  const app = $('#convApp');
  if (!app) return;

  const MAX_FILE_SIZE = 25 * 1024 * 1024; // 25MB

  /* ==========================================================
     01. CAPABILITY DETECTION
     ========================================================== */
  function canEncode(mimeType) {
    try {
      const canvas = document.createElement('canvas');
      canvas.width = 1;
      canvas.height = 1;
      const dataUrl = canvas.toDataURL(mimeType);
      return dataUrl.indexOf(`data:${mimeType}`) === 0;
    } catch (e) {
      return false;
    }
  }

  const SUPPORT = {
    webp: canEncode('image/webp'),
    avif: canEncode('image/avif'),
  };

  const OUTPUT_FORMATS = [
    { value: 'image/jpeg', label: 'JPG', ext: 'jpg', lossy: true },
    { value: 'image/png', label: 'PNG', ext: 'png', lossy: false },
  ];
  if (SUPPORT.webp) OUTPUT_FORMATS.push({ value: 'image/webp', label: 'WebP', ext: 'webp', lossy: true });
  if (SUPPORT.avif) OUTPUT_FORMATS.push({ value: 'image/avif', label: 'AVIF', ext: 'avif', lossy: true });

  const canPaste = !!(navigator.clipboard && window.ClipboardItem);

  /* ==========================================================
     02. FORMAT SIGNATURE DETECTION (magic bytes, not filename)
     ========================================================== */
  function detectFormat(bytes) {
    const b = bytes;
    if (b.length >= 8 && b[0] === 0x89 && b[1] === 0x50 && b[2] === 0x4E && b[3] === 0x47) return 'PNG';
    if (b.length >= 3 && b[0] === 0xFF && b[1] === 0xD8 && b[2] === 0xFF) return 'JPEG';
    if (b.length >= 6 && b[0] === 0x47 && b[1] === 0x49 && b[2] === 0x46) return 'GIF';
    if (b.length >= 2 && b[0] === 0x42 && b[1] === 0x4D) return 'BMP';
    if (b.length >= 12 && b[8] === 0x57 && b[9] === 0x45 && b[10] === 0x42 && b[11] === 0x50) return 'WEBP';
    if (b.length >= 12 && b[4] === 0x66 && b[5] === 0x74 && b[6] === 0x79 && b[7] === 0x70) return 'AVIF/HEIF';
    return null;
  }

  async function readSignature(file) {
    const buf = await file.slice(0, 16).arrayBuffer();
    return detectFormat(new Uint8Array(buf));
  }

  /* ==========================================================
     03. STATE
     ========================================================== */
  let entries = [];
  let entrySeq = 0;

  function showError(message) {
    const el = $('#convError');
    el.textContent = message;
    el.hidden = false;
    setTimeout(() => { el.hidden = true; }, 6000);
  }

  function formatBytes(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
  }

  /* ==========================================================
     04. VALIDATION
     ========================================================== */
  async function validateFile(file) {
    if (file.size === 0) return 'This file is empty.';
    if (file.size > MAX_FILE_SIZE) return `This file is too large (${formatBytes(file.size)}). Max size is ${formatBytes(MAX_FILE_SIZE)}.`;

    const signature = await readSignature(file);
    if (!signature) return 'Unable to read this image — it may be corrupted or not actually an image file.';

    return null;
  }

  /* ==========================================================
     05. IMAGE DECODING + CONVERSION
     ========================================================== */
  async function decodeImage(file) {
    if ('createImageBitmap' in window) {
      try {
        return await createImageBitmap(file, { imageOrientation: 'from-image' });
      } catch (e) {
        // fall through to <img> based decode
      }
    }

    return new Promise((resolve, reject) => {
      const url = URL.createObjectURL(file);
      const img = new Image();
      img.onload = () => { resolve(img); URL.revokeObjectURL(url); };
      img.onerror = () => { reject(new Error('decode failed')); URL.revokeObjectURL(url); };
      img.src = url;
    });
  }

  function computeOutputSize(entry, sourceWidth, sourceHeight) {
    const mode = $('#convResizeMode').value;

    if (mode === 'percent') {
      const pct = Number($('#convPercent').value) / 100;
      return { width: Math.max(1, Math.round(sourceWidth * pct)), height: Math.max(1, Math.round(sourceHeight * pct)) };
    }

    if (mode === 'custom') {
      const w = Number($('#convWidth').value);
      const h = Number($('#convHeight').value);
      if (w > 0 && h > 0) return { width: w, height: h };
      if (w > 0) return { width: w, height: Math.max(1, Math.round((w / sourceWidth) * sourceHeight)) };
      if (h > 0) return { width: Math.max(1, Math.round((h / sourceHeight) * sourceWidth)), height: h };
    }

    return { width: sourceWidth, height: sourceHeight };
  }

  function convertEntry(entry) {
    return decodeImage(entry.file).then(image => {
      const sourceWidth = image.width || image.naturalWidth;
      const sourceHeight = image.height || image.naturalHeight;
      entry.originalWidth = sourceWidth;
      entry.originalHeight = sourceHeight;

      const { width, height } = computeOutputSize(entry, sourceWidth, sourceHeight);
      const format = $('#convFormat').value;
      const formatDef = OUTPUT_FORMATS.find(f => f.value === format);

      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext('2d');

      // JPG/BMP have no alpha channel — fill a background first so
      // transparent areas don't render as black.
      if (format === 'image/jpeg') {
        let bg = $('#convBackground').value;
        if (bg === 'custom') bg = $('#convBackgroundCustom').value;
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, height);
      }

      ctx.drawImage(image, 0, 0, width, height);

      if (image.close) image.close();

      const quality = formatDef && formatDef.lossy ? Number($('#convQuality').value) / 100 : undefined;

      return new Promise((resolve, reject) => {
        canvas.toBlob(blob => {
          if (!blob) { reject(new Error('Conversion failed. Please try another image.')); return; }
          resolve({ blob, width, height, ext: formatDef ? formatDef.ext : 'jpg' });
        }, format, quality);
      });
    });
  }

  /* ==========================================================
     06. CARD RENDERING
     ========================================================== */
  const cardTemplate = $('#convCardTemplate');
  const grid = $('#convGrid');

  function baseName(filename) {
    const idx = filename.lastIndexOf('.');
    return idx === -1 ? filename : filename.slice(0, idx);
  }

  function originalMetaText(entry) {
    const parts = [entry.signature || entry.file.type || 'unknown'];
    if (entry.originalWidth) parts.push(`${entry.originalWidth}×${entry.originalHeight}`);
    parts.push(formatBytes(entry.file.size));
    return parts.join(' · ');
  }

  function renderCard(entry) {
    const node = cardTemplate.content.firstElementChild.cloneNode(true);
    node.dataset.id = entry.id;
    node.querySelector('.conv-card__name').textContent = entry.file.name;

    const thumbImg = node.querySelector('.conv-card__thumb img');
    thumbImg.src = entry.previewUrl;
    thumbImg.alt = entry.file.name;

    node.querySelector('.conv-card__meta--original').textContent = originalMetaText(entry);

    node.querySelector('.conv-card__status').textContent = 'Ready to convert';

    node.querySelector('.conv-card__remove').addEventListener('click', () => removeEntry(entry.id));
    node.querySelector('.conv-card__download').addEventListener('click', () => {
      if (entry.convertedBlob) downloadBlob(entry.convertedBlob, entry.convertedName);
    });

    entry.el = node;
    grid.appendChild(node);
  }

  function updateCard(entry) {
    if (!entry.el) return;
    const statusEl = entry.el.querySelector('.conv-card__status');
    const resultMetaEl = entry.el.querySelector('.conv-card__meta--result');
    const downloadBtn = entry.el.querySelector('.conv-card__download');

    entry.el.classList.toggle('is-error', entry.status === 'error');

    if (entry.status === 'converting') {
      statusEl.textContent = 'Converting…';
    } else if (entry.status === 'done') {
      const savedPct = entry.file.size > 0
        ? Math.round((1 - entry.convertedSize / entry.file.size) * 100)
        : 0;
      statusEl.textContent = 'Converted';
      resultMetaEl.hidden = false;
      resultMetaEl.textContent = `→ ${formatBytes(entry.convertedSize)} (${savedPct >= 0 ? savedPct + '% smaller' : Math.abs(savedPct) + '% larger'})`;
      downloadBtn.hidden = false;
      downloadBtn.href = entry.convertedUrl;
      downloadBtn.download = entry.convertedName;
    } else if (entry.status === 'error') {
      statusEl.textContent = entry.errorMessage || 'Conversion failed.';
    }
  }

  function removeEntry(id) {
    const entry = entries.find(e => e.id === id);
    if (!entry) return;
    if (entry.previewUrl) URL.revokeObjectURL(entry.previewUrl);
    if (entry.convertedUrl) URL.revokeObjectURL(entry.convertedUrl);
    if (entry.el) entry.el.remove();
    entries = entries.filter(e => e.id !== id);
    refreshActionsVisibility();
  }

  function clearAll() {
    entries.forEach(entry => {
      if (entry.previewUrl) URL.revokeObjectURL(entry.previewUrl);
      if (entry.convertedUrl) URL.revokeObjectURL(entry.convertedUrl);
    });
    entries = [];
    grid.innerHTML = '';
    refreshActionsVisibility();
  }

  function refreshActionsVisibility() {
    const hasEntries = entries.length > 0;
    $('#convSettings').hidden = !hasEntries;
    $('#convActions').hidden = !hasEntries;
    $('#convDownloadAllBtn').hidden = !entries.some(e => e.status === 'done');
  }

  /* ==========================================================
     07. BATCH CONVERSION
     ========================================================== */
  async function addFiles(fileList) {
    const files = Array.from(fileList);

    for (const file of files) {
      if (!file.type.startsWith('image/') && !/\.(jpe?g|png|webp|gif|bmp|avif)$/i.test(file.name)) {
        showError(`"${file.name}" is not an image file.`);
        continue;
      }

      const error = await validateFile(file);
      if (error) {
        showError(`${file.name}: ${error}`);
        continue;
      }

      const signature = await readSignature(file);
      const entry = {
        id: ++entrySeq,
        file,
        signature,
        previewUrl: URL.createObjectURL(file),
        status: 'pending',
        el: null,
      };
      entries.push(entry);
      renderCard(entry);
      probeDimensions(entry);
    }

    refreshActionsVisibility();
  }

  function probeDimensions(entry) {
    const img = new Image();
    img.onload = () => {
      entry.originalWidth = img.naturalWidth;
      entry.originalHeight = img.naturalHeight;
      if (entry.el) {
        entry.el.querySelector('.conv-card__meta--original').textContent = originalMetaText(entry);
      }
    };
    img.src = entry.previewUrl;
  }

  async function convertAll() {
    const pending = entries.slice(); // re-convert everything with the current settings
    const progressEl = $('#convProgress');
    let done = 0;

    for (const entry of pending) {
      entry.status = 'converting';
      updateCard(entry);
      progressEl.textContent = `Converting ${done + 1} / ${pending.length}`;

      try {
        const result = await convertEntry(entry);
        if (entry.convertedUrl) URL.revokeObjectURL(entry.convertedUrl);

        entry.convertedBlob = result.blob;
        entry.convertedSize = result.blob.size;
        entry.convertedUrl = URL.createObjectURL(result.blob);
        entry.convertedName = `${baseName(entry.file.name)}.${result.ext}`;
        entry.status = 'done';
      } catch (err) {
        entry.status = 'error';
        entry.errorMessage = err && err.message ? err.message : 'Conversion failed. Please try another image.';
      }

      updateCard(entry);
      done++;
      // Yield to the browser so the UI stays responsive between images.
      await new Promise(r => setTimeout(r, 0));
    }

    progressEl.textContent = `Converted ${done} of ${pending.length} image${pending.length === 1 ? '' : 's'}.`;
    refreshActionsVisibility();
  }

  /* ==========================================================
     08. DOWNLOAD / ZIP
     ========================================================== */
  function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  async function downloadAllAsZip() {
    if (typeof JSZip === 'undefined') {
      showError('ZIP download isn’t available right now — try downloading images individually.');
      return;
    }

    const done = entries.filter(e => e.status === 'done');
    if (done.length === 0) return;

    const zip = new JSZip();
    const usedNames = new Set();

    done.forEach(entry => {
      let name = entry.convertedName;
      let i = 1;
      while (usedNames.has(name)) {
        name = `${baseName(entry.convertedName)}-${i}.${entry.convertedName.split('.').pop()}`;
        i++;
      }
      usedNames.add(name);
      zip.file(name, entry.convertedBlob);
    });

    const zipBlob = await zip.generateAsync({ type: 'blob' });
    downloadBlob(zipBlob, 'converted-images.zip');
  }

  /* ==========================================================
     09. EVENT WIRING
     ========================================================== */
  function populateFormatSelect() {
    const select = $('#convFormat');
    OUTPUT_FORMATS.forEach(f => {
      const option = document.createElement('option');
      option.value = f.value;
      option.textContent = f.label;
      select.appendChild(option);
    });
    if (SUPPORT.webp) select.value = 'image/webp';
  }

  function updateFormatDependentUI() {
    const format = $('#convFormat').value;
    const formatDef = OUTPUT_FORMATS.find(f => f.value === format);
    $('#convQualityGroup').hidden = !(formatDef && formatDef.lossy);
    $('#convBgGroup').hidden = format !== 'image/jpeg';
  }

  function init() {
    app.hidden = false;
    populateFormatSelect();
    updateFormatDependentUI();

    if (canPaste) $('#convPasteBtn').hidden = false;

    const drop = $('#convDrop');
    const fileInput = $('#convFileInput');

    $('#convChooseBtn').addEventListener('click', () => fileInput.click());
    drop.addEventListener('click', (e) => {
      if (e.target.closest('button')) return;
      fileInput.click();
    });
    drop.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
    });

    fileInput.addEventListener('change', (e) => {
      if (e.target.files.length) addFiles(e.target.files);
      fileInput.value = '';
    });

    ['dragenter', 'dragover'].forEach(evt => {
      drop.addEventListener(evt, (e) => { e.preventDefault(); drop.classList.add('is-dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
      drop.addEventListener(evt, (e) => { e.preventDefault(); drop.classList.remove('is-dragover'); });
    });
    drop.addEventListener('drop', (e) => {
      if (e.dataTransfer.files.length) addFiles(e.dataTransfer.files);
    });

    if (canPaste) {
      $('#convPasteBtn').addEventListener('click', async () => {
        try {
          const items = await navigator.clipboard.read();
          const files = [];
          for (const item of items) {
            const imageType = item.types.find(t => t.startsWith('image/'));
            if (imageType) {
              const blob = await item.getType(imageType);
              files.push(new File([blob], `pasted-image.${imageType.split('/')[1]}`, { type: imageType }));
            }
          }
          if (files.length) addFiles(files);
          else showError('No image found on your clipboard.');
        } catch (e) {
          showError('Unable to read the clipboard. Your browser may require a permission prompt.');
        }
      });
    }

    $('#convFormat').addEventListener('change', updateFormatDependentUI);

    $('#convQuality').addEventListener('input', (e) => {
      $('#convQualityValue').textContent = `${e.target.value}%`;
    });

    $('#convPercent').addEventListener('input', (e) => {
      $('#convPercentValue').textContent = `${e.target.value}%`;
    });

    $('#convBackground').addEventListener('change', (e) => {
      $('#convBackgroundCustom').hidden = e.target.value !== 'custom';
    });

    $('#convResizeMode').addEventListener('change', (e) => {
      $('#convDimsGroup').hidden = e.target.value !== 'custom';
      $('#convPercentGroup').hidden = e.target.value !== 'percent';
    });

    $('#convWidth').addEventListener('input', () => {
      if (!$('#convLockRatio').checked) return;
      const entry = entries[0];
      if (!entry || !entry.originalWidth) return;
      const ratio = entry.originalHeight / entry.originalWidth;
      const w = Number($('#convWidth').value);
      if (w > 0) $('#convHeight').value = Math.round(w * ratio);
    });
    $('#convHeight').addEventListener('input', () => {
      if (!$('#convLockRatio').checked) return;
      const entry = entries[0];
      if (!entry || !entry.originalHeight) return;
      const ratio = entry.originalWidth / entry.originalHeight;
      const h = Number($('#convHeight').value);
      if (h > 0) $('#convWidth').value = Math.round(h * ratio);
    });

    $('#convConvertAllBtn').addEventListener('click', convertAll);
    $('#convDownloadAllBtn').addEventListener('click', downloadAllAsZip);
    $('#convClearAllBtn').addEventListener('click', clearAll);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
