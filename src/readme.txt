=== Tainacan Interface ===
Contributors: andrebenedito, daltonmartins, eduardohumberto, fabianobn, jacsonp, jessicafpx, leogermani, marinagiolo, omarceloavila, r-guimaraes, ravipassos, weryques, wetah, marcossigismundo
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.11.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
Tags: two-columns, right-sidebar, grid-layout, flexible-header, custom-colors, custom-header, custom-menu, custom-logo, featured-images, footer-widgets, theme-options, threaded-comments, translation-ready, photography, portfolio

A highly adjustable theme for digital collections with Tainacan: headers, cards, item preview, item page and menus for each collection.

== Description ==

This is the default theme for the Tainacan project. Use this in conjunction with Tainacan plugin to easily manage and publish your digital collections with a beautiful faceted search interface. Ideal for museums, galleries, libraries, and anyone who has a digital repository.

Version 2.11 brings a new design, with almost everything adjustable in Appearance > Customize > Tainacan Interface, and leaves Bootstrap behind:

* collection header with four layouts, banner and icon that can come from the items themselves;
* breadcrumb as a capsule, list of all the collections as cards, item preview over the list;
* item page with three layouts, record with capsules, sections in panels and a "How to cite" panel;
* a "Tainacan" box in Appearance > Menus, a submenu with all the collections that keeps itself up to date, and a menu of its own for each collection;
* colors, fonts, shapes and ready-made combinations, some of them per collection.

There is one design: a site updated from an earlier version gets it at once, and an administrator is told where to adjust it. Child themes keep working: the template parts, hooks, script and style handles and the class names of the grid stay.

It also adds, each with its own switch in Appearance > Customize:

* an item preview drawer on the lists, in every view mode;
* a file viewer on the item page (images with zoom, video, audio) and a PDF reader in the page instead of the browser's;
* a highlighted item record;
* a side column of related items beside the metadata (by relationship metadata and by shared terms).

It also fixes the security problems of 2.10.0 and ships the Roboto font instead of loading it from Google Fonts. Child themes, Customizer settings, actions and CSS classes of 2.x keep working.

For more information about Tainacan and Tainacan Interface please go to http://tainacan.org

== Upgrade Notice ==

= 2.11.0 =
A new design, adjustable in Appearance > Customize: a site updated from 2.x gets it at once and an administrator is told where to adjust it. Review a child theme that relied on Bootstrap's scripts or on jQuery without declaring it. Security fixes of 2.10.0.

== Copyright ==

Tainacan Interface WordPress Theme, Copyright 2021-2026 Tainacan.org
Tainacan Interface is distributed under the terms of the GNU GPL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details. The text of the license is in the LICENSE file.

== Resources ==

Tainacan Interface bundles the following third-party resources:

Poppins (latin subset, from Fontsource) Copyright 2020 The Poppins Project Authors
License: SIL Open Font License 1.1 (assets/fonts/poppins/OFL.txt)
Source: https://github.com/itfoundry/Poppins

Lora (latin subset, variable, from Fontsource 5.3.0) Copyright 2011 The Lora Project Authors
License: SIL Open Font License 1.1 (assets/fonts/lora/OFL.txt)
Source: https://github.com/cyrealtype/Lora-Cyrillic

Inter, Open Sans, Montserrat, Source Serif 4, Merriweather and Playfair Display (latin subset, variable, from Fontsource 5.3.0), each by its authors as named in its license file
License: SIL Open Font License 1.1 (assets/fonts/<font>/OFL.txt)
Source: https://fontsource.org

Roboto (latin subset, from Fontsource 5.1.0) Copyright 2011 Google Inc.
License: Apache License 2.0 (assets/fonts/roboto/LICENSE.txt)
Source: https://github.com/googlefonts/roboto

PDF.js 4.10.38 Copyright Mozilla Foundation
License: Apache License 2.0 (assets/vendor/pdfjs/LICENSE)
Source: https://github.com/mozilla/pdf.js (pdf.mjs and pdf.worker.mjs are the originals of the minified files)

