<?php
/**
 * Title: Home: about the institution
 * Slug: {{THEME_SLUG}}/about-split
 * Categories: {{THEME_SLUG}}, about
 * Keywords: about, institution, columns, image
 * Viewport Width: 1400
 * Description: Image and text side by side in a panel (text alone when the collection has no image), with a button to the page about the institution.
 *
 * @package {{THEME_SLUG}}
 */

$tt2_kit_collection = function_exists( 'tainacan_interface_kit_main_collection' ) ? tainacan_interface_kit_main_collection() : array();
$tt2_kit_image      = ! empty( $tt2_kit_collection['thumbnail'] ) ? $tt2_kit_collection['thumbnail'] : '';
?>
<!-- wp:group {"align":"full","className":"{{THEME_SLUG}}-section","layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull {{THEME_SLUG}}-section"><!-- wp:group {"className":"is-style-tt2-panel","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-tt2-panel"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><?php if ( '' !== $tt2_kit_image ) : ?><!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="<?php echo esc_url( $tt2_kit_image ); ?>" alt="" style="aspect-ratio:4/3;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<?php endif; ?><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"className":"{{THEME_SLUG}}-eyebrow"} -->
<p class="{{THEME_SLUG}}-eyebrow"><?php esc_html_e( 'About us', '{{THEME_SLUG}}' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'A collection open to everyone', '{{THEME_SLUG}}' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Tell who keeps this collection, how it came together and what visitors find in it. Two or three sentences are enough here; the full story goes on its own page.', '{{THEME_SLUG}}' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Learn more', '{{THEME_SLUG}}' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
