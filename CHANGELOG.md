# Changelog

## 2.13.0 (2026-11-20)

- **Pictures in the Customizer too** (`inc/customizer-pictures.php`): the options a collection can change (header layout, banner, icon, description, menu, item preview, item layout, open panels of the metadata sections) show the same drawings of the collection form, in the colors being set, instead of a select.
- **Site and collection, explained in both places:** the Customizer panel says its options are the defaults of the site and that a collection can choose otherwise on its form, which then prevails for it; each option a collection can change says so; the collection panel says its choices are only for the collection and prevail, and that "As the site" keeps following the Customizer.
- The warning "does not change these collections" of the open panels also lists collections whose sections are tabs or an accordion.

## 2.12.2 (2026-11-19)

- Collection panel: "Use the site's" stays hidden while the color is the site's (the admin of Tainacan forces the display of buttons).

## 2.12.1 (2026-11-19)

- Review of 2.12.0: migration step 3 waits for Tainacan when it is not active (it used to count as done with nothing converted); colors of three digits are saved and shown as six; the drawings no longer repeat an SVG id; the step an activation starts from is a named constant.

## 2.12.0 (2026-11-19)

### Changed

- **Collection form: one panel, "Look of this collection on the site"** (`inc/collection-form.php`), in place of the 2.x "extra settings" block and the list of selects of the new design. Four groups that open and close (Colors, Header, List of items, Item page), each with a badge saying whether the collection follows the site or how many settings it changes. Every choice is a small drawing of the result, in the colors of the collection; the first one is always "As the site" and says what the site uses today. Colors have a picker, the code and "Use the site's". Each group links to its section of the Customizer.
- **Metadata sections: one setting.** The 2.x layout (default, tabs, collapses, accordion) and "which panels start open" became one list: panels with the first one open, all open or all closed, tabs, accordion. It writes both former settings, so the item page did not change. Migration step 3 converts the collections that had either; the 2.x "collapses" becomes panels with the first one open (what it showed).

### Removed

- `functions/class-tainacan-interface-collection-settings.php`, `assets/css/collection-extra-options.css` and the four `section_*.png` pictures. `tainacan_interface_section_layout()` moved to `inc/collection-form.php`.

## 2.11.0 (2026-11-19)

Public release of the new design. Versions 3.0.0 to 4.10.2 below were development builds, never published; 2.11.0 contains all of them and continues the numbering of the official 2.x (2.10.0).

- Upgrade: what a site still needs is counted in migration steps (`migration` in the `tainacan_interface` option), not by comparing the version it last saw. With 4.x back to 2.11, comparing versions would run old migrations again on every update. Sites that saw 4.3.0+ count as step 1 and 4.9.0+ as step 2.
- Texts: "version 4" became "version 2.11" in the description of the theme and the readme; the Customizer section of the extra features no longer says "version 3".
- Reactivating the theme keeps the migration steps a site still lacks (it used to mark the site as up to date).

## 4.10.2 (2026-11-19)

- Table view: column headers use the metadata name size (Customizer, default 0.875rem) instead of 0.75em of the plugin font, which made them tiny.

## 4.10.1 (2026-11-19)

### Fixed

- Customizer pencils that "sometimes" did nothing: opening a section takes the preview to a page of its kind, and a pencil opens a section too. The breadcrumb pencil on an item page reloaded the preview as a collection page, and every pencil clicked during the reload was lost. A section opened by a pencil now leaves the preview where it is, and the breadcrumb (on every kind of page) no longer moves it at all.
- Item heading: the theme's pencil sat exactly on the 2.x pencil of the same area and could catch the click meant for it; the theme's one is gone.

## 4.10.0 (2026-11-19)

### Added

- Typography: a size for each level of title, H1 to H6 (px), live in the Customizer. H2 to H6 apply to the text of pages, posts and Tainacan content; H1 is also the maximum of the big title of every page, which still shrinks on phones. "Size of the titles (%)" keeps scaling them all.
- Item page: "Size of the names of the metadata (px)", default 14 (0.875rem), and "Size of the values of the metadata (px)", default 16. They replace "Size of the text of the record (%)" of 4.7, which scaled both together relative to the parent and could not be set apart.

### Changed

- Default view of the lists of items outside a collection (all items, items of a term): Records instead of Masonry, unless the site chose a default in the Tainacan settings. Each collection keeps the default set on its own form.
- Terms panel: "See all items" and "Copy link" sit side by side.

## 4.9.0 (2026-11-19)

### Changed

