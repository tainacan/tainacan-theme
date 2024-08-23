<?php
if (get_theme_mod('tainacan_single_item_collection_header', false))  {
	echo ('<style>
	nav.menu-belowheader #menubelowHeader ul.dropdown-menu {
		min-width: 10rem !important;
	}');

	$background_color = esc_attr( get_post_meta( tainacan_get_collection_id(), 'tainacan_theme_collection_background_color', true ) );
	$text_color = esc_attr( get_post_meta( tainacan_get_collection_id(), 'tainacan_theme_collection_color', true ) );
	if ( $background_color ) {
		echo ".t-bg-collection {
			background-color: " . esc_attr($background_color) . " !important;
		}";
		echo ".t-bg-collection h1, .t-bg-collection h2, .t-bg-collection p {
			color: " . esc_attr($text_color) . " !important;
		}";

		echo ".t-bg-collection a {
			color: " . esc_attr($text_color) . " !important;
			opacity: 1;
		}";
	}

	echo '</style>';
}

$adjacent_links = tainacan_get_adjacent_item_links();
$previous = $adjacent_links['previous'];
$next = $adjacent_links['next'];
?>

<?php if ( get_theme_mod('tainacan_single_item_collection_header', false) ): ?>
    <div class="tainacan-single-post single-item-collection-banner">
        <div class="t-bg-collection">
            <div class="collection-name aside-thumbnail">
                <div class="title-page">
                    <p><?php echo __('Collection', 'tainacan-interface') ?></p>
                    <h1>
                        <?php if ( function_exists('tainacan_the_collection_url') ): ?>
                            <a href="<?php tainacan_the_collection_url(); ?>">
                                <?php tainacan_the_collection_name(); ?>
                            </a>
                        <?php else : ?>
                            <span><?php tainacan_the_collection_name(); ?></span>
                        <?php endif; ?>
                    </h1>
                </div>
            </div>
        </div>
        <div class="collection-thumbnail">
            <?php if ( function_exists('tainacan_the_collection_url') ): ?>
                <a href="<?php tainacan_the_collection_url(); ?>">
                    <?php if ( has_post_thumbnail( tainacan_get_collection_id() ) ) : 
                        $thumbnail_id = get_post_thumbnail_id( tainacan_get_collection_id() );
                        $alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true); ?>
                        <img src="<?php echo get_the_post_thumbnail_url( tainacan_get_collection_id() ); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute text-left" alt="<?php echo esc_attr($alt); ?>">
                    <?php else : ?>
                        <div class="image-placeholder rounded-circle border border-white position-absolute">
                            <h4 class="text-center">
                            <?php
                                echo esc_html( tainacan_get_initials( tainacan_get_the_collection_name() ) );
                            ?>
                            </h4>
                        </div>
                    <?php endif; ?>
                </a>
            <?php else : ?>
                <span>
                    <?php if ( has_post_thumbnail( tainacan_get_collection_id() ) ) : 
                        $thumbnail_id = get_post_thumbnail_id( tainacan_get_collection_id());
                        $alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true); ?>
                        <img src="<?php echo get_the_post_thumbnail_url( tainacan_get_collection_id() ); ?>" class="t-collection--info-img rounded-circle img-fluid border border-white position-absolute text-left" alt="<?php echo esc_attr($alt); ?>">
                    <?php else : ?>
                        <div class="image-placeholder rounded-circle border border-white position-absolute">
                            <h4 class="text-center">
                            <?php
                                echo esc_html( tainacan_get_initials( tainacan_get_the_collection_name() ) );
                            ?>
                            </h4>
                        </div>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
        <?php 
            global $wp; 
            if (get_theme_mod( 'tainacan_single_item_display_share_buttons', true )) : ?>
            <div class="collection-header--share">
                <div class="btn trigger tainacan-item-share-container">
                    <span class="tainacan-icon tainacan-icon-share"></span>
                </div>

                <div class="icons">
                    <?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
                        <div class="rotater">
                            <a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" target="_blank" title="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>">
                                <div class="btn btn-icon">
                                    <i class="tainacan-icon tainacan-icon-facebook"></i>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) && get_theme_mod( 'tainacan_twitter_user' ) ) : ?> 
                        <div class="rotater">
                            <?php
                                $twitter_option = get_theme_mod( 'tainacan_twitter_user' );
                                $via = ! empty( $twitter_option ) ? '&amp;via=' . esc_attr( get_theme_mod( 'tainacan_twitter_user' ) ) : '';
                            ?>
                            <a href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title_attribute(); ?><?php echo $via; ?>"  target="_blank" title="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>">
                                <div class="btn btn-icon">
                                    <i class="tainacan-icon tainacan-icon-twitter"></i>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ( true == get_theme_mod( 'tainacan_whatsapp_share', false ) ) : ?>
                        <div class="rotater">
                            <a href="https://api.whatsapp.com/send?1=pt_BR&text=<?php the_permalink(); ?>" target="_blank" title="<?php esc_attr_e('Share this on WhatsApp', 'tainacan-interface') ?>">
                                <div class="btn btn-icon">
                                    <i class="tainacan-icon tainacan-icon-whatsapp"></i>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if ( true == get_theme_mod( 'tainacan_telegram_share', false ) ) : ?>
                        <div class="rotater">
                            <a href="https://t.me/share/url?url=<?php the_permalink(); ?>" target="_blank" title="<?php esc_attr_e('Share this on Telegram', 'tainacan-interface') ?>">
                                <div class="btn btn-icon">
                                    <i class="tainacan-icon tainacan-icon-telegram"></i>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="rotater">
						<button id="tainacan-interface-sharer" data-link="<?php the_permalink(); ?>" title="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>" class="tainacan-copy-link-button">
							<div class="btn btn-icon">
								<i class="tainacan-icon tainacan-icon-url"></i>
							</div>
						</button>
					</div>
                </div>
            </div>
        <?php endif; ?>
        <div class="tainacan-single-item-heading item-title">
            <div class="title-page aside-thumbnail">
                <p><?php echo __('Item', 'tainacan-interface') ?></p>
                <h1><?php the_title(); ?></h1> 
            </div>
            <!--<div class="title-back">
                <a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a>
            </div>-->
        </div>
    </div>
