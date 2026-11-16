<?php
/**
 * Header of a term page of a Tainacan taxonomy.
 *
 * @package tainacan-interface
 */

$tainacan_interface_wpterm = tainacan_get_term();
if ( ! $tainacan_interface_wpterm instanceof WP_Term ) {
	return;
}

$tainacan_interface_taxonomy = get_taxonomy( $tainacan_interface_wpterm->taxonomy );
$tainacan_interface_term     = \Tainacan\Repositories\Terms::get_instance()->fetch( $tainacan_interface_wpterm->term_id, $tainacan_interface_wpterm->taxonomy );
$tainacan_interface_src      = $tainacan_interface_term ? wp_get_attachment_image_src( $tainacan_interface_term->get_header_image_id(), 'full' ) : false;
$tainacan_interface_link     = get_term_link( $tainacan_interface_wpterm );
$tainacan_interface_link     = is_wp_error( $tainacan_interface_link ) ? tainacan_interface_current_url() : $tainacan_interface_link;
$tainacan_interface_label    = $tainacan_interface_taxonomy ? $tainacan_interface_taxonomy->labels->name : '';
?>
<style>
	nav.menu-belowheader #menubelowHeader ul.dropdown-menu { min-width: 10rem !important; }
</style>

<div class="page-header-taxonomy <?php echo $tainacan_interface_src ? '' : 'page-header-taxonomy--height-auto'; ?>">

	<div class="container-fluid mt-0 mb-0 p-0 d-flex flex-wrap <?php echo $tainacan_interface_src ? 'max-large margin-one-column' : ''; ?>">
		<?php do_action( 'tainacan-interface-taxonomy-header' ); ?>

		<?php if ( $tainacan_interface_src ) : ?>
			<img class="page-header-image" src="<?php echo esc_url( $tainacan_interface_src[0] ); ?>" alt="<?php echo esc_attr( $tainacan_interface_wpterm->name ); ?>">
		<?php endif; ?>
		<div class="col-12 col-sm p-0 page-header-content">
			<div class="page-header-content-meta <?php echo $tainacan_interface_src ? '' : 'max-large'; ?>">
				<div class="page-header-content-title d-inline-flex border-bottom">
					<h2 class="page-header-title">
						<?php echo esc_html( $tainacan_interface_label . ':' ); ?>
						<span style="font-weight: 500;">
							<?php tainacan_the_term_name(); ?>
						</span>
					</h2>
					<a class="page-header-back ml-auto" href="javascript:history.go(-1)"><?php esc_html_e( 'Back', 'tainacan-interface' ); ?></a>
				</div>
				<div class="page-header-hightlights d-flex flex-wrap">
					<div class="col-12 col-xl-10 col-lg-9 page-header-description tainacan-interface-truncate-term tainacan-interface-rich-text">
						<?php tainacan_the_term_description(); ?>
						<?php do_action( 'tainacan-interface-term-description' ); ?>
					</div>
					<?php do_action( 'tainacan-interface-taxonomy-description' ); ?>
					<div class="col-12 col-xl-2 col-lg-3 d-flex flex-wrap page-header-share">
						<div class="page-header-icons">
							<p class="share-title"><?php esc_html_e( 'Share', 'tainacan-interface' ); ?></p>
							<?php tainacan_interface_the_share_links( $tainacan_interface_link, $tainacan_interface_label . ': ' . $tainacan_interface_wpterm->name, 'images', 'share-link' ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'tainacan-interface-taxonomy-header-bottom' ); ?>
	</div>
</div>