- Item page: "Layout" and "What comes first: document or record" were two settings that could contradict each other ("document on top" with "record first"). They are one setting now, "Layout", with five choices that say where each part goes: document and attachments on top, record below; document on top, then the record, attachments at the end; record on top, document and attachments below; document on the left, record on the right; record on the left, document on the right. In the side-by-side layouts, phones show first what is on the left. Per collection too.
- Update in place: the order saved for the site or for a collection (4.5 to 4.8, or the 2.x "sections order") becomes the equivalent layout and the old value is removed. A side-by-side layout keeps its side.
- The layout now refreshes the Customizer preview (it changes the order of the page, which only the server can do).

## 4.8.1 (2026-11-19)

### Fixed

- "Show more" says how many are left only when more than one page is left; the block of taxonomies keeps its distance from the cards of the collections.

## 4.8.0 (2026-11-19)

### Changed

- Terms explorer: the map and radial views are gone. With a large taxonomy (hundreds of terms on one level) neither stayed readable. In their place, **Columns**: each level opens a column beside the previous one (like a file manager), with the whole path in sight and the keyboard (arrows go up, down, into a term and back).
- Terms explorer: pages of 100 terms. The top level of the tree has numbered pages ("1–100 of 146"); a longer level under a term, and every column, show 100 and a "Show 100 more" button. Opening a term (link, panel, search) goes to the page that holds it.
- Terms explorer: A–Z index above the views; a letter keeps the terms that start with it, at any level, combined with the search.
- `tainacan_terms_viewmode` takes `tree` or `columns`; `map` and `radial` fall back to the tree.

### Added

- List of collections: "Taxonomies below the collections" (on by default) lists the public taxonomies of the site, with the number of terms and a link to each page of terms; "Title of the taxonomies" names the block. Edit shortcut in the Customizer.

## 4.7.0 (2026-11-19)

### Added

- Item page: "Size of the text of the record (%)", 80 to 150, live in the Customizer; it scales the names and the values of the metadata. The default is one step larger than before (names 0.875em, values 1em).
- Customizer: edit shortcuts (the pencil) on the areas of the theme (header, band, breadcrumb, page title, collection header, list of collections, item heading, document, record, "How to cite", share, profiles, footer); the pencil opens the option that shapes that area. Child themes add or change them with the `tainacan_interface_customizer_shortcuts` filter.

### Fixed

- Customizer labels in English on a site in another language: the schema of the options was built, and kept, before the theme loaded its translations on `init`. It is kept only from `init` on.
- List of collections: "Field to find a collection" only appeared from 7 collections on, so it seemed to do nothing; it appears from 2 on.

### Changed

- The panel "Tainacan Interface" is now "Look of the site and collections"; "Social networks" is now "Profiles of the institution", with a description saying the icons appear once an address is filled in and that sharing is a separate option.
- "Order of the document and the record" is now "What comes first: document or record", with a note on how to put the document below the record. "Image of the card" explains that it only changes collections without a thumbnail of their own, except "Always the mosaic".

## 4.6.4 (2026-11-19)

### Fixed

- Terms explorer, radial view: long labels at the top of the circle ran over the hint above it; the drawing has room for them now.

## 4.6.3 (2026-11-19)

### Fixed

- Terms explorer: the headings of the panel ("Terms below", "Items with this term") took the size of the page headings; they carry the `label` class now, like the other titles of the theme's own components. The search field can shrink to the width of a phone.

## 4.6.2 (2026-11-19)

### Fixed

- Terms explorer: opening the page on a term (`#termo-<id>` or `termsparent`) jumped the page to an empty area; the term is now scrolled to the middle of the screen, and only when it is out of sight.

## 4.6.1 (2026-11-19)

### Fixed

- Terms explorer: the counts came in English (the plural forms are now translated before they reach the script); on the map, small blocks broke words in the middle (names wrap by word with hyphenation, tiny blocks show the name on hover only); on the radial, a first ring with many terms covered the next one with its labels (above 48 terms the circle shows a single ring).

## 4.6.0 (2026-11-19)

### Changed

