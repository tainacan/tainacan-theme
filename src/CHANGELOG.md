# Changelog

## 4.3.0 (2026-11-07)

One design. The classic look stayed, until 4.2, so that an update in place would not change what the visitor saw. That rule was lifted on 07/11/2026: the new design is the theme, and what only the old look used went away.

### Removed

- The option "Page design" and `tainacan_interface_is_modern()`, with every branch of the old look in templates, Customizer and scripts.
- `assets/css/bootstrap-legacy.css` (115 kB), `assets/js/js.js` and `assets/js/tainacan-interface-truncate.js` (the jQuery scripts), `assets/js/color-scheme-control.js` and `assets/js/customize-preview.js` (live color scheme), `assets/js/collection-color.js`, `assets/js/spectrum.js` and `assets/css/spectrum.css` (color picker of the old collection header), `functions/customizer/colors.php`, `functions/customizer/tainacan-items-page-collection-banner.php`, the three `loop-tainacan-collection*.php` templates.
- Options of the old look: color scheme, link and tooltip color, box opacity of the site banner, height of the collection banner, item thumbnail in the record, columns of metadata per screen, "Highlighted item record" (always on), the header color fields of the collection form.

### Kept for child themes

- `template-parts/headercollection.php`, `headertaxonomy.php` and `single-items-header.php`, now one call each, so a child or a plugin that asks for them by name gets the new header.
- The actions `tainacan-interface-*`, the handles `bootstrap4CSS` and `bootstrap4JS` (aliases of foundation.css and nav.js), `lib/class-wp-bootstrap-navwalker.php`, the grid and utility classes of foundation.css, and the header color a collection saved (`tainacan_theme_collection_background_color`), read as its main color.
- A child with its own copy of `tainacan_enqueues()` from 2.x still gets the files that replaced Bootstrap.

### Also

- Menu bars: `head_menu` (per collection) and `list_menu` turn off the bars of a collection and of the list of collections.
- The back to top arrow is centered; the pagination of the Tainacan lists is on one line.

## 4.2.2 (2026-11-07)

- The share box of the modern design had no width of its own: absolute inside the small "Share" box, it took that width and squeezed the icons into ovals. It now has its own width, and the copy link button lost the default look of a button.

## 4.2.1 (2026-11-07)

Compared with the Customizer of Blocksy 2.1 (free), option by option, and the ones that make sense for a theme of collections were added. Every new option is a CSS variable or a body class of the modern design, so it costs no request: the only new code that runs is a few lines of `assets/js/modern.js`, and the modern design stops loading jQuery.

### Options added

- **Typography:** weight, size (%), letter spacing, capitals and color of the titles; line height; text size on phones; size and capitals of the menu.
- **Layout:** space at the sides, space between the parts of a page, a frame around the site.
- **Buttons, fields and links:** shape, style (filled or outline) and capitals of the buttons; text fields with a border, filled or underlined; links always, on hover or never underlined; color of the selected text.
- **Header:** accent and dark tones, height, current item as a line or a capsule, a button with a text and an address, a side panel menu on phones.
- **Social networks:** Facebook, Instagram, X, YouTube, LinkedIn, TikTok, Bluesky and Threads, in the header, the footer or both, with icons inline (Tabler Icons, MIT).
- **Footer:** compact footer in the main color, dark or light; a footer menu location (`tt2_footer`); a last line with `{year}` and `{site}`.
- **Posts and pages:** the banner at the top with an image, plain or none, aligned left or center, with a minimum height; the list of posts as a list or as cards in 2 to 4 columns, the image proportion, the words of the summary; the sidebar on either side or none, its width, kept in view; a narrow text with its width; featured image, date and author, share links, categories and tags, previous and next can each be turned off; a reading progress bar drawn by the browser with `animation-timeline`, no script.
- **Back to top:** on or off, circle, rounded or square, left or right, on phones or not.
- **Breadcrumb:** arrow, slash or dot as separator.
- **Performance:** leave out the emoji script.

Not added, on purpose: a header and footer builder by drag and drop, mega menus, a dark mode and per-device values for every option. They weigh more than they give to a site of collections, or depend on the Tainacan lists following them.

### Lighter

- The modern design does not enqueue `js.js` and `tainacan-interface-truncate.js`, the two scripts that needed jQuery; `modern.js` does their work on every page of the design. On a page without plugins that ask for it, jQuery and jQuery Migrate are no longer downloaded.
- `copy-link.js` used jQuery without declaring it; it is plain JavaScript now, on both designs.
- `tainacan_interface_value_maps()` holds the values of the named options for the page and the preview.

## 4.1.0 (2026-11-04)

Every value of every option was tested, one by one, on the published page and in the Customizer, and the theme was reviewed with the criteria of the Tainacan AI stack and for the WordPress.org theme directory. See readme.txt for the list of changes.

### The options