Fonts of PDF.js in assets/vendor/pdfjs/standard_fonts: Foxit fonts, from PDFium (BSD style license), and Liberation fonts (SIL Open Font License 1.1)
Source: https://github.com/mozilla/pdf.js/tree/master/external/standard_fonts

assets/css/foundation.css is derived from Bootstrap 4.6.1, Copyright 2011-2021 The Bootstrap Authors, Copyright 2011-2021 Twitter, Inc.
License: MIT
Source: https://github.com/twbs/bootstrap

Tabler Icons (the icons of the social networks, in inc/social.php) Copyright (c) 2020-2024 Paweł Kuna
License: MIT
Source: https://github.com/tabler/tabler-icons

inc/class-tainacan-interface-nav-walker.php is derived from WP Bootstrap Navwalker, Copyright Edward McIntyre
License: GPLv3+
Source: https://github.com/wp-bootstrap/wp-bootstrap-navwalker

Works shown in the screenshot, in the public domain

1. Claude Monet, Water Lilies (Agapanthus). The Cleveland Museum of Art, Open Access (CC0).
Source: https://clevelandart.org/art/1960.81
2. Vincent van Gogh, Wheat Field with Cypresses. The Metropolitan Museum of Art, Open Access (CC0).
Source: https://www.metmuseum.org/art/collection/search/436535

TainacanIcons font (assets/fonts/tainacan-icons-font) Copyright Tainacan.org
License: to be confirmed by the Tainacan team before publishing

Images in assets/images, Copyright Tainacan.org, CC0 1.0 Universal, except:
facebook-circle.png, twitter-circle.png, whatsapp-circle.png and telegram-circle.png, logos of the respective services, used to name them on the share links.

== Changelog ==

= 2.11.0 =
* Release of the new design. The numbers 3.0.0 to 4.10.2 below were development builds that never reached the public; 2.11.0 contains all of them.
* Migrations of a site are now counted in steps, not compared by version number.

= 4.10.2 =
* Fix: table view headers follow the metadata name size (were 0.75em of an already small font).

= 4.10.1 =
* Customizer pencils always open their option, without moving the preview.

= 4.10.0 =
* Size of each title level (H1 to H6) and of the names and values of the metadata; Records as the default view of the lists outside a collection.

= 4.9.0 =
* Item page: one "Layout" setting with five choices replaces the layout and the order of document and record, which could contradict each other; saved choices are converted.

= 4.8.1 =
* Small fixes to the "Show more" button and to the spacing of the taxonomies.

= 4.8.0 =
* Taxonomy page: tree and columns views, A–Z index, pages of 100 terms (the map and radial views were removed). List of collections: the taxonomies of the site below the collections.

= 4.7.0 =
* Size of the text of the record; edit shortcuts (pencil) on the areas of the theme in the Customizer; Customizer labels translated; collection search field from two collections on; clearer names and help for the panel, the profiles, the order of document and record and the image of the card.

= 4.6.4 =
* Terms explorer: room for the long labels of the radial view.

= 4.6.3 =
* Terms explorer: headings of the panel at their own size.

= 4.6.2 =
* Terms explorer: a page opened on a term shows that term in the middle of the screen.

= 4.6.1 =
* Terms explorer: translated counts, readable names on the map, uncluttered radial view of large flat taxonomies.

= 4.6.0 =
* The page of a taxonomy shows the whole hierarchy in three views, tree (default), map and radial, with a panel for the chosen term: its place in the hierarchy, description, counts, child terms and the first items. Replaces the cards, thumbnails and table views.

= 4.5.1 =
* The smaller titles follow the weight chosen for the titles.

= 4.5.0 =
* Choose the font and the weight of the body text and of the titles, with six more fonts shipped with the theme; choose whether the document and attachments come before or after the record, per site or per collection.

= 4.4.9 =
* Phone menu as a side panel opens at full height even when the site's own CSS blurs or transforms the header.

