=== Tainacan Interface ===
Contributors: andrebenedito, daltonmartins, eduardohumberto, fabianobn, jacsonp, jessicafpx, leogermani, marinagiolo, omarceloavila, r-guimaraes, ravipassos, weryques, wetah, marcossigismundo
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.0.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
Tags: two-columns, right-sidebar, grid-layout, flexible-header, custom-colors, custom-header, custom-menu, custom-logo, featured-images, footer-widgets, theme-options, threaded-comments, translation-ready, photography, portfolio

A simple and classic theme for WordPress digital collections with support for Tainacan.

== Description ==

This is the default theme for the Tainacan project. Use this in conjunction with Tainacan plugin to easily manage and publish you digital collections with a beautiful faceted search interface. Ideal for museums, galleries, libraries, and anyone who have a digital repository.

Version 3 adds, each with its own switch in Appearance > Customize:

* an item preview drawer on the lists, in every view mode;
* a file viewer on the item page (images with zoom, video, audio) and a PDF reader in the page instead of the browser's;
* a highlighted item record;
* a side column of related items beside the metadata (by relationship metadata and by shared terms).

On a site updated from 2.x these features stay off until an administrator turns them on; the page looks as it did. Version 3 also fixes the security problems of 2.10.0 and ships the Roboto font instead of loading it from Google Fonts. Child themes, Customizer settings, actions and CSS classes of 2.x keep working.

For more information about Tainacan and Tainacan Interface please go to http://tainacan.org

== Upgrade Notice ==

= 3.0.3 =
Child themes: the 2.10.0 item record files of a child theme are used again and its own style.css is loaded; administrators are told about child theme copies of 2.10.0 templates with security flaws. Links in text are underlined and block tables have their borders back.

= 3.0.2 =
The filters panel of the lists in the look of the cards (with "Highlighted item record" on).

= 3.0.1 =
Security fix: the item navigation no longer accepts filters on metadata the visitor cannot see.

= 3.0.0 =
Security fixes. New features (preview drawer, file viewer and PDF reader, highlighted record, related items column) come switched off on updated sites; turn them on in Appearance > Customize.

== Copyright ==

Tainacan Interface WordPress Theme, Copyright 2021 Tainacan.org
Tainacan Interface is distributed under the terms of the GNU GPL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

Tainacan Interface bundles the following third-party resources:

Roboto (latin subset, from Fontsource 5.1.0) Copyright 2011 Google Inc.
License: Apache License 2.0 (assets/fonts/roboto/LICENSE.txt)

PDF.js 4.10.38 Copyright Mozilla Foundation
Licenses: Apache-2.0
Source: https://github.com/mozilla/pdf.js

Bootstrap 4.1.3 Copyright 2011-2018 The Bootstrap Authors (https://github.com/twbs/bootstrap/graphs/contributors)
Licenses: MIT
Source: https://github.com/twbs/bootstrap

Popper  Copyright (C) Federico Zivolo 2018
Licenses: MIT
Source: https://github.com/FezVrasta/popper.js

Spectrum Colorpicker v1.8.1 Copyright (c) Brian Grinstead
Licenses: MIT
Source: https://github.com/bgrins/spectrum

WP Bootstrap Navwalker Class Copyright Edward McIntyre
Licenses: GPLv3+
Source: https://github.com/wp-bootstrap/wp-bootstrap-navwalker

Photos used in the screenshot

1. https://pxhere.com/en/photo/73320
License: CC0

2. https://pxhere.com/en/photo/73383
License CC0

3. https://pxhere.com/en/photo/73485
License: CC0

4. https://pxhere.com/en/photo/1432253
License: CC0

Bundled images, Copyright Tainacan.org
License: CC0 1.0 Universal (CC0 1.0)

== Changelog ==

= 3.0.3 =
* Child themes: a child's template-parts/single-items-metadata_new.php and single-items-attachments_new.php (where 2.10.0 kept the item record) are used again.
* Child themes: the child's own style.css is loaded after the parent's (2.10.0 stopped loading it once style.min.css existed), without loading the parent's CSS twice when the child imports or enqueues it.
* Child themes: administrators see a notice listing the child's copies of 2.10.0 templates that still have a security flaw.
* Blocks: links in running text are underlined again (Bootstrap's reboot removed it); the table block has the editor's borders on the front end; block buttons with a color, gradient or the outline style keep the editor's choice.

= 3.0.2 =
* Highlighted record: the filters panel of the lists in the same look as the cards (card frame with the accent on top, quiet filter names, rounded fields with an accent focus ring, accent checkboxes with the counts aligned, "Collapse all" as a pill).

= 3.0.1 =
* Security: the previous/next item navigation keeps only the filters a public list sends (metadata the visitor may read, published taxonomies); author, status and internal meta keys are dropped.
* Security: the "Show more" of collection and term descriptions cuts the DOM instead of re-parsing a cut HTML string.

= 3.0.0 =
* Next version of Tainacan Interface: security fixes, item preview drawer, file viewer and PDF reader, highlighted item record, related items column, local Roboto. New features off on sites updated from 2.x. Details in CHANGELOG.md.

= 2.2.1 =
* Side column headings at their own size; relationship threads say their direction.

= 2.2.0 =
* Side column beside the item metadata: related items by relationship and by shared term.
* Settings of the previous theme imported also from a child theme of Tainacan Interface or from Tainacan Acervo.
* Translations of Tainacan Interface used as a fallback; Roboto shipped with the theme.

= 2.1.1 =
* PDF reader toolbar in one row on phones.

= 2.1.0 =
* PDF reader in the page, in place of the browser viewer.

= 2.0.4 =
* PDF reader fits the page without horizontal scroll.

= 2.0.3 =
* Viewer layout with a single file; readable links on the viewer bar.

= 2.0.2 =
* Show in the side drawer option per metadatum, media box in the drawer, drawer opens from the whole card or table row.

= 2.0.1 =
* File viewer on Tainacan 1.4 galleries, "Open in the viewer" button for embedded PDF, video and audio, drawer metadata in Masonry.

= 2.0.0 =
* First version, derived from Tainacan Interface 2.10.0. Security fixes (reflected and stored XSS, open link through ref, CSS injection through collection colors, drafts on the item navigation), item preview drawer, file viewer, highlighted item record. See CHANGELOG.md.
