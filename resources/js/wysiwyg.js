import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/table';

// Skin UI editor di-load dari public/vendor/tinymce/skins (disalin scripts/copy-tinymce.mjs).
// Konten iframe diberi style via content_style.
window.tinymce = tinymce;
tinymce.baseURL = '/vendor/tinymce';

const DEFAULT_INIT = {
    height: 320,
    menubar: false,
    plugins: 'lists link image table code autolink fullscreen media',
    toolbar:
        'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | link image table | removeformat code fullscreen',
    branding: false,
    promotion: false,
    relative_urls: false,
    content_css: false,
    content_style:
        "body{font-family:Roboto,sans-serif;font-size:.875rem;color:#191c1e;padding:.5rem}img{max-width:100%;height:auto}",
    block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Preformatted=pre',
};

/**
 * Pulihkan textarea dari snapshot wire:navigate yang membawa .tox-tinymce
 * mati (tanpa instance JS), lalu inisialisasi ulang.
 */
function restoreStale(el) {
    const wrapper = el.closest('.tox-tinymce');
    if (wrapper && !tinymce.get(el.id)) {
        wrapper.parentNode.insertBefore(el, wrapper);
        wrapper.remove();
        delete el.dataset.wysiwygInit;
    }
}

function initEl(el) {
    restoreStale(el);

    if (!el.id) el.id = 'wysiwyg-' + Math.random().toString(36).slice(2);
    if (el.dataset.wysiwygInit === '1' || tinymce.get(el.id)) return;

    // Halaman CMS menyediakan initWysiwyg sendiri (tombol Media Library)
    if (typeof window.initWysiwyg === 'function') {
        window.initWysiwyg(el);
        return;
    }

    el.dataset.wysiwygInit = '1';
    tinymce.init({ ...DEFAULT_INIT, target: el });
}

function initAll() {
    if (!window.tinymce) return;
    document.querySelectorAll('textarea.cms-wysiwyg').forEach(initEl);
}

document.addEventListener('livewire:navigated', () => setTimeout(initAll, 50));
document.addEventListener('DOMContentLoaded', initAll);

// Dipakai halaman CMS sebagai trigger manual (repeater, polling lama)
window.wysiwygInitAll = initAll;

export default tinymce;
