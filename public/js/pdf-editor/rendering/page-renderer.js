/** Renders one PDF.js page into a <canvas> at a given scale + extra (user-applied) rotation. */
export async function renderPageToCanvas(pdfPage, canvas, { scale, extraRotation = 0 }) {
  const totalRotation = ((pdfPage.rotate || 0) + extraRotation + 360) % 360;
  const viewport = pdfPage.getViewport({ scale, rotation: totalRotation });

  const outputScale = window.devicePixelRatio || 1;
  canvas.width = Math.floor(viewport.width * outputScale);
  canvas.height = Math.floor(viewport.height * outputScale);
  canvas.style.width = `${Math.floor(viewport.width)}px`;
  canvas.style.height = `${Math.floor(viewport.height)}px`;

  const ctx = canvas.getContext('2d');
  const transform = outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null;

  if (canvas._peRenderTask) {
    try { canvas._peRenderTask.cancel(); } catch { /* already finished */ }
  }
  const renderTask = pdfPage.render({ canvasContext: ctx, viewport, transform });
  canvas._peRenderTask = renderTask;
  await renderTask.promise;
  canvas._peRenderTask = null;

  return viewport;
}

/** Renders a small thumbnail, auto-scaling to fit `maxWidth` CSS pixels. */
export async function renderThumbnail(pdfPage, canvas, { maxWidth, extraRotation = 0 }) {
  const totalRotation = ((pdfPage.rotate || 0) + extraRotation + 360) % 360;
  const base = pdfPage.getViewport({ scale: 1, rotation: totalRotation });
  const scale = maxWidth / base.width;
  return renderPageToCanvas(pdfPage, canvas, { scale, extraRotation });
}
