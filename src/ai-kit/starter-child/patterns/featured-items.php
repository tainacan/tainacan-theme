<?php
/**
 * Title: Home: items of the collection in a carousel
 * Slug: {{THEME_SLUG}}/featured-items
 * Categories: {{THEME_SLUG}}, query
 * Keywords: items, carousel, tainacan, query
 * Viewport Width: 1400
 * Description: Heading, the latest items of the main collection in a carousel of cards (Query loop, rendered by the server) and a button to the full list.
 *
 * @package {{THEME_SLUG}}
 */

$tt2_kit_collection = function_exists( 'tainacan_interface_kit_main_collection' ) ? tainacan_interface_kit_main_collection() : array();
if ( empty( $tt2_kit_collection['post_type'] ) ) {
	return;
}
$tt2_kit_type = $tt2_kit_collection['post_type'];
?>
<!-- wp:group {"align":"full","className":"{{THEME_SLUG}}-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull {{THEME_SLUG}}-section"><!-- wp:paragraph {"className":"{{THEME_SLUG}}-eyebrow"} -->
<p class="{{THEME_SLUG}}-eyebrow"><?php echo esc_html( $tt2_kit_collection['name'] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Recently added', '{{THEME_SLUG}}' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:query {"queryId":21,"query":{"perPage":10,"pages":0,"offset":0,"postType":"<?php echo esc_attr( $tt2_kit_type ); ?>","order":"desc","orderBy":"date","inherit":false},"namespace":"<?php echo esc_attr( 'tainacan-items-' . $tt2_kit_type ); ?>"} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"is-style-tt2-carousel is-style-tt2-cards","layout":{"type":"grid","columnCount":4}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"1","sizeSlug":"medium"} /-->

<!-- wp:post-title {"isLink":true,"level":3,"fontSize":"regular"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $tt2_kit_collection['url'] ); ?>"><?php esc_html_e( 'See all the items', '{{THEME_SLUG}}' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
