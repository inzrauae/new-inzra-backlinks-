/**
 * Detects existing AcroForm fields via PDF.js's public annotation layer API
 * (used for the on-screen fill UI). The actual values are written into the
 * exported PDF's form via pdf-lib at export time (see pdf/exporter.js),
 * since that's the only place we have a real pdf-lib document to write to.
 */
export async function detectFormFields(pdfjsPage) {
  try {
    const annotations = await pdfjsPage.getAnnotations({ intent: 'display' });
    return annotations
      .filter((a) => a.fieldName && a.fieldType)
      .map((a) => ({
        name: a.fieldName,
        type: a.fieldType,
        rect: a.rect,
        value: a.fieldValue ?? a.buttonValue ?? '',
        isCheckbox: a.checkBox === true,
        isRadio: a.radioButton === true,
        readOnly: !!a.readOnly,
        multiLine: !!a.multiLine,
        options: (a.options || []).map((o) => (typeof o === 'string' ? o : o.exportValue ?? o.displayValue)),
      }));
  } catch {
    return [];
  }
}

/** Applies collected {name: value} pairs onto a pdf-lib source document's form, before pages are copied out. */
export function applyFormValues(sourceDoc, values, { flatten = false } = {}) {
  if (!values || !values.size) return;
  let form;
  try {
    form = sourceDoc.getForm();
  } catch {
    return;
  }
  for (const [name, value] of values) {
    try {
      const field = form.getField(name);
      const ctor = field.constructor.name;
      if (ctor === 'PDFTextField') field.setText(String(value ?? ''));
      else if (ctor === 'PDFCheckBox') { if (value) field.check(); else field.uncheck(); }
      else if (ctor === 'PDFRadioGroup') field.select(String(value));
      else if (ctor === 'PDFDropdown' || ctor === 'PDFOptionList') field.select(String(value));
    } catch { /* unknown/removed field — skip it rather than aborting the whole export */ }
  }
  try {
    form.updateFieldAppearances();
  } catch { /* appearance regeneration is best-effort */ }
  if (flatten) {
    try { form.flatten(); } catch { /* leave fields interactive if flatten fails */ }
  }
}
