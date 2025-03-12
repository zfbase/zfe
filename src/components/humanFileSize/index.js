/**
 * Formats size in bytes
 * @param {number} bytes
 * @returns {string}
 */
export default function humanFileSize(bytes) {
  if (bytes === 0) {
    return '0 байт';
  }

  const k = 1000;
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  const num = parseFloat((bytes / k ** i).toFixed(2));
  const sizes = ['байт', 'КБ', 'МБ', 'ГБ'];
  return `${num} ${sizes[i]}`;
}
