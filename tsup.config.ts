import { defineConfig } from 'tsup';

// Зависимости и peerDependencies остаются внешними: их подключает сборка приложения.
export default defineConfig({
  entry: { index: 'src/js/zfe.ts' },
  format: ['esm', 'cjs'],
  target: 'es2020',
  platform: 'browser',
  dts: true,
  sourcemap: true,
  clean: true,
  splitting: false,
});
