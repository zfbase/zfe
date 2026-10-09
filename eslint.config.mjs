import globals from "globals";
import pluginJs from "@eslint/js";
import tseslint from "typescript-eslint";
import pluginReact from "eslint-plugin-react";


/** @type {import('eslint').Linter.Config[]} */
export default [
  {files: ["**/*.{js,mjs,cjs,ts,jsx,tsx}"]},
  {languageOptions: { globals: globals.browser }},
  pluginJs.configs.recommended,
  ...tseslint.configs.recommended,
  pluginReact.configs.flat.recommended,
  {
    settings: { react: { version: "detect" } },
    rules: {
      // Постепенная типизация: `any` допустим, пока код не описан точными типами
      "@typescript-eslint/no-explicit-any": "off",
    },
  },
  {
    // Сторонний код (jquery-tmpl) не типизирован
    files: ["src/lib/**"],
    rules: { "@typescript-eslint/ban-ts-comment": "off" },
  },
  { ignores: ["dist/**"] },
];