<?php 
    /** 
     * The new metadata sections function makes it a bit more complicated to add
     * the thumbnail and share buttons in the middle of the metadata.
     * So we have some logic that is only needed if they are set.
     * The following uses a filter to add them right above the first metadatum in the first section.
     **/
    if (
        ( has_post_thumbnail() && get_theme_mod( 'tainacan_single_item_display_thumbnail', true ) ) ||
        ( !get_theme_mod('tainacan_single_item_collection_header', false) && get_theme_mod( 'tainacan_single_item_display_share_buttons', true ) )
    ) {
        // Gets collection so we can obtain firtst metadata
        $collection = tainacan_get_collection();

        if ( !is_null($collection) ) {

            // Gets array of metadata order
            $metadata_order = $collection->get_metadata_order();

            if ( is_array($metadata_order) ) {

                $first_metadatum_id = -1;

                foreach( $metadata_order as $metadatum ) {

                    // Checks if the metadata is enabled
                    if ( isset($metadatum['enabled']) && $metadatum['enabled'] && isset($metadatum['id']) ) {
                        $first_metadatum_id = $metadatum['id'];

                        // IF we are not displaying the title here, we must look for the second metadata
                        if ( get_theme_mod('tainacan_single_item_hide_core_title_metadata', false) ) {

                            $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
                            $metadatum_object = $Tainacan_Metadata->fetch($first_metadatum_id);
                            $metadata_type_object = $metadatum_object->get_metadata_type_object();
                            
                            if ( $metadata_type_object->get_related_mapped_prop() == 'title' ) {
                                continue;
                            }
                        }

                        break;
                    }
                }

                if ( is_numeric($first_metadatum_id) && $first_metadatum_id >= 0 ) {

                    add_filter('tainacan-get-item-metadatum-as-html-before--id-' . $first_metadatum_id, function($before, $item_metadatum) {

                        ob_start();
                
                        if (has_post_thumbnail() && get_theme_mod( 'tainacan_single_item_display_thumbnail', true )): ?>
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
                                        <button onclick="copyTextToClipboard('<?php the_permalink(); ?>')" title="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>" class="item-card-link--sharing tainacan-copy-link-button">
                                            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/url-circle.png'; ?>" alt="<?php esc_attr_e('Copy link', 'tainacan-interface') ?>">
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif;
                
                        $extra_content = ob_get_contents();
                        ob_end_clean();
                
                        return $extra_content . $before;
                
                    }, 10, 2);
                }
            }
        }
    }

    $metadata_args = array(
        'display_slug_as_class' => true,
        'before_title' => '<div><h3>',
        'after_title' => '</h3>',
        'before_value' => '<p>',
        'after_value' => '</p></div>',
        'exclude_title' => get_theme_mod('tainacan_single_item_hide_core_title_metadata', false)
    );

    $section_layout = esc_attr( get_post_meta( tainacan_get_collection_id(), 'tainacan_interface_section_layout', 'default' ) );
    
    if ( $section_layout == 'tabs') {

        add_filter('tainacan-get-metadata-section-as-html-before-name--index-0', function($before, $item_metadatum) {
            return str_replace('<input', '<input checked="checked"', $before);
        }, 10, 2);

        $args = array(
            'before' => '',
            'after' => '',
            'before_name' => '<input name="tabs" type="radio" id="tab-section-$id" />
                        <label for="tab-section-$id">
                            <h2 class="title-content-items" id="metadata-section-$slug">',
            'after_name' => '</h2>
                        </label>',
            'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ). '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
            'after_metadata_list' => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
            'metadata_list_args' => $metadata_args,
            'hide_empty' => true
        );
        
        echo '<div class="single-item-collection--section-metadata--layout-tabs mt-3 tainacan-single-post">';
        tainacan_the_metadata_sections( $args );
        echo '</div><div class="my-5 border-bottom border-silver"></div>';

    } else  if ( $section_layout == 'collapses') {

        add_filter('tainacan-get-metadata-section-as-html-before-name--index-0', function($before, $item_metadatum) {
            return str_replace('<input', '<input checked="checked"', $before);
        }, 10, 2);

        $args = array(
            'before' => '',
            'after' => '',
            'before_name' => '<input name="collapses" type="checkbox" id="collapse-section-$id"/>
                        <label for="collapse-section-$id">
                            <i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>
                            <h2 class="title-content-items" id="metadata-section-$slug">',
            'after_name' => '</h2>
                        </label>',
            'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ). '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
            'after_metadata_list' => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
            'metadata_list_args' => $metadata_args,
            'hide_empty' => true
        );

        echo '<div class="single-item-collection--section-metadata--layout-collapses mt-3 tainacan-single-post">';
        tainacan_the_metadata_sections( $args );
        echo '</div><div class="my-5 border-bottom border-silver"></div>';

    } else if ( $section_layout == 'accordion') {

        add_filter('tainacan-get-metadata-section-as-html-before-name--index-0', function($before, $item_metadatum) {
            return str_replace('<input', '<input checked="checked"', $before);
        }, 10, 2);

        $args = array(
            'before' => '',
            'after' => '',
            'metadata_sections__not_in' => ['default_section'],
            'before_name' => '<input name="accordion" type="radio" id="accordion-section-$id"/>
                        <label for="accordion-section-$id">
                            <i class="tainacan-icon tainacan-icon-arrowright tainacan-icon-30px"></i>
                            <h2 class="title-content-items" id="metadata-section-$slug">',
            'after_name' => '</h2>
                        </label>',
            'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ). '
                <section class="single-item-collection tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
            'after_metadata_list' => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
            'metadata_list_args' => $metadata_args,
            'hide_empty' => true
        );

        echo '<div class="single-item-collection--section-metadata--layout-accordion mt-3 tainacan-single-post">';
        tainacan_the_metadata_sections( $args );
        echo '</div><div class="my-5 border-bottom border-silver"></div>';

    } else {
        $args = array(
            'before' => '<div class="mt-3 tainacan-single-post">',
            'after' => '</div><div class="my-5 border-bottom border-silver"></div>',
            'before_name' => '<h2 class="title-content-items" id="metadata-section-$slug">',
            'after_name' => '</h2>',
            'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ). '
                <section class="single-item-collection margin-two-column tainacan-metadata-section" aria-labelledby="metadata-section-$slug">
                    <div class="single-item-collection--information justify-content-center">
                        <div class="row">
                            <div class="col s-item-collection--metadata">',
            'after_metadata_list' => '
                            </div>
                        </div>
                    </div>
                </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
            'metadata_list_args' => $metadata_args,
            'hide_empty' => true
        );
        tainacan_the_metadata_sections( $args );
    }

    
?>