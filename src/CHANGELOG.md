# Changelog

## 3.0.3 (2026-10-30)

From the study `docs/ESTUDO_LEGADO_BOOTSTRAP_BLOCOS_FILHOS.md`.

### Fixed

- **Child themes, item record.** In 2.10.0 `single-items-metadata.php` and `single-items-attachments.php` only chose between a `_new` and an `_old` file, and the record lived in `_new`. A child theme that customized the record copied `_new`, and 3.0 skipped that copy silently. Now a child's `template-parts/single-items-metadata_new.php` / `single-items-attachments_new.php` is loaded instead (`tainacan_interface_child_legacy_part()`, `inc/child-theme.php`).
- **Child themes, style.css (inherited from 2.10.0).** `tainacan_enqueues()` registered `get_stylesheet_uri()` and swapped it for the parent's `style.min.css`, so a child's own CSS was never loaded outside debug mode. The parent's handle now always points to the parent's file, and the child's `style.css` follows as `tainacan-interface-child`. No duplicate parent CSS: when the child `@import`s the parent's stylesheet, the parent's handle loses its file; when the child enqueues its own `style.css` or the parent's, ours is dropped or the parent copy becomes an alias (dependencies still resolve). A child that replaced `tainacan_enqueues()` with a 2.x copy is left as it is.
- **Blocks.** Links inside running text (`.tainacan-content` p, li, dd, td, th, figcaption, blockquote, cite; links without a class) are underlined again: Bootstrap's reboot removed it, and color alone does not tell a link apart (WCAG 1.4.1). The table block gets on the front end the table the editor already showed (`editor-style.scss`): header line, light cell borders. Block buttons with a background, text or link color, or the outline style no longer get the theme's `btn btn-jelly-bean`, so the editor's choice wins.

### Added

- **Child themes, copies of flawed 2.10.0 templates.** Administrators see a notice listing the child theme's copies of 2.10.0 templates that still carry a known flaw (collections and terms search, term share link, raw query string in item links, unchecked collection colors), recognised by the old code itself, so a copy made from 3.0 does not trigger it. Nothing is changed in the child; the notice can be dismissed and comes back when the findings change.

## 3.0.2 (2026-10-30)

### Changed

- Highlighted record (switch "Highlighted item record"): the side filters panel of the lists takes the look of the cards. The panel is a card (line, radius, soft shadow, accent on top), "Collapse all" a pill under the heading, filter names the quiet uppercase labels of the cards with the arrow in the accent, a hairline between two filters, fields and single selects rounded with the line color and an accent focus ring, checkbox options with an accent box, a hover band and the count closing the line, "View all" in the accent, and the button that hides the panel a tab in the soft accent. The search field above follows the field style. On phones the panel is the full screen modal without the card frame. The horizontal filters of the Faceted Search block keep Tainacan's own layout. Logical properties, so right-to-left sites mirror. Tainacan's 64px margin under the filter list (which would sit inside the card) is removed. New token `--tt2-soft-strong`. CSS only (`assets/css/item-highlight.css`), scoped by `body.tt2-skin`.

## 3.0.1 (2026-10-30)

### Security

