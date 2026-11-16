<?php
/**
 * Collection settings class: extra options added to Tainacan collections.
 *
 * @package tainacan-interface
 */

/**
 * Class that hooks in the tainacan plugin (if present) to add new options to Collections
 */
class TainacanInterfaceCollectionSettings {
	/**
	 * Post meta key of the collection background color. The name is kept from the original theme.
	 *
	 * @var string
	 */
	public $tainacan_background_color = 'tainacan_theme_collection_background_color';

	/**
	 * Post meta key of the collection text color.
	 *
	 * @var string
	 */
	public $tainacan_text_color = 'tainacan_theme_collection_color';

	/**
	 * Post meta key of the metadata sections layout.
	 *
	 * @var string
	 */
	public $tainacan_sections_layout = 'tainacan_interface_section_layout';

	/**
	 * Hooks into the Tainacan collection API and admin screens.
	 */
	public function __construct() {
		add_filter( 'tainacan-api-response-collection-meta', array( $this, 'add_meta_to_response' ), 10, 2 );
		add_action( 'tainacan-register-admin-hooks', array( $this, 'register_hook' ) );
		add_action( 'tainacan-insert-tainacan-collection', array( $this, 'save_meta' ) );
		add_action( 'tainacan-enqueue-admin-scripts', array( $this, 'action_tainacan_enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueues the color picker assets on the Tainacan admin.
	 */
	public function action_tainacan_enqueue_admin_scripts() {
		wp_enqueue_style( 'tainacan_collection_extra_options', get_template_directory_uri() . '/assets/css/collection-extra-options.css', array(), TAINACAN_INTERFACE_VERSION );
	}

	/**
	 * Registers the form on the collection page.
	 */
	public function register_hook() {
		if ( function_exists( 'tainacan_register_admin_hook' ) ) {
			tainacan_register_admin_hook( 'collection', array( $this, 'form' ), 'end-right' );
		}
	}

	/**
	 * Prints the form with the extra collection settings.
	 *
	 * @return string Empty when the Tainacan API helper is missing.
	 */
	public function form() {
		if ( ! function_exists( 'tainacan_get_api_postdata' ) ) {
			return '';
		}

		ob_start();
		?>

		<div class="field tainacan-collection--section-header">
			<h4><?php esc_html_e( 'Tainacan Interface extra settings', 'tainacan-interface' ); ?></h4>
			<hr>
		</div>

		<?php
		if ( function_exists( 'tainacan_get_the_metadata_sections' ) ) :
			?>
			<!-- if (version_compare(TAINACAN_VERSION, '0.19') >= 0)-->
		<div class="field tainacan-metadata-section--change-section-layout">
			<label class="label" style="position: relative;">
				<?php esc_html_e( 'Metadata sections layout', 'tainacan-interface' ); ?>
				<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							<i class="tainacan-icon tainacan-icon-help"></i>
						</span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php esc_html_e( 'Metadata sections layout', 'tainacan-interface' ); ?></h5>
						</div> 
						<div class="help-tooltip-body">
							<p><?php esc_html_e( 'Define how the metadata sections will appear in the public item page.', 'tainacan-interface' ); ?></p>
						</div>
					</div>
				</span>
			</label>
			<div class="control is-clearfix metadata-section-options"> 
				<label for="default" id="layout-default" class="layout-options">
					<input
						type="radio" 
						value="default" 
						name="<?php echo esc_attr( $this->tainacan_sections_layout ); ?>"
						id="default" checked>
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/section_default.png" alt="<?php esc_attr_e( 'Default', 'tainacan-interface' ); ?>"  />
					<?php esc_html_e( 'Default', 'tainacan-interface' ); ?>
				</label>
				
				<label for="tabs" id="layout-tabs" class="layout-options">
					<input
						type="radio" 
						value="tabs" 
						name="<?php echo esc_attr( $this->tainacan_sections_layout ); ?>"
						id="tabs">
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/section_tabs.png" alt="<?php esc_attr_e( 'Tabs', 'tainacan-interface' ); ?>"  />
					<?php esc_html_e( 'Tabs', 'tainacan-interface' ); ?>
				</label>

				<label for="collapses" id="layout-collapses" class="layout-options">
					<input
						type="radio" 
						value="collapses" 
						name="<?php echo esc_attr( $this->tainacan_sections_layout ); ?>"
						id="collapses">
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/section_collapse.png" alt="<?php esc_attr_e( 'Collapses', 'tainacan-interface' ); ?>"  />
					<?php esc_html_e( 'Collapses', 'tainacan-interface' ); ?>
				</label>

				<label for="accordion" id="layout-accordion" class="layout-options">
					<input
						type="radio" 
						value="accordion" 
						name="<?php echo esc_attr( $this->tainacan_sections_layout ); ?>"
						id="accordion">
					<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/section_accordion.png" alt="<?php esc_attr_e( 'Accordion', 'tainacan-interface' ); ?>"  />
					<?php esc_html_e( 'Accordion', 'tainacan-interface' ); ?>
				</label>
			</div>
		</div>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Declares the theme's collection meta to the Tainacan API, next to what
	 * other extensions declared (the original replaced their list).
	 *
	 * @param array           $extra_meta Meta keys.
	 * @param WP_REST_Request $request    Request.
	 * @return array
	 */
	public function add_meta_to_response( $extra_meta, $request ) {
		return array_merge(
			is_array( $extra_meta ) ? $extra_meta : array(),
			array(
				$this->tainacan_background_color,
				$this->tainacan_text_color,
				$this->tainacan_sections_layout,
			)
		);
	}

	/**
	 * Saves the collection options. The background is a hex color, the text
	 * color is white or black and the layout one of four: anything else is
	 * dropped. The original stored the values as sent, and they were printed
	 * inside a style tag, so any collection editor could put CSS on the public
	 * pages.
	 *
	 * @param \Tainacan\Entities\Collection $object Collection being saved.
	 */
	public function save_meta( $object ) {
		if ( ! function_exists( 'tainacan_get_api_postdata' ) || ! $object->can_edit() ) {
			return;
		}

		$post = tainacan_get_api_postdata();

		if ( isset( $post->{$this->tainacan_background_color} ) ) {
			$color = sanitize_hex_color( (string) $post->{$this->tainacan_background_color} );
			update_post_meta( $object->get_id(), $this->tainacan_background_color, $color ? $color : '' );
		}
		if ( isset( $post->{$this->tainacan_text_color} ) ) {
			$text = in_array( $post->{$this->tainacan_text_color}, array( '#fff', '#000' ), true ) ? $post->{$this->tainacan_text_color} : '#fff';
			update_post_meta( $object->get_id(), $this->tainacan_text_color, $text );
		}
		if ( isset( $post->{$this->tainacan_sections_layout} ) ) {
			$layout = in_array( $post->{$this->tainacan_sections_layout}, array( 'default', 'tabs', 'collapses', 'accordion' ), true ) ? $post->{$this->tainacan_sections_layout} : 'default';
			update_post_meta( $object->get_id(), $this->tainacan_sections_layout, $layout );
		}
	}
}

/**
 * CSS for a collection's banner colors, built only from validated colors
 * (values saved before the fix are checked again here).
 *
 * @param int    $collection_id  Collection ID.
 * @param string $text_selectors Selectors that take the text color.
 * @return string CSS, or '' when the collection has no background color.
 */
function tainacan_interface_collection_colors_css( $collection_id, $text_selectors ) {
	$background = sanitize_hex_color( (string) get_post_meta( $collection_id, 'tainacan_theme_collection_background_color', true ) );
	if ( ! $background ) {
		return '';
	}

	$text = get_post_meta( $collection_id, 'tainacan_theme_collection_color', true );
	$text = in_array( $text, array( '#fff', '#000' ), true ) ? $text : '#fff';

	return '.t-bg-collection { background-color: ' . $background . ' !important; } '
		. $text_selectors . ' { color: ' . $text . ' !important; } '
		. '.t-bg-collection a { color: ' . $text . ' !important; opacity: 1; }';
}

/**
 * Metadata sections layout of a collection, from the closed list.
 *
 * @param int $collection_id Collection ID.
 * @return string default|tabs|collapses|accordion
 */
function tainacan_interface_section_layout( $collection_id ) {
	$layout = get_post_meta( $collection_id, 'tainacan_interface_section_layout', true );
	return in_array( $layout, array( 'tabs', 'collapses', 'accordion' ), true ) ? $layout : 'default';
}

new TainacanInterfaceCollectionSettings();
