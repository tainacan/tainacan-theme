# PDF viewer integrations

Tainacan Interface delegates its document and attachment galleries to the Tainacan plugin. Optional integrations can provide specialist PDF readers without editing `single-items-attachments_new.php` or copying a parent theme template into a child theme.

## Extension points in Tainacan

The integration developed for [issue #78](https://github.com/tainacan/tainacan-theme/issues/78) needs two extension points:

- `tainacan-item-get-attachment-as-html`: rendered HTML, requested image size, item entity and attachment ID (fourth argument). It covers the uploaded main document and attachments in the main slider.
- `tainacan-get-attachment-html-url`: viewer URL and attachment ID. It covers attachments opened in the gallery lightbox, including thumbnail-only galleries.

The additional argument and URL filter are being proposed separately in Tainacan. Check their availability before installing an integration; a released minimum version has not yet been assigned. Registering only the HTML filter does not cover every gallery mode.

## DearFlip integration

The optional **Tainacan DearFlip Integration** plugin is maintained in this repository, under [`integrations/tainacan-dearflip`](../integrations/tainacan-dearflip/README.md). It uses DearFlip's registered `dflip` shortcode with the URL obtained from a validated WordPress PDF attachment and a custom identifier. This also works with Lite, which does not resolve a plain PDF attachment from a numeric shortcode ID. Install Tainacan with the extension points above, DearFlip, and the integration, then enable **Settings → Tainacan DearFlip → Use DearFlip for Tainacan PDF documents and attachments**.

The integration adds the same checkbox to **Appearance → Customize → Tainacan single item pages → Items document and attachments** through WordPress's `customize_register` hook. The theme does not load the integration automatically or require DearFlip. The theme build copies `src/` only; package this optional plugin separately following [`integrations/README.md`](../integrations/README.md). Both artifacts are maintained in the same Git repository.

Each viewer runs in a same-origin iframe with its own WordPress head/footer, so resources load through DearFlip and the viewer can initialize when the lightbox opens. Item permissions, attachment relationship and password protection are checked by the integration endpoint. The default renderer remains available when the integration is disabled or DearFlip is absent. Clear cached pages after deactivating the integration itself.

The initial scope is uploaded PDF documents and attachments. External URL documents keep their current rendering. Images, audio, video, captions, filenames, download controls and thumbnail ordering remain managed by Tainacan and the theme.

## Manual verification

Check both separate document/attachments sections and grouped gallery mode, multiple PDFs alongside images, thumbnail-only galleries, opening/closing/reopening the lightbox, mobile layout, DearFlip selective script loading, disabled/missing dependencies and anonymous access to private items. Validate DearFlip Pro separately before advertising support for it.

References: [DearFlip shortcode options](https://wordpress.dearflip.com/docs/shortcode-options/) and [selective script loading](https://wordpress.dearflip.com/docs/selective-script-loading/).