- The old color scheme printed `!important` over the accent color; it is off on the modern design.
- Options the modern design replaced, with no effect on it, leave the panel, and a section with no option left goes too. Rules in `tainacan_interface_option_rules()`.
- The colors, sizes and classes change the preview at once (`tainacan_interface_live_options()`), and opening a section takes the preview to a page where it shows.
- An option a collection overrides says which collections, with a link to each form.
- A Customizer control and the code that reads it had different defaults for "Hide core title" and "Proudly powered by".

### For the directory

- One database option, `tainacan_interface` (the options of 3.x and 4.0 move to it on the first request).
- Activation only asks: settings of a derived theme come by a button.
- Original files of everything minified, a list of resources, a skip link, handles and transients with a prefix of four letters or more (`tainacan-interface-`, `tainacan_interface_`; the CSS classes stay `tt2-`, and the menu locations keep their saved names).
- `tainacanInterfaceDrawer`, `tainacanInterfaceViewer`, `tainacanInterfaceModern`, `tainacanInterfaceCustomizer` and `tainacanInterfacePreview` replace the `tt2*` globals of the scripts.

### For child themes

- `assets/css/bootstrap-legacy.css` holds the Bootstrap rules the theme no longer uses, minus the ones that collided with the Tainacan lists. It loads on the classic design only; the filter `tainacan_interface_bootstrap_legacy` turns it off or on.
- jQuery plugins of Bootstrap (`$.fn.modal`, `.tooltip`, `.collapse`, `.dropdown`) are gone: a child theme that calls them must bring them.

## 4.0.0 (2026-11-03)

A new design, highly adjustable, and no Bootstrap. A site updated in place keeps its look: it is recorded on the classic design and the administrators get a notice (`inc/upgrade.php`); a new activation starts on the modern one.

### Changed

- **No Bootstrap.** The 191 kB stylesheet and the 83 kB script are gone, with `assets/vendor/bootstrap`, the npm package and the carousel code nobody called. `assets/css/foundation.css` (82 kB, 14 kB compressed) keeps the same class names and measures for the grid, utilities and base components the templates and child themes of 2.x use; `assets/js/nav.js` answers to `data-toggle="dropdown"` and `data-toggle="collapse"` without jQuery. The `.modal`, `.tooltip`, `.collapse` and `.fade` rules that collided with the Tainacan lists are gone. Compared pixel by pixel on the classic design: 0 pixels on the list of collections, 625 on an item page (the map tiles), 0.3% on a list of items (loading animations). The handles `bootstrap4CSS` and `bootstrap4JS` stay as empty aliases, and `WP_Bootstrap_Navwalker` as an alias of `Tainacan_Interface_Nav_Walker`, so child themes that name them keep working. A child theme that calls `$.fn.dropdown`, or uses Bootstrap's modal, carousel, tooltip, alert or badge, must bring them itself.

### Added

- **Settings (`inc/settings.php`).** One schema feeds the Customizer panel "Tainacan Interface" (eight sections, about 70 options, with ready-made color combinations) and the values the templates read. Some options can be set per collection, in the "Public pages of this collection" fields of the collection form. Filters `tainacan_interface_settings_schema`, `tainacan_interface_opt`, `tainacan_interface_presets`, `tainacan_interface_modern_enabled`.
- **Look (`inc/design.php`, `assets/css/modern.css`).** Main and accent colors, decorative band, backgrounds, three fonts (Poppins and Lora come with the theme, under the OFL; nothing from another server), text size, corners, shadows, content width.
- **Collection header.** Four layouts (centered with the icon on the edge of the banner, left, title over the banner, compact), banner from the header image or a mosaic of items, icon as tilted card, square or circle, description in a panel, number of items, share links and "Surprise me", which opens the preview of a random item. A collection without images gets a pattern chosen by its name. Terms use the same header. The actions of the classic header keep firing.
- **Breadcrumb** as a capsule with the current page highlighted, or plain, or hidden.
- **List of collections.** Lists every collection, without pages: cards in 2 to 6 columns, three card styles, image proportion, description, number of items, order (including most items first) and a field that filters while typing.
- **Menus (`inc/nav-menus.php`).** A "Tainacan" box in Appearance > Menus adds the list of collections, the list of items, each collection and each taxonomy, and a "Collections" item whose submenu with all of them keeps itself up to date. Each collection and the list of collections have a menu location of their own, shown as a bar on their pages, and one button creates a starting menu per collection (its start and the most used terms of its taxonomies).
- **Item preview.** Over the list or beside it, left or right, width, darkened page, image mode, line with the collection, kind of document and number of files, capsules in four tints, floating button with its own text. Can be turned off per collection.
- **Item page.** Three layouts (document above, on the left, on the right), large title with a line of date and first terms, record with names beside or above the values, one or two columns, short values as capsules, sections in panels with their number of fields, and a "How to cite" panel (`inc/item-citation.php`: ABNT, APA, Vancouver, BibTeX, RIS).
- **Footer** compact, in the main color.

### Fixed

- The values of a multivalued text metadatum ran together on the highlighted record ("RubensWorkshop"): on the modern design the separator stays when the values are not terms.

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

- Item navigation (`?pos=`): the arguments it accepts are narrowed to what a public list sends.
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
