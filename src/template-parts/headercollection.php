<?php
/**
 * Banner of a collection's items page.
 *
 * @package tainacan-interface
 */

$tainacan_interface_collection_id = tainacan_get_collection_id();
$tainacan_interface_banner_height = absint( get_theme_mod( 'tainacan_collection_banner_max_height', 368 ) );
?>
<style>
	nav.menu-belowheader #menubelowHeader ul.dropdown-menu { min-width: 10rem !important; }
	<?php echo esc_html( tainacan_interface_collection_colors_css( $tainacan_interface_collection_id, '.t-bg-collection h2, .t-bg-collection .t-collection--info-description-text' ) ); ?>
</style>

<?php if ( get_header_image_tag() ) : ?>
	<div class="page-header header-filter page-height page-header--image-full">
		<?php echo wp_kses_post( get_header_image_tag( array( 'style' => 'max-height: ' . ( $tainacan_interface_banner_height ? $tainacan_interface_banner_height : 368 ) . 'px;' ) ) ); ?>
<?php elseif ( get_header_image() ) : ?>
	<div class="page-header header-filter page-height page-header--image-full">
		<img class="page-header__image" src="<?php echo esc_url( get_header_image() ); ?>" alt="">
<?php else : ?>
	<div class="page-header header-filter page-collection page-header--image-full">
		<img class="page-header__image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/capa.png' ); ?>" alt="">
<?php endif; ?>
	<div class="container-fluid px-0 t-bg-collection">
		<div class="collection-header position-relative max-large">
			<?php do_action( 'tainacan-interface-collection-header' ); ?>
			<?php if ( has_post_thumbnail( $tainacan_interface_collection_id ) ) : ?>
				<img src="<?php echo esc_url( get_the_post_thumbnail_url( $tainacan_interface_collection_id ) ); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute text-left" alt="<?php echo esc_attr( get_post_meta( get_post_thumbnail_id( $tainacan_interface_collection_id ), '_wp_attachment_image_alt', true ) ); ?>">
			<?php else : ?>
				<div class="image-placeholder rounded-circle border border-white position-absolute">
					<h4 class="text-center"><?php echo esc_html( tainacan_get_initials( tainacan_get_the_collection_name() ) ); ?></h4>
				</div>
			<?php endif; ?>
			<div class="collection-header--share">
				<div class="btn trigger">
					<span class="tainacan-icon tainacan-icon-share"></span>
				</div>
				<div class="icons">
					<?php tainacan_interface_the_share_links( tainacan_interface_current_url(), tainacan_get_the_collection_name(), 'icons' ); ?>
				</div>
			</div>
		</div>
		<div class="row t-collection--info max-large margin-one-column" style="overflow-x: inherit;">
			<div class="col-md-2 col-3 px-0"></div>
			<div class="col-md-10 col-9 pl-0 t-collection--col-9" style="z-index: 2">
				<h2 class="t-collection--info-title text-white">
					<?php tainacan_the_collection_name(); ?>
				</h2>
				<?php $tainacan_interface_description = tainacan_get_the_collection_description(); ?>
				<?php if ( ! empty( $tainacan_interface_description ) || has_action( 'tainacan-interface-collection-description' ) ) : ?>
					<div class="text-white t-collection--info-description-text tainacan-interface-truncate tainacan-interface-rich-text">
						<?php tainacan_the_collection_description(); ?>
						<?php do_action( 'tainacan-interface-collection-description' ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php do_action( 'tainacan-interface-collection-header-bottom' ); ?>
		</div>
	</div>
</div>
