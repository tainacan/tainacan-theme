# Changelog

## 2.20.2 (2026-11-26)

### Security

- The link back to the list (`tainacan_interface_public_list_args()`) dropped `metakey` when the visitor could not read it, but kept `metavalue`, `metavaluenum` and `metacompare`. Tainacan copies `metavalue` to `meta_value` even with no key, and `WP_Meta_Query` then matches the value in any field, private ones included. The three now go with the key, the same rule as `meta_key` in the item navigation (review of tainacan/tainacan-theme#93).

### Fixed

- Item navigation: the visitor's own items enter only as `draft`, `pending` or `private`; with `status=any` or `status=trash` the author's trash and auto-drafts no longer show up.
- The comment of the `request` filter of the terms list now says what it does: the HTTP 500 came from `termspaged`; `perpage` is not a public query var (the terms per page come from the `tainacan-default-taxonomy-terms-perpage` filter), so its clamp only acts on a site that registers it.

## 2.20.1 (2026-11-25)

### Added

- In the Customizer preview, a pencil on the description of a collection (`.tt2-head__description`, shortcut `coll_desc`) opens the option "Description" of Collection header, with its width right below. The pencil of the whole header still opens the layout.

## 2.20.0 (2026-11-25)

### Added

- **Width of the description of a collection** (`head_desc_width`, Customize > Collections and item lists > Collection header, and the form of each collection): as the layout of the header (780, 860 or 900 px as before), narrow 620 px, medium 780 px, wide 1040 px, the whole width of the page, or "width of my choice" (`head_desc_px`, 400–1600 px). Class `tt2-head--desc-<value>` on the header and the token `--tt2-desc-width` for child themes.
- **Size of the capsules of the record** (`item_chip_size`, 100 = base text, default 93 = 0.93rem, the size of the capsules of the Acervo em Cena plugin on the vitrine), token `--tt2-chip-size`.

### Fixed

- The capsules of the record had three sizes on the same page (bnb and bpb): the short values were 0.875 em of the record, which the older `style.css` sets to 0.813rem (11.4 px); the terms 0.875 em of the value (14 px); and a short value of a long-text metadatum, such as "Notes" of an authority, 1.05 of the value (16.8 px). Now all capsules use `--tt2-chip-size` and the reading size of long text does not reach a capsule.

## 2.19.1 (2026-11-25)

### Fixed

- Tainacan blocks with the look of the theme, after the first look at the vitrine: the card fills the slide of the carousel (it shrank to the image of 120 px); the size of each term of the facets cloud is written by the plugin on the link, not on the item, and the gap of the list in its style attribute; the mosaic paints the image as the background of the item, which the tile covered, and now draws the "no image" picture at its own size there. On phones the carousels fit the screen (the plugin widened them past both edges by their "space around") and the arrows go over the images.

## 2.19.0 (2026-11-25)

### Added

- **Tainacan blocks in pages take the look of the theme** (`inc/tainacan-blocks.php`, `assets/scss/tainacan-blocks.scss` → `assets/css/tainacan-blocks.css`, `assets/js/tainacan-blocks.js`). Carousels of items, collections and terms, the list of items (grid, list, mosaic), the lists of collections and terms and the facets list used to keep the plugin look: 120 px pictures in wide slides, underlined titles, a cloud of facets in giant letters, the "no image" drawing as big as a mosaic tile. Now:
  - cards, clean tiles or title over the image; shape of the images (as the block asks, square, 4:3, 3:2, 16:9, 3:4) and fill or whole image; lines of the titles; zoom, lift or nothing on hover; corners; round arrows (white, accent or the plugin ones); slides of equal height and centered when they all fit;
  - facets in cloud or list as labels with the number in a badge (same size, or five sizes by the number of items), facets with images as cards;
  - mosaic with rounded frame, title on a dark gradient on hover, the "no image" picture at its own size;
  - focus ring, `prefers-reduced-motion`.
- **Four ways to choose it.** Customize > Collections and item lists > Tainacan blocks in pages (options `tblocks_*`, the default of the site, including "the look of the Tainacan plugin"); the Styles panel of each block in the editor (Cards, Clean, Title over the image, Labels, Cloud of labels, Tainacan plugin); classes in "Additional CSS class(es)" for one detail of one block (`tt2-ratio-4-3`, `tt2-fit-contain`, `tt2-lines-1`, `tt2-hover-none`, `tt2-arrows-plain`, `tt2-radius-round` and siblings); tokens `--tt2-tb-*` and the filter `tainacan_interface_tblocks_classes` for child themes. The classes are put on the root of the block when it renders (`render_block`), so blocks saved before need no edit.

### Fixed

- The underline of links in running text (`_gutenberg-support.scss`) and the option "links underlined" (`modern.css`) no longer reach the items, collections, terms and facets of the Tainacan blocks.
- The button styles of the theme (radius, weight, the "outline" mode with `!important`) no longer reach the arrows of the Tainacan carousels, which are `wp-element-button`.
- An outline button over a cover or a group with its own text color takes that color; the accent of the site disappeared on a dark cover (vitrine, "Conhecer as soluções").

## 2.18.2 (2026-11-24)

### Fixed

- **Terms explorer: items without thumbnail get the "no image" picture** (`assets/js/terms-explorer.js`, `inc/terms-explorer.php`, `assets/css/terms-explorer.css`). The panel "Items with this term" on the taxonomy page drew an empty gray square (`.tt2-tx-thumb.is-empty`); 2.18.0 had covered the lists, blocks and the threads beside the item, not this panel. It now picks the kind from `document_mimetype` (sent by the Tainacan REST with the thumbnail since 1.3.0) with the same table as `inc/placeholders.php`. With `placeholder_style` = original the gray square stays. Seen on the BPB subjects page.

## 2.18.1 (2026-11-24)

### Fixed

- **`tainacan-interface-single-item-metadata-begin` fires before the sections are rendered**, not only before they are printed (`template-parts/single-items-metadata.php`), as in 4.x. Since 4.4.8 the sections were rendered first: TainacanBiblio, which prints its availability panel at "begin" and otherwise falls back to the `--index-0` section filter, put the panel into the first section, which Tainacan then dropped (it held only the title), and found it already done at "begin". The item page of the BPB lost the panel with copies and "Reserve"; found in the rehearsal of 4.4.6 → 2.x on the BPB.

## 2.18.0 (2026-11-24)

### Added

- **Pictures for what has no image** (`inc/placeholders.php`, `assets/js/placeholders.js`). Items, collections and terms without a thumbnail show a drawing of the kind of file instead of the gray square of the Tainacan plugin: image, audio, video, PDF, text, spreadsheet, presentation, compressed file, 3D model, link, other file, no document. Word, spreadsheet, presentation, compressed and 3D files, which the plugin draws as an empty square, get their own picture.
  - Option `placeholder_style` in Customize > Item cards: a color for each kind (default), the colors of the site (brand and accent, also those of the collection), or the plugin pictures.
  - SVG drawn by the theme at `/?tt2-ph=<kind>&s=<s|m>&v=<version>`, kept by the browser for a year; the version in the address changes with the colors, the language and the theme. The label is translated.
  - Covers the PHP filter `tainacan-get-the-mime-type-icon`, the `wp.hooks` filter `tainacan_get_the_mime_type_icon` (lists of items and blocks), the blocks that write the gray square straight in the page, the grid view mode of the theme and the threads beside the item.
  - Icons adapted from Lucide (ISC).

## 2.17.0 (2026-11-23)

### Added

- **Kit for AI** (`ai-kit/`, Appearance > Kit for AI, a section in Customize and a notice on the dashboard until dismissed). Lets Claude, ChatGPT, Gemini or any other AI change this theme the right way: make a child theme, set the options and build pages with blocks that keep Tainacan and the block editor working.
  - **Instructions for any AI**: `AGENTS.md` (with `CLAUDE.md` and `GEMINI.md` pointing to it): reading order, the order of decisions (options, tokens, patterns, hooks, copies of templates last), the rules and a checklist.
  - **Guides** (`docs/`, pt-BR): how the theme works, the child theme standard, options without code, which blocks can be written by hand, design recipes (home, collection, exhibition, about, visit) with accessibility and performance, validation.
  - **Reference**: `options.json` (the 146 options with defaults and accepted values, made by `wp tainacan-interface kit reference`), CSS tokens, hooks, the markup of the Tainacan 1.4 blocks as the editor saves them.
  - **Starter child theme** (`starter-child/`): loads after the parent, starting values of the options, `theme.json` without its own palette, and block patterns for a home page of a collection (opening with search, items in a carousel, the collections in cards, about, visit, and the whole page). Validated in the block editor; laid out for 390 px.
  - **Ready requests** for Claude, ChatGPT, Gemini and coding agents, copied from the admin page.
  - **Context of the site** (`site-context.json`, made when the kit is downloaded): public collections with their metadata, taxonomies with the most used terms, pages, menus, options changed, versions. No users, e-mails or items.
  - **Admin page**: download the kit with the context, download the starter child with the name of the site, the requests for each AI, and a check of an installed child theme (header and parent, outside files, copies of templates, patterns, `theme.json`), with a link to preview it in Customize.
  - **Command line**: `wp tainacan-interface kit context|zip|child|check|blocks|reference`.
  - `tools/check-editor.js`: opens pages in the block editor with a headless browser and lists invalid blocks (optional, Node + Puppeteer).
- **Search the collection** block (`tainacan-interface/search`): a search box rendered by the server that opens the Tainacan items list (all items or one collection).
- **Block styles**: Carousel and Cards (Query Loop), Panel and Band on top (Group), Brand veil (Cover).
- **Palette that follows Customize**: `site-brand`, `site-accent`, `site-text`, `site-surface`, `site-page` in the block editor; the design tokens also apply inside the editor.
- **Rebuild of Tainacan blocks in the editor**: blocks whose HTML only the editor makes (carousels, lists, faceted search) written outside it are rebuilt from their settings when the page opens; other invalid blocks get a "Rebuild them" notice.

### Fixed

- **Palette of the theme lost after the Blocksy import** (2.16.0): the imported palette replaced the palette of the theme instead of adding to it.
- **Child theme activation**: a child theme without options of its own now gets a copy of the options of the parent (with undo), as the notice promised.
- Headings inside a Cover keep the color of the Cover.

## 2.16.0 (2026-11-21)

### Added

- **Import from Blocksy** (Appearance > Import from Blocksy, also a section in Customize and a notice after switching from Blocksy). Moves a site made with Blocksy and Tainacan Support for Blocksy to this theme in three steps: where the settings come from, what each one becomes, what was done.
  - **Sources.** The settings this site kept in `theme_mods_blocksy` (or of a Blocksy child theme), or the `.dat` file the Blocksy Companion exports (Customize > Manage Options > Export Customizations), or a JSON of the theme mods. The `.dat` is PHP `serialize()` with `stdClass` widgets: it is read by a parser of the format that never calls `unserialize()` (objects become arrays, references become null, `C:` fails) and tolerates string lengths broken by an editor, as the Companion does (`Tainacan_Interface_Serialized_Reader`).
  - **Mapping** (`Tainacan_Interface_Blocksy_Map`). Palette and global colors, typography (fonts this theme does not serve become the nearest in style), widths and spacing, buttons and fields, the header builder (arrangement, row color, including a solid "transparent" header shown on most pages, menu type and font, mobile menu, search, button, logo and its height), the footer builder, social profiles, breadcrumb and back to top, posts, pages, hero and sidebar, the search, filters and item page options of Tainacan Support for Blocksy (from the repository list or what most collections use, with the differing collections named), the item layout and metadata sections of each collection, menu locations, widgets and Additional CSS. Blocksy's defaults are applied to keys never saved. Each row says whether it comes the same, as the nearest value, adjusted to keep the look, or has no equivalent; what was not read is listed by area. Colors this theme has no option for (header and footer of any color, an exact button radius) go into a short block of tokens at the top of the imported Additional CSS.
  - **Images.** From this site the logo, the banner and the images in the Additional CSS are reused; from another site they are downloaded once to the media library (deduplicated by their address), and an image known only by its id is asked to the public media endpoint of the other site. SVG is not accepted, as in WordPress.
  - **Content.** Blocks painted with the Blocksy palette (`has-palette-color-N-*`, `--theme-palette-color-N`, `--theme-text-color` and the like) keep their colors, and the palette is offered in the block editor.
  - **Try and undo.** "Try it in Customize, without saving" opens Customize with a draft changeset of the import. Undo restores the theme mods, the Additional CSS, the widgets and the meta of the collections touched.
  - **Command line.** `wp tainacan-interface blocksy plan|apply|undo [--source=site:<theme>] [--file=<path>] [--groups=<list>] [--format=json]`.
- pt_BR translation of the importer.

## 2.15.2 (2026-11-20)

### Fixed

- **Root-relative `?ref=`.** An address that starts with `/` (the `$route.href` of the Tainacan lists) gets only the origin of the site in front: it already carries the install path, and `home_url()` added it again on a site in a subdirectory, so the "back to the list" link pointed to a page that does not exist.

## 2.15.1 (2026-11-20)

Follow-up of the review of tainacan/tainacan-theme#93 (item navigation and the "back to the list" link).

### Security

- **Core title and description keys.** `tainacan_core_title` and `tainacan_core_description` were accepted as filters of the navigation without any check, and Tainacan only expands them later (`Items::fetch()`), so a private description, or one in a private section, could be tested through the "next" link. They now pass only when the title or description metadatum of every collection Tainacan expands them to passes the test of a metadatum ID: the list's collection, or, for a term or repository list, every collection whose item post type is registered, as `Items::fetch()` gathers them (`tainacan_interface_queryable_core_key()`).
- **Back to the list.** The link keeps the query of `?ref=` (the list address the Tainacan lists send), so with plain permalinks it no longer turns into the item (`?p=`) and keeps `?post_type=`; the variables that name the item or the navigation are left out; unless the visitor may see the whole list, `status` and filters or an order on metadata and taxonomies the visitor may not read are left out too (`tainacan_interface_public_list_args()`). `?ref=` is checked by parsing and comparing scheme, host and port: `wp_http_validate_url()` resolved the host of any other site, a DNS query from the server while the page rendered.
- The author filter stays out of the narrowed navigation and of the back link, as before.
- **Anonymous visitors** never navigate a list unfiltered and never get private items in it, whatever capabilities a site may grant them.
- CSS background images (grid view mode, top banner, 404 page) are quoted, and quotes, parentheses and backslashes in the address are percent-encoded before `esc_url()`, which keeps them (`tainacan_interface_css_url()`).

### Changed

- **Own drafts in the navigation.** The status asked by the list is kept and the navigation query keeps, in SQL, only items in a public status, the visitor's own items and, for a logged-in visitor who may read them, private items of the list's collection (`tainacan_interface_navigation_where()`). An author who edits only their own items walks the list of their drafts; the drafts of others never appear. Before, the status was dropped for everyone but editors of others' items.
- **Private metadata sections** count only for the list's collection: a repository metadatum in a private section of another collection is filterable again in a collection list where it is public. A term or repository list spans collections, so every section still counts there.

## 2.15.0 (2026-11-20)

### Changed

- **Customizer sections with form and pictures.** In the sections of the theme each option is a card; check boxes are switches on the right of their name; help is plain text, not italics; fields and lists are rounded; radio buttons without pictures are rows with the chosen one marked.
- **Where it is on the page.** Each section of the theme, except the ones of style that apply everywhere (colors, typography, layout, buttons), opens with a sketch of the page (list, item, collections, blog or post) with the part it changes marked in amber, followed by its help (`inc/customizer-drawings.php`, `tainacan_interface_section_intro()`).
- **42 more options chosen by pictures** of the result (`tainacan_interface_site_drawing()`): band, shadow, spacing, heading color and case, menu case, button shape, style and case, fields, links, selection, header style, menu mark, phone menu, header layout, footer style and tone, profiles, breadcrumb style and separator, banner width, list of collections (style, columns, ratio, image), card mark and hover, preview side and image, chips, labels and columns of the metadata, top banner style and alignment, page titles, blog layout and columns, sidebar, text width and back-to-top shape and side.

### Fixed

- The pictures of the Customizer took the colors being set only if they were already drawn; the variables now sit on the panel (`#customize-controls`), so a section opened later shows them too. The band colors are followed as well.

## 2.14.3 (2026-11-20)

### Removed

- Files nothing loads any more: `lib/class-wp-bootstrap-navwalker.php` (the menu uses `inc/class-tainacan-interface-nav-walker.php`), the collection loops of the old view modes (`template-parts/loop-tainacan-collection.php`, `-grid.php`, `-table.php`; the list of collections is `template-parts/collections-grid.php`), `template-parts/header-social-share.php` (the collection header prints its own share links) and `tainacan/README`.
- Functions only those loops used: `tainacan_active()` and `tainacan_interface_list_view_mode()`, and the query var `tainacan_collections_viewmode`.

## 2.14.2 (2026-11-20)

- Customizer menu: icons of 16 px (were 20); the mark on hover is a shadow, not a border, and the line under each title counts its padding within its width; both widened the menu past the panel and brought a horizontal scroll bar. (2.14.1 lived a few minutes on one test site.)

## 2.14.0 (2026-11-20)

### Changed

- **One Customizer, organized by topic.** Five panels take the place of "Look of the site and collections" and of the three panels of the original theme ("Site header settings", "Tainacan items list pages", "Tainacan single item pages") and its loose sections (footer, share, list of posts, header image):
  - **Style of the site:** Colors · Typography · Layout, shapes and spacing · Buttons, fields and links · Back to top and performance.
  - **Header, menu and footer:** Site header · Search in the header · Breadcrumb · Footer · Profiles of the institution · Share buttons.
  - **Collections and item lists:** List of collections · Collection header · Item cards · Search and view modes · Filters · Pagination · Item preview.
  - **Item page:** Layout and metadata · Document and attachments · Side column · Related items · Previous and next item.
  - **Posts and pages:** Top banner (with the header image of the site) · Page titles · List of posts · A post or page · Sidebar.
- Each panel and section of the menu shows an icon and a line saying what it holds; a label separates the panels of the theme from the content of the site (menus, widgets, home page). The switch of each feature (item preview, file viewer, side column) sits on top of the section of its options. The tree lives in `tainacan_interface_settings_panels()` and `tainacan_interface_settings_sections()` (`inc/settings.php`), which also say where the preview goes when a section opens.
- With the file viewer of the theme on, the lightbox options of the gallery leave the panel (the viewer replaces the lightbox).

### Removed

- Options of the original that repeated newer ones: "Fix header position during scroll" (now "Keep the header visible on scroll"), "Hide the header banner completely" (now "Top banner: No banner"), "Hide the item publish date and author" (now the line under the title of the item). Migration step 4 carries a value chosen there to the newer option, unless the site already chose that one.
- Options with no effect on the new design: "Header elements alignment" and "Site header minimum height"; the core "Colors" section and the title/tagline switches (the theme does not print them), and the background image when the theme does not support it.
- The setting of the 2.x sections order, kept only for a migration that reads the stored value.

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

