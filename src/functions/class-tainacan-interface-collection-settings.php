<?php
/**
 * Class that hooks in the tainacan plugin (if present) to add new options to Collections
 */
class TainacanInterfaceCollectionSettings {
	public $tainacan_background_color = 'tainacan_theme_collection_background_color'; // These has bad naming, but its too late to change
	public $tainacan_text_color = 'tainacan_theme_collection_color';
	public $tainacan_sections_layout = 'tainacan_interface_section_layout';

	function __construct() {
		add_filter( 'tainacan-api-response-collection-meta', array( $this, 'add_meta_to_response' ), 10, 2 );
		add_action( 'tainacan-register-admin-hooks', array( $this, 'register_hook' ) );
		add_action( 'tainacan-insert-tainacan-collection', array( $this, 'save_meta' ) );
		add_action( 'tainacan-enqueue-admin-scripts', array( $this, 'action_tainacan_enqueue_admin_scripts' ) );
	}

	function action_tainacan_enqueue_admin_scripts() {
		wp_enqueue_script( 'tainacan_colorWell', get_template_directory_uri() . '/assets/js/collection-color.js', false, false, true );
		wp_enqueue_style( 'tainacan_collection_extra_options', get_template_directory_uri() . '/assets/css/collection-extra-options.css', [], TAINACAN_INTERFACE_VERSION  );
		wp_localize_script( 'tainacan_colorWell', 'tainacan_colorPickerVars', array(
			'cancelText' => __( 'Cancel', 'tainacan-interface' ),
			'chooseText' => __( 'Choose', 'tainacan-interface' ),
			'togglePaletteMoreText' => __( 'more', 'tainacan-interface' ),
			'togglePaletteLessText' => __( 'less', 'tainacan-interface' ),
			'clearText' => __( 'Clear Color Selection', 'tainacan-interface' ),
			'noColorSelectedText' => __( 'No Color Selected', 'tainacan-interface' ),
		));

		wp_enqueue_script( 'spectrum', get_template_directory_uri() . '/assets/js/spectrum.js', false, false, true );
		wp_enqueue_style( 'spectrum', get_template_directory_uri() . '/assets/css/spectrum.css' );
	}

	function register_hook() {
		if ( function_exists( 'tainacan_register_admin_hook' ) ) {
			tainacan_register_admin_hook( 'collection', array( $this, 'form' ), 'end-right' );
		}
	}

