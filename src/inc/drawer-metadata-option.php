<?php
/**
 * "Show in the side drawer" option on the metadatum form of each collection.
 *
 * The option is a flat meta on the metadatum post (tainacan_interface_drawer =
 * yes|no), declared to the Tainacan API so the Vue form opens with the saved
 * value, and saved when Tainacan saves the metadatum. Reading it costs no
 * query: the metadatum post meta is already in the cache when the item
 * metadata are fetched.
 *
 * @package tainacan-interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TAINACAN_INTERFACE_DRAWER_META = 'tainacan_interface_drawer';

/**
 * Registers the field on the metadatum form.
 */
function tainacan_interface_drawer_option_register_hook() {
	if ( function_exists( 'tainacan_register_admin_hook' ) ) {
		tainacan_register_admin_hook( 'metadatum', 'tainacan_interface_drawer_option_form', 'end-left' );
	}
}
add_action( 'tainacan-register-admin-hooks', 'tainacan_interface_drawer_option_register_hook' );

/**
 * Declares the meta, so the form is filled with the saved value.
 *
 * @param array $extra_meta Meta keys declared by others.
 * @return array
 */
function tainacan_interface_drawer_option_expose( $extra_meta ) {
	$extra_meta   = is_array( $extra_meta ) ? $extra_meta : array();
	$extra_meta[] = TAINACAN_INTERFACE_DRAWER_META;
	return $extra_meta;
}
add_filter( 'tainacan-api-response-metadatum-meta', 'tainacan_interface_drawer_option_expose' );

/**
 * The form field. Tainacan prints the returned HTML in its Vue form (v-html),
 * so the callback returns it and carries no script.
 *
 * @return string
 */
function tainacan_interface_drawer_option_form() {
	ob_start();
	?>
	<div class="field">
		<label class="label" for="<?php echo esc_attr( TAINACAN_INTERFACE_DRAWER_META ); ?>"><?php esc_html_e( 'Show in the side drawer', 'tainacan-interface' ); ?></label>
		<div class="control is-clearfix">
			<select id="<?php echo esc_attr( TAINACAN_INTERFACE_DRAWER_META ); ?>" name="<?php echo esc_attr( TAINACAN_INTERFACE_DRAWER_META ); ?>">
				<option value="no"><?php esc_html_e( 'No', 'tainacan-interface' ); ?></option>
				<option value="yes"><?php esc_html_e( 'Yes', 'tainacan-interface' ); ?></option>
			</select>
		</div>
		<p class="help"><?php esc_html_e( 'Tainacan Interface: the item preview drawer of the lists shows the metadata marked here. When no metadatum of a collection is marked, it shows what the item page shows. On a repository metadatum the choice applies to every collection.', 'tainacan-interface' ); ?></p>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Saves the option with the metadatum.
 *
 * @param \Tainacan\Entities\Metadatum $metadatum Metadatum being saved.
 */
function tainacan_interface_drawer_option_save( $metadatum ) {
	if ( ! function_exists( 'tainacan_get_api_postdata' ) || ! $metadatum instanceof \Tainacan\Entities\Metadatum || ! $metadatum->can_edit() ) {
		return;
	}

	$posted = tainacan_get_api_postdata();
	if ( ! is_object( $posted ) || ! isset( $posted->{TAINACAN_INTERFACE_DRAWER_META} ) ) {
		return;
	}

	$value = 'yes' === $posted->{TAINACAN_INTERFACE_DRAWER_META} ? 'yes' : 'no';
	update_post_meta( (int) $metadatum->get_id(), TAINACAN_INTERFACE_DRAWER_META, $value );
}
add_action( 'tainacan-insert-tainacan-metadatum', 'tainacan_interface_drawer_option_save' );

/**
 * Whether a metadatum is marked to show in the drawer.
 *
 * @param \Tainacan\Entities\Metadatum $metadatum Metadatum.
 * @return bool
 */
function tainacan_interface_drawer_shows( $metadatum ) {
	return 'yes' === get_post_meta( (int) $metadatum->get_id(), TAINACAN_INTERFACE_DRAWER_META, true );
}
