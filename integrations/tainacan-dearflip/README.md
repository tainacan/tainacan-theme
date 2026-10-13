# Tainacan DearFlip Integration

Optional WordPress integration for PDF documents and attachments in Tainacan, including the main gallery and thumbnail-only lightboxes. The integration adds a checkbox to **Settings → Tainacan DearFlip** and, when Tainacan Interface is active, **Appearance → Customize → Tainacan single item pages → Items document and attachments**. Both controls save the same site option. The integration is disabled by default.

## Requirements

- WordPress 6.5 or newer and PHP 7.4 or newer.
- Tainacan with the fourth attachment ID argument on `tainacan-item-get-attachment-as-html` and the `tainacan-get-attachment-html-url` filter. A released minimum version providing both extension points is not yet assigned.
- DearFlip with the `dflip` shortcode registered. DearFlip Lite is the initial validation target; Pro compatibility requires separate validation.

## Install

1. Install and activate Tainacan and DearFlip.
2. Copy this folder (`integrations/tainacan-dearflip` in the theme repository) to `wp-content/plugins/tainacan-dearflip`, or install the plugin ZIP described in [`../README.md`](../README.md), and activate **Tainacan DearFlip Integration**.
3. Enable the integration in Settings or the Customizer.
4. Open an item with a PDF uploaded as its main document or attachment.

PDFs are rendered with the official `[dflip source="VALIDATED_ATTACHMENT_URL" id="tainacan-pdf-ATTACHMENT_ID"][/dflip]` shortcode. DearFlip Lite 2.4.37 treats a numeric ID as a flipbook post and does not resolve a plain PDF attachment as its source, so the adapter supplies the URL from the validated attachment. A same-origin iframe supplies an independent WordPress document, including `wp_head()` and `wp_footer()`. This avoids copying a live DearFlip canvas between gallery/lightbox containers and supports selective script loading. Resources follow DearFlip's own enqueue mechanism; no vendor script paths or library copies are bundled.

The endpoint validates the attachment MIME, its relationship to the requested Tainacan item, item read permission and password protection. It accepts attachment/item IDs, never arbitrary source URLs. Images, videos, audio, external URL documents, thumbnails and Tainacan download controls retain their existing rendering. The option is site-wide and also applies to galleries from other compatible themes.

Disabling the option or DearFlip restores the standard Tainacan renderer. If DearFlip disappears after a page was cached, the authorized endpoint uses the browser PDF viewer. Deactivating this integration removes its endpoint, so clear page caches after deactivation.

## Validation

Check a PDF main document, multiple PDF attachments, images mixed with PDFs, the separate attachment section, grouped gallery, thumbnail-only gallery, reopen/switch lightbox, mobile dimensions and DearFlip selective script loading. Confirm anonymous users cannot open an endpoint for a private or password-protected item, an unrelated PDF, a non-PDF attachment or a missing attachment. The permissions of the raw uploaded files remain governed by Tainacan; this integration does not add file protection.

Related issue: https://github.com/tainacan/tainacan-theme/issues/78

No DearFlip code is bundled.
