<?php get_header(); ?>

<!-- Get the banner to display -->
<?php get_template_part( 'template-parts/bannerheader' ); ?>

	<?php tainacan_the_faceted_search([
		'default-view-mode' => get_theme_mod('tainacan_items_repository_list_default_view_mode', 'masonry'),
		'hide-filters' => get_theme_mod('tainacan_items_page_hide_filters', false),
		'hide-hide-filters-button' => get_theme_mod('tainacan_items_page_hide_hide_filters_button', false),
		'hide-search' => get_theme_mod('tainacan_items_page_hide_search', false),
		'hide-advanced-search' => get_theme_mod('tainacan_items_page_hide_advanced_search', false),
		'hide-sort-by-button' => get_theme_mod('tainacan_items_page_hide_sort_by_button', false),
		'hide-exposers-button' => get_theme_mod('tainacan_items_page_hide_exposers_button', false),
		'hide-items-per-page-button' => get_theme_mod('tainacan_items_page_hide_items_per_page_button', false),
		'hide-go-to-page-button' => get_theme_mod('tainacan_items_page_hide_go_to_page_button', false),
		'show-filters-button-inside-search-control' => get_theme_mod('tainacan_items_page_show_filters_button_inside_search_control', false),
		'start-with-filters-hidden' => get_theme_mod('tainacan_items_page_start_with_filters_hidden', false),
		'filters-as-modal' => get_theme_mod('tainacan_items_page_filters_as_modal', false),
		'show-inline-view-mode-options' => get_theme_mod('tainacan_items_page_show_inline_view_mode_options', false),
		'show-fullscreen-with-view-modes' => get_theme_mod('tainacan_items_page_show_fullscreen_with_view_modes', false)
	]); ?>

<?php get_footer(); ?>