- Item navigation (`?pos=`): Tainacan turns the whole query string into WP_Query arguments, and the presence of a "next" item told whether some item matched a filter the visitor cannot see: a private metadatum (`metaquery[0][key]=<id>`), an internal meta key (`metakey=_edit_lock`), the author (`authorid`), a status. The arguments Tainacan maps are now narrowed (`tainacan-api-prepare-items-args`, hooked only around the navigation call) unless the visitor edits the collection the list comes from (`source_entity_id`, not the item's) or manages Tainacan: no status, no author, meta clauses only on metadata the visitor may read (outside private sections, parent included), tax clauses only on readable taxonomies, `meta_value` only with a readable `meta_key`, and clauses rebuilt from their known fields (no `compare_key`). Still open, and Tainacan's to fix: the same filters on the items REST route, and the full-text search, which for logged-in users also matches private metadata values.
- "Show more" of the collection and term descriptions: the original cut the HTML as a string and put the halves back with `.html()`, so a cut inside a tag or attribute was parsed again as new markup. The preview is now a copy of the DOM with the visible text trimmed (script and style text not counted), swapped with the original nodes, so only one of them is in the page at a time; nothing is re-parsed. Same jQuery plugins, classes and texts.

## 3.0.0 (2026-10-30)

The next version of Tainacan Interface, not a new theme: same folder (`tainacan-interface`), same text domain, same theme mods, actions and CSS classes as 2.10.0. The versions 2.0.0 to 2.2.1 below were developed under the working name "Tema Tainacan 2.0".

### Changed

- Identity of Tainacan Interface everywhere: folder and text domain `tainacan-interface`, prefix `tainacan_interface_` for the functions, filters, theme mods and metadatum options added since 2.0.0 (`tainacan_interface_drawer`, `tainacan_interface_related`), REST namespace `tainacan-interface/v1`, one constant `TAINACAN_INTERFACE_VERSION` (3.0.0).
- Translations: the WordPress.org language pack of `tainacan-interface` is loaded first and the files in languages/ add the texts new in 3.0. The fallback lookup of 2.2.0 is gone: it is the same domain now.

### Added

- Update from 2.x in place (the WordPress.org update): the features the visitor sees (drawer, viewer, highlighted record, side column) are recorded as off, and administrators get a notice with a link to turn them on. An activation (new site, or coming from another theme) leaves them on. The security fixes and the local font have no switch.
- Coming from a derivative theme (a child theme, Tainacan Acervo): its settings are carried over on activation; the ones stored before are kept in `tainacan_interface_mods_before_import`.

## 2.2.1 (2026-10-30)

### Fixed

- Side column: headings took the theme's global h2/h3 sizes; relationship threads say their direction ("Refer to this item in …").

## 2.2.0 (2026-10-30)

### Added

- Side column beside the metadata of the item page (on by default; Customizer → Tema Tainacan 2.0: side column of the item). Threads of related items: items related by a Tainacan relationship metadatum in both directions (who points to the item, as set in "Display in related items", and whom it points to), items of the same collection with the same term (rarest term first), and more from the collection when there is nothing else. Only published items of public collections; cached per item until a post or term changes. Right or left, title, sources, order, items per group, sticky, and the bottom "Items related to this" section left out when the column shows the relationships. Under 960px the column comes right after the metadata.
- "Use to suggest related items" on the metadatum form: chooses the taxonomies of the shared-term threads.
- Roboto shipped with the theme (latin subset, fontFace in theme.json): the original loaded it from Google Fonts, and 2.0.0 left it to the device.
- Translations of Tainacan Interface (language packs of translate.wordpress.org) used for the texts this theme does not translate.
- docs/ESTUDO_IMPACTO_ATUALIZACAO.md: what changes for a site coming from Tainacan Interface.

### Changed

- Settings imported on activation also from the previous theme when it is a Tainacan Interface derivative (child theme, Tainacan Acervo), not only from Tainacan Interface.

## 2.1.1 (2026-10-30)

### Fixed

- PDF reader in the page: the toolbar fits one row on a phone.

## 2.1.0 (2026-10-30)

### Added

- PDF reader in the page: the item document, when it is a PDF, is drawn by the theme's reader (PDF.js) where Tainacan printed the browser's own viewer in an iframe. Toolbar with page field, previous/next page, zoom, fit to width, download and full screen (the file viewer). Fits the width of its column and refits when it changes; fetched only when it comes near the view; without JavaScript, a link to the PDF. Steps aside when the Memorial COVID Toolkit is active.

### Changed

- The PDF reader of the viewer and of the page is one piece of code; the current page is followed on scroll, not by the first page near the view.

## 2.0.4 (2026-10-30)

### Fixed

- PDF reader: pages fitted to the width minus the vertical scrollbar, so no horizontal scroll.

## 2.0.3 (2026-10-30)

### Fixed

- Viewer: with a single file the hidden arrows left the stage in the first grid track, a narrow column on the left. Columns are fixed now.
- Viewer: "Download" and "Open the original file" took the link color of the theme (printed with !important) on the dark bar.

## 2.0.2 (2026-10-30)

### Added

- "Show in the side drawer" option on the metadatum form of each collection (Tainacan admin hook, flat meta `tema_tainacan_drawer` on the metadatum). The drawer shows the marked metadata; with none marked, what the item page shows minus what the list card already shows.
- Media box in the drawer: the item document plays there (image, video and audio with `preload="none"`; PDF as cover and link).
- The drawer opens from a click anywhere on the card or table row, in every view mode, not only on the item link; links to terms or authors are still followed.

## 2.0.1 (2026-10-30)

### Fixed

- File viewer did not open on Tainacan 1.4: a gallery with one file has no carousel there, and its media are `.tainacan-media-item`, not `.swiper-slide`. Both are marked now.
- PDF, video and audio embedded by Tainacan get an "Open in the viewer" button: a click inside an iframe never reaches the page.
- Drawer showed no metadata when the list asks for the title only (Masonry, Thumbnail): it falls back to what the item page shows (metadata set to display first, then those hidden only from the lists).

## 2.0.0 (2026-10-30)

First version, derived from Tainacan Interface 2.10.0 (tainacan-theme `develop`, `84b9a20`).

### Added

- Item preview drawer on the Tainacan lists (ported from TainacanBiblio's item peek): route `tema-tainacan-2-0/v1/item/<id>`, read permission asked to the collection and the item, password-protected items never summarised, per-IP budget in the callback, DOM built with `textContent`, late responses discarded, focus kept on the button pressed, modal with the rest of the page inert on phones.
- File viewer on the item page (rebuilt from the Memorial COVID Toolkit gallery): native `<dialog>`, image zoom/pan/pinch, swipe and arrow keys between files, video/audio with an error state, PDF.js 4.10.38 loaded only when a PDF opens, far pages release their canvas, back button closes, `#tt2-file-<id>` deep link. Slides are marked server side (`data-tt2-file`) through `get_tainacan_item_gallery`.
- Highlighted item record (from the MHN card skin): list cards, records, masonry and table; identification line under the item title with the collection and the registration number.
- Customizer section "Tema Tainacan 2.0" with one switch per feature.
- Customizer settings of Tainacan Interface copied on the first activation.

### Security

- Reflected XSS in the terms list search (`?search=`) and in the collections list search (`?s=`).
- Stored XSS through a term name in the share link of the term header.
- `?ref=` accepted only when it points to this site, and escaped (open link and HTML injection in the breadcrumb).
- Collection colors validated when saved and when printed (CSS injection by collection editors).
- Drafts' titles and thumbnails no longer shown to visitors through `?pos=&status=draft` on the item navigation.
- Adjacent items computed once per request (up to six list queries per page before).
- `?termspaged=abc` no longer ends in HTTP 500.
- No `$_SERVER['QUERY_STRING']` or `$_GET` printed or used without sanitizing; view mode slugs from a closed list.
- Numeric and URL Customizer settings sanitized as numbers and http(s) URLs.
- Share links URL-encoded and over HTTPS; `rel="noopener noreferrer"` on every new-tab link.
- Every template escaped at the point of output (273 findings of the Tainacan AI Stack checker on the original, 0 now).
- Google Fonts removed (visitor IP sent to Google); system font fallback.

### Fixed

- Custom link and tooltip colors never printed (wrong style handle).
- Chosen color scheme always replaced by the default one.
- "Only pages" search filter queried a post type that does not exist (`pages`).
- Collection meta declared to the API replaced the list of other extensions.
- Footer address and phone printed with backslashes (`wp_filter_nohtml_kses` used as output escape).
- Script printed after `</html>` on the item page.

### Removed

- Code paths for Tainacan older than 0.19 and WordPress older than 5.9 (`*_old.php` templates, legacy editor style).
