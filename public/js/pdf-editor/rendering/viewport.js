import { clamp } from '../utils/geometry.js';

export const ZOOM_PRESETS = [0.25, 0.5, 0.75, 1, 1.25, 1.5, 2, 3, 4];

export class ViewportState {
  constructor() {
    this.scale = 1;
    this.mode = 'value'; // 'value' | 'fit-width' | 'fit-page'
  }

  setScale(scale) {
    this.scale = clamp(scale, 0.1, 6);
    this.mode = 'value';
  }

  setFitWidth() {
    this.mode = 'fit-width';
  }

  setFitPage() {
    this.mode = 'fit-page';
  }

  /** Effective scale for a given page, resolving fit-width/fit-page against the current container size. */
  resolveScale(pageWidth, pageHeight, containerWidth, containerHeight) {
    const padding = 48;
    if (this.mode === 'fit-width') {
      this.scale = clamp((containerWidth - padding) / pageWidth, 0.1, 6);
    } else if (this.mode === 'fit-page') {
      this.scale = clamp(Math.min((containerWidth - padding) / pageWidth, (containerHeight - padding) / pageHeight), 0.1, 6);
    }
    return this.scale;
  }
}