- Taxonomy page: the cards, thumbnails and table views, which listed one level at a time behind "Children" links, are replaced by a terms explorer with three views of the whole hierarchy. **Tree** (default): every level with guide lines, item count, number of terms in the branch and a bar comparing the size of each branch; expand or collapse all; keyboard navigation of an ARIA tree. **Map**: squarified treemap of the branches, sized by items plus terms; a click opens the branch, crumbs go back up. **Radial**: the hierarchy around the centered term, up to four rings (about 360 terms at once); a second click on a term centers on it, a click on the center goes up. The search filters every view, keeping the path to each match open, and the order (alphabetical or most items first) applies to all of them.
- Panel of the chosen term: place in the hierarchy, image, description, item count (the real total from the Tainacan REST, with the permissions of the visitor), child terms, terms on the same level, the first eight items with thumbnails, "See all items", "Center here" and a link to the term (`#termo-<id>`). On phones and tablets it is a bottom sheet.
- The structure of the taxonomy is cached in a transient until any term changes. Without JavaScript the page shows the hierarchy as a nested list of links.
- Links of 2.x keep working: `tainacan_terms_viewmode=cards|grid|table` opens the tree, `termsparent=<id>` opens the tree on that term and `search=` fills the search. `tainacan_terms_viewmode` now takes `tree`, `map` or `radial`.
- Removed `template-parts/loop-tainacan-terms.php`, `loop-tainacan-terms-grid.php` and `loop-tainacan-terms-table.php`; a child theme with its own `tainacan/archive-terms.php` keeps its old page.

## 4.5.1 (2026-11-17)

### Fixed

- The smaller titles had a fixed weight of 600 and ignored "Weight of the titles"; they now follow it one step lighter.

## 4.5.0 (2026-11-17)

### Added

- Typography: the font and the weight of the body text and of the titles are chosen in one place (Typography section). New weight of the body text (light, regular, medium, semibold); the weight of the titles gains regular and black, and the smaller titles (sections of the record, cards of lists and of the blog, collections, "How to cite", the side column) follow it one step lighter, 600 with the default 700. Six more fonts, all shipped with the theme (latin subset, Fontsource, SIL OFL 1.1, variable files so every weight is real): Inter, Open Sans and Montserrat (sans serif), Source Serif, Merriweather and Playfair Display (serif). Lora is now variable (400 to 700, with italic); Poppins gains 300 and 800 and Roboto gains 300. A font file is downloaded only when the page uses it.
- Item page: "Order of the document and the record" (document and attachments first, document then record then attachments, or record first), for the site and per collection on its form. In the side-by-side layouts it sets the order on phones. It replaces the 2.x "Layout sections order" control; a site that chose an order there keeps it until it chooses again.

## 4.4.9 (2026-11-10)

### Fixed

- Phone menu as a side panel: a header styled by the site with `backdrop-filter`, `filter` or `transform` becomes the containing block of everything `position: fixed` inside it, and the panel was trapped in the header's height (seen on the bpb: 78 px of a menu with six items, the rest reachable only by scrolling inside it). While the panel is open those effects are turned off on the header; the panel covers it anyway.

## 4.4.8 (2026-11-08)

Two decisions of the coordinator on the pending points of the 4.4.7 review.

### Changed

- Site search: the open search, which includes the items of every collection, looks at titles only (`search_columns`). With the items in, a search over title, excerpt and content read the whole `wp_posts` twice per query in a large archive. A search narrowed to posts or pages (`?post_type=post`, `?onlyposts=1`, `?onlypages=1`) keeps the full search; the full-text search of the items is the Tainacan list.

### Added

- "Bring the settings" can be undone: after the import a notice says what was brought and offers to put back the settings, menus and logo the theme had before, discarding what was changed since, including the Additional CSS the import copied. The record is written at every import (even when the theme had no settings yet), kept until the notice is dismissed, and forgotten when another theme is activated.

## 4.4.7 (2026-11-08)

Review of the whole theme with the Tainacan AI stack (six roles plus a correctness pass). What it found and this version fixes:

### Fixed

