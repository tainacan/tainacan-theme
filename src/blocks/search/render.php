<?php
/**
 * Search the collection: a form that opens the list of items of Tainacan with
 * the words typed (?search=), for all items or one collection.
 *
 * @package tainacan-interface
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tainacan_interface_collection = isset( $attributes['collectionId'] ) ? absint( $attributes['collectionId'] ) : 0;
$tainacan_interface_label      = ! empty( $attributes['label'] ) ? $attributes['label'] : __( 'Search the collection', 'tainacan-interface' );
$tainacan_interface_hint       = ! empty( $attributes['placeholder'] ) ? $attributes['placeholder'] : __( 'Search by title, author, subject...', 'tainacan-interface' );
$tainacan_interface_button     = ! empty( $attributes['buttonText'] ) ? $attributes['buttonText'] : __( 'Search', 'tainacan-interface' );
$tainacan_interface_size       = isset( $attributes['size'] ) && 'large' === $attributes['size'] ? 'large' : 'regular';
$tainacan_interface_field      = wp_unique_id( 'tt2-search-' );
?>
<form role="search" method="get" action="<?php echo esc_url( tainacan_interface_search_block_action( $tainacan_interface_collection ) ); ?>" <?php echo get_block_wrapper_attributes( array( 'class' => 'tt2-search is-size-' . $tainacan_interface_size ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns attributes it already escaped. tnc-check-ignore TNC-S05: same reason, as the core blocks print it. ?>>
	<label class="tt2-search__label screen-reader-text" for="<?php echo esc_attr( $tainacan_interface_field ); ?>"><?php echo esc_html( $tainacan_interface_label ); ?></label>
	<input class="tt2-search__input" type="search" name="search" id="<?php echo esc_attr( $tainacan_interface_field ); ?>" placeholder="<?php echo esc_attr( $tainacan_interface_hint ); ?>" autocomplete="off">
	<button class="tt2-search__button wp-element-button" type="submit"><?php echo esc_html( $tainacan_interface_button ); ?></button>
</form>
