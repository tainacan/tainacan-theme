<?php
/**
 * Tainacan Interface - Explanatory Modals & Tooltips System
 *
 * Provides contextual help modals and tooltips for both
 * backend (admin/customizer) and frontend (visitor-facing) interfaces.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all registered help content
 */
function tainacan_get_help_content() {
	return array(
		// Frontend modals
		'faceted-search' => array(
			'title'   => __( 'How to Search', 'tainacan-interface' ),
			'content' => __( 'Use the filters on the left to narrow your results. You can combine multiple filters, search by keyword, and change the view mode. Click on any item to see its full details.', 'tainacan-interface' ),
			'context' => 'frontend',
			'icon'    => 'tainacan-icon-search',
		),
		'view-modes' => array(
			'title'   => __( 'View Modes', 'tainacan-interface' ),
			'content' => __( 'Switch between different display modes: Cards show a preview with description, Grid shows thumbnails in a compact layout, Table provides a detailed spreadsheet-like view, and other modes offer specialized displays.', 'tainacan-interface' ),
			'context' => 'frontend',
			'icon'    => 'tainacan-icon-viewcards',
		),
		'filters-panel' => array(
			'title'   => __( 'Using Filters', 'tainacan-interface' ),
			'content' => __( 'Filters allow you to refine your search. Select values in one or more filters to narrow down results. Active filters appear as tags that can be removed individually. The number next to each filter option shows how many items match.', 'tainacan-interface' ),
			'context' => 'frontend',
			'icon'    => 'tainacan-icon-filter',
		),
		'item-gallery' => array(
			'title'   => __( 'Media Gallery', 'tainacan-interface' ),
			'content' => __( 'Click on any image to open it in the lightbox viewer. Use arrow keys or swipe to navigate between images. The gallery shows the main document and all attachments associated with this item.', 'tainacan-interface' ),
			'context' => 'frontend',
			'icon'    => 'tainacan-icon-attachments',
		),
		'metadata-sections' => array(
			'title'   => __( 'Item Metadata', 'tainacan-interface' ),
			'content' => __( 'Metadata are organized in sections. Click on section tabs or headers to navigate between them. Each section contains related information fields about this item.', 'tainacan-interface' ),
			'context' => 'frontend',
			'icon'    => 'tainacan-icon-metadata',
		),
		'related-items' => array(
			'title'   => __( 'Related Items', 'tainacan-interface' ),
			'content' => __( 'These items share common characteristics with the current item. Browse them to discover related content in the collection.', 'tainacan-interface' ),
			'context' => 'frontend',
			'icon'    => 'tainacan-icon-items',
		),
		// Backend modals
		'admin-layout-types' => array(
			'title'   => __( 'Layout Types', 'tainacan-interface' ),
			'content' => __( 'Choose how the single item page is structured: Document-Attachments-Metadata shows the document first, followed by attachments gallery and metadata below. Gallery-Metadata combines document and attachments into a unified gallery alongside metadata. Other options rearrange these sections to best suit your content.', 'tainacan-interface' ),
			'context' => 'backend',
			'icon'    => 'dashicons-layout',
		),
		'admin-pastel-colors' => array(
			'title'   => __( 'Pastel Color Palettes', 'tainacan-interface' ),
			'content' => __( 'Pastel palettes provide soft, harmonious colors across all theme elements. Each palette includes primary, secondary, accent, background, surface, text, and muted color variants. Choose "Custom" to define your own colors. The dark mode toggle allows visitors to switch to a dark theme.', 'tainacan-interface' ),
			'context' => 'backend',
			'icon'    => 'dashicons-art',
		),
		'admin-collection-settings' => array(
			'title'   => __( 'Per-Collection Appearance', 'tainacan-interface' ),
			'content' => __( 'Each collection can have its own visual identity. Set custom colors, choose layout types, configure metadata display, and define typography settings independently. Items that do not belong to a specific collection will use the global theme settings.', 'tainacan-interface' ),
			'context' => 'backend',
			'icon'    => 'dashicons-admin-appearance',
		),
		'admin-typography' => array(
			'title'   => __( 'Typography Settings', 'tainacan-interface' ),
			'content' => __( 'Configure fonts and sizes for different sections of the item page independently. You can set different typography for metadata labels, metadata values, document titles, section headings, and navigation elements. This allows precise control over the visual hierarchy.', 'tainacan-interface' ),
			'context' => 'backend',
			'icon'    => 'dashicons-editor-textcolor',
		),
		'admin-gallery-unified' => array(
			'title'   => __( 'Unified Gallery Mode', 'tainacan-interface' ),
			'content' => __( 'When enabled, the document and attachments are displayed together in a single gallery component. This provides a seamless media browsing experience with thumbnail navigation. You can configure the gallery to be sticky (follows scroll), set its color scheme, and control thumbnail sizes.', 'tainacan-interface' ),
			'context' => 'backend',
			'icon'    => 'dashicons-format-gallery',
		),
		'admin-lightbox' => array(
			'title'   => __( 'Lightbox Settings', 'tainacan-interface' ),
			'content' => __( 'The lightbox is a fullscreen image viewer that opens when clicking on media items. Configure what information is displayed: file names, captions, descriptions, and download buttons. You can also choose between light and dark color schemes for the lightbox overlay.', 'tainacan-interface' ),
			'context' => 'backend',
			'icon'    => 'dashicons-visibility',
		),
	);
}

/**
 * Render a help button that triggers a modal
 */
