<?php
/**
 * Customizer control: radio buttons drawn as pictures of the result. Loaded
 * by inc/customizer-pictures.php once the Customizer classes exist.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Radio buttons drawn as pictures of the result.
 */
class Tainacan_Interface_Picture_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'tt2_pictures';

	/**
	 * Drawing key of tainacan_interface_cform_picture().
	 *
	 * @var string
	 */
	public $picture = '';

	/**
	 * Value of the setting => value of the drawing.
	 *
	 * @var array
	 */
	public $picture_values = array();

	/**
	 * The setting can be changed by a collection on its form.
	 *
	 * @var bool
	 */
	public $per_collection = false;

	/**
	 * Prints the control.
	 */
	protected function render_content() {
		$name = '_customize-tt2-' . $this->id;
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<div class="tt2-cform tt2-cform--customizer">
			<div class="tt2-cform__tiles" role="radiogroup" aria-label="<?php echo esc_attr( $this->label ); ?>">
				<?php foreach ( $this->choices as $value => $label ) : ?>
					<?php $drawing = isset( $this->picture_values[ $value ] ) ? $this->picture_values[ $value ] : $value; ?>
					<label class="tt2-tile">
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php $this->link(); ?> <?php checked( $this->value(), $value ); ?>>
						<span class="tt2-tile__pic"><?php tainacan_interface_cform_print_picture( $this->picture, $drawing ); ?></span>
						<span class="tt2-tile__name"><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $this->per_collection ) : ?>
			<p class="tt2-percoll"><?php esc_html_e( 'Site default. A collection can change it on its form in Tainacan.', 'tainacan-interface' ); ?></p>
		<?php endif; ?>
		<?php
	}
}
