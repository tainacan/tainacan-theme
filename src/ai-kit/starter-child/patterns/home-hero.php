<?php
/**
 * Title: Home: opening with search
 * Slug: {{THEME_SLUG}}/home-hero
 * Categories: {{THEME_SLUG}}, featured
 * Keywords: hero, cover, search, home
 * Viewport Width: 1400
 * Description: Full-width opening with the image of the main collection under the brand veil, the name of the site, a line about it and the search of the collection.
 *
 * @package {{THEME_SLUG}}
 */

$tt2_kit_collection = function_exists( 'tainacan_interface_kit_main_collection' ) ? tainacan_interface_kit_main_collection() : array();
$tt2_kit_image      = ! empty( $tt2_kit_collection['thumbnail'] ) ? $tt2_kit_collection['thumbnail'] : '';
?>
<?php if ( '' !== $tt2_kit_image ) : ?>
<!-- wp:cover {"url":"<?php echo esc_url( $tt2_kit_image ); ?>","dimRatio":70,"overlayColor":"site-brand","isUserOverlayColor":true,"minHeight":560,"minHeightUnit":"px","contentPosition":"center center","align":"full","className":"is-style-tt2-veil","layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-cover alignfull is-style-tt2-veil" style="min-height:560px"><span aria-hidden="true" class="wp-block-cover__background has-site-brand-background-color has-background-dim-70 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( $tt2_kit_image ); ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
<?php else : ?>
<!-- wp:cover {"overlayColor":"site-brand","isUserOverlayColor":true,"minHeight":560,"minHeightUnit":"px","contentPosition":"center center","align":"full","layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-cover alignfull" style="min-height:560px"><span aria-hidden="true" class="wp-block-cover__background has-site-brand-background-color has-background-dim-100 has-background-dim"></span><div class="wp-block-cover__inner-container">
<?php endif; ?>
<!-- wp:paragraph {"align":"center","className":"{{THEME_SLUG}}-eyebrow"} -->
<p class="has-text-align-center {{THEME_SLUG}}-eyebrow"><?php esc_html_e( 'Digital collection', '{{THEME_SLUG}}' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(2.25rem, 4vw + 1rem, 4rem)","lineHeight":"1.1"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:clamp(2.25rem, 4vw + 1rem, 4rem);line-height:1.1"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"><?php esc_html_e( 'Explore the objects, documents and images of our collection, with the stories behind them.', '{{THEME_SLUG}}' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:tainacan-interface/search {"size":"large","align":"center"} /-->
</div></div>
<!-- /wp:cover -->