	function form() {
		if ( ! function_exists( 'tainacan_get_api_postdata' ) ) {
			return '';
		}

		ob_start();
		?>

		<div class="field tainacan-collection--section-header">
			<h4><?php _e( 'Tainacan Interface extra settings', 'tainacan-interface' ); ?></h4>
			<hr>
		</div>

		<div class="field tainacan-metadata-section--change-section-layout">
			<label class="label"><?php _e( 'Metadata sections layout', 'tainacan-interface' ); ?></label>
			<span class="help-wrapper">
				<a class="help-button has-text-secondary">
					<span class="icon is-small">
						<i class="tainacan-icon tainacan-icon-help"></i>
					</span>
				</a>
				<div class="help-tooltip">
					<div class="help-tooltip-header">
						<h5><?php _e( 'Metadata sections layout', 'tainacan-interface' ); ?></h5>
					</div> 
					<div class="help-tooltip-body">
						<p><?php _e( 'Define how the metadata sections will appear in the public item page.', 'tainacan-interface' ); ?></p>
					</div>
				</div>
			</span>
			<div class="control is-clearfix metadata-section-options"> 
				<label for="default" id="layout-default" class="layout-options">
					<input
						type="radio" 
						value="default" 
						name="<?php echo esc_attr($this->tainacan_sections_layout); ?>"
						id="default" checked>
					<img src="<?php echo esc_url( get_template_directory_uri()); ?>/assets/images/section_default.png" alt="<?php _e( 'Default', 'tainacan-interface' ); ?>"  />
					<?php _e( 'Default', 'tainacan-interface' ); ?>
				</label>
				
				<label for="tabs" id="layout-tabs" class="layout-options">
					<input
						type="radio" 
						value="tabs" 
						name="<?php echo esc_attr($this->tainacan_sections_layout); ?>"
						id="tabs">
					<img src="<?php echo esc_url( get_template_directory_uri()); ?>/assets/images/section_tabs.png" alt="<?php _e( 'Tabs', 'tainacan-interface' ); ?>"  />
					<?php _e( 'Tabs', 'tainacan-interface' ); ?>
				</label>

				<label for="collapses" id="layout-collapses" class="layout-options">
					<input
						type="radio" 
						value="collapses" 
						name="<?php echo esc_attr($this->tainacan_sections_layout); ?>"
						id="collapses">
					<img src="<?php echo esc_url( get_template_directory_uri()); ?>/assets/images/section_collapse.png" alt="<?php _e( 'Collapses', 'tainacan-interface' ); ?>"  />
					<?php _e( 'Collapses', 'tainacan-interface' ); ?>
				</label>

				<label for="accordion" id="layout-accordion" class="layout-options">
					<input
						type="radio" 
						value="accordion" 
						name="<?php echo esc_attr($this->tainacan_sections_layout); ?>"
						id="accordion">
					<img src="<?php echo esc_url( get_template_directory_uri()); ?>/assets/images/section_accordion.png" alt="<?php _e( 'Accordion', 'tainacan-interface' ); ?>"  />
					<?php _e( 'Accordion', 'tainacan-interface' ); ?>
				</label>
			</div>
		</div>

		<div>
			<label class="label"><?php _e( 'Collection items list header colors', 'tainacan-interface' ); ?></label>
			<span class="help-wrapper">
				<a class="help-button has-text-secondary">
					<span class="icon is-small">
						<i class="tainacan-icon tainacan-icon-help"/></i>
					</span>
				</a>
				<div class="help-tooltip">
					<div class="help-tooltip-header">
						<h5><?php _e( 'Collection items list header colorsr', 'tainacan-interface' ); ?></h5>
					</div> 
					<div class="help-tooltip-body">
						<p><?php _e( 'Select which color will be used for the text and background of your collection items list header.', 'tainacan-interface' ); ?></p>
					</div>
				</div>
			</span>
		</div>
		<div class="columns is-multiline">
			<div class="column is-one-third-desktop is-full-tablet is-one-third-mobile field tainacan-collection--change-color-picker">
				<label class="label"><?php _e( 'Background', 'tainacan-interface' ); ?></label>
				<div class="control is-clearfix"> 
					<input type="text" value="" id="colorpicker" name="<?php echo esc_attr($this->tainacan_background_color); ?>">
				</div>
			</div>

			<div class="column is-two-thirds-desktop is-full-tablet is-two-thirds-mobile field tainacan-collection--change-text-color">
				<label class="label"><?php _e( 'Text', 'tainacan-interface' ); ?></label>
				<div class="control is-clearfix"> 
					<label for="white" id="color-white" class="color-text">
						<input
							type="radio" 
							value="#fff" 
							name="<?php echo esc_attr($this->tainacan_text_color); ?>"
							id="white" checked>
						<?php _e( 'White', 'tainacan-interface' ); ?>
					</label>

					<label for="black" id="color-black" class="color-text">
						<input
							type="radio" 
							value="#000" 
							name="<?php echo esc_attr($this->tainacan_text_color); ?>"
							id="black">
							<?php _e( 'Black', 'tainacan-interface' ); ?>
					</label>
				</div>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	function add_meta_to_response( $extra_meta, $request ) {
		$extra_meta = array(
			$this->tainacan_background_color,
			$this->tainacan_text_color,
			$this->tainacan_sections_layout
		);
		return $extra_meta;
	}

	function save_meta( $object ) {
		if ( ! function_exists( 'tainacan_get_api_postdata' ) ) {
			return;
		}

		$post = tainacan_get_api_postdata();

		if ( $object->can_edit() ) {
			if ( isset( $post->{$this->tainacan_background_color} ) ) {
				update_post_meta( $object->get_id(), $this->tainacan_background_color, $post->{$this->tainacan_background_color} );
			}
			if ( isset( $post->{$this->tainacan_text_color} ) ) {
				update_post_meta( $object->get_id(), $this->tainacan_text_color, $post->{$this->tainacan_text_color} );
			}
			if ( isset( $post->{$this->tainacan_sections_layout} ) ) {
				update_post_meta( $object->get_id(), $this->tainacan_sections_layout, $post->{$this->tainacan_sections_layout} );
			}
		}
	}

}

new TainacanInterfaceCollectionSettings();
