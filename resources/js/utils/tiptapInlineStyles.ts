/**
 * Utility function to convert CKEditor content to inline styles
 * This replaces the old TipTap utility and provides compatibility
 * for templates created with the new CKEditor.
 */
export function convertTipTapToInlineStyles(html: string): string {
  if (!html) return '';

  // CKEditor already produces content with inline styles
  // but we'll ensure it renders correctly by wrapping in a div with some additional styles
  return `<div class="ck-content">${html}</div>`;
}