- Thumbnail view mode: the links of the items of a collection now send `source_list=collection` with `source_entity_id`, as the Vue view modes do; the template sent `repository` (or `term`), so the item page computed "previous/next" over the whole repository and the editors' unfiltered navigation never applied. Tainacan includes the template inside its REST route, where `tainacan_get_collection_id()` is false (the query has `post_type` as an array), so the collection is read from the request of that route. Inherited from 2.x.
- Thumbnail view mode shows Tainacan's loading indicator while the list loads: with a `skeleton_template` Tainacan 1.4 shows neither the indicator nor the skeleton, which sits in a container it hides.
- Metadata sections: the first section that reaches the page opens (or is the active tab). Tainacan numbers the empty sections `hide_empty` skips, so a collection whose first section is empty got no open section and, in the tabs layout, no visible panel; and a plugin may remove a section Tainacan has already rendered (TainacanBiblio replaces the first one with its own sheet). The mark is now applied to the rendered HTML, after every filter, with no filter of the theme left behind for other calls of `tainacan_the_metadata_sections()`.
- The hooks `tainacan-interface-single-item-metadata-begin` and `-end` fire once around the sections in every layout. In the tabs, collapses, accordion and 2.x layouts they were concatenated into strings (`do_action()` returns null) and printed before the wrapper. Inherited from 2.x.
- The item file viewer steps aside entirely when the Memorial COVID Toolkit is active: it only did so for the PDF document, while its click listener, registered first, still opened the theme's dialog instead of the Toolkit's overlay with the conditions of use.
- Site search: a search already narrowed to a post type (the Search block set to one type, a plugin's form) stays narrowed; the theme widened it to posts, pages, collections, taxonomies and every item type.
- Collections page: an order asked in the URL (the sorting links of a 2.x child theme's own page) wins over the theme setting, which overrode it.
- A 2.x child theme with its own `tainacan_enqueues()` that registers `bootstrap4JS` with the Bootstrap files keeps jQuery in the dependency chain when the theme swaps the handle for `nav.js` (it was dropped). The theme's own `bootstrap4JS`, without jQuery since 4.3, is unchanged: a child that needs jQuery declares it. The handle `RobotoFonts` of 2.x exists again, empty, so a child stylesheet that depends on it still prints; `foundation.css` is enqueued by its own handle.
- The preview drawer never summarizes an item with a password, even for a visitor who typed it: the summary goes out with a public `Cache-Control`.
- Customizer preview: changing the main colour no longer forces the colour of titles (it follows the "colour of titles" option), and the header and footer repaint at once when they take the main or accent colour.
- The closing fragments of the ABNT reference ("Available at … Accessed on …") are translatable; they were fixed in Portuguese.
- `modern.js`: a metadata section with one field no longer throws when the localized strings are missing (operator precedence).
- Reading progress bar lets clicks through (`pointer-events: none`).
- Menu walker fallback output passes through `wp_kses_post()`; `copy-link.js` no longer defines global functions; `lib/class-wp-bootstrap-navwalker.php` has the `ABSPATH` guard; a stale third argument of `tainacan_interface_state_set()` removed; `fetch_by_collection()` called with its two parameters.
- readme: credits to `bootstrap-legacy.css` and Spectrum Colorpicker, which are not in the theme, removed.

### Performance

- "Keep exploring": terms shared by more than 2,000 items (filter `tainacan_interface_aside_term_ceiling`) are not used as threads: a term like "Book" suggests nothing and its query sorted thousands of rows. The status of the taxonomies is read with one query instead of one per metadatum.
- Preview drawer: metadata already shown on the card and not marked for the drawer are skipped before their value is rendered.
- Collection menu: the posts behind the links of the automatic submenu are primed with one query instead of one per collection on every page.

## 4.4.6 (2026-11-08)

### Fixed

- The colour of links (`style.scss`, `style.css`, `style.min.css`) weighs as a plain `a` again: its two `:not()` were outside `:where()` and beat any component that colours its own links with a class. Seen on bpb: the "Abrir ficha completa" button of the TainacanBiblio preview drawer had blue text on a blue background (contrast 1.3:1), and the titles of "Keep exploring" took the link colour instead of the `color: inherit` of item-aside.css. The rule came with the official 2.10.0 (develop) imported in 3.0; the published 2.8.3 does not have it.

## 4.4.5 (2026-11-07)

### Fixed

- The rule of 2.x that drew a grey line under every `nav` on phones is limited to `nav.navbar` in `style.scss`, `style.css` and `style.min.css`; the reset added to modern.css in 4.4.4 is gone (it also removed lines set on purpose by blocks, plugins and the block header).
- The list takes the rest of the row beside the filters only with side filters, not with the horizontal filters of the Faceted Search block.
- The 6px above the first row of a list only when the cards lift or ring under the pointer.
- The 32px under the item body only below 960px, where "Keep exploring" falls under the record.

## 4.4.4 (2026-11-07)

### Fixed

- Phones: the title of the banner had a line height of 24px for a 28px font, so a name on two lines overlapped; it is 1.15 now, balanced.
- Phones: `style.css` gives every `nav` a grey bottom line under 768px (a rule of 2.x); in the design of version 4 only the header keeps a line, and a navigation block or a plugin menu (the header of TainacanBiblio, for example) no longer gets one.

## 4.4.3 (2026-11-07)

### Fixed

- Tablets: the search field of the lists grows to 240px (320px in 4.4.2 pushed the button of the exposers to a line of its own at 820px).
- Item page: 32px below the body of the item, so the previous and next item no longer touch the "Keep exploring" box when it falls below the record.

## 4.4.2 (2026-11-07)

### Fixed

- Tablets: the search field of the lists keeps Tainacan's 136px no more; it grows to 320px.
- The search bar and its parts have `min-width: 0`, so a long select in it no longer widens the page on a phone (4.4.1 only limited the select).
- The room above the first row of a list is padding only: the negative margin of 4.4.1 moved the list up and the outer area cut it instead.
- Same files as 4.4.1 under a new version, so that browsers holding 4.4.1 fetch them again.

## 4.4.1 (2026-11-07)

### Fixed

- Tablets (769 to 1279px): the side filters had 20% of the width with 32px inside on each side (90px for the text on a 820px tablet), and the list at most 80%, so a panel with a margin pushed it below the filters. The panel is 228 to 280px with less inner space, the list takes the rest (`flex: 1 1 0`, no maximum) and the search field leaves its 136px.
- Phones: a `select` in the search bar (the scope of TainacanBiblio, "Search in: the whole library network") measured its longest option and widened the page to 556px on a 390px phone; it now fits the bar.
- Phones: the rows of previous and next post and of comments passed the edge by 3px with their negative margins.
- The list area of Tainacan hides what passes its edges, which cut the top border of the first row of cards and records when the pointer lifted them.

## 4.4.0 (2026-11-07)

### Added

- `template-parts/page-title.php` and `tainacan_interface_page_title_parts()` (inc/page-title.php): the title of the blog, posts, pages, searches, archives and taxonomy terms, with a kicker (category of a post, parent of a page, kind of listing), the title, the count of posts or results and the description of a term or author. Filter `tainacan_interface_page_title_parts`; a call to the part can replace any piece by its arguments.
- Options `page_title_style` (editorial, centered, compact; body class `tt2-title-*`, live in the preview), `page_title_meta` and `page_title_back`.

### Changed

- `template-parts/loop.php`, `loop-singular.php` and `tainacan/archive-terms.php` call the new part. The classes `tainacan-title`, `tainacan-title-page`, `title-page` and `title-back` stay on it for child themes; a child with its own copy of those files keeps its own title. The structure is no longer a list: `title-page` is a `div` and `title-back` is the link itself, so a child rule written for `ul.list-inline`, `li.title-page` or `.title-back a` needs a look.
- Date archives take their title from the address, not from the first post.
- The back link has a real address (the blog, the parent page or the start page) and goes back in the history only when the previous page is on the site.

### Fixed

- `tainacan_meta_author()` prints nothing when the post has no author, instead of a hanging "by".

## 4.3.0 (2026-11-07)

One design. The classic look stayed, until 4.2, so that an update in place would not change what the visitor saw. That rule was lifted on 07/11/2026: the new design is the theme, and what only the old look used went away.

### Removed

- The option "Page design" and `tainacan_interface_is_modern()`, with every branch of the old look in templates, Customizer and scripts.
- `assets/css/bootstrap-legacy.css` (115 kB), `assets/js/js.js` and `assets/js/tainacan-interface-truncate.js` (the jQuery scripts), `assets/js/color-scheme-control.js` and `assets/js/customize-preview.js` (live color scheme), `assets/js/collection-color.js`, `assets/js/spectrum.js` and `assets/css/spectrum.css` (color picker of the old collection header), `functions/customizer/colors.php`, `functions/customizer/tainacan-items-page-collection-banner.php`.
- Options of the old look: color scheme, link and tooltip color, box opacity of the site banner, height of the collection banner, item thumbnail in the record, columns of metadata per screen, "Highlighted item record" (always on), the header color fields of the collection form.

### Kept for child themes

- `template-parts/headercollection.php`, `headertaxonomy.php` and `single-items-header.php`, now one call each, so a child or a plugin that asks for them by name gets the new header.
- The actions `tainacan-interface-*`, the handles `bootstrap4CSS` and `bootstrap4JS` (aliases of foundation.css and nav.js), `lib/class-wp-bootstrap-navwalker.php`, the grid and utility classes of foundation.css.
- A child with its own copy of `tainacan_enqueues()` from 2.x still gets the files that replaced Bootstrap.
- `tainacan_interface_is_modern()` (always true, deprecated) and `tainacan_get_color_scheme()` (drawn from the colors of the design) stay for children of 4.0–4.2 and 2.x; the handles `tainacan_tainacanJS` and `tainacan_tainacanTruncate` stay, empty and depending on jQuery, so a child script that depends on them still loads.
- The offset and order classes of the grid are in foundation.css; a child with its own `archive-tainacan-collection.php` still finds the `loop-tainacan-collection*` parts and gets twelve collections per page.
- The header color a collection saved for the old look is no longer read: its main color comes from its own option or from the site.

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
