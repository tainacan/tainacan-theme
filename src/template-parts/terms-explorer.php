<?php
/**
 * Terms explorer of a Tainacan taxonomy page (tree, map, radial).
 * The nested list inside is the page without JavaScript; the script
 * replaces it with the explorer.
 *
 * @package tainacan-interface
 */

$tainacan_interface_terms = tainacan_interface_terms_explorer_data( get_the_ID() );

if ( empty( $tainacan_interface_terms['terms'] ) ) {
	echo '<p class="tt2-tx-empty">' . esc_html__( 'This taxonomy has no terms yet.', 'tainacan-interface' ) . '</p>';
	return;
}
?>
<div class="tt2-tx"
	data-mode="<?php echo esc_attr( tainacan_interface_terms_view_mode() ); ?>"
	data-term="<?php echo esc_attr( (string) absint( get_query_var( 'termsparent', 0 ) ) ); ?>"
	data-search="<?php echo esc_attr( tainacan_interface_query_arg( 'search' ) ); ?>">
	<script type="application/json" class="tt2-tx-data"><?php echo wp_json_encode( $tainacan_interface_terms['terms'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
	<nav class="tt2-tx-fallback" aria-label="<?php esc_attr_e( 'Terms', 'tainacan-interface' ); ?>">
		<?php tainacan_interface_terms_fallback_list( $tainacan_interface_terms['terms'] ); ?>
	</nav>
</div>