= 4.4.8 =
* The open site search, which includes the items of every collection, looks at titles only; a search narrowed to posts or pages keeps the full search. "Bring the settings" can be undone from a notice shown after the import.

= 4.4.7 =
* Review of the whole theme: the Thumbnail view mode keeps previous/next inside the collection and shows the skeleton while loading; the first printed metadata section opens even when an earlier section is empty; the file viewer steps aside for the Memorial COVID Toolkit; a search narrowed to a post type stays narrowed; the preview drawer never summarizes an item with a password; the Customizer preview repaints header, footer and titles correctly; the ABNT reference is translatable; fewer queries on the item page and in the collection menu.

= 4.4.6 =
* Links coloured by a component (a white label on a coloured button, for example) keep their colour: the link colour of the theme weighed more than a class.

= 4.4.5 =
* The grey line of version 2 on phones stays on the theme's menu bar only, at its source; the reset of 4.4.4 also removed lines that blocks, plugins and the block header set on purpose.
* The list beside the filters on tablets no longer applies to horizontal filters; the room above the first row only when the cards react to the pointer; the room above the item navigation only where "Keep exploring" falls under the record.

= 4.4.4 =
* Phones: the name of the site in the banner no longer overlaps itself on two lines, and menus of blocks or plugins lose the grey line of version 2.

= 4.4.3 =
* Tablets: the search field stops at 240px, so the controls of the list stay on one line at 820px.
* Item page: room between "Keep exploring" and the previous and next item on phones and tablets.

= 4.4.2 =
* Tablets: the search field of the lists grows to 320px. The search bar may shrink to the width of a phone.
* The first row of a list keeps 6px above it for the pointer, without moving the list up.

= 4.4.1 =
* Tablets: the side filters get a readable width (228 to 280px) and the list takes the rest instead of falling below them; the search field of the lists is wider.
* Phones: a select with a long option in the search bar no longer widens the page; the previous and next post links no longer pass the edge.
* The pointer over a card or record of the first row no longer cuts its top border.

= 4.4.0 =
* The title at the top of the blog, posts, pages, searches, archives and taxonomy terms: a line above it says what the page is (the category of a post, the search, the archive), the title is large, and a capsule gives the count of posts or results, with the description of a category, tag or author.
* Three styles in Appearance > Customize > Tainacan Interface > Posts and pages: large with a fine line, large and centered, compact. The count and description and the back button can be turned off.
* The back button goes to the previous page of the site, or to the blog or the start page for a visitor who came from outside; it no longer uses a javascript: address.
* A post without an author shows the date alone, without a hanging "by".

= 4.3.0 =
* One design: the classic look of versions 2 and 3 is gone, with its options (color scheme, link color, box opacity of the banner, height of the collection banner, columns of metadata, item thumbnail in the record), the old Bootstrap rules (bootstrap-legacy.css), the scripts that needed jQuery (js.js, tainacan-interface-truncate.js) and the color picker of the collection header (Spectrum).
* A site updated from an earlier version gets the design at once, with a notice that says where to adjust it.
* Child themes: the template part files (headercollection, headertaxonomy, single-items-header), the actions, the handles bootstrap4CSS and bootstrap4JS, lib/class-wp-bootstrap-navwalker.php and the grid classes stay.
* The menu bar of a collection and of the list of collections can be turned off, and a collection can turn off its own.
* The back to top arrow is centered, and the pagination of the Tainacan lists is on one line.

= 4.2.2 =
* The share links of the collection header and of the item page kept their size: the box that holds them was taking the width of the button and squeezing the icons.

