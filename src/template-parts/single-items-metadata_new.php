<?php 
    $metadata_args = array(
        'display_slug_as_class' => true,
        'before_title' => '<div><h3>',
        'after_title' => '</h3>',
        'before_value' => '<p>',
        'after_value' => '</p></div>',
        'exclude_title' => get_theme_mod('tainacan_single_item_hide_core_title_metadata', false)
    );
    $args = array(
        'before' => '<div class="mt-3 tainacan-single-post">',
        'after' => '</div><div class="my-5 border-bottom border-silver"></div>',
        'before_name' => '<h2 class="title-content-items">',
        'after_name' => '</h2>',
        'before_metadata_list' => do_action( 'tainacan-interface-single-item-metadata-begin' ). '
            <section class="single-item-collection margin-two-column">
                <div class="single-item-collection--information justify-content-center">
                    <div class="row">
                        <div class="col s-item-collection--metadata">',
        'after_metadata_list' => '
                        </div>
                    </div>
                </div>
            </section>' . do_action( 'tainacan-interface-single-item-metadata-end' ),
        'metadata_list_args' => $metadata_args
    );

    tainacan_the_metadata_sections( $args );
?>