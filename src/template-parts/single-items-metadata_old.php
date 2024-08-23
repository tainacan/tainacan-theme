<div class="mt-3 tainacan-single-post">

    <?php if ( get_theme_mod('tainacan_single_item_metadata_section_label', '') != '') : ?>
        <h2 class="title-content-items" id="single-item-metadata-label">
            <?php echo esc_html( get_theme_mod('tainacan_single_item_metadata_section_label', '') ); ?>
        </h2>
    <?php endif; ?>
    <section class="tainacan-content single-item-collection margin-two-column">
        <div class="single-item-collection--information justify-content-center">
            <div class="row">
                <div class="col s-item-collection--metadata">
                    <?php if (has_post_thumbnail() && get_theme_mod( 'tainacan_single_item_display_thumbnail', true )): ?>
                        <div class="tainacan-item-thumbnail-container card border-0 mb-3">
                            <div class="card-body border-0 pl-0 pt-0 pb-1">
                                <h3><?php _e( 'Thumbnail', 'tainacan-interface' ); ?></h3>
                                <?php the_post_thumbnail('tainacan-medium-full', array('class' => 'item-card--thumbnail mt-2')); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!get_theme_mod('tainacan_single_item_collection_header', false) && get_theme_mod( 'tainacan_single_item_display_share_buttons', true )): ?>
                        <div class="card border-0 mb-3">
                            <div class="tainacan-item-share-container card-body border-0 pl-0 pt-0 pb-1">
                                <h3><?php _e( 'Share', 'tainacan-interface' ); ?></h3>
                                <div class="btn-group" role="group">
                                    <?php if ( true == get_theme_mod( 'tainacan_facebook_share', true ) ) : ?> 
                                        <a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" class="item-card-link--sharing" target="_blank" title="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>">
                                            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/facebook-circle.png'; ?>" alt="<?php esc_attr_e('Share this on facebook', 'tainacan-interface') ?>">
                                        </a>
                                    <?php endif; ?>
                                    <?php if ( true == get_theme_mod( 'tainacan_twitter_share', true ) ) : ?> 
                                        <?php
                                        $twitter_option = get_theme_mod( 'tainacan_twitter_user' );
                                        $via = ! empty( $twitter_option ) ? '&amp;via=' . esc_attr( get_theme_mod( 'tainacan_twitter_user' ) ) : '';
                                        ?>
                                        <a href="http://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title_attribute(); ?><?php echo $via; ?>" target="_blank" class="item-card-link--sharing" title="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>">
                                            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/twitter-circle.png'; ?>" alt="<?php esc_attr_e('Share this on twitter', 'tainacan-interface') ?>">
                                        </a>
                                    <?php endif; ?>
                                    <?php if ( true == get_theme_mod( 'tainacan_whatsapp_share', false ) ) : ?>
                                        <a href="https://api.whatsapp.com/send?1=pt_BR&text=<?php the_permalink(); ?>" target="_blank" class="item-card-link--sharing" title="<?php esc_attr_e('Share this on WhatsApp', 'tainacan-interface') ?>">
                                            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/whatsapp-circle.png'; ?>" alt="<?php esc_attr_e('Share this on WhatsApp', 'tainacan-interface') ?>">
                                        </a>
                                    <?php endif; ?>
                                    <?php if ( true == get_theme_mod( 'tainacan_telegram_share', false ) ) : ?>
                                        <a href="https://t.me/share/url?url=<?php the_permalink(); ?>" target="_blank" class="item-card-link--sharing" title="<?php esc_attr_e('Share this on Telegram', 'tainacan-interface') ?>">
                                            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/telegram-circle.png'; ?>" alt="<?php esc_attr_e('Share this on Telegram', 'tainacan-interface') ?>">
                                        </a>
                                    <?php endif; ?>
                                    <button id="tainacan-interface-sharer" data-link="<?php the_permalink(); ?>" title="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>" class="item-card-link--sharing tainacan-copy-link-button">
                                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/url-circle.png'; ?>" alt="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>">
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php do_action( 'tainacan-interface-single-item-metadata-begin' ); ?>
                    <?php
                        $args = array(
                            'display_slug_as_class' => true,
                            'before_title' => '<div><h3>',
                            'after_title' => '</h3>',
                            'before_value' => '<p>',
                            'after_value' => '</p></div>',
                            'exclude_title' => get_theme_mod('tainacan_single_item_hide_core_title_metadata', false)
                        );
                        tainacan_the_metadata( $args );
                    ?>
                    <?php do_action( 'tainacan-interface-single-item-metadata-end' ); ?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="my-5 border-bottom border-silver"></div>