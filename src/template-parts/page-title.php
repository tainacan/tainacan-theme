<?php
/**
 * Title at the top of the blog, posts, pages, searches and archives.
 *
 * The classes tainacan-title, tainacan-title-page, title-page and title-back are
 * the ones of 2.x, kept for child themes that style them.
 *
 * @package tainacan-interface
 *
 * @var array $args Optional: replaces parts of what the context says (see
 *                   tainacan_interface_page_title_parts()); 'title' is HTML.
 */

$tainacan_interface_head = array_merge(
	tainacan_interface_page_title_parts(),
	array_map( 'strval', array_intersect_key( isset( $args ) ? (array) $args : array(), array_flip( array( 'kicker', 'kicker_url', 'title', 'meta', 'description', 'back' ) ) ) )
);
if ( tainacan_interface_opt( 'page_title_meta' ) ) {
	$tainacan_interface_head['description'] = wp_trim_words( $tainacan_interface_head['description'], 40 );
} else {
	$tainacan_interface_head['meta']        = '';
	$tainacan_interface_head['description'] = '';
}
?>
<header class="tainacan-title tt2-page-head">
	<div class="tainacan-title-page">
		<div class="title-page tt2-page-head__text">
			<?php if ( '' !== $tainacan_interface_head['kicker'] ) : ?>
				<p class="tt2-page-head__kicker">
					<?php if ( '' !== $tainacan_interface_head['kicker_url'] ) : ?>
						<a href="<?php echo esc_url( $tainacan_interface_head['kicker_url'] ); ?>"><?php echo esc_html( $tainacan_interface_head['kicker'] ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $tainacan_interface_head['kicker'] ); ?>
					<?php endif; ?>
				</p>
			<?php endif; ?>
			<h1><?php echo wp_kses_post( $tainacan_interface_head['title'] ); ?></h1>
			<?php if ( '' !== $tainacan_interface_head['description'] ) : ?>
				<p class="tt2-page-head__description"><?php echo esc_html( $tainacan_interface_head['description'] ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $tainacan_interface_head['meta'] ) : ?>
				<p class="tt2-page-head__meta"><?php echo esc_html( $tainacan_interface_head['meta'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( tainacan_interface_opt( 'page_title_back' ) && '' !== $tainacan_interface_head['back'] ) : ?>
			<a class="title-back tt2-page-head__back" href="<?php echo esc_url( $tainacan_interface_head['back'] ); ?>" data-tt2-back>
				<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg>
				<span><?php esc_html_e( 'Back', 'tainacan-interface' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
</header>
