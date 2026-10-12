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
  // Автоматический JSX runtime (tsconfig: jsx = react-jsx): импорт React в файле не нужен
  pluginReact.configs.flat['jsx-runtime'],
  {
    settings: { react: { version: "detect" } },
    rules: {
      // Постепенная типизация: `any` допустим, пока код не описан точными типами
      "@typescript-eslint/no-explicit-any": "off",
    },
  },
  {
    // Устаревшие API (по тегам @deprecated в типах jQuery, DOM и Bootstrap); нужна информация о типах
    files: ["src/**/*.{ts,tsx}"],
    ignores: ["src/lib/**"],
    languageOptions: {
      parserOptions: { projectService: true, tsconfigRootDir: import.meta.dirname },
    },
    rules: { "@typescript-eslint/no-deprecated": "error" },
  },
  {
    // Сторонний код (jquery-tmpl) не типизирован
    files: ["src/lib/**"],
    rules: { "@typescript-eslint/ban-ts-comment": "off" },
  },
  { ignores: ["dist/**"] },
];