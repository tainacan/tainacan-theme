<?php
/**
 * Title: Home: visit and contact
 * Slug: {{THEME_SLUG}}/visit-band
 * Categories: {{THEME_SLUG}}, call-to-action
 * Keywords: visit, contact, address, hours
 * Viewport Width: 1400
 * Description: A heading and three columns with address, opening hours and contact, under the decorative band of the theme.
 *
 * @package {{THEME_SLUG}}
 */

?>
<!-- wp:group {"align":"full","className":"is-style-tt2-band {{THEME_SLUG}}-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull is-style-tt2-band {{THEME_SLUG}}-section"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Plan your visit', '{{THEME_SLUG}}' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Visit', '{{THEME_SLUG}}' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Street, number, district, city.', '{{THEME_SLUG}}' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Opening hours', '{{THEME_SLUG}}' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Tuesday to Sunday, 9 am to 5 pm.', '{{THEME_SLUG}}' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Contact', '{{THEME_SLUG}}' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'E-mail and phone of the institution.', '{{THEME_SLUG}}' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