<?php else: ?>

    <div class="tainacan-single-post tainacan-single-item-heading tainacan-title">
        <div class="tainacan-title-page">
            <ul class="list-inline mb-1">
                <li class="list-inline-item text-midnight-blue font-weight-bold title-page">
                    <h1><?php the_title(); ?></h1> 
                </li>
                <li class="list-inline-item float-right title-back">
                    <a href="javascript:history.go(-1)"><?php _e( 'Back', 'tainacan-interface' ); ?></a>
                </li>
            </ul>
        </div>
    </div>

<?php endif; ?>

<?php if ( get_theme_mod('tainacan_single_item_collection_header', false) ): ?>
    <div class="mt-2 pb-3 tainacan-single-post collection-single-item aside-thumbnail">
<?php else : ?>
    <div class="mt-3 tainacan-single-post collection-single-item">
<?php endif; ?>
        <header class="mb-4 tainacan-content">
            <div class="header-meta text-muted mb-2 d-flex ">
                <?php 
                    if ( !get_theme_mod('tainacan_single_item_hide_item_meta', false) ) {
                        echo '<span class="time">';
                            tainacan_meta_date_author();
                        echo '</span> &nbsp';
                    }
                    if ( function_exists('tainacan_the_item_edit_link') ) {
                        echo '<span class="tainacan-edit-item-collection">';
                            tainacan_the_item_edit_link(null, ' - ', ' ');
                        echo '</span>';
                    }
                ?>
            </div>
        </header>
    </div>