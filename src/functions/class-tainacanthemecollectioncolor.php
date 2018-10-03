<?php
class TainacanThemeCollectionColor {


	public $tainacan_background_color = 'tainacan_theme_collection_background_color';
	public $tainacan_text_color = 'tainacan_theme_collection_color';

	function __construct() {
		add_filter( 'tainacan-api-response-collection-meta', array( $this, 'add_meta_to_response' ), 10, 2 );

		add_action( 'tainacan-register-admin-hooks', array( $this, 'register_hook' ) );

		add_action( 'tainacan-insert-tainacan-collection', array( $this, 'save_meta' ) );
		add_action( 'tainacan-enqueue-admin-scripts', array( $this, 'action_tainacan_enqueue_admin_scripts' ) );
	}

	function action_tainacan_enqueue_admin_scripts() {
		wp_enqueue_script( 'tainacan_colorWell', get_template_directory_uri() . '/functions/collection-color.js', false, false, true );
		wp_enqueue_style( 'tainacan_colorWellStyle', get_template_directory_uri() . '/functions/collection-color.css' );
		wp_localize_script( 'tainacan_colorWell', 'tainacan_colorPickerVars', [
			'cancelText' => __("cancel", 'tainacan-interface'),
			'chooseText' => __("choose", 'tainacan-interface'),
			'togglePaletteMoreText' => __("more", 'tainacan-interface'),
			'togglePaletteLessText' => __("less", 'tainacan-interface'),
			'clearText' => __("Clear Color Selection", 'tainacan-interface'),
			'noColorSelectedText' => __("No Color Selected", 'tainacan-interface'),
		] );

		wp_enqueue_script( 'tainacan_Spectrum', get_template_directory_uri() . '/assets/js/spectrum.js', false, false, true );
		wp_enqueue_style( 'tainacan_Spectrum', get_template_directory_uri() . '/assets/js/spectrum.css' );
	}

	function register_hook() {
		if ( function_exists( 'register_admin_hook' ) ) {
			register_admin_hook( 'collection', array( $this, 'form' ) );
		}
	}

	function form() {

		if ( ! function_exists( 'tainacan_get_api_postdata' ) ) {
			return '';
		}

		ob_start();
		?>

		<div class="field tainacan-collection--change-color-picker">
			<label class="label"><?php _e( 'Collection Background Color', 'tainacan-interface' ); ?></label>
			<span class="help-wrapper">
				<a class="help-button has-text-secondary">
					<span class="icon is-small">
						<i class="mdi mdi-help-circle-outline"/></i>
					</span>
				</a>
				<div class="help-tooltip">
					<div class="help-tooltip-header">
						<h5><?php _e( 'Collection Background Color', 'tainacan-interface' ); ?></h5>
					</div> 
					<div class="help-tooltip-body">
						<p><?php _e( 'Collection header fill color', 'tainacan-interface' ); ?></p>
					</div>
				</div>
			</span>
			<div class="control is-clearfix"> 
				<p style="font-weight: 600;"><?php _e( 'Picker color', 'tainacan-interface' ); ?></p>
				<input type="text" value="" id="colorpicker" name="<?php echo $this->tainacan_background_color; ?>">
			</div>
		</div>

		<div class="field tainacan-collection--change-text-color">
			<label class="label"><?php _e( 'Collection Text Color', 'tainacan-interface' ); ?></label>
			<span class="help-wrapper">
				<a class="help-button has-text-secondary">
					<span class="icon is-small">
						<i class="mdi mdi-help-circle-outline"></i>
					</span>
				</a>
				<div class="help-tooltip">
					<div class="help-tooltip-header">
						<h5><?php _e( 'Collection Text Color', 'tainacan-interface' ); ?></h5>
					</div> 
					<div class="help-tooltip-body">
						<p><?php _e( 'Text color on collection header', 'tainacan-interface' ); ?></p>
					</div>
				</div>
			</span>
			<div class="control is-clearfix"> 
				<input
					type="radio" 
					value="#fff" 
					name="<?php echo $this->tainacan_text_color; ?>"
					id="white" checked>
					<label for="white" id="color-white" class="color-text"><?php _e( 'White', 'tainacan-interface' ); ?></label>
				<input
					type="radio" 
					value="#000" 
					name="<?php echo $this->tainacan_text_color; ?>"
					id="black">
					<label for="black" id="color-black" class="color-text"><?php _e( 'Black', 'tainacan-interface' ); ?></label>
			</div>
		</div>

		<?php

		return ob_get_clean();

	}

	function add_meta_to_response( $extra_meta, $request ) {
		$extra_meta = array(
			$this->tainacan_background_color,
			$this->tainacan_text_color,
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
		}

	}


}

new TainacanThemeCollectionColor();

?>