= 4.2.1 =
* Customizer, typography: weight, size, letter spacing, capitals and color of the titles; line height; text size on phones; size and capitals of the menu.
* Customizer, layout: space at the sides, space between the parts of a page, frame around the site.
* Customizer, buttons, fields and links: shape, style and capitals of the buttons; three styles of text fields; underline of links; color of selected text.
* Customizer, header and footer: header in the accent color or dark, height, current item as a line or a capsule, a highlighted button, a side panel menu on phones; footer in the main color, dark or light, a footer menu and a line with {year} and {site}.
* Customizer, social networks: links to eight networks, in the header, the footer or both.
* Customizer, posts and pages: banner with an image, plain or none, its alignment and height; list of posts or cards in columns, image proportion, words of the summary; sidebar on either side or none, its width, kept in view; narrow text; each part of a post can be turned off; reading progress bar.
* Customizer: shape, side and phones of the back to top button; separator of the breadcrumb; leaving out the emoji script.
* The modern design does not load jQuery: what the theme's scripts did with it is done in assets/js/modern.js. The copy link button no longer needs it on either design.
* The page not found is readable on the modern design.

= 4.1.0 =
* Customizer: every value of every option was tested on the published page and in the preview; the ones with no effect were fixed.
* Customizer: colors, sizes and shapes change the preview at once, and the preview goes to a page where the open section shows.
* Customizer: options with no effect under the current choices leave the panel, and an option a collection overrides names that collection.
* Modern design: the old color scheme and link color no longer win over the accent color. The item preview follows the accent, surface and text colors, and beside the list it makes room for itself.
* Collection header: with the title over the banner, the veil, the height and the icon size follow their options.
* Item page: the columns of metadata follow their option beside the side column; "Show the collection header", "Item navigation in the breadcrumb" and the labels of the sections work on the modern design.
* Item cards: the ring on hover was missing. The "Metadata" section label was never read, and now is.
* Security: the name of a collection or metadatum could run a script in the menus.
* Security: a password protected item shows the password form on its page.
* Mosaics leave out password protected items.
* Performance: one grouped count for the list of collections, thumbnails loaded at once, a mosaic takes 6 queries instead of 98, the menu button works in parts.
* Accessibility: a skip link, and the keyboard focus is visible on the back to top button, the menu button and the attachment arrows.
* Activation changes nothing without asking: the settings of a derived theme come only when an administrator presses the button.
* The theme keeps one option in the database. All notices can be dismissed for good.
* Child themes: the files of Bootstrap a 2.x copy of tainacan_enqueues() registers are replaced; the classic design keeps the Bootstrap rules posts and child themes may use; lib/class-wp-bootstrap-navwalker.php still answers.

= 4.0.0 =
* New design, highly adjustable, off on updated sites until an administrator chooses it.
* Bootstrap removed. Its classes that child themes use stay in a smaller stylesheet.
* Menus: a "Tainacan" box in Appearance > Menus, a submenu with all the collections and a menu location per collection.
* List of collections with every collection, as cards. Collection header with four layouts. Item preview with options. Item page with three layouts and a "How to cite" panel.

= 3.0.3 =
* Child themes: a child's template-parts/single-items-metadata_new.php and single-items-attachments_new.php (where 2.10.0 kept the item record) are used again.
* Child themes: the child's own style.css is loaded after the parent's (2.10.0 stopped loading it once style.min.css existed), without loading the parent's CSS twice when the child imports or enqueues it.
* Child themes: administrators see a notice listing the child's copies of 2.10.0 templates that still have a security flaw.
* Blocks: links in running text are underlined again (Bootstrap's reboot removed it); the table block has the editor's borders on the front end; block buttons with a color, gradient or the outline style keep the editor's choice.

= 3.0.2 =
* Highlighted record: the filters panel of the lists in the same look as the cards (card frame with the accent on top, quiet filter names, rounded fields with an accent focus ring, accent checkboxes with the counts aligned, "Collapse all" as a pill).

= 3.0.1 =
* Security fixes in the item navigation.
* Security: the "Show more" of collection and term descriptions cuts the DOM instead of re-parsing a cut HTML string.

= 3.0.0 =
* Next version of Tainacan Interface: security fixes, item preview drawer, file viewer and PDF reader, highlighted item record, related items column, local Roboto. New features off on sites updated from 2.x. Details in CHANGELOG.md.

Earlier versions: see CHANGELOG.md in the theme.
