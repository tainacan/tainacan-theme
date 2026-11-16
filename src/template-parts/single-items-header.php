<?php
/**
 * Heading of the item page: collection banner (optional), title,
 * identification line, date/author and edit link.
 *
 * @package tainacan-interface
 */

if ( tainacan_interface_is_modern() ) {
	tainacan_interface_the_item_heading();
	return;
}

$tainacan_interface_collection_id = tainacan_get_collection_id();
$tainacan_interface_banner        = (bool) get_theme_mod( 'tainacan_single_item_collection_header', false );

if ( $tainacan_interface_banner ) :
	?>
	<style>
		nav.menu-belowheader #menubelowHeader ul.dropdown-menu { min-width: 10rem !important; }
		<?php echo esc_html( tainacan_interface_collection_colors_css( $tainacan_interface_collection_id, '.t-bg-collection h1, .t-bg-collection h2, .t-bg-collection p' ) ); ?>
	</style>

	<div class="tainacan-single-post single-item-collection-banner">
		<div class="t-bg-collection">
			<div class="collection-name aside-thumbnail">
				<div class="title-page">
					<p><?php esc_html_e( 'Collection', 'tainacan-interface' ); ?></p>
					<h1>
						<a href="<?php echo esc_url( tainacan_get_the_collection_url() ); ?>">
							<?php tainacan_the_collection_name(); ?>
						</a>
					</h1>
				</div>
			</div>
		</div>
		<div class="collection-thumbnail">
			<a href="<?php echo esc_url( tainacan_get_the_collection_url() ); ?>">
				<?php if ( has_post_thumbnail( $tainacan_interface_collection_id ) ) : ?>
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $tainacan_interface_collection_id ) ); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute text-left" alt="<?php echo esc_attr( get_post_meta( get_post_thumbnail_id( $tainacan_interface_collection_id ), '_wp_attachment_image_alt', true ) ); ?>">
				<?php else : ?>
					<div class="image-placeholder rounded-circle border border-white position-absolute">
						<h4 class="text-center"><?php echo esc_html( tainacan_get_initials( tainacan_get_the_collection_name() ) ); ?></h4>
					</div>
				<?php endif; ?>
			</a>
		</div>
		<?php if ( get_theme_mod( 'tainacan_single_item_display_share_buttons', true ) ) : ?>
			<div class="collection-header--share">
				<div class="btn trigger tainacan-item-share-container">
					<span class="tainacan-icon tainacan-icon-share"></span>
				</div>
				<div class="icons">
					<?php tainacan_interface_the_share_links( get_permalink(), get_the_title(), 'icons' ); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="tainacan-single-item-heading item-title">
			<div class="title-page aside-thumbnail">
				<p><?php esc_html_e( 'Item', 'tainacan-interface' ); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php tainacan_interface_the_item_identity(); ?>
			</div>
		</div>
	</div>
<?php else : ?>

	<div class="tainacan-single-post tainacan-single-item-heading tainacan-title">
		<div class="tainacan-title-page">
			<ul class="list-inline mb-1">
				<li class="list-inline-item text-midnight-blue font-weight-bold title-page">
					<h1><?php the_title(); ?></h1>
				</li>
				<li class="list-inline-item float-right title-back">
					<a href="javascript:history.go(-1)"><?php esc_html_e( 'Back', 'tainacan-interface' ); ?></a>
				</li>
			</ul>
			<?php tainacan_interface_the_item_identity(); ?>
		</div>
	</div>

<?php endif; ?>

<div class="<?php echo $tainacan_interface_banner ? 'mt-2 pb-3 tainacan-single-post collection-single-item aside-thumbnail' : 'mt-3 tainacan-single-post collection-single-item'; ?>">
	<header class="mb-4 tainacan-content">
		<div class="header-meta text-muted mb-2 d-flex ">
			<?php
			if ( ! get_theme_mod( 'tainacan_single_item_hide_item_meta', false ) ) {
				echo '<span class="time">';
				tainacan_meta_date_author();
				echo '</span> &nbsp;';
			}
			if ( function_exists( 'tainacan_the_item_edit_link' ) ) {
				echo '<span class="tainacan-edit-item-collection">';
				tainacan_the_item_edit_link( null, ' - ', ' ' );
				echo '</span>';
			}
			?>
		</div>
	</header>
