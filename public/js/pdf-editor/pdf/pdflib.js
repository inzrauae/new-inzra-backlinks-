/* Single point of import for pdf-lib so every module references the same
   vendored ESM build (see pdf/loader.js for why pdf.js/pdf-lib are vendored
   locally rather than loaded from a CDN — Worker same-origin rules). */
export * from '../vendor/pdf-lib.esm.min.js';
