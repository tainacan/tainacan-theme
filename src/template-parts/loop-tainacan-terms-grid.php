<?php if ( have_posts() ) : ?>

    <?php while ( have_posts() ) {
        the_post();
        $taxonomy_terms_list = tainacan_get_single_taxonomy_content($post, array(
            'before_terms_list_container' => '<div class="tainacan-list-post px-md-0 mt-5 tainacan-taxonomy-terms-list-container">',
            'before_terms_list' => '<ul class="tainacan-list-collection--container-grid justify-content-center tainacan-taxonomy-terms-list">',
            'after_terms_list' => '</ul>',
            'before_term' => '<li class="tainacan-term-single tainacan-list-collection--grid" id="term-id-$id">',
            'after_term' => '</li>',
            'before_term_thumbnail' => '<figure class="term-thumbnail tainacan-list-collection--grid-img rounded-0 align-self-center mr-3">',
            'after_term_thumbnail' => '</figure>',
            'before_term_name' => '<h2 class="term-name text-truncate">',
            'after_term_name' => '</h2>',
            'thumbnails_size' => 'tainacan-medium',
            'hide_term_thumbnail_placeholder' => false,
            'hide_term_empty_name' => false,
            'trim_description_words' => 20
        ));
        echo $taxonomy_terms_list['content'];

        $current_args = \Tainacan\Theme_Helper::get_instance()->get_taxonomies_query_args();
        $current_total_terms = $taxonomy_terms_list['total_terms'];
        $current_first_term_index = max(($current_args['termspaged'] - 1) * $current_args['perpage'] + 1, 1);
        $current_last_term_index = min($current_args['termspaged'] * $current_args['perpage'], $current_total_terms);

        // Terms pagination 
        tainacan_pagination_terms($current_total_terms);
    } ?>

<?php else : ?>
    <?php _e( 'Nothing found', 'tainacan-interface' ); ?>
<?php endif; ?>