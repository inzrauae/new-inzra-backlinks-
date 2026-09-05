function buildRegex(query, { caseSensitive = false, wholeWord = false } = {}) {
  const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const pattern = wholeWord ? `\\b${escaped}\\b` : escaped;
  return new RegExp(pattern, caseSensitive ? 'g' : 'gi');
}

/** Finds every text object (across all pages) whose text matches `query`. Search-all-pages by construction. */
export function findMatches(project, query, options = {}) {
  if (!query) return [];
  const re = buildRegex(query, options);
  const matches = [];
  project.pages.forEach((page, pageIndex) => {
    page.textObjects.forEach((t) => {
      if (t.deleted) return;
      re.lastIndex = 0;
      if (re.test(t.text)) matches.push({ pageId: page.id, pageIndex, textId: t.id });
    });
  });
  return matches;
}

/** Returns {before, after, changed} without mutating — caller applies `after` through a history command. */
export function replaceInText(text, query, replacement, options = {}) {
  const re = buildRegex(query, options);
  const after = text.replace(re, replacement);
  return { before: text, after, changed: after !== text };
}
