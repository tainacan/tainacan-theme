<?php
/**
 * Title: Home: the collections in cards
 * Slug: {{THEME_SLUG}}/collections-grid
 * Categories: {{THEME_SLUG}}, query
 * Keywords: collections, cards, tainacan, query
 * Viewport Width: 1400
 * Description: The public collections in a grid of cards, with image, name and summary (Query loop of Tainacan collections).
 *
 * @package {{THEME_SLUG}}
 */

?>
<!-- wp:group {"align":"full","backgroundColor":"site-surface","className":"{{THEME_SLUG}}-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull {{THEME_SLUG}}-section has-site-surface-background-color has-background"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Our collections', '{{THEME_SLUG}}' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:query {"queryId":22,"query":{"perPage":6,"pages":0,"offset":0,"postType":"tainacan-collection","order":"asc","orderBy":"title","inherit":false},"namespace":"tainacan-collection"} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"is-style-tt2-cards","layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","sizeSlug":"large"} /-->

<!-- wp:post-title {"isLink":true,"level":3} /-->

<!-- wp:post-excerpt {"excerptLength":24} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
