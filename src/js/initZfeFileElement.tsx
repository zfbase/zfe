import React, { type ComponentProps } from 'react';
import { createRoot } from 'react-dom/client';
import { FileAjaxElement, getFileAjaxProps } from 'zfe-files';

/**
 * Initialize ZFE-Files element
 * @param root
 */
export function initZfeFileElement(root: HTMLElement) {
  const props = getFileAjaxProps(root, {}) as ComponentProps<typeof FileAjaxElement>;
  const reactRoot = createRoot(root);
  reactRoot.render(<FileAjaxElement {...props} />);
}
