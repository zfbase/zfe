import React from 'react';
import { createRoot } from 'react-dom/client';
import { FileAjaxElement, getFileAjaxProps } from 'zfe-files';

/**
 * Initialize ZFE-Files element
 * @param {HTMLElement} root
 */
export function initZfeFileElement(root) {
  const props = getFileAjaxProps(root);
  const reactRoot = createRoot(root);
  reactRoot.render(<FileAjaxElement {...props} />);
}