function tainacan_help_button( $modal_id, $size = 'small' ) {
	$help_content = tainacan_get_help_content();

	if ( ! isset( $help_content[ $modal_id ] ) ) {
		return '';
	}

	$item = $help_content[ $modal_id ];
	$size_class = $size === 'large' ? 'tainacan-help-btn--large' : 'tainacan-help-btn--small';

	$html = '<button type="button" class="tainacan-help-btn ' . esc_attr( $size_class ) . '" ';
	$html .= 'data-modal-id="' . esc_attr( $modal_id ) . '" ';
	$html .= 'aria-label="' . esc_attr( sprintf( __( 'Help: %s', 'tainacan-interface' ), $item['title'] ) ) . '" ';
	$html .= 'title="' . esc_attr( $item['title'] ) . '">';
	$html .= '<span class="tainacan-help-btn__icon" aria-hidden="true">?</span>';
	$html .= '</button>';

	return $html;
}

/**
 * Render the modal container (once per page)
 */
function tainacan_render_modal_container() {
	$help_content = tainacan_get_help_content();
	$show_modals = get_theme_mod( 'tainacan_show_help_modals', true );

	if ( ! $show_modals ) {
		return;
	}
	?>
	<div id="tainacan-modal-overlay" class="tainacan-modal-overlay" role="presentation" aria-hidden="true">
		<div class="tainacan-modal" role="dialog" aria-modal="true" aria-labelledby="tainacan-modal-title">
			<div class="tainacan-modal__header">
				<div class="tainacan-modal__icon" aria-hidden="true"></div>
				<h3 id="tainacan-modal-title" class="tainacan-modal__title"></h3>
				<button type="button" class="tainacan-modal__close" aria-label="<?php esc_attr_e( 'Close', 'tainacan-interface' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="tainacan-modal__body">
				<p class="tainacan-modal__content"></p>
			</div>
			<div class="tainacan-modal__footer">
				<label class="tainacan-modal__dismiss-label">
					<input type="checkbox" class="tainacan-modal__dismiss-check" />
					<?php esc_html_e( 'Do not show this again', 'tainacan-interface' ); ?>
				</label>
				<button type="button" class="tainacan-modal__ok-btn">
					<?php esc_html_e( 'Got it!', 'tainacan-interface' ); ?>
				</button>
			</div>
		</div>
	</div>
	<script id="tainacan-help-data" type="application/json"><?php echo wp_json_encode( $help_content ); ?></script>
	<?php
}
add_action( 'wp_footer', 'tainacan_render_modal_container' );

/**
 * Render admin modals container
 */
function tainacan_render_admin_modal_container() {
	if ( ! is_admin() ) {
		return;
	}

	$help_content = tainacan_get_help_content();
	$admin_content = array_filter( $help_content, function( $item ) {
		return $item['context'] === 'backend';
	} );

	if ( empty( $admin_content ) ) {
		return;
	}
	?>
	<div id="tainacan-admin-modal-overlay" class="tainacan-admin-modal-overlay" style="display:none;">
		<div class="tainacan-admin-modal">
			<div class="tainacan-admin-modal__header">
				<span class="tainacan-admin-modal__icon dashicons"></span>
				<h3 class="tainacan-admin-modal__title"></h3>
				<button type="button" class="tainacan-admin-modal__close">&times;</button>
			</div>
			<div class="tainacan-admin-modal__body">
				<p class="tainacan-admin-modal__content"></p>
			</div>
			<div class="tainacan-admin-modal__footer">
				<button type="button" class="button button-primary tainacan-admin-modal__ok"><?php esc_html_e( 'Got it!', 'tainacan-interface' ); ?></button>
			</div>
		</div>
	</div>
	<script>
	(function() {
		var adminHelp = <?php echo wp_json_encode( $admin_content ); ?>;
		document.addEventListener('click', function(e) {
			var btn = e.target.closest('.tainacan-help-btn');
			if (!btn) return;
			var id = btn.getAttribute('data-modal-id');
			if (!adminHelp[id]) return;
			var overlay = document.getElementById('tainacan-admin-modal-overlay');
			overlay.querySelector('.tainacan-admin-modal__title').textContent = adminHelp[id].title;
			overlay.querySelector('.tainacan-admin-modal__content').textContent = adminHelp[id].content;
			overlay.querySelector('.tainacan-admin-modal__icon').className = 'tainacan-admin-modal__icon dashicons ' + adminHelp[id].icon;
			overlay.style.display = 'flex';
		});
		document.addEventListener('click', function(e) {
			if (e.target.closest('.tainacan-admin-modal__close') || e.target.closest('.tainacan-admin-modal__ok') || e.target.id === 'tainacan-admin-modal-overlay') {
				document.getElementById('tainacan-admin-modal-overlay').style.display = 'none';
			}
		});
	})();
	</script>
	<?php
}
add_action( 'admin_footer', 'tainacan_render_admin_modal_container' );

/**
 * Register customizer setting for help modals
 */
function tainacan_modal_customizer_settings( $wp_customize ) {
	$wp_customize->add_setting( 'tainacan_show_help_modals', array(
		'default'           => true,
		'sanitize_callback' => 'tainacan_callback_sanitize_checkbox',
	) );

	$wp_customize->add_control( 'tainacan_show_help_modals', array(
		'label'       => __( 'Show contextual help buttons', 'tainacan-interface' ),
		'description' => __( 'Display help buttons (?) next to key interface elements that open explanatory modals when clicked.', 'tainacan-interface' ),
		'section'     => 'tainacan_pastel_colors',
		'type'        => 'checkbox',
	) );
}
add_action( 'customize_register', 'tainacan_modal_customizer_settings' );
