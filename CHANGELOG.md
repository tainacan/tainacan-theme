# Changelog

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

Public release of the new design, on top of the official 2.10.0. The design was built in a fork as development builds numbered 3.0.0 to 4.10.2, never published; 2.11.0 contains all of them and continues the numbering of the official 2.x. Their entries are not repeated here; what changed from 2.10.0 is summarized in the pull request that brings this release.

- Upgrade: what a site still needs is counted in migration steps (`migration` in the `tainacan_interface` option), not by comparing the version it last saw. With 4.x back to 2.11, comparing versions would run old migrations again on every update. Sites that saw 4.3.0+ count as step 1 and 4.9.0+ as step 2.
- Texts: "version 4" became "version 2.11" in the description of the theme and the readme; the Customizer section of the extra features no longer says "version 3".
- Reactivating the theme keeps the migration steps a site still lacks (it used to mark the site as up to date).
