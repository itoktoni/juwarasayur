// Salin skins TinyMCE dari node_modules ke public agar bisa di-load via URL.
// Dijalankan otomatis via "postinstall".
import { cpSync, existsSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const src = join(root, 'node_modules', 'tinymce', 'skins');
const dest = join(root, 'public', 'vendor', 'tinymce', 'skins');

if (!existsSync(src)) {
    console.warn('[copy-tinymce] node_modules/tinymce/skins tidak ditemukan — dilewati.');
    process.exit(0);
}

mkdirSync(dest, { recursive: true });
cpSync(src, dest, { recursive: true });
console.log('[copy-tinymce] skins tersalin ke public/vendor/tinymce/skins');
