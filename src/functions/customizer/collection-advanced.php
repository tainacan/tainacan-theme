<?php
/**
 * Tainacan Interface - Advanced Per-Collection Customizer
 *
 * Extends collection-level settings with full customizer options:
 * layout type, typography, gallery, and color overrides per collection.
 *
 * @package Tainacan_Interface
 * @since 2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extended collection settings in Tainacan admin
 */
class Tainacan_Interface_Collection_Advanced {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'tainacan-register-admin-hooks', array( $this, 'register_hooks' ) );
		add_filter( 'tainacan-api-response-collection-meta', array( $this, 'api_response_meta' ), 10, 3 );
	}

	public function register_hooks() {
		if ( ! class_exists( '\Tainacan\Admin_Hooks' ) ) {
			return;
		}

		$admin_hooks = \Tainacan\Admin_Hooks::get_instance();

		// Appearance source selector
		$admin_hooks->register( 'collection', array(
			'class'    => get_class( $this ),
			'instance' => $this,
			'method'   => 'form_appearance_source',
			'type'     => 'begin-left',
			'label'    => __( 'Appearance Settings', 'tainacan-interface' ),
		) );

		// Layout type per collection
		$admin_hooks->register( 'collection', array(
			'class'    => get_class( $this ),
			'instance' => $this,
			'method'   => 'form_layout_type',
			'type'     => 'begin-left',
			'label'    => __( 'Item Page Layout', 'tainacan-interface' ),
		) );

		// Collection accent color
		$admin_hooks->register( 'collection', array(
			'class'    => get_class( $this ),
			'instance' => $this,
			'method'   => 'form_accent_color',
			'type'     => 'begin-left',
			'label'    => __( 'Collection Accent Color', 'tainacan-interface' ),
		) );
	}

	/**
	 * Form: Appearance source selector
	 */
	public function form_appearance_source( $args ) {
		$collection_id = $args['collection_id'] ?? 0;
		$source = get_post_meta( $collection_id, 'tainacan_interface_appearance_source', true );
		if ( ! $source ) {
			$source = 'global';
		}
		?>
		<div class="tainacan-interface-appearance-source">
			<p class="description">
				<?php esc_html_e( 'Choose whether this collection uses global theme settings or its own custom appearance.', 'tainacan-interface' ); ?>
				<?php echo tainacan_help_button( 'admin-collection-settings' ); ?>
			</p>
			<label>
				<input type="radio" name="tainacan_interface_appearance_source"
					value="global" <?php checked( $source, 'global' ); ?> />
				<?php esc_html_e( 'Use global settings', 'tainacan-interface' ); ?>
			</label>
			<br/>
			<label>
				<input type="radio" name="tainacan_interface_appearance_source"
					value="custom" <?php checked( $source, 'custom' ); ?> />
				<?php esc_html_e( 'Use custom appearance for this collection', 'tainacan-interface' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Form: Layout type per collection
	 */
	public function form_layout_type( $args ) {
		$collection_id = $args['collection_id'] ?? 0;
		$layout = get_post_meta( $collection_id, 'tainacan_interface_layout_type', true );
		if ( ! $layout ) {
			$layout = 'type-dam';
		}

		$layouts = array(
			'type-dam' => __( 'Document → Attachments → Metadata', 'tainacan-interface' ),
			'type-dma' => __( 'Document → Metadata → Attachments', 'tainacan-interface' ),
			'type-mda' => __( 'Metadata → Document → Attachments', 'tainacan-interface' ),
			'type-gm'  => __( 'Gallery (sidebar) → Metadata', 'tainacan-interface' ),
			'type-gtm' => __( 'Gallery (top) → Metadata', 'tainacan-interface' ),
			'type-mg'  => __( 'Metadata → Gallery (sidebar)', 'tainacan-interface' ),
		);
		?>
		<div class="tainacan-interface-layout-type">
			<p class="description">
				<?php esc_html_e( 'Choose the layout structure for single item pages in this collection.', 'tainacan-interface' ); ?>
				<?php echo tainacan_help_button( 'admin-layout-types' ); ?>
			</p>
			<select name="tainacan_interface_layout_type">
				<?php foreach ( $layouts as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $layout, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Form: Collection accent color
	 */
	public function form_accent_color( $args ) {
		$collection_id = $args['collection_id'] ?? 0;
		$color = get_post_meta( $collection_id, 'tainacan_interface_accent_color', true );
		?>
		<div class="tainacan-interface-accent-color">
			<p class="description">
				<?php esc_html_e( 'Set a custom accent color for this collection. Leave empty to use the global palette.', 'tainacan-interface' ); ?>
			</p>
			<input type="text" class="tainacan-color-picker"
				name="tainacan_interface_accent_color"
				value="<?php echo esc_attr( $color ); ?>"
				data-default-color="" />
		</div>
		<?php
	}

	/**
	 * Save collection meta via API
	 */
	public function api_response_meta( $extra_metadata, $request, $collection ) {
		$body = json_decode( $request->get_body(), true );
		$collection_id = $collection->get_id();

		$fields = array(
			'tainacan_interface_appearance_source',
			'tainacan_interface_layout_type',
			'tainacan_interface_accent_color',
		);

		foreach ( $fields as $field ) {
			if ( isset( $body[ $field ] ) ) {
				update_post_meta( $collection_id, $field, sanitize_text_field( $body[ $field ] ) );
			}
		}

		return $extra_metadata;
	}
}

/**
 * Get effective layout type for a collection
 */
function tainacan_get_collection_layout_type( $collection_id = 0 ) {
	if ( $collection_id ) {
		$source = get_post_meta( $collection_id, 'tainacan_interface_appearance_source', true );
		if ( $source === 'custom' ) {
			$layout = get_post_meta( $collection_id, 'tainacan_interface_layout_type', true );
			if ( $layout ) {
				return $layout;
			}
		}
	}

	// Fall back to customizer global setting
	$layout_type = get_theme_mod( 'tainacan_single_item_layout_type', 'type-dam' );

	// Backwards compatibility with old 3-option order setting
	if ( ! $layout_type || $layout_type === 'type-dam' ) {
		$old_order = get_theme_mod( 'tainacan_single_item_layout_sections_order', 'document-attachments-metadata' );
		$map = array(
			'document-attachments-metadata' => 'type-dam',
			'metadata-document-attachments' => 'type-mda',
			'document-metadata-attachments' => 'type-dma',
		);
		if ( isset( $map[ $old_order ] ) ) {
			return $map[ $old_order ];
		}
	}

	return $layout_type;
}

/**
 * Check if layout type uses unified gallery mode
 */
function tainacan_is_gallery_layout( $layout_type = '' ) {
	if ( ! $layout_type ) {
		$layout_type = tainacan_get_collection_layout_type();
	}
	return in_array( $layout_type, array( 'type-gm', 'type-gtm', 'type-mg' ), true );
}

/**
 * Get collection accent color
 */
function tainacan_get_collection_accent_color( $collection_id = 0 ) {
	if ( $collection_id ) {
		$source = get_post_meta( $collection_id, 'tainacan_interface_appearance_source', true );
		if ( $source === 'custom' ) {
			$color = get_post_meta( $collection_id, 'tainacan_interface_accent_color', true );
			if ( $color ) {
				return sanitize_hex_color( $color );
			}
		}
	}
	return '';
}

// Initialize
add_action( 'init', function() {
	if ( defined( 'TAINACAN_VERSION' ) ) {
		Tainacan_Interface_Collection_Advanced::get_instance();
	}
} );
